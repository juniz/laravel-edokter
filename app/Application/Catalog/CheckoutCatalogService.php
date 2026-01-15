<?php

namespace App\Application\Catalog;

use App\Application\Billing\GenerateInvoiceService;
use App\Application\Order\PlaceOrderService;
use App\Application\Order\ValidateCouponService;
use App\Domain\Billing\Contracts\PaymentAdapterInterface;
use App\Domain\Catalog\Contracts\ProductRepository;
use App\Models\Domain\Billing\Invoice;
use App\Models\Domain\Billing\Payment;
use App\Models\Domain\Customer\Customer;
use Illuminate\Support\Facades\DB;

class CheckoutCatalogService
{
    public function __construct(
        private PlaceOrderService $placeOrderService,
        private GenerateInvoiceService $generateInvoiceService,
        private PaymentAdapterInterface $paymentAdapter,
        private ProductRepository $productRepository,
        private ValidateCouponService $validateCouponService
    ) {}

    /**
     * Checkout langsung dari catalog dengan payment
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(Customer $customer, array $data): Payment
    {
        return DB::transaction(function () use ($customer, $data) {
            // Get product details
            $product = $this->productRepository->findByUlid($data['product_id']);

            if (! $product) {
                throw new \Exception('Product tidak ditemukan');
            }

            // Validasi durasi
            $durationMonths = $data['duration_months'] ?? 1;
            if ($durationMonths === 1 && ! ($product->duration_1_month_enabled ?? true)) {
                throw new \Exception('Durasi 1 bulan tidak tersedia untuk product ini.');
            }
            if ($durationMonths === 12 && ! ($product->duration_12_months_enabled ?? true)) {
                throw new \Exception('Durasi 12 bulan tidak tersedia untuk product ini.');
            }

            // Calculate totals berdasarkan durasi
            // price_cents sudah dalam bentuk per bulan
            $originalSubtotalCents = $product->price_cents * $durationMonths;

            // Apply annual discount if duration is 12 months
            $annualDiscountCents = 0;
            if ($durationMonths === 12 && ($product->annual_discount_percent ?? 0) > 0) {
                $annualDiscountCents = (int) round($originalSubtotalCents * ($product->annual_discount_percent / 100));
            }

            $setupFeeCents = $product->setup_fee_cents ?? 0;

            // Apply coupon discount if provided (on top of annual discount)
            // Calculate coupon discount based on original subtotal + setup fee
            $couponDiscountCents = 0;
            $couponId = null;
            if (isset($data['coupon_code']) && ! empty($data['coupon_code'])) {
                $productIds = [$product->id];
                $validation = $this->validateCouponService->validate($data['coupon_code'], $productIds);

                if ($validation['valid'] && $validation['coupon']) {
                    $couponId = $validation['coupon']->id;
                    $couponDiscountCents = $this->validateCouponService->calculateDiscount(
                        $validation['coupon'],
                        $originalSubtotalCents + $setupFeeCents
                    );
                }
            }

            // Total discount = annual discount + coupon discount
            $discountCents = $annualDiscountCents + $couponDiscountCents;

            // Calculate PPH (Pajak Penghasilan) - get from database settings or config
            $setting = \App\Models\Domain\Shared\Setting::where('key', 'billing_settings')->first();
            $pphRate = $setting?->value['pph_rate'] ?? config('billing.pph_rate', 0.11);

            // Subtotal setelah diskon (untuk perhitungan pajak dan total)
            $subtotalAfterDiscountCents = $originalSubtotalCents - $discountCents;

            // Calculate tax based on amount after discount
            $taxableAmount = $subtotalAfterDiscountCents + $setupFeeCents;
            $taxCents = (int) round($taxableAmount * $pphRate);

            // Total = subtotal setelah diskon + setup fee + tax
            $totalCents = $subtotalAfterDiscountCents + $setupFeeCents + $taxCents;

            // Create order
            $order = $this->placeOrderService->execute([
                'customer_id' => $customer->id,
                'currency' => $product->currency ?? 'IDR',
                'subtotal_cents' => $originalSubtotalCents,
                'discount_cents' => $discountCents,
                'tax_cents' => $taxCents,
                'total_cents' => $totalCents,
                'coupon_id' => $couponId,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'qty' => 1,
                        'unit_price_cents' => $subtotalAfterDiscountCents,
                        'total_cents' => $subtotalAfterDiscountCents,
                        'meta' => [
                            'type' => 'catalog',
                            'setup_fee' => $setupFeeCents,
                            'duration_months' => $durationMonths,
                            'original_subtotal' => $originalSubtotalCents,
                            'original_unit_price' => $originalSubtotalCents,
                            'discount_applied' => $discountCents,
                        ],
                    ],
                ],
            ]);

            // Generate invoice
            // Subtotal should be original amount before discount (accounting semantics)
            $invoice = $this->generateInvoiceService->execute([
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'currency' => $product->currency ?? 'IDR',
                'subtotal_cents' => $originalSubtotalCents + $setupFeeCents,
                'discount_cents' => $discountCents,
                'tax_cents' => $taxCents,
                'total_cents' => $totalCents,
                'due_at' => now()->addDays(1),
                'notes' => "Pembayaran untuk {$product->name}",
            ]);

            // Add invoice items
            // Midtrans requires gross_amount to equal sum of item_details
            // We need to ensure sum of all item_details equals invoice total_cents
            $durationLabel = $durationMonths === 1 ? '1 bulan' : '12 bulan';

            // Calculate product price after discount (for item_details)
            // Invoice total = (original_subtotal + setup_fee - discount) + tax
            // We'll create items that sum up to the total
            $productPriceAfterDiscount = $subtotalAfterDiscountCents + $setupFeeCents;

            // Build description with discount info
            $description = "{$product->name} ({$durationLabel})";
            if ($annualDiscountCents > 0) {
                $description .= " - Diskon Tahunan {$product->annual_discount_percent}%";
            }
            if ($couponDiscountCents > 0) {
                $description .= ' - Diskon Kupon';
            }

            // Main product item (subtotal + setup fee - discount)
            $invoice->items()->create([
                'description' => $description,
                'qty' => 1,
                'unit_price_cents' => $productPriceAfterDiscount,
                'total_cents' => $productPriceAfterDiscount,
                'meta' => [
                    'product_id' => $product->id,
                    'type' => 'catalog',
                    'setup_fee' => $setupFeeCents,
                    'duration_months' => $durationMonths,
                    'original_subtotal' => $originalSubtotalCents,
                    'annual_discount' => $annualDiscountCents,
                    'coupon_discount' => $couponDiscountCents,
                    'discount_applied' => $discountCents,
                ],
            ]);

            // Add tax as separate item (required to match gross_amount)
            if ($taxCents > 0) {
                $invoice->items()->create([
                    'description' => 'Pajak (PPH)',
                    'qty' => 1,
                    'unit_price_cents' => $taxCents,
                    'total_cents' => $taxCents,
                    'meta' => [
                        'type' => 'tax',
                        'pph_rate' => $pphRate,
                    ],
                ]);
            }

            // Verify: productPriceAfterDiscount + taxCents should equal totalCents
            // ($subtotalAfterDiscountCents + $setupFeeCents) + $taxCents = $totalCents ✓

            // Create payment via Midtrans
            $paymentOptions = [
                'payment_method' => $data['payment_method'] ?? 'credit_card',
            ];

            $payment = $this->paymentAdapter->createCharge($invoice, $paymentOptions);

            return $payment;
        });
    }
}
