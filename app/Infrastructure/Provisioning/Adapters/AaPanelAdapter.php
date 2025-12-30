<?php

namespace App\Infrastructure\Provisioning\Adapters;

use App\Domain\Provisioning\Contracts\ProvisioningAdapterInterface;
use App\Infrastructure\Provisioning\AaPanel\HttpClient;
use App\Models\Domain\Provisioning\PanelAccount;
use App\Models\Domain\Provisioning\Server;
use App\Models\Domain\Subscription\Subscription;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AaPanelAdapter implements ProvisioningAdapterInterface
{
    private ?HttpClient $client = null;

    private ?Server $defaultServer = null;

    public function __construct()
    {
        // Lazy initialization - server akan di-set saat createAccount dipanggil
    }

    /**
     * Initialize client dengan server
     */
    private function initializeClient(Server $server): void
    {
        $endpoint = $server->endpoint;
        $apiKey = Crypt::decryptString($server->auth_secret_ref);
        // Ambil verify_ssl dari meta, default true untuk security
        $verifySsl = $server->meta['verify_ssl'] ?? true;
        $this->client = new HttpClient($endpoint, $apiKey, $verifySsl);
    }

    /**
     * Get server untuk provisioning
     */
    private function getServer(array $params): Server
    {
        // Gunakan server dari params jika ada
        if (isset($params['server']) && $params['server'] instanceof Server) {
            return $params['server'];
        }

        // Fallback ke default server
        if (! $this->defaultServer) {
            $this->defaultServer = Server::where('type', 'aapanel')
                ->where('status', 'active')
                ->first();

            if (! $this->defaultServer) {
                throw new \Exception('No active aaPanel server found');
            }
        }

        return $this->defaultServer;
    }

    public function createAccount(Subscription $sub, array $params): PanelAccount
    {
        $server = $this->getServer($params);

        // Initialize client jika belum atau server berbeda
        if (! $this->client || $this->defaultServer?->id !== $server->id) {
            $this->initializeClient($server);
        }

        // Get domain dari params atau subscription meta
        $domain = $params['domain'] ?? $sub->meta['domain'] ?? null;

        if (! $domain) {
            throw new \Exception('Domain is required for shared hosting');
        }

        // Generate username dari domain (remove dots, max 16 chars untuk aaPanel)
        $username = $this->generateUsername($domain);
        $password = Str::random(16);
        $ftpPassword = Str::random(16);
        $dbPassword = Str::random(16);

        // Prepare webname (JSON format)
        $webname = json_encode([
            'domain' => $domain,
            'domainlist' => [],
            'count' => 0,
        ]);

        // Get PHP version dari plan metadata atau default
        $phpVersion = $sub->plan->metadata['php_version'] ?? '82'; // Default PHP 8.2
        $port = $sub->plan->metadata['port'] ?? 80;
        $path = $sub->plan->metadata['path'] ?? '/www/wwwroot/' . $domain;
        $typeId = $sub->plan->metadata['type_id'] ?? 0; // Default classification

        // Prepare data untuk AddSite
        $siteData = [
            'webname' => $webname,
            'path' => $path,
            'type_id' => $typeId,
            'type' => 'PHP',
            'version' => $phpVersion,
            'port' => $port,
            'ps' => $sub->plan->name ?? 'Shared Hosting',
            'ftp' => true,
            'ftp_username' => $username,
            'ftp_password' => $ftpPassword,
            'sql' => true,
            'codeing' => 'utf8mb4',
            'datauser' => $username,
            'datapassword' => $dbPassword,
        ];

        // Create website di aaPanel
        $response = $this->client->post('site?action=AddSite', $siteData);

        if (! isset($response['siteStatus']) || ! $response['siteStatus']) {
            $errorMsg = $response['msg'] ?? 'Failed to create website in aaPanel';
            Log::error('aaPanel create website failed', [
                'subscription_id' => $sub->id,
                'domain' => $domain,
                'response' => $response,
            ]);
            throw new \Exception($errorMsg);
        }

        // Create panel account record
        $panelAccount = PanelAccount::create([
            'server_id' => $server->id,
            'subscription_id' => $sub->id,
            'username' => $username,
            'domain' => $domain,
            'status' => 'active',
            'meta' => [
                'ftp_username' => $response['ftpUser'] ?? $username,
                'ftp_password' => Crypt::encryptString($response['ftpPass'] ?? $ftpPassword),
                'database_user' => $response['databaseUser'] ?? $username,
                'database_password' => Crypt::encryptString($response['databasePass'] ?? $dbPassword),
                'website_path' => $path,
                'php_version' => $phpVersion,
                'port' => $port,
            ],
        ]);

        Log::info('aaPanel website created successfully', [
            'subscription_id' => $sub->id,
            'domain' => $domain,
            'username' => $username,
            'panel_account_id' => $panelAccount->id,
        ]);

        return $panelAccount;
    }

    public function suspendAccount(PanelAccount $acc): void
    {
        $response = $this->client->post('site?action=SiteStop', [
            'id' => $acc->meta['site_id'] ?? null,
            'name' => $acc->domain,
        ]);

        if (! isset($response['status']) || ! $response['status']) {
            throw new \Exception('Failed to suspend website: ' . ($response['msg'] ?? 'Unknown error'));
        }

        $acc->update(['status' => 'suspended']);
        Log::info('aaPanel website suspended', ['panel_account_id' => $acc->id]);
    }

    public function unsuspendAccount(PanelAccount $acc): void
    {
        $response = $this->client->post('site?action=SiteStart', [
            'id' => $acc->meta['site_id'] ?? null,
            'name' => $acc->domain,
        ]);

        if (! isset($response['status']) || ! $response['status']) {
            throw new \Exception('Failed to unsuspend website: ' . ($response['msg'] ?? 'Unknown error'));
        }

        $acc->update(['status' => 'active']);
        Log::info('aaPanel website unsuspended', ['panel_account_id' => $acc->id]);
    }

    public function terminateAccount(PanelAccount $acc): void
    {
        // Get site ID dari meta atau cari via API
        $siteId = $acc->meta['site_id'] ?? null;

        if (! $siteId) {
            // Try to find site ID via API
            $sites = $this->client->post('data?action=getData&table=sites', [
                'search' => $acc->domain,
                'limit' => 1,
            ]);

            if (isset($sites['data'][0]['id'])) {
                $siteId = $sites['data'][0]['id'];
            }
        }

        if ($siteId) {
            $response = $this->client->post('site?action=DeleteSite', [
                'id' => $siteId,
                'webname' => $acc->domain,
                'ftp' => 1, // Delete FTP
                'database' => 1, // Delete database
                'path' => 1, // Delete website directory
            ]);

            if (! isset($response['status']) || ! $response['status']) {
                Log::warning('aaPanel delete website failed', [
                    'panel_account_id' => $acc->id,
                    'response' => $response,
                ]);
            }
        }

        $acc->update(['status' => 'terminated']);
        Log::info('aaPanel website terminated', ['panel_account_id' => $acc->id]);
    }

    public function changePlan(PanelAccount $acc, string $planCode): void
    {
        // aaPanel tidak memiliki built-in change plan API
        // Implementasi bisa dilakukan dengan update PHP version atau limits
        // Untuk sekarang, hanya log perubahan
        Log::info('aaPanel change plan requested', [
            'panel_account_id' => $acc->id,
            'new_plan_code' => $planCode,
        ]);

        // TODO: Implementasi change plan jika diperlukan
    }

    /**
     * Generate username dari domain
     * Format: domain tanpa extension, max 16 chars, lowercase, alphanumeric + underscore
     */
    private function generateUsername(string $domain): string
    {
        // Remove http://, https://, www.
        $domain = preg_replace('/^https?:\/\//', '', $domain);
        $domain = preg_replace('/^www\./', '', $domain);

        // Extract domain name tanpa extension
        $parts = explode('.', $domain);
        $name = $parts[0];

        // Clean: hanya alphanumeric dan underscore
        $username = preg_replace('/[^a-z0-9_]/', '', strtolower($name));

        // Max 16 chars untuk aaPanel
        $username = substr($username, 0, 16);

        // Ensure tidak kosong
        if (empty($username)) {
            $username = 'user_' . substr(md5($domain), 0, 8);
        }

        return $username;
    }

    /**
     * Create account secara manual tanpa subscription
     *
     * @param  array<string, mixed>  $data
     */
    public function createManualAccount(Server $server, array $data): PanelAccount
    {
        // Initialize client dengan server
        if (! $this->client || $this->defaultServer?->id !== $server->id) {
            $this->initializeClient($server);
        }

        $domain = $data['domain'];
        $username = $data['username'] ?? $this->generateUsername($domain);
        $password = $data['password'] ?? Str::random(16);
        $ftpPassword = $data['ftp_password'] ?? Str::random(16);
        $dbPassword = $data['db_password'] ?? Str::random(16);

        // Prepare webname (JSON format)
        $webname = json_encode([
            'domain' => $domain,
            'domainlist' => [],
            'count' => 0,
        ]);

        // Get PHP version atau default
        $phpVersion = $data['php_version'] ?? '82'; // Default PHP 8.2
        $port = $data['port'] ?? 80;
        $path = $data['path'] ?? '/www/wwwroot/' . $domain;
        $typeId = $data['type_id'] ?? 0; // Default classification

        // Prepare data untuk AddSite
        $siteData = [
            'webname' => $webname,
            'path' => $path,
            'type_id' => $typeId,
            'type' => 'PHP',
            'version' => $phpVersion,
            'port' => $port,
            'ps' => $data['description'] ?? 'Manual Account',
            'ftp' => true,
            'ftp_username' => $username,
            'ftp_password' => $ftpPassword,
            'sql' => true,
            'codeing' => 'utf8mb4',
            'datauser' => $username,
            'datapassword' => $dbPassword,
        ];

        // Create website di aaPanel
        $response = $this->client->post('site?action=AddSite', $siteData);

        if (! isset($response['siteStatus']) || ! $response['siteStatus']) {
            $errorMsg = $response['msg'] ?? 'Failed to create website in aaPanel';
            Log::error('aaPanel create website failed (manual)', [
                'server_id' => $server->id,
                'domain' => $domain,
                'response' => $response,
            ]);
            throw new \Exception($errorMsg);
        }

        // Create panel account record
        $panelAccount = PanelAccount::create([
            'server_id' => $server->id,
            'subscription_id' => null, // Manual account tidak punya subscription
            'username' => $username,
            'domain' => $domain,
            'status' => 'active',
            'meta' => [
                'site_id' => $response['siteId'] ?? null,
                'ftp_username' => $response['ftpUser'] ?? $username,
                'ftp_password' => Crypt::encryptString($response['ftpPass'] ?? $ftpPassword),
                'database_user' => $response['databaseUser'] ?? $username,
                'database_password' => Crypt::encryptString($response['databasePass'] ?? $dbPassword),
                'website_path' => $path,
                'php_version' => $phpVersion,
                'port' => $port,
                'type_id' => $typeId,
                'created_manually' => true,
            ],
        ]);

        Log::info('aaPanel website created successfully (manual)', [
            'server_id' => $server->id,
            'domain' => $domain,
            'username' => $username,
            'panel_account_id' => $panelAccount->id,
        ]);

        return $panelAccount;
    }

    /**
     * Create virtual account/sub user di aaPanel
     * Endpoint: /v2/virtual/create_account.json
     *
     * @param  array<string, mixed>  $data
     */
    public function createVirtualAccount(Server $server, array $data): PanelAccount
    {
        // Initialize client dengan server
        if (! $this->client || $this->defaultServer?->id !== $server->id) {
            $this->initializeClient($server);
        }

        $username = $data['username'];
        $password = $data['password'] ?? Str::random(16);
        $email = $data['email'] ?? '';

        // Handle expire_date berdasarkan expire_type
        $expireDate = '0000-00-00';
        if (isset($data['expire_type']) && $data['expire_type'] === 'custom' && ! empty($data['expire_date'])) {
            $expireDate = $data['expire_date'];
        }

        // Gunakan storage_disk sebagai mountpoint jika ada
        $mountpoint = $data['storage_disk'] ?? $data['mountpoint'] ?? '/';

        // Prepare data untuk create virtual account
        $virtualAccountData = [
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'expire_date' => $expireDate,
            'package_id' => $data['package_id'] ?? 1,
            'mountpoint' => $mountpoint,
            'disk_space_quota' => $data['disk_space_quota'] ?? 0,
            'monthly_bandwidth_limit' => $data['monthly_bandwidth_limit'] ?? 0,
            'max_site_limit' => $data['max_site_limit'] ?? 5,
            'max_database' => $data['max_database'] ?? 5,
            'php_start_children' => $data['php_start_children'] ?? 1,
            'php_max_children' => $data['php_max_children'] ?? 5,
            'remark' => $data['remark'] ?? '',
            'automatic_dns' => $data['automatic_dns'] ?? 0,
        ];

        // Create virtual account di aaPanel
        // Endpoint menggunakan /v2/virtual/create_account.json
        $response = $this->client->post('v2/virtual/create_account.json', $virtualAccountData);

        // Check response success
        if (! isset($response['status']) || ! $response['status']) {
            $errorMsg = $response['msg'] ?? 'Failed to create virtual account in aaPanel';
            Log::error('aaPanel create virtual account failed', [
                'server_id' => $server->id,
                'username' => $username,
                'response' => $response,
            ]);
            throw new \Exception($errorMsg);
        }

        // Create panel account record
        $panelAccount = PanelAccount::create([
            'server_id' => $server->id,
            'subscription_id' => null, // Virtual account tidak punya subscription
            'username' => $username,
            'domain' => $data['domain'] ?? $username . '.local', // Default domain jika tidak ada
            'status' => 'active',
            'meta' => [
                'virtual_account' => true,
                'create_website' => $data['create_website'] ?? '0',
                'email' => $email,
                'expire_type' => $data['expire_type'] ?? 'perpetual',
                'expire_date' => $virtualAccountData['expire_date'],
                'package_id' => $virtualAccountData['package_id'],
                'storage_disk' => $mountpoint,
                'mountpoint' => $mountpoint,
                'disk_space_quota' => $virtualAccountData['disk_space_quota'],
                'monthly_bandwidth_limit' => $virtualAccountData['monthly_bandwidth_limit'],
                'max_site_limit' => $virtualAccountData['max_site_limit'],
                'max_database' => $virtualAccountData['max_database'],
                'php_start_children' => $virtualAccountData['php_start_children'],
                'php_max_children' => $virtualAccountData['php_max_children'],
                'remark' => $virtualAccountData['remark'],
                'automatic_dns' => $virtualAccountData['automatic_dns'],
                'created_manually' => true,
            ],
        ]);

        Log::info('aaPanel virtual account created successfully', [
            'server_id' => $server->id,
            'username' => $username,
            'panel_account_id' => $panelAccount->id,
        ]);

        return $panelAccount;
    }
}
