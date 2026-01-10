<?php

namespace App\Application\Order;

use App\Domain\Catalog\Contracts\PlanRepository;
use App\Domain\Catalog\Contracts\ProductRepository;
use App\Models\Domain\Customer\Customer;
use App\Models\Domain\Order\Cart;
use App\Models\Domain\Order\CartItem;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function __construct(
        private ProductRepository $productRepository,
        private PlanRepository $planRepository
    ) {}

    /**
     * Get or create cart for customer
     */
    public function getOrCreateCart(Customer $customer): Cart
    {
        return Cart::firstOrCreate(
            ['customer_id' => $customer->id],
            ['currency' => 'IDR']
        );
    }

    /**
     * Add item to cart
     *
     * @param  array<string, mixed>  $data
     */
    public function addItem(Customer $customer, array $data): CartItem
    {
        return DB::transaction(function () use ($customer, $data) {
            $cart = $this->getOrCreateCart($customer);

            // Validate product and plan
            $product = $this->productRepository->findByUlid($data['product_id']);
            if (! $product) {
                throw new \Exception('Product tidak ditemukan');
            }

            $plan = null;
            if (isset($data['plan_id'])) {
                $plan = $this->planRepository->findByUlid($data['plan_id']);
                if (! $plan) {
                    throw new \Exception('Plan tidak ditemukan');
                }
            }

            // Check if item already exists in cart
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $data['product_id'])
                ->where('plan_id', $data['plan_id'] ?? null)
                ->first();

            if ($existingItem) {
                // Update quantity
                $existingItem->update([
                    'qty' => $existingItem->qty + ($data['qty'] ?? 1),
                ]);

                $this->recalculateCart($cart);

                return $existingItem->fresh();
            }

            // Calculate price
            $unitPriceCents = $plan?->price_cents ?? $product->metadata['price_cents'] ?? 0;
            if ($unitPriceCents === 0) {
                throw new \Exception('Harga tidak tersedia untuk produk ini');
            }

            // Create new cart item
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $data['product_id'],
                'plan_id' => $data['plan_id'] ?? null,
                'qty' => $data['qty'] ?? 1,
                'unit_price_cents' => $unitPriceCents,
                'meta' => $data['meta'] ?? [],
            ]);

            $this->recalculateCart($cart);

            return $cartItem;
        });
    }

    /**
     * Update cart item
     *
     * @param  array<string, mixed>  $data
     */
    public function updateItem(CartItem $cartItem, array $data): CartItem
    {
        return DB::transaction(function () use ($cartItem, $data) {
            if (isset($data['qty'])) {
                if ($data['qty'] <= 0) {
                    $cartItem->delete();
                } else {
                    $cartItem->update(['qty' => $data['qty']]);
                }
            }

            if (isset($data['meta'])) {
                $cartItem->update(['meta' => array_merge($cartItem->meta ?? [], $data['meta'])]);
            }

            $this->recalculateCart($cartItem->cart);

            return $cartItem->fresh();
        });
    }

    /**
     * Remove item from cart
     */
    public function removeItem(CartItem $cartItem): void
    {
        DB::transaction(function () use ($cartItem) {
            $cart = $cartItem->cart;
            $cartItem->delete();
            $this->recalculateCart($cart);
        });
    }

    /**
     * Clear cart
     */
    public function clearCart(Cart $cart): void
    {
        DB::transaction(function () use ($cart) {
            $cart->items()->delete();
            $cart->update([
                'totals_json' => [
                    'subtotal' => 0,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => 0,
                ],
            ]);
        });
    }

    /**
     * Recalculate cart totals
     */
    public function recalculateCart(Cart $cart): void
    {
        $items = $cart->items()->with(['product', 'plan'])->get();

        $subtotal = 0;
        $setupFee = 0;

        foreach ($items as $item) {
            $itemSubtotal = $item->unit_price_cents * $item->qty;
            $subtotal += $itemSubtotal;

            // Add setup fee if plan has setup fee
            if ($item->plan && $item->plan->setup_fee_cents > 0) {
                $setupFee += $item->plan->setup_fee_cents;
            }
        }

        // TODO: Apply coupon discount if exists
        $discount = 0;

        // TODO: Calculate tax if needed
        $tax = 0;

        $total = $subtotal + $setupFee - $discount + $tax;

        $cart->update([
            'totals_json' => [
                'subtotal' => $subtotal,
                'setup_fee' => $setupFee,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
            ],
        ]);
    }
}
