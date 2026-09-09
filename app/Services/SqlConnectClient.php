<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Central HTTP client for the Firebase SQL Connect GraphQL endpoint.
 *
 * Every repository calls this instead of talking to PostgreSQL directly.
 * Laravel handles business logic, validation and authorization; SQL Connect
 * owns the database.
 *
 * Authentication uses a Google OAuth2 access token obtained from the service-
 * account credentials file (the same one used for Firebase Auth verification).
 */
class SqlConnectClient
{
    private string $endpoint;
    private string $credentialsPath;
    private ?string $emulatorHost;

    /** @var array<string, string> */
    private static array $operationCache = [];
    private ?string $cachedToken = null;
    private ?int $tokenExpiresAt = null;

    public function __construct()
    {
        $projectId   = config('services.firebase.project_id');
        $location    = config('services.firebase.dataconnect_location', 'southamerica-west1');
        $serviceId   = config('services.firebase.dataconnect_service', 'puksquash');

        $this->emulatorHost = config('services.firebase.dataconnect_emulator_host');

        if (! $projectId) {
            throw new RuntimeException(
                'Missing FIREBASE_PROJECT_ID. Set FIREBASE_PROJECT_ID in your environment (see .env.example).'
            );
        }

        $baseUrl = $this->emulatorHost
            ? rtrim('http://' . $this->emulatorHost, '/')
            : 'https://firebasedataconnect.googleapis.com';

        $this->endpoint = sprintf(
            '%s/v1/projects/%s/locations/%s/services/%s:executeGraphql',
            $baseUrl,
            $projectId,
            $location,
            $serviceId,
        );

        $this->credentialsPath = base_path(config('services.firebase.credentials'));
    }

    /**
     * Execute a named query against SQL Connect.
     *
     * @param  string     $operationName  The query/mutation name defined in the .gql connector files.
     * @param  array      $variables      GraphQL variables.
     * @return array                      The 'data' portion of the GraphQL response.
     *
     * @throws RuntimeException           On network, auth or GraphQL errors.
     */
    public function execute(string $operationName, array $variables = []): array
    {
        $query = $this->loadOperation($operationName);

        $request = Http::timeout(15);

        if (! $this->emulatorHost) {
            $request = $request->withToken($this->getAccessToken());
        }

        $response = $request->post($this->endpoint, [
            'query'         => $query,
            'operationName' => $operationName,
            'variables'     => (object) $variables,
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                "SQL Connect request [{$operationName}] failed: " . $response->body(),
                $response->status(),
            );
        }

        $body = $response->json();

        if (! empty($body['errors'])) {
            $messages = collect($body['errors'])->pluck('message')->implode('; ');
            throw new RuntimeException(
                "SQL Connect GraphQL error [{$operationName}]: {$messages}",
            );
        }

        return $body['data'] ?? [];
    }

    private function loadOperation(string $operationName): string
    {
        if (isset(self::$operationCache[$operationName])) {
            return self::$operationCache[$operationName];
        }

        $candidates = [
            base_path('dataconnect/example/queries.gql'),
            base_path('dataconnect/example/mutations.gql'),
        ];

        foreach ($candidates as $file) {
            if (! is_file($file)) {
                continue;
            }

            $contents = file_get_contents($file);

            if ($contents === false) {
                continue;
            }

            $operation = $this->extractOperation($contents, $operationName);

            if ($operation !== null) {
                self::$operationCache[$operationName] = $operation;
                return $operation;
            }
        }

        throw new RuntimeException("SQL Connect operation [{$operationName}] was not found in the connector .gql files.");
    }

    private function extractOperation(string $source, string $operationName): ?string
    {
        $pattern = '/\b(query|mutation)\s+' . preg_quote($operationName, '/') . '\b/';

        if (! preg_match($pattern, $source, $m, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $start = $m[0][1];
        $len = strlen($source);

        $bracePos = strpos($source, '{', $start);
        if ($bracePos === false) {
            return null;
        }

        $depth = 0;
        for ($i = $bracePos; $i < $len; $i++) {
            $ch = $source[$i];

            if ($ch === '{') {
                $depth++;
            } elseif ($ch === '}') {
                $depth--;

                if ($depth === 0) {
                    $end = $i + 1;
                    return trim(substr($source, $start, $end - $start));
                }
            }
        }

        return null;
    }

    /**
     * Obtain a Google OAuth2 access token from the service-account JSON key.
     *
     * Tokens are cached in memory for the lifetime of the request (or worker
     * process) and refreshed once they are within 60 s of expiry.
     */
    private function getAccessToken(): string
    {
        if ($this->cachedToken && $this->tokenExpiresAt > time() + 60) {
            return $this->cachedToken;
        }

        if (! $this->credentialsPath || ! is_file($this->credentialsPath)) {
            throw new RuntimeException(
                'Missing FIREBASE_CREDENTIALS or credentials file not found. Set FIREBASE_CREDENTIALS to the service-account JSON key path (see .env.example).'
            );
        }

        $credentials = json_decode(file_get_contents($this->credentialsPath), true);

        $now = time();
        $header  = base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64url_encode(json_encode([
            'iss'   => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]));

        openssl_sign(
            "{$header}.{$payload}",
            $signature,
            $credentials['private_key'],
            OPENSSL_ALGO_SHA256,
        );

        $jwt = "{$header}.{$payload}." . base64url_encode($signature);

        $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]);

        if ($tokenResponse->failed()) {
            throw new RuntimeException(
                'Failed to obtain Google access token: ' . $tokenResponse->body(),
            );
        }

        $tokenData = $tokenResponse->json();

        $this->cachedToken    = $tokenData['access_token'];
        $this->tokenExpiresAt = $now + ($tokenData['expires_in'] ?? 3600);

        return $this->cachedToken;
    }
}

/**
 * URL-safe Base64 encoding (no padding).
 */
if (! function_exists('base64url_encode')) {
    function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
