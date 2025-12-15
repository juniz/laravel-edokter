<?php

namespace App\Infrastructure\Provisioning\AaPanel;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HttpClient
{
    private string $endpoint;

    private string $apiKey;

    private bool $verifySsl;

    public function __construct(string $endpoint, string $apiKey, bool $verifySsl = true)
    {
        $this->endpoint = rtrim($endpoint, '/');
        $this->apiKey = $apiKey;
        $this->verifySsl = $verifySsl;
    }

    /**
     * Generate request token untuk signature algorithm
     * request_token = md5(string(request_time) + md5(api_sk))
     */
    private function generateRequestToken(int $requestTime): string
    {
        return md5((string) $requestTime.md5($this->apiKey));
    }

    /**
     * Make POST request ke aaPanel API
     */
    public function post(string $action, array $params = []): array
    {
        $requestTime = time();
        $requestToken = $this->generateRequestToken($requestTime);

        $url = $this->endpoint.'/'.$action;

        $data = array_merge([
            'request_time' => $requestTime,
            'request_token' => $requestToken,
        ], $params);

        Log::info('aaPanel API Request', [
            'url' => $url,
            'action' => $action,
            'params' => $this->sanitizeParams($data),
        ]);

        try {
            $httpClient = Http::timeout(30)
                ->asForm();

            // Skip SSL verification jika diminta
            if (! $this->verifySsl) {
                $httpClient = $httpClient->withoutVerifying();
            }

            $response = $httpClient->post($url, $data);

            $responseData = $response->json();

            Log::info('aaPanel API Response', [
                'url' => $url,
                'action' => $action,
                'status' => $response->status(),
                'response' => $responseData,
            ]);

            if (! $response->successful()) {
                throw new \Exception('aaPanel API request failed: '.$response->body());
            }

            return $responseData ?? [];
        } catch (\Exception $e) {
            Log::error('aaPanel API Error', [
                'url' => $url,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Test connection ke aaPanel API
     * Menggunakan endpoint GetSystemTotal untuk test koneksi
     */
    public function testConnection(): array
    {
        try {
            $response = $this->post('system?action=GetSystemTotal');

            return [
                'success' => true,
                'message' => 'Koneksi berhasil',
                'data' => $response,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Koneksi gagal: '.$e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Sanitize params untuk logging (hide sensitive data)
     */
    private function sanitizeParams(array $params): array
    {
        $sensitiveKeys = ['request_token', 'api_sk', 'password', 'datapassword', 'ftp_password'];
        $sanitized = $params;

        foreach ($sensitiveKeys as $key) {
            if (isset($sanitized[$key])) {
                $sanitized[$key] = '***HIDDEN***';
            }
        }

        return $sanitized;
    }
}
