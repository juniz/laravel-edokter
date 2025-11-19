<?php

namespace App\Infrastructure\Rdash;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HttpClient
{
    private PendingRequest $client;
    private string $baseUrl;
    private string $resellerId;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('rdash.api_url', 'https://api.rdash.id/v1');
        $this->resellerId = config('rdash.reseller_id');
        $this->apiKey = config('rdash.api_key');

        $this->client = Http::baseUrl($this->baseUrl)
            ->withBasicAuth($this->resellerId, $this->apiKey)
            ->timeout(30)
            ->retry(3, 100);
    }

    /**
     * Make GET request
     *
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    public function get(string $endpoint, array $query = []): array
    {
        try {
            $response = $this->client->get($endpoint, $query);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            $this->handleError($response, 'GET', $endpoint);

            return [];
        } catch (\Exception $e) {
            Log::error('RDASH API GET Error', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException("RDASH API request failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Make POST request
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function post(string $endpoint, array $data = []): array
    {
        try {
            $response = $this->client->asForm()->post($endpoint, $data);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            $this->handleError($response, 'POST', $endpoint);

            return [];
        } catch (\Exception $e) {
            Log::error('RDASH API POST Error', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException("RDASH API request failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Make PUT request
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function put(string $endpoint, array $data = []): array
    {
        try {
            $response = $this->client->asForm()->put($endpoint, $data);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            $this->handleError($response, 'PUT', $endpoint);

            return [];
        } catch (\Exception $e) {
            Log::error('RDASH API PUT Error', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException("RDASH API request failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Make DELETE request
     *
     * @return array<string, mixed>
     */
    public function delete(string $endpoint): array
    {
        try {
            $response = $this->client->delete($endpoint);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            $this->handleError($response, 'DELETE', $endpoint);

            return [];
        } catch (\Exception $e) {
            Log::error('RDASH API DELETE Error', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException("RDASH API request failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Handle API error response
     */
    private function handleError($response, string $method, string $endpoint): void
    {
        $status = $response->status();
        $body = $response->json() ?? $response->body();
        $bodyString = is_string($body) ? $body : json_encode($body);

        Log::warning('RDASH API Error Response', [
            'method' => $method,
            'endpoint' => $endpoint,
            'status' => $status,
            'body' => $body,
        ]);

        // Format error message yang lebih informatif
        $errorMessage = "HTTP request returned status code {$status}: {$bodyString}";
        
        throw new \RuntimeException($errorMessage, $status);
    }
}

