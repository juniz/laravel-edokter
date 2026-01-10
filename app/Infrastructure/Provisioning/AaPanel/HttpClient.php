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
     * Format resmi aaPanel: md5(timestamp_ms + md5(api_key))
     */
    private function generateRequestToken(int $requestTimeMs): string
    {
        return md5((string) $requestTimeMs.md5($this->apiKey));
    }

    /**
     * Make POST request ke aaPanel API
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function post(string $action, array $params = []): array
    {
        // Gunakan timestamp millisecond seperti di modul WHMCS resmi
        $requestTimeMs = (int) (microtime(true) * 1000);
        $requestToken = $this->generateRequestToken($requestTimeMs);

        $url = $this->endpoint.'/'.$action;

        // Konversi semua parameter ke string seperti modul resmi
        $stringParams = [];
        foreach ($params as $key => $value) {
            if (is_bool($value)) {
                $stringParams[$key] = $value ? '1' : '0';
            } elseif (is_array($value)) {
                // Handle nested array parameters
                foreach ($value as $nestedKey => $nestedValue) {
                    $stringParams[$key.'['.$nestedKey.']'] = (string) $nestedValue;
                }
            } else {
                $stringParams[$key] = (string) $value;
            }
        }

        $data = array_merge([
            'request_time' => (string) $requestTimeMs,
            'request_token' => $requestToken,
        ], $stringParams);

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
     * Test connection ke aaPanel API dengan cek virtual service
     *
     * @return array<string, mixed>
     */
    public function testConnection(): array
    {
        try {
            // Test koneksi basic dulu
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
     * Cek apakah virtual/multi-user service terinstall dan running
     *
     * @return array{installed: bool, running: bool, message: string}
     */
    public function checkVirtualServiceStatus(): array
    {
        try {
            $response = $this->post('v2/virtual/get_service_info.json');

            $installStatus = $response['message']['install_status'] ?? 0;
            $runStatus = $response['message']['run_status'] ?? 0;

            return [
                'installed' => (int) $installStatus === 2,
                'running' => (int) $runStatus === 1,
                'message' => $this->getVirtualServiceStatusMessage($installStatus, $runStatus),
            ];
        } catch (\Exception $e) {
            return [
                'installed' => false,
                'running' => false,
                'message' => 'Failed to check virtual service: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get status message untuk virtual service
     */
    private function getVirtualServiceStatusMessage(int $installStatus, int $runStatus): string
    {
        if ($installStatus !== 2) {
            return 'Multi-user service is not installed';
        }

        if ($runStatus !== 1) {
            return 'Multi-user service is not running';
        }

        return 'Multi-user service is installed and running';
    }

    /**
     * Sanitize params untuk logging (hide sensitive data)
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function sanitizeParams(array $params): array
    {
        $sensitiveKeys = ['request_token', 'api_sk', 'password', 'datapassword', 'ftp_password', 'db_password'];
        $sanitized = $params;

        foreach ($sensitiveKeys as $key) {
            if (isset($sanitized[$key])) {
                $sanitized[$key] = '***HIDDEN***';
            }
        }

        return $sanitized;
    }
}
