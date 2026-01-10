<?php

namespace App\Application\Order;

use App\Application\Billing\GenerateInvoiceService;
use App\Domain\Billing\Contracts\PaymentAdapterInterface;
use App\Models\Domain\Billing\Payment;
use App\Models\Domain\Order\Cart;
use Illuminate\Support\Facades\DB;

class CheckoutCartService
{
    public function __construct(
        private PlaceOrderService $placeOrderService,
        private GenerateInvoiceService $generateInvoiceService,
        private PaymentAdapterInterface $paymentAdapter
    ) {}

    /**
     * Checkout cart dengan payment
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(Cart $cart, array $data): Payment
    {
        return DB::transaction(function () use ($cart, $data) {
            // Load cart items with relationships
            $cart->load(['items.product', 'items.plan']);

            if ($cart->items->isEmpty()) {
                throw new \Exception('Cart kosong. Silakan tambahkan item terlebih dahulu.');
            }

            $totals = $cart->totals_json ?? [
                'subtotal' => 0,
                'setup_fee' => 0,
                'discount' => 0,
                'tax' => 0,
                'total' => 0,
            ];

            // Prepare order items
            $orderItems = [];
            foreach ($cart->items as $cartItem) {
                $itemTotal = $cartItem->unit_price_cents * $cartItem->qty;
                $setupFeeCents = $cartItem->plan?->setup_fee_cents ?? 0;

                $orderItems[] = [
                    'product_id' => $cartItem->product_id,
                    'plan_id' => $cartItem->plan_id,
                    'qty' => $cartItem->qty,
                    'unit_price_cents' => $cartItem->unit_price_cents,
                    'total_cents' => $itemTotal,
                    'meta' => array_merge($cartItem->meta ?? [], [
                        'setup_fee' => $setupFeeCents,
                    ]),
                ];
            }

            // Create order
            $order = $this->placeOrderService->execute([
                'customer_id' => $cart->customer_id,
                'currency' => $cart->currency ?? 'IDR',
                'subtotal_cents' => $totals['subtotal'],
                'discount_cents' => $totals['discount'] ?? 0,
                'tax_cents' => $totals['tax'] ?? 0,
                'total_cents' => $totals['total'],
                'coupon_id' => $cart->coupon_id,
                'items' => $orderItems,
            ]);

            // Calculate invoice totals (include setup fee in subtotal for invoice)
            $invoiceSubtotal = $totals['subtotal'] + ($totals['setup_fee'] ?? 0);

            // Generate invoice
            $invoice = $this->generateInvoiceService->execute([
                'order_id' => $order->id,
                'customer_id' => $cart->customer_id,
                'currency' => $cart->currency ?? 'IDR',
                'subtotal_cents' => $invoiceSubtotal,
                'discount_cents' => $totals['discount'] ?? 0,
                'tax_cents' => $totals['tax'] ?? 0,
                'total_cents' => $totals['total'],
                'due_at' => now()->addDays(1),
                'notes' => 'Pembayaran untuk order dari cart',
            ]);

            // Add invoice items
            foreach ($cart->items as $cartItem) {
                $itemTotal = $cartItem->unit_price_cents * $cartItem->qty;
                $setupFeeCents = $cartItem->plan?->setup_fee_cents ?? 0;
                $description = $cartItem->product->name;
                if ($cartItem->plan) {
                    $description .= ' - '.$cartItem->plan->code;
                }

                // Add product item
                $invoice->items()->create([
                    'description' => $description,
                    'qty' => $cartItem->qty,
                    'unit_price_cents' => $cartItem->unit_price_cents,
                    'total_cents' => $itemTotal,
                    'meta' => array_merge($cartItem->meta ?? [], [
                        'product_id' => $cartItem->product_id,
                        'plan_id' => $cartItem->plan_id,
                        'type' => 'cart',
                    ]),
                ]);

                // Add setup fee item if exists
                if ($setupFeeCents > 0) {
                    $invoice->items()->create([
                        'description' => "Setup Fee - {$description}",
                        'qty' => 1,
                        'unit_price_cents' => $setupFeeCents,
                        'total_cents' => $setupFeeCents,
                        'meta' => [
                            'product_id' => $cartItem->product_id,
                            'plan_id' => $cartItem->plan_id,
                            'type' => 'setup_fee',
                        ],
                    ]);
                }
            }

            // Create payment
            $paymentOptions = [
                'payment_method' => $data['payment_method'] ?? 'credit_card',
            ];

            $payment = $this->paymentAdapter->createCharge($invoice, $paymentOptions);

            // Clear cart after successful checkout
            $cart->items()->delete();
            $cart->update([
                'totals_json' => [
                    'subtotal' => 0,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => 0,
                ],
            ]);

            return $payment;
        });
    }
}
