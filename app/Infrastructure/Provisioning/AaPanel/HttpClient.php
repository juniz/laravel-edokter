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
        // Normalize endpoint: hapus trailing slash dan path tambahan
        // Endpoint harus hanya berupa base URL (scheme://host:port) tanpa path
        // Contoh: https://192.168.100.7:12636 (bukan https://192.168.100.7:12636/abah1)
        $parsed = parse_url($endpoint);

        if (! $parsed || ! isset($parsed['scheme']) || ! isset($parsed['host'])) {
            throw new \InvalidArgumentException('Invalid endpoint URL format: ' . $endpoint);
        }

        // Rebuild endpoint hanya dengan scheme, host, dan port (tanpa path)
        $this->endpoint = $parsed['scheme'] . '://' . $parsed['host'];
        if (isset($parsed['port'])) {
            $this->endpoint .= ':' . $parsed['port'];
        }

        $this->apiKey = $apiKey;
        $this->verifySsl = $verifySsl;
    }

    /**
     * Generate request token untuk signature algorithm
     * Format sesuai demo.php resmi: md5(timestamp.''.md5(api_key))
     * Menggunakan timestamp dalam detik (time()), bukan millisecond
     */
    private function generateRequestToken(int $requestTime): string
    {
        // Format sesuai demo.php: md5($now_time.''.md5($this->BT_KEY))
        return md5($requestTime . '' . md5($this->apiKey));
    }

    /**
     * Make POST request ke aaPanel API
     * Format sesuai demo.php resmi: menggunakan time() (detik) bukan millisecond
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function post(string $action, array $params = []): array
    {
        // Gunakan timestamp dalam detik seperti di demo.php resmi
        $requestTime = time();
        $requestToken = $this->generateRequestToken($requestTime);

        $url = $this->endpoint . '/' . $action;

        // Konversi semua parameter ke string seperti modul resmi
        $stringParams = [];
        foreach ($params as $key => $value) {
            if (is_bool($value)) {
                $stringParams[$key] = $value ? '1' : '0';
            } elseif (is_array($value)) {
                // Handle nested array parameters
                foreach ($value as $nestedKey => $nestedValue) {
                    $stringParams[$key . '[' . $nestedKey . ']'] = (string) $nestedValue;
                }
            } else {
                $stringParams[$key] = (string) $value;
            }
        }

        $data = array_merge([
            'request_time' => $requestTime,
            'request_token' => $requestToken,
        ], $stringParams);

        Log::info('aaPanel API Request', [
            'url' => $url,
            'action' => $action,
            'params' => $this->sanitizeParams($data),
        ]);

        try {
            // Laravel Http facade otomatis handle cookie untuk session management
            // Tidak perlu set cookies secara eksplisit karena sudah di-handle otomatis
            $httpClient = Http::timeout(30)
                ->asForm();

            // Skip SSL verification jika diminta (sesuai demo.php)
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

            // Check HTTP status
            if (! $response->successful()) {
                throw new \Exception('aaPanel API request failed: ' . $response->body());
            }

            // Check response body untuk error dari aaPanel
            // aaPanel API mengembalikan HTTP 200 meskipun ada error di response body
            // Format: {"status": 0} = success, {"status": false} atau {"status": -1} = error
            if (isset($responseData['status'])) {
                $statusValue = $responseData['status'];

                // Handle status: false (boolean) - biasanya untuk IP validation failed
                if ($statusValue === false) {
                    $errorMsg = $responseData['msg'] ?? $responseData['error_msg'] ?? 'Unknown error from aaPanel';

                    Log::error('aaPanel API returned status false', [
                        'url' => $url,
                        'action' => $action,
                        'error_msg' => $errorMsg,
                        'response' => $responseData,
                    ]);

                    throw new \Exception("aaPanel API error: {$errorMsg}");
                }

                // Handle status: -1 atau status integer lainnya yang bukan 0
                if (is_numeric($statusValue) && (int) $statusValue !== 0) {
                    $errorMsg = $responseData['msg'] ?? $responseData['error_msg'] ?? 'Unknown error from aaPanel';
                    $errorCode = $responseData['code'] ?? $statusValue ?? 'unknown';

                    Log::error('aaPanel API returned error in response body', [
                        'url' => $url,
                        'action' => $action,
                        'error_code' => $errorCode,
                        'error_msg' => $errorMsg,
                        'response' => $responseData,
                    ]);

                    throw new \Exception("aaPanel API error (code: {$errorCode}): {$errorMsg}");
                }
            }

            // Check untuk error code 500 atau lainnya
            if (isset($responseData['code']) && (int) $responseData['code'] !== 0 && (int) $responseData['code'] !== 200) {
                $errorMsg = $responseData['msg'] ?? $responseData['error_msg'] ?? 'Unknown error from aaPanel';
                $errorCode = $responseData['code'];

                Log::error('aaPanel API returned error code', [
                    'url' => $url,
                    'action' => $action,
                    'error_code' => $errorCode,
                    'error_msg' => $errorMsg,
                    'response' => $responseData,
                ]);

                throw new \Exception("aaPanel API error (code: {$errorCode}): {$errorMsg}");
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
                'message' => 'Koneksi gagal: ' . $e->getMessage(),
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
                'message' => 'Failed to check virtual service: ' . $e->getMessage(),
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
