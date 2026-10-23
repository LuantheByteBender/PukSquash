<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PayfastService
{
    public function amountStringToCents(string $amount): int
    {
        $amount = trim($amount);
        if ($amount === '') {
            return 0;
        }

        if (! preg_match('/^(-?\d+)(?:\.(\d{1,2}))?$/', $amount, $m)) {
            return 0;
        }

        $whole = (int) $m[1];
        $dec = $m[2] ?? '0';
        $dec = str_pad($dec, 2, '0');

        return ($whole * 100) + (int) $dec;
    }

    public function isValidSourceIp(string $ip): bool
    {
        if (! (bool) config('payfast.validate_ip', false)) {
            return true;
        }

        $hosts = [
            'sandbox.payfast.co.za',
            'www.payfast.co.za',
        ];

        $ips = [];
        foreach ($hosts as $host) {
            $resolved = gethostbynamel($host);
            if (is_array($resolved)) {
                foreach ($resolved as $addr) {
                    $ips[$addr] = true;
                }
            }
        }

        return isset($ips[$ip]);
    }

    public function processUrl(): string
    {
        return (string) config('payfast.process_url');
    }

    public function validateItn(array $data): bool
    {
        $url = (bool) config('payfast.sandbox', true)
            ? 'https://sandbox.payfast.co.za/eng/query/validate'
            : 'https://www.payfast.co.za/eng/query/validate';

        $resp = Http::asForm()
            ->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])
            ->post($url, $data);

        if (! $resp->ok()) {
            return false;
        }

        return trim((string) $resp->body()) === 'VALID';
    }

    public function signature(array $fields): string
    {
        unset($fields['signature']);

        $passphrase = trim((string) config('payfast.passphrase'));

        $output = '';
        foreach ($fields as $key => $value) {
            if ($value === null) {
                continue;
            }

            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }

            $output .= $key . '=' . urlencode($value) . '&';
        }

        if ($passphrase === '') {
            $output = rtrim($output, '&');
        } else {
            $output .= 'passphrase=' . urlencode($passphrase);
        }

        return md5($output);
    }

    protected function itnSignature(array $fields): string
    {
        unset($fields['signature']);

        $passphrase = trim((string) config('payfast.passphrase'));

        $filtered = [];
        foreach ($fields as $key => $value) {
            if ($value === null) {
                continue;
            }

            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }

            $filtered[$key] = $value;
        }

        ksort($filtered);

        $output = '';
        foreach ($filtered as $key => $value) {
            $output .= $key . '=' . urlencode($value) . '&';
        }

        $output = rtrim($output, '&');

        if ($passphrase !== '') {
            $output .= '&passphrase=' . urlencode($passphrase);
        }

        return md5($output);
    }

    public function signatureMatches(array $data): bool
    {
        $incoming = (string) ($data['signature'] ?? '');
        if ($incoming === '') {
            return false;
        }

        return hash_equals($incoming, $this->itnSignature($data));
    }
}
