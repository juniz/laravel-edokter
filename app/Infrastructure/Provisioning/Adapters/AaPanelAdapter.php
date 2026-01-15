<?php

namespace App\Infrastructure\Provisioning\Adapters;

use App\Domain\Provisioning\Contracts\ProvisioningAdapterInterface;
use App\Infrastructure\Provisioning\AaPanel\HttpClient;
use App\Models\Domain\Provisioning\PanelAccount;
use App\Models\Domain\Provisioning\Server;
use App\Models\Domain\Subscription\Subscription;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

/**
 * aaPanel Adapter - Menggunakan API resmi sesuai dengan modul WHMCS
 *
 * API Endpoints yang digunakan:
 * - /v2/virtual/get_service_info.json - Cek status multi-user service
 * - /v2/virtual/get_package_list.json - Daftar resource package
 * - /v2/virtual/get_disk_list.json - Daftar disk/mountpoint
 * - /v2/virtual/get_account_list.json - Daftar akun virtual
 * - /v2/virtual/create_account.json - Buat akun virtual
 * - /v2/virtual/modify_account.json - Update akun virtual
 * - /v2/virtual/remove_account.json - Hapus akun virtual
 * - /v2/virtual/get_account_temp_login_token.json - SSO token untuk user
 * - /v2/config?action=set_temp_login - Admin SSO
 */
class AaPanelAdapter implements ProvisioningAdapterInterface
{
    private ?HttpClient $client = null;

    private ?Server $currentServer = null;

    public function __construct()
    {
        // Lazy initialization - server akan di-set saat method dipanggil
    }

    /**
     * Initialize client dengan server
     */
    private function initializeClient(Server $server): void
    {
        $endpoint = $server->endpoint;
        $apiKey = Crypt::decryptString($server->auth_secret_ref);
        // Ambil verify_ssl dari meta, default false untuk kompatibilitas
        $verifySsl = $server->meta['verify_ssl'] ?? false;
        $this->client = new HttpClient($endpoint, $apiKey, $verifySsl);
        $this->currentServer = $server;
    }

    /**
     * Ensure client sudah diinisialisasi untuk server tertentu
     */
    private function ensureClient(Server $server): void
    {
        if (! $this->client || $this->currentServer?->id !== $server->id) {
            $this->initializeClient($server);
        }
    }

    /**
     * Get server untuk provisioning
     *
     * @param  array<string, mixed>  $params
     */
    private function getServer(array $params): Server
    {
        // Gunakan server dari params jika ada
        if (isset($params['server']) && $params['server'] instanceof Server) {
            return $params['server'];
        }

        // Fallback ke default server aktif
        $server = Server::where('type', 'aapanel')
            ->where('status', 'active')
            ->first();

        if (! $server) {
            throw new \Exception('No active aaPanel server found');
        }

        return $server;
    }

    /**
     * Cek apakah multi-user service terinstall dan running
     */
    public function checkVirtualService(Server $server): bool
    {
        $this->ensureClient($server);

        $status = $this->client->checkVirtualServiceStatus();

        if (! $status['installed'] || ! $status['running']) {
            Log::warning('aaPanel virtual service not ready', [
                'server_id' => $server->id,
                'status' => $status,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Get daftar resource package dari aaPanel
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPackageList(Server $server): array
    {
        $this->ensureClient($server);

        $response = $this->client->post('v2/virtual/get_package_list.json', [
            'p' => 1,
            'rows' => 10000,
            'search_value' => '',
        ]);

        return $response['message']['list'] ?? [];
    }

    /**
     * Get package info by name
     *
     * @return array<string, mixed>|null
     */
    public function getPackageByName(Server $server, string $packageName): ?array
    {
        $packages = $this->getPackageList($server);

        foreach ($packages as $package) {
            if ($package['package_name'] === $packageName) {
                return $package;
            }
        }

        return null;
    }

    /**
     * Get daftar disk/mountpoint dari aaPanel
     *
     * Response format dari aaPanel:
     * {
     *   "code": 200,
     *   "message": [ {...disk1}, {...disk2} ],  // message langsung array, bukan message.list
     *   "status": 0
     * }
     *
     * @return array<int, array<string, mixed>>
     */
    public function getDiskList(Server $server): array
    {
        $this->ensureClient($server);

        $response = $this->client->post('v2/virtual/get_disk_list.json');

        // Response disk list: message langsung berupa array (bukan message.list seperti package)
        $disks = $response['message'] ?? [];

        // Pastikan hasil adalah array (bukan object)
        if (is_array($disks) && ! isset($disks['list'])) {
            return $disks;
        }

        // Fallback jika format berbeda
        return $response['message']['list'] ?? $disks;
    }

    /**
     * Get default mountpoint
     */
    public function getDefaultMountpoint(Server $server): string
    {
        $disks = $this->getDiskList($server);

        $mountpoint = '/';

        foreach ($disks as $disk) {
            if ($mountpoint === '/') {
                $mountpoint = $disk['mountpoint'];
            }
            // Prioritaskan root "/"
            if ($disk['mountpoint'] === '/') {
                $mountpoint = $disk['mountpoint'];
                break;
            }
        }

        return $mountpoint;
    }

    /**
     * Get daftar akun virtual dari aaPanel
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAccountList(Server $server, string $search = ''): array
    {
        $this->ensureClient($server);

        $response = $this->client->post('v2/virtual/get_account_list.json', [
            'p' => 1,
            'rows' => 10000,
            'type_id' => -1,
            'search_value' => $search,
        ]);

        return $response['message']['list'] ?? [];
    }

    /**
     * Get akun by username
     *
     * @return array<string, mixed>|null
     */
    public function getAccountByUsername(Server $server, string $username): ?array
    {
        $accounts = $this->getAccountList($server, $username);

        foreach ($accounts as $account) {
            if ($account['username'] === $username) {
                return $account;
            }
        }

        return null;
    }

    /**
     * Create account untuk subscription (shared hosting)
     *
     * @param  array<string, mixed>  $params
     */
    public function createAccount(Subscription $sub, array $params): PanelAccount
    {
        $server = $this->getServer($params);
        $this->ensureClient($server);

        // Cek virtual service
        if (! $this->checkVirtualService($server)) {
            throw new \Exception('Multi-user service not installed or running on aaPanel server');
        }

        // Get package dari product metadata atau params
        $packageName = $params['package_name'] ?? $sub->product->metadata['aapanel_package'] ?? 'Default';
        $package = $this->getPackageByName($server, $packageName);

        if (! $package) {
            throw new \Exception("Cannot find '{$packageName}' resource package, please create it first in aaPanel");
        }

        // Get mountpoint
        $mountpoint = $params['mountpoint'] ?? $this->getDefaultMountpoint($server);

        // Generate username dan password
        $domain = $params['domain'] ?? $sub->meta['domain'] ?? null;
        $username = $params['username'] ?? $this->generateUsername($domain ?? 'user');
        $password = $params['password'] ?? $this->generateSecurePassword();
        $email = $params['email'] ?? $sub->customer?->email ?? '';

        // Handle expire date
        $expireDate = '0000-00-00'; // Default: tidak expire
        if (isset($params['expire_date']) && $params['expire_date']) {
            $expireDate = $params['expire_date'];
        }

        // Create virtual account via API resmi
        $response = $this->client->post('v2/virtual/create_account.json', [
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'expire_date' => $expireDate,
            'package_id' => $package['package_id'],
            'mountpoint' => $mountpoint,
            'disk_space_quota' => $package['disk_space_quota'],
            'monthly_bandwidth_limit' => $package['monthly_bandwidth_limit'],
            'max_site_limit' => $package['max_site_limit'],
            'max_database' => $package['max_database'],
            'php_start_children' => $package['php_start_children'],
            'php_max_children' => $package['php_max_children'],
            'remark' => $params['remark'] ?? 'Created via '.config('app.name'),
            'automatic_dns' => $params['automatic_dns'] ?? 0,
        ]);

        // Check response - status 0 = success di API aaPanel
        if (! isset($response['status']) || (int) $response['status'] !== 0) {
            $errorMsg = $response['msg'] ?? 'Failed to create virtual account in aaPanel';
            Log::error('aaPanel create account failed', [
                'subscription_id' => $sub->id,
                'username' => $username,
                'response' => $response,
            ]);
            throw new \Exception($errorMsg);
        }

        // Create panel account record
        $panelAccount = PanelAccount::create([
            'server_id' => $server->id,
            'subscription_id' => $sub->id,
            'username' => $username,
            'domain' => $domain ?? $username.'.local',
            'status' => 'active',
            'meta' => [
                'email' => $email,
                'password_encrypted' => Crypt::encryptString($password),
                'package_id' => $package['package_id'],
                'package_name' => $package['package_name'],
                'mountpoint' => $mountpoint,
                'disk_space_quota' => $package['disk_space_quota'],
                'monthly_bandwidth_limit' => $package['monthly_bandwidth_limit'],
                'max_site_limit' => $package['max_site_limit'],
                'max_database' => $package['max_database'],
                'php_start_children' => $package['php_start_children'],
                'php_max_children' => $package['php_max_children'],
                'expire_date' => $expireDate,
            ],
        ]);

        Log::info('aaPanel virtual account created successfully', [
            'subscription_id' => $sub->id,
            'username' => $username,
            'panel_account_id' => $panelAccount->id,
        ]);

        return $panelAccount;
    }

    /**
     * Suspend account via modify_account
     */
    public function suspendAccount(PanelAccount $acc): void
    {
        $server = $acc->server;
        $this->ensureClient($server);

        // Get account info dari aaPanel
        $accountInfo = $this->getAccountByUsername($server, $acc->username);

        if (! $accountInfo) {
            throw new \Exception('Account not found in aaPanel: '.$acc->username);
        }

        // Modify account dengan status = 0 (suspended)
        $response = $this->client->post('v2/virtual/modify_account.json', [
            'account_id' => $accountInfo['account_id'],
            'username' => $accountInfo['username'],
            'email' => $accountInfo['email'],
            'expire_date' => $accountInfo['expire_date'],
            'package_id' => $accountInfo['package_id'],
            'disk_space_quota' => $accountInfo['disk_space_quota'],
            'monthly_bandwidth_limit' => $accountInfo['monthly_bandwidth_limit'],
            'max_site_limit' => $accountInfo['max_site_limit'],
            'max_database' => $accountInfo['max_database'],
            'php_start_children' => $accountInfo['php_start_children'],
            'php_max_children' => $accountInfo['php_max_children'],
            'remark' => $accountInfo['remark'] ?? '',
            'domain' => $accountInfo['domain'] ?? '',
            'automatic_dns' => 0,
            'status' => 0, // 0 = suspended
        ]);

        if (! isset($response['status']) || (int) $response['status'] !== 0) {
            throw new \Exception('Failed to suspend account: '.($response['msg'] ?? 'Unknown error'));
        }

        $acc->update(['status' => 'suspended']);
        Log::info('aaPanel account suspended', ['panel_account_id' => $acc->id]);
    }

    /**
     * Unsuspend account via modify_account
     */
    public function unsuspendAccount(PanelAccount $acc): void
    {
        $server = $acc->server;
        $this->ensureClient($server);

        // Get account info dari aaPanel
        $accountInfo = $this->getAccountByUsername($server, $acc->username);

        if (! $accountInfo) {
            throw new \Exception('Account not found in aaPanel: '.$acc->username);
        }

        // Modify account dengan status = 1 (active)
        $response = $this->client->post('v2/virtual/modify_account.json', [
            'account_id' => $accountInfo['account_id'],
            'username' => $accountInfo['username'],
            'email' => $accountInfo['email'],
            'expire_date' => $accountInfo['expire_date'],
            'package_id' => $accountInfo['package_id'],
            'disk_space_quota' => $accountInfo['disk_space_quota'],
            'monthly_bandwidth_limit' => $accountInfo['monthly_bandwidth_limit'],
            'max_site_limit' => $accountInfo['max_site_limit'],
            'max_database' => $accountInfo['max_database'],
            'php_start_children' => $accountInfo['php_start_children'],
            'php_max_children' => $accountInfo['php_max_children'],
            'remark' => $accountInfo['remark'] ?? '',
            'domain' => $accountInfo['domain'] ?? '',
            'automatic_dns' => 0,
            'status' => 1, // 1 = active
        ]);

        if (! isset($response['status']) || (int) $response['status'] !== 0) {
            throw new \Exception('Failed to unsuspend account: '.($response['msg'] ?? 'Unknown error'));
        }

        $acc->update(['status' => 'active']);
        Log::info('aaPanel account unsuspended', ['panel_account_id' => $acc->id]);
    }

    /**
     * Terminate/hapus account
     */
    public function terminateAccount(PanelAccount $acc): void
    {
        $server = $acc->server;
        $this->ensureClient($server);

        // Get account info dari aaPanel
        $accountInfo = $this->getAccountByUsername($server, $acc->username);

        if (! $accountInfo) {
            // Account sudah tidak ada di aaPanel, update status lokal saja
            Log::warning('Account not found in aaPanel, marking as terminated locally', [
                'panel_account_id' => $acc->id,
                'username' => $acc->username,
            ]);
            $acc->update(['status' => 'terminated']);

            return;
        }

        // Remove account via API
        $response = $this->client->post('v2/virtual/remove_account.json', [
            'account_id' => $accountInfo['account_id'],
            'is_del_resources' => true, // Hapus semua resources (sites, databases, etc)
        ]);

        if (! isset($response['status']) || (int) $response['status'] !== 0) {
            Log::warning('aaPanel terminate account response', [
                'panel_account_id' => $acc->id,
                'response' => $response,
            ]);
        }

        $acc->update(['status' => 'terminated']);
        Log::info('aaPanel account terminated', ['panel_account_id' => $acc->id]);
    }

    /**
     * Change password untuk account
     */
    public function changePassword(PanelAccount $acc, string $newPassword): void
    {
        $server = $acc->server;
        $this->ensureClient($server);

        // Get account info dari aaPanel
        $accountInfo = $this->getAccountByUsername($server, $acc->username);

        if (! $accountInfo) {
            throw new \Exception('Account not found in aaPanel: '.$acc->username);
        }

        // Modify account dengan password baru
        $response = $this->client->post('v2/virtual/modify_account.json', [
            'account_id' => $accountInfo['account_id'],
            'username' => $accountInfo['username'],
            'password' => $newPassword,
            'email' => $accountInfo['email'],
            'expire_date' => $accountInfo['expire_date'],
            'package_id' => $accountInfo['package_id'],
            'disk_space_quota' => $accountInfo['disk_space_quota'],
            'monthly_bandwidth_limit' => $accountInfo['monthly_bandwidth_limit'],
            'max_site_limit' => $accountInfo['max_site_limit'],
            'max_database' => $accountInfo['max_database'],
            'php_start_children' => $accountInfo['php_start_children'],
            'php_max_children' => $accountInfo['php_max_children'],
            'remark' => $accountInfo['remark'] ?? '',
            'domain' => $accountInfo['domain'] ?? '',
            'automatic_dns' => 1,
        ]);

        if (! isset($response['status']) || (int) $response['status'] !== 0) {
            throw new \Exception('Failed to change password: '.($response['msg'] ?? 'Unknown error'));
        }

        // Update encrypted password di meta
        $meta = $acc->meta ?? [];
        $meta['password_encrypted'] = Crypt::encryptString($newPassword);
        $acc->update(['meta' => $meta]);

        Log::info('aaPanel password changed', ['panel_account_id' => $acc->id]);
    }

    /**
     * Change plan/package untuk account
     */
    public function changePlan(PanelAccount $acc, string $planCode): void
    {
        $server = $acc->server;
        $this->ensureClient($server);

        // Get package baru
        $newPackage = $this->getPackageByName($server, $planCode);

        if (! $newPackage) {
            throw new \Exception("Cannot find '{$planCode}' resource package in aaPanel");
        }

        // Get current account info
        $accountInfo = $this->getAccountByUsername($server, $acc->username);

        if (! $accountInfo) {
            throw new \Exception('Account not found in aaPanel: '.$acc->username);
        }

        // Modify account dengan package baru
        $response = $this->client->post('v2/virtual/modify_account.json', [
            'account_id' => $accountInfo['account_id'],
            'username' => $accountInfo['username'],
            'email' => $accountInfo['email'],
            'expire_date' => $accountInfo['expire_date'],
            'package_id' => $newPackage['package_id'],
            'disk_space_quota' => $newPackage['disk_space_quota'],
            'monthly_bandwidth_limit' => $newPackage['monthly_bandwidth_limit'],
            'max_site_limit' => $newPackage['max_site_limit'],
            'max_database' => $newPackage['max_database'],
            'php_start_children' => $newPackage['php_start_children'],
            'php_max_children' => $newPackage['php_max_children'],
            'remark' => $accountInfo['remark'] ?? '',
            'domain' => $accountInfo['domain'] ?? '',
            'automatic_dns' => 0,
        ]);

        if (! isset($response['status']) || (int) $response['status'] !== 0) {
            throw new \Exception('Failed to change plan: '.($response['msg'] ?? 'Unknown error'));
        }

        // Update meta dengan package info baru
        $meta = $acc->meta ?? [];
        $meta['package_id'] = $newPackage['package_id'];
        $meta['package_name'] = $newPackage['package_name'];
        $meta['disk_space_quota'] = $newPackage['disk_space_quota'];
        $meta['monthly_bandwidth_limit'] = $newPackage['monthly_bandwidth_limit'];
        $meta['max_site_limit'] = $newPackage['max_site_limit'];
        $meta['max_database'] = $newPackage['max_database'];
        $meta['php_start_children'] = $newPackage['php_start_children'];
        $meta['php_max_children'] = $newPackage['php_max_children'];
        $acc->update(['meta' => $meta]);

        Log::info('aaPanel plan changed', [
            'panel_account_id' => $acc->id,
            'new_plan' => $planCode,
        ]);
    }

    /**
     * Get SSO login URL untuk user (client area)
     */
    public function getLoginUrl(PanelAccount $acc): string
    {
        $server = $acc->server;
        $this->ensureClient($server);

        // Get account info
        $accountInfo = $this->getAccountByUsername($server, $acc->username);

        if (! $accountInfo) {
            throw new \Exception('Account not found in aaPanel: '.$acc->username);
        }

        // Get temp login token
        $response = $this->client->post('v2/virtual/get_account_temp_login_token.json', [
            'account_id' => $accountInfo['account_id'],
        ]);

        $token = $response['message']['token'] ?? '';
        $loginUrl = $response['message']['login_url'] ?? '';

        if (empty($token) || empty($loginUrl)) {
            throw new \Exception('Failed to get login token');
        }

        // Construct final URL
        $separator = str_contains($loginUrl, '?') ? '&' : '?';

        return $loginUrl.$separator.'token='.$token;
    }

    /**
     * Get admin SSO URL untuk masuk ke panel aaPanel utama
     */
    public function getAdminLoginUrl(Server $server): string
    {
        $this->ensureClient($server);

        // Get temp login token untuk admin
        $response = $this->client->post('v2/config?action=set_temp_login', [
            'expire_time' => time() + 3600, // 1 jam
        ]);

        $token = $response['message']['token'] ?? '';

        if (empty($token)) {
            throw new \Exception('Failed to get admin login token');
        }

        return $server->endpoint.'/login?tmp_token='.$token;
    }

    /**
     * Create manual account tanpa subscription
     *
     * Menerima data dari frontend yang bisa berupa:
     * 1. package_id + resource values langsung (dari form yang sudah fetch package info)
     * 2. package_name saja (akan di-lookup ke server)
     *
     * @param  array<string, mixed>  $data
     */
    public function createManualAccount(Server $server, array $data): PanelAccount
    {
        $this->ensureClient($server);

        // Cek virtual service
        if (! $this->checkVirtualService($server)) {
            throw new \Exception('Multi-user service not installed or running on aaPanel server');
        }

        // Get package info - bisa dari package_id yang dikirim atau lookup by name
        $packageId = $data['package_id'] ?? null;
        $packageName = $data['package_name'] ?? 'Default';

        // Jika package_id sudah ada dan resource values sudah dikirim, gunakan langsung
        // Jika tidak, lookup package dari server
        if ($packageId && isset($data['disk_space_quota'])) {
            // Gunakan values yang dikirim dari frontend (sesuai API screenshot)
            $package = [
                'package_id' => (int) $packageId,
                'package_name' => $packageName,
                'disk_space_quota' => (int) ($data['disk_space_quota'] ?? 0),
                'monthly_bandwidth_limit' => (int) ($data['monthly_bandwidth_limit'] ?? 0),
                'max_site_limit' => (int) ($data['max_site_limit'] ?? 5),
                'max_database' => (int) ($data['max_database'] ?? 5),
                'php_start_children' => (int) ($data['php_start_children'] ?? 1),
                'php_max_children' => (int) ($data['php_max_children'] ?? 5),
            ];
        } else {
            // Lookup package dari server
            $package = $this->getPackageByName($server, $packageName);

            if (! $package) {
                throw new \Exception("Cannot find '{$packageName}' resource package, please create it first in aaPanel");
            }
        }

        // Get mountpoint
        $mountpoint = $data['mountpoint'] ?? $data['storage_disk'] ?? $this->getDefaultMountpoint($server);

        $username = $data['username'];
        $password = $data['password'] ?? $this->generateSecurePassword();
        $email = $data['email'] ?? '';

        // Handle expire date
        $expireDate = '0000-00-00';
        if (isset($data['expire_type']) && $data['expire_type'] === 'custom' && ! empty($data['expire_date'])) {
            $expireDate = $data['expire_date'];
        } elseif (isset($data['expire_date']) && $data['expire_date'] !== '0000-00-00') {
            $expireDate = $data['expire_date'];
        }

        // Create virtual account - sesuai format API aaPanel dari screenshot
        $response = $this->client->post('v2/virtual/create_account.json', [
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'expire_date' => $expireDate,
            'package_id' => $package['package_id'],
            'mountpoint' => $mountpoint,
            'disk_space_quota' => $package['disk_space_quota'],
            'monthly_bandwidth_limit' => $package['monthly_bandwidth_limit'],
            'max_site_limit' => $package['max_site_limit'],
            'max_database' => $package['max_database'],
            'php_start_children' => $package['php_start_children'],
            'php_max_children' => $package['php_max_children'],
            'remark' => $data['remark'] ?? '',
            'automatic_dns' => (int) ($data['automatic_dns'] ?? 0),
        ]);

        if (! isset($response['status']) || (int) $response['status'] !== 0) {
            $errorMsg = $response['msg'] ?? 'Failed to create virtual account in aaPanel';
            Log::error('aaPanel create manual account failed', [
                'server_id' => $server->id,
                'username' => $username,
                'response' => $response,
            ]);
            throw new \Exception($errorMsg);
        }

        // Create panel account record
        $panelAccount = PanelAccount::create([
            'server_id' => $server->id,
            'subscription_id' => null,
            'username' => $username,
            'domain' => $data['domain'] ?? $username.'.local',
            'status' => 'active',
            'meta' => [
                'email' => $email,
                'password_encrypted' => Crypt::encryptString($password),
                'package_id' => $package['package_id'],
                'package_name' => $package['package_name'],
                'mountpoint' => $mountpoint,
                'disk_space_quota' => $package['disk_space_quota'],
                'monthly_bandwidth_limit' => $package['monthly_bandwidth_limit'],
                'max_site_limit' => $package['max_site_limit'],
                'max_database' => $package['max_database'],
                'php_start_children' => $package['php_start_children'],
                'php_max_children' => $package['php_max_children'],
                'expire_type' => $data['expire_type'] ?? 'perpetual',
                'expire_date' => $expireDate,
                'created_manually' => true,
            ],
        ]);

        Log::info('aaPanel manual account created successfully', [
            'server_id' => $server->id,
            'username' => $username,
            'panel_account_id' => $panelAccount->id,
        ]);

        return $panelAccount;
    }

    /**
     * Alias untuk createManualAccount (backward compatibility)
     *
     * @param  array<string, mixed>  $data
     */
    public function createVirtualAccount(Server $server, array $data): PanelAccount
    {
        return $this->createManualAccount($server, $data);
    }

    /**
     * Test connection ke aaPanel server
     *
     * @return array{success: bool, message: string, virtual_service?: bool}
     */
    public function testConnection(Server $server): array
    {
        $this->ensureClient($server);

        // Test basic connection
        $basicTest = $this->client->testConnection();

        if (! $basicTest['success']) {
            return $basicTest;
        }

        // Test virtual service
        $virtualStatus = $this->client->checkVirtualServiceStatus();

        return [
            'success' => true,
            'message' => 'Connection successful. '.$virtualStatus['message'],
            'virtual_service_installed' => $virtualStatus['installed'],
            'virtual_service_running' => $virtualStatus['running'],
        ];
    }

    /**
     * Get account usage/statistics
     *
     * @return array<string, mixed>|null
     */
    public function getAccountUsage(PanelAccount $acc): ?array
    {
        $server = $acc->server;
        $this->ensureClient($server);

        return $this->getAccountByUsername($server, $acc->username);
    }

    /**
     * Generate username dari domain atau string
     */
    private function generateUsername(string $input): string
    {
        // Remove http://, https://, www.
        $input = preg_replace('/^https?:\/\//', '', $input);
        $input = preg_replace('/^www\./', '', $input);

        // Extract domain name tanpa extension
        $parts = explode('.', $input);
        $name = $parts[0];

        // Clean: hanya alphanumeric dan underscore
        $username = preg_replace('/[^a-z0-9_]/', '', strtolower($name));

        // Tambahkan random suffix untuk uniqueness
        $username = substr($username, 0, 10).'_'.substr(md5((string) time()), 0, 4);

        // Ensure tidak kosong
        if (empty($username) || strlen($username) < 3) {
            $username = 'user_'.substr(md5((string) time()), 0, 8);
        }

        return $username;
    }

    /**
     * Generate secure password
     */
    private function generateSecurePassword(int $length = 12): string
    {
        $lowercase = 'abcdefghjkmnpqrstuvwxyz';
        $uppercase = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        $numbers = '23456789';
        $special = '!@#$%^&*()_+-=';

        $charPool = $lowercase.$uppercase.$numbers.$special;

        // Ensure at least one character from each category
        $password = '';
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        // Fill remaining length
        $poolLength = strlen($charPool);
        for ($i = 4; $i < $length; $i++) {
            $password .= $charPool[random_int(0, $poolLength - 1)];
        }

        // Shuffle
        return str_shuffle($password);
    }
}
