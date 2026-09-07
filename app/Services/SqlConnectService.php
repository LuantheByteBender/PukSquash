<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use stdClass;

class SqlConnectService
{
    private function getAccessToken(): string
    {
        $credentialsPath = base_path(
            config('sqlconnect.credentials')
        );

        if (! file_exists($credentialsPath)) {
            throw new RuntimeException(
                'Firebase credentials file could not be found.'
            );
        }

        $key = json_decode(
            file_get_contents($credentialsPath),
            true
        );

        $credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/cloud-platform'],
            $key
        );

        $token = $credentials->fetchAuthToken();

        if (! isset($token['access_token'])) {
            throw new RuntimeException(
                'Unable to obtain Google OAuth access token.'
            );
        }

        return $token['access_token'];
    }

    private function connectorUrl(string $operation): string
    {
        return config('sqlconnect.base_url')
            . '/projects/' . config('sqlconnect.project_id')
            . '/locations/' . config('sqlconnect.location')
            . '/services/' . config('sqlconnect.service_id')
            . '/connectors/' . config('sqlconnect.connector_id')
            . ':' . $operation;
    }

    private function userExtensions(
        string $uid,
        bool $emailVerified
    ): array {
        return [
            'impersonate' => [
                'authClaims' => [
                    'sub' => $uid,
                    'email_verified' => $emailVerified,
                ],
            ],
        ];
    }

    /**
     * Execute a SQL Connect query as a Firebase user.
     */
    public function queryAsUser(
        string $operationName,
        string $uid,
        bool $emailVerified,
        array $variables = []
    ): array {
        $response = Http::withToken(
            $this->getAccessToken()
        )
            ->acceptJson()
            ->post(
                $this->connectorUrl('impersonateQuery'),
                [
                    'operationName' => $operationName,

                    'variables' => empty($variables)
                        ? new stdClass()
                        : $variables,

                    'extensions' => $this->userExtensions(
                        $uid,
                        $emailVerified
                    ),
                ]
            );

        $response->throw();

        return $response->json();
    }

    /**
     * Execute a SQL Connect mutation as a Firebase user.
     */
    public function mutationAsUser(
        string $operationName,
        string $uid,
        bool $emailVerified,
        array $variables = []
    ): array {
        $response = Http::withToken(
            $this->getAccessToken()
        )
            ->acceptJson()
            ->post(
                $this->connectorUrl('impersonateMutation'),
                [
                    'operationName' => $operationName,

                    'variables' => empty($variables)
                        ? new stdClass()
                        : $variables,

                    'extensions' => $this->userExtensions(
                        $uid,
                        $emailVerified
                    ),
                ]
            );

        $response->throw();

        return $response->json();
    }

    /**
     * Privileged query.
     *
     * Keep this for server/admin tasks only.
     */
    public function query(
        string $operationName,
        array $variables = []
    ): array {
        $response = Http::withToken(
            $this->getAccessToken()
        )
            ->acceptJson()
            ->post(
                $this->connectorUrl('impersonateQuery'),
                [
                    'operationName' => $operationName,

                    'variables' => empty($variables)
                        ? new stdClass()
                        : $variables,
                ]
            );

        $response->throw();

        return $response->json();
    }

    /**
     * Privileged mutation.
     *
     * Keep this for server/admin tasks only.
     */
    public function mutation(
        string $operationName,
        array $variables = []
    ): array {
        $response = Http::withToken(
            $this->getAccessToken()
        )
            ->acceptJson()
            ->post(
                $this->connectorUrl('impersonateMutation'),
                [
                    'operationName' => $operationName,

                    'variables' => empty($variables)
                        ? new stdClass()
                        : $variables,
                ]
            );

        $response->throw();

        return $response->json();
    }
}