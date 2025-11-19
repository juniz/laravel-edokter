<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Order\Contracts\OrderRepository as OrderRepositoryContract;
use App\Models\Domain\Order\Order;
use App\Models\Domain\Order\OrderItem;

class OrderRepository implements OrderRepositoryContract
{
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function findByUlid(string $id): ?Order
    {
        return Order::find($id);
    }

    public function addItem(Order $order, array $itemData): void
    {
        $order->items()->create($itemData);
    }

    public function markAsPaid(Order $order, string $paymentId): void
    {
        $order->update(['status' => 'paid']);
    }

    public function findByCustomer(string $customerId): array
    {
        return Order::where('customer_id', $customerId)->get()->all();
    }
}

