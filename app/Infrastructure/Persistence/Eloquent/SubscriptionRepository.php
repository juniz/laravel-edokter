<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Subscription\Contracts\SubscriptionRepository as SubscriptionRepositoryContract;
use App\Models\Domain\Subscription\Subscription;

class SubscriptionRepository implements SubscriptionRepositoryContract
{
    public function create(array $data): Subscription
    {
        return Subscription::create($data);
    }

    public function findByUlid(string $id): ?Subscription
    {
        return Subscription::find($id);
    }

    public function findByCustomer(string $customerId): array
    {
        return Subscription::where('customer_id', $customerId)->get()->all();
    }

    public function updateStatus(Subscription $subscription, string $status): void
    {
        $subscription->update(['status' => $status]);
    }

    public function findDueForRenewal(): array
    {
        return Subscription::where('auto_renew', true)
            ->where('status', 'active')
            ->where('next_renewal_at', '<=', now())
            ->get()
            ->all();
    }
}

