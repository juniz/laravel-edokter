<?php

namespace App\Infrastructure\Provisioning\Adapters;

use App\Domain\Provisioning\Contracts\ProvisioningAdapterInterface;
use App\Models\Domain\Provisioning\PanelAccount;
use App\Models\Domain\Provisioning\Server;
use App\Models\Domain\Subscription\Subscription;
use Illuminate\Support\Facades\Log;

class DirectAdminAdapter implements ProvisioningAdapterInterface
{
    public function createAccount(Subscription $sub, array $params): PanelAccount
    {
        $server = Server::where('type', 'directadmin')
            ->where('status', 'active')
            ->first();

        if (!$server) {
            throw new \Exception('No active DirectAdmin server found');
        }

        // TODO: Implementasi API call ke DirectAdmin
        $username = 'user_' . substr($sub->id, 0, 8);
        $domain = $params['domain'] ?? 'example.com';
        $password = \Illuminate\Support\Str::random(16);

        $panelAccount = PanelAccount::create([
            'server_id' => $server->id,
            'subscription_id' => $sub->id,
            'username' => $username,
            'domain' => $domain,
            'status' => 'active',
            'meta' => [
                'password' => encrypt($password),
            ],
        ]);

        Log::info("DirectAdmin account created: {$username}");

        return $panelAccount;
    }

    public function suspendAccount(PanelAccount $acc): void
    {
        // TODO: Implementasi suspend via DirectAdmin API
        $acc->update(['status' => 'suspended']);
    }

    public function unsuspendAccount(PanelAccount $acc): void
    {
        // TODO: Implementasi unsuspend via DirectAdmin API
        $acc->update(['status' => 'active']);
    }

    public function terminateAccount(PanelAccount $acc): void
    {
        // TODO: Implementasi terminate via DirectAdmin API
        $acc->update(['status' => 'terminated']);
    }

    public function changePlan(PanelAccount $acc, string $planCode): void
    {
        // TODO: Implementasi change plan via DirectAdmin API
        Log::info("Plan changed for DirectAdmin account: {$acc->username}");
    }
}

