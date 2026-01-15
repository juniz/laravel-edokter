<?php

namespace App\Application\Order;

use App\Domain\Catalog\Contracts\ProductRepository;
use App\Domain\Order\Contracts\OrderRepository;
use App\Models\Domain\Order\Order;
use Illuminate\Support\Facades\DB;

class PlaceOrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private ProductRepository $productRepository
    ) {}

    public function execute(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $order = $this->orderRepository->create([
                'customer_id' => $data['customer_id'],
                'status' => 'pending',
                'currency' => $data['currency'] ?? 'IDR',
                'subtotal_cents' => $data['subtotal_cents'],
                'discount_cents' => $data['discount_cents'] ?? 0,
                'tax_cents' => $data['tax_cents'] ?? 0,
                'total_cents' => $data['total_cents'],
                'coupon_id' => $data['coupon_id'] ?? null,
                'placed_at' => now(),
            ]);

            foreach ($data['items'] as $item) {
                $this->orderRepository->addItem($order, $item);
            }

            return $order;
        });
    }
}
