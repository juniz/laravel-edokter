<?php

namespace App\Application\Order;

use App\Models\Domain\Catalog\Coupon;
use Carbon\Carbon;

class ValidateCouponService
{
    /**
     * Validate dan get coupon info
     *
     * @param  array<string>  $productIds
     * @return array{valid: bool, coupon?: Coupon, message?: string, discount_amount?: int}
     */
    public function validate(string $code, array $productIds = []): array
    {
        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (! $coupon) {
            return [
                'valid' => false,
                'message' => 'Kode promo tidak ditemukan',
            ];
        }

        // Check if coupon is deleted
        if ($coupon->trashed()) {
            return [
                'valid' => false,
                'message' => 'Kode promo tidak valid',
            ];
        }

        // Check validity period
        $now = Carbon::now();
        if ($coupon->valid_from && $now->lt($coupon->valid_from)) {
            return [
                'valid' => false,
                'message' => 'Kode promo belum berlaku',
            ];
        }

        if ($coupon->valid_until && $now->gt($coupon->valid_until)) {
            return [
                'valid' => false,
                'message' => 'Kode promo sudah kadaluarsa',
            ];
        }

        // Check max uses
        if ($coupon->max_uses && $coupon->used_count >= $coupon->max_uses) {
            return [
                'valid' => false,
                'message' => 'Kode promo sudah habis digunakan',
            ];
        }

        // Check applicable products
        if ($coupon->applicable_product_ids && ! empty($coupon->applicable_product_ids)) {
            $hasApplicableProduct = false;
            foreach ($productIds as $productId) {
                if (in_array($productId, $coupon->applicable_product_ids)) {
                    $hasApplicableProduct = true;
                    break;
                }
            }

            if (! $hasApplicableProduct) {
                return [
                    'valid' => false,
                    'message' => 'Kode promo tidak berlaku untuk produk yang dipilih',
                ];
            }
        }

        return [
            'valid' => true,
            'coupon' => $coupon,
            'message' => 'Kode promo valid',
        ];
    }

    /**
     * Calculate discount amount dari coupon
     *
     * @return int Discount amount in cents
     */
    public function calculateDiscount(Coupon $coupon, int $subtotalCents): int
    {
        if ($coupon->type === 'percent') {
            return (int) round($subtotalCents * ($coupon->value / 100));
        }

        // Fixed discount
        return (int) round($coupon->value * 100); // Convert to cents
    }
}
