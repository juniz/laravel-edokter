<?php

namespace App\Application\Provisioning;

use App\Infrastructure\Provisioning\AaPanel\HttpClient;
use App\Infrastructure\Provisioning\AdapterResolver;
use App\Models\Domain\Provisioning\Server;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class TestServerConnectionService
{
    public function __construct(
        private AdapterResolver $adapterResolver
    ) {}

    /**
     * Test connection ke server berdasarkan type
     *
     * @return array{success: bool, message: string, data?: array, error?: string}
     */
    public function execute(Server $server): array
    {
        try {
            // Test connection berdasarkan server type
            return match ($server->type) {
                'aapanel' => $this->testAaPanelConnection($server),
                'cpanel' => $this->testCpanelConnection($server),
                'directadmin' => $this->testDirectAdminConnection($server),
                'proxmox' => $this->testProxmoxConnection($server),
                default => [
                    'success' => false,
                    'message' => 'Tipe server tidak didukung untuk test connection',
                ],
            };
        } catch (\Exception $e) {
            Log::error('Test server connection failed', [
                'server_id' => $server->id,
                'server_type' => $server->type,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal test koneksi: '.$e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test connection ke aaPanel
     */
    private function testAaPanelConnection(Server $server): array
    {
        try {
            $endpoint = $server->endpoint;
            $apiKey = Crypt::decryptString($server->auth_secret_ref);
            // Ambil verify_ssl dari meta, default true untuk security
            $verifySsl = $server->meta['verify_ssl'] ?? true;

            $client = new HttpClient($endpoint, $apiKey, $verifySsl);
            $result = $client->testConnection();

            return $result;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal test koneksi aaPanel: '.$e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test connection ke cPanel (placeholder)
     */
    private function testCpanelConnection(Server $server): array
    {
        // TODO: Implementasi test connection untuk cPanel
        return [
            'success' => false,
            'message' => 'Test connection untuk cPanel belum diimplementasikan',
        ];
    }

    /**
     * Test connection ke DirectAdmin (placeholder)
     */
    private function testDirectAdminConnection(Server $server): array
    {
        // TODO: Implementasi test connection untuk DirectAdmin
        return [
            'success' => false,
            'message' => 'Test connection untuk DirectAdmin belum diimplementasikan',
        ];
    }

    /**
     * Test connection ke Proxmox (placeholder)
     */
    private function testProxmoxConnection(Server $server): array
    {
        // TODO: Implementasi test connection untuk Proxmox
        return [
            'success' => false,
            'message' => 'Test connection untuk Proxmox belum diimplementasikan',
        ];
    }
}
