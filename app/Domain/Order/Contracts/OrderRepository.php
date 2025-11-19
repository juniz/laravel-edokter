<?php

namespace App\Domain\Order\Contracts;

use App\Models\Domain\Order\Order;

interface OrderRepository
{
    public function create(array $data): Order;
    public function findByUlid(string $id): ?Order;
    public function addItem(Order $order, array $itemData): void;
    public function markAsPaid(Order $order, string $paymentId): void;
    public function findByCustomer(string $customerId): array;
}

