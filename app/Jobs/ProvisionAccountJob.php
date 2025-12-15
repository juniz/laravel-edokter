<?php

namespace App\Jobs;

use App\Domain\Subscription\Contracts\SubscriptionRepository;
use App\Infrastructure\Provisioning\AdapterResolver;
use App\Models\Domain\Provisioning\Server;
use App\Models\Domain\Subscription\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProvisionAccountJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $subscriptionId
    ) {}

    public function handle(
        SubscriptionRepository $subscriptionRepository,
        AdapterResolver $adapterResolver
    ): void {
        $subscription = $subscriptionRepository->findByUlid($this->subscriptionId);

        if (! $subscription) {
            Log::error("Subscription not found: {$this->subscriptionId}");

            return;
        }

        try {
            $subscription->update(['provisioning_status' => 'in_progress']);

            // Determine server type berdasarkan product type
            $serverType = $this->determineServerType($subscription);

            // Get server untuk provisioning
            $server = Server::where('type', $serverType)
                ->where('status', 'active')
                ->first();

            if (! $server) {
                throw new \Exception("No active {$serverType} server found");
            }

            // Resolve adapter berdasarkan server type
            $adapter = $adapterResolver->resolveByType($serverType);

            $panelAccount = $adapter->createAccount($subscription, [
                'plan' => $subscription->plan->code,
                'domain' => $subscription->meta['domain'] ?? null,
                'server' => $server,
            ]);

            $subscription->update([
                'provisioning_status' => 'done',
                'status' => 'active',
                'meta' => array_merge($subscription->meta ?? [], [
                    'panel_account_id' => $panelAccount->id,
                    'username' => $panelAccount->username,
                    'server_id' => $server->id,
                ]),
            ]);

            Log::info("Account provisioned successfully for subscription: {$this->subscriptionId}", [
                'server_type' => $serverType,
                'panel_account_id' => $panelAccount->id,
            ]);
        } catch (\Exception $e) {
            $subscription->update(['provisioning_status' => 'failed']);
            Log::error("Failed to provision account: {$e->getMessage()}", [
                'subscription_id' => $this->subscriptionId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Determine server type berdasarkan product type
     */
    private function determineServerType(Subscription $subscription): string
    {
        $productType = $subscription->product->type;

        // Map product type ke server type
        return match ($productType) {
            'hosting_shared' => 'aapanel', // Default untuk shared hosting adalah aaPanel
            'vps' => 'proxmox',
            default => config('provisioning.default', 'cpanel'),
        };
    }
}
