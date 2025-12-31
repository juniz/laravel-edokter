<?php

namespace App\Application\Catalog;

use App\Application\Billing\GenerateInvoiceService;
use App\Application\Order\PlaceOrderService;
use App\Domain\Billing\Contracts\PaymentAdapterInterface;
use App\Domain\Catalog\Contracts\PlanRepository;
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
        private PlanRepository $planRepository
    ) {}

    /**
     * Checkout langsung dari catalog dengan payment
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(Customer $customer, array $data): Payment
    {
        return DB::transaction(function () use ($customer, $data) {
            // Get plan details
            $plan = $this->planRepository->findByUlid($data['plan_id']);

            if (! $plan) {
                throw new \Exception('Plan tidak ditemukan');
            }

            // Calculate totals
            $subtotalCents = $plan->price_cents;
            $setupFeeCents = $plan->setup_fee_cents ?? 0;
            $totalCents = $subtotalCents + $setupFeeCents;

            // Create order
            $order = $this->placeOrderService->execute([
                'customer_id' => $customer->id,
                'currency' => $plan->currency ?? 'IDR',
                'subtotal_cents' => $subtotalCents,
                'discount_cents' => 0,
                'tax_cents' => 0,
                'total_cents' => $totalCents,
                'items' => [
                    [
                        'product_id' => $plan->product_id,
                        'plan_id' => $plan->id,
                        'qty' => 1,
                        'unit_price_cents' => $subtotalCents,
                        'total_cents' => $subtotalCents,
                        'meta' => [
                            'type' => 'catalog',
                            'setup_fee' => $setupFeeCents,
                        ],
                    ],
                ],
            ]);

            // Generate invoice
            $invoice = $this->generateInvoiceService->execute([
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'currency' => $plan->currency ?? 'IDR',
                'subtotal_cents' => $subtotalCents,
                'discount_cents' => 0,
                'tax_cents' => 0,
                'total_cents' => $totalCents,
                'due_at' => now()->addDays(1),
                'notes' => "Pembayaran untuk {$plan->product->name} - {$plan->code}",
            ]);

            // Add invoice item
            $invoice->items()->create([
                'description' => "{$plan->product->name} - {$plan->code}",
                'qty' => 1,
                'unit_price_cents' => $subtotalCents,
                'total_cents' => $subtotalCents,
                'meta' => [
                    'product_id' => $plan->product_id,
                    'plan_id' => $plan->id,
                    'type' => 'catalog',
                    'setup_fee' => $setupFeeCents,
                ],
            ]);

            // Create payment via Midtrans
            $paymentOptions = [
                'payment_method' => $data['payment_method'] ?? 'credit_card',
            ];

            $payment = $this->paymentAdapter->createCharge($invoice, $paymentOptions);

            return $payment;
        });
    }
}
