<?php

namespace App\Domain\Subscription\Contracts;

use App\Models\Domain\Subscription\Subscription;

interface SubscriptionRepository
{
    public function create(array $data): Subscription;
    public function findByUlid(string $id): ?Subscription;
    public function findByCustomer(string $customerId): array;
    public function updateStatus(Subscription $subscription, string $status): void;
    public function findDueForRenewal(): array;
}

