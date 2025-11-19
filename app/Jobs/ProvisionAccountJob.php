<?php

namespace App\Jobs;

use App\Domain\Provisioning\Contracts\ProvisioningAdapterInterface;
use App\Domain\Subscription\Contracts\SubscriptionRepository;
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
        ProvisioningAdapterInterface $adapter
    ): void {
        $subscription = $subscriptionRepository->findByUlid($this->subscriptionId);

        if (!$subscription) {
            Log::error("Subscription not found: {$this->subscriptionId}");
            return;
        }

        try {
            $subscription->update(['provisioning_status' => 'in_progress']);
            
            $panelAccount = $adapter->createAccount($subscription, [
                'plan' => $subscription->plan->code,
                'domain' => $subscription->meta['domain'] ?? null,
            ]);

            $subscription->update([
                'provisioning_status' => 'done',
                'status' => 'active',
                'meta' => array_merge($subscription->meta ?? [], [
                    'panel_account_id' => $panelAccount->id,
                    'username' => $panelAccount->username,
                ]),
            ]);

            Log::info("Account provisioned successfully for subscription: {$this->subscriptionId}");
        } catch (\Exception $e) {
            $subscription->update(['provisioning_status' => 'failed']);
            Log::error("Failed to provision account: {$e->getMessage()}");
            throw $e;
        }
    }
}

