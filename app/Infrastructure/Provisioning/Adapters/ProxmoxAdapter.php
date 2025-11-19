<?php

namespace App\Infrastructure\Provisioning\Adapters;

use App\Domain\Provisioning\Contracts\ProvisioningAdapterInterface;
use App\Models\Domain\Provisioning\PanelAccount;
use App\Models\Domain\Provisioning\Server;
use App\Models\Domain\Subscription\Subscription;
use Illuminate\Support\Facades\Log;

class ProxmoxAdapter implements ProvisioningAdapterInterface
{
    public function createAccount(Subscription $sub, array $params): PanelAccount
    {
        $server = Server::where('type', 'proxmox')
            ->where('status', 'active')
            ->first();

        if (!$server) {
            throw new \Exception('No active Proxmox server found');
        }

        // TODO: Implementasi API call ke Proxmox untuk create VM
        $vmId = 'vm' . substr($sub->id, 0, 8);
        $domain = $params['domain'] ?? 'example.com';

        $panelAccount = PanelAccount::create([
            'server_id' => $server->id,
            'subscription_id' => $sub->id,
            'username' => $vmId,
            'domain' => $domain,
            'status' => 'active',
            'meta' => [
                'vm_id' => $vmId,
                'node' => $server->meta['default_node'] ?? 'node1',
            ],
        ]);

        Log::info("Proxmox VM created: {$vmId}");

        return $panelAccount;
    }

    public function suspendAccount(PanelAccount $acc): void
    {
        // TODO: Implementasi stop VM via Proxmox API
        $acc->update(['status' => 'suspended']);
    }

    public function unsuspendAccount(PanelAccount $acc): void
    {
        // TODO: Implementasi start VM via Proxmox API
        $acc->update(['status' => 'active']);
    }

    public function terminateAccount(PanelAccount $acc): void
    {
        // TODO: Implementasi delete VM via Proxmox API
        $acc->update(['status' => 'terminated']);
    }

    public function changePlan(PanelAccount $acc, string $planCode): void
    {
        // TODO: Implementasi resize VM via Proxmox API
        Log::info("Plan changed for Proxmox VM: {$acc->username}");
    }
}

