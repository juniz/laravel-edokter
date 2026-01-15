<?php

namespace App\Application\Order;

use App\Domain\Catalog\Contracts\ProductRepository;
use App\Models\Domain\Customer\Customer;
use App\Models\Domain\Order\Cart;
use App\Models\Domain\Order\CartItem;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function __construct(
        private ProductRepository $productRepository,
        private ValidateCouponService $validateCouponService
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

            // Validate product
            $product = $this->productRepository->findByUlid($data['product_id']);
            if (! $product) {
                throw new \Exception('Product tidak ditemukan');
            }

            // Check if item already exists in cart
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $data['product_id'])
                ->first();

            if ($existingItem) {
                // Update quantity
                $existingItem->update([
                    'qty' => $existingItem->qty + ($data['qty'] ?? 1),
                ]);

                $this->recalculateCart($cart);

                return $existingItem->fresh();
            }

            // Calculate price - gunakan harga dari product
            $unitPriceCents = $product->price_cents ?? 0;
            if ($unitPriceCents === 0) {
                throw new \Exception('Harga tidak tersedia untuk produk ini');
            }

            // Create new cart item
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $data['product_id'],
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
                'coupon_id' => null,
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
     * Apply coupon to cart
     *
     * @return array{success: bool, message: string, discount?: int}
     */
    public function applyCoupon(Cart $cart, string $code): array
    {
        $items = $cart->items()->with('product')->get();
        $productIds = $items->pluck('product_id')->toArray();

        $validation = $this->validateCouponService->validate($code, $productIds);

        if (! $validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message'] ?? 'Kode promo tidak valid',
            ];
        }

        $cart->update(['coupon_id' => $validation['coupon']->id]);
        $this->recalculateCart($cart);

        $cart->refresh();
        $discount = $cart->totals_json['discount'] ?? 0;

        return [
            'success' => true,
            'message' => 'Kode promo berhasil diterapkan',
            'discount' => $discount,
        ];
    }

    /**
     * Remove coupon from cart
     */
    public function removeCoupon(Cart $cart): void
    {
        DB::transaction(function () use ($cart) {
            $cart->update(['coupon_id' => null]);
            $this->recalculateCart($cart);
        });
    }

    /**
     * Recalculate cart totals dengan diskon dan PPH
     */
    public function recalculateCart(Cart $cart): void
    {
        $items = $cart->items()->with('product')->get();

        $subtotal = 0;
        $setupFee = 0;

        foreach ($items as $item) {
            $itemSubtotal = $item->unit_price_cents * $item->qty;
            $subtotal += $itemSubtotal;

            // Add setup fee if product has setup fee
            if ($item->product && ($item->product->setup_fee_cents ?? 0) > 0) {
                $setupFee += $item->product->setup_fee_cents;
            }
        }

        // Apply coupon discount if exists
        $discount = 0;
        if ($cart->coupon_id) {
            $coupon = $cart->coupon;
            if ($coupon) {
                $productIds = $items->pluck('product_id')->toArray();
                $validation = $this->validateCouponService->validate($coupon->code, $productIds);

                if ($validation['valid'] && $validation['coupon']) {
                    $discount = $this->validateCouponService->calculateDiscount(
                        $validation['coupon'],
                        $subtotal + $setupFee
                    );
                }
            }
        }

        // Calculate PPH (Pajak Penghasilan) - get from database settings or config
        $setting = \App\Models\Domain\Shared\Setting::where('key', 'billing_settings')->first();
        $pphRate = $setting?->value['pph_rate'] ?? config('billing.pph_rate', 0.11);
        $taxableAmount = $subtotal + $setupFee - $discount;
        $tax = (int) round($taxableAmount * $pphRate);

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
