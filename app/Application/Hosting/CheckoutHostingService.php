<?php

namespace App\Application\Hosting;

use App\Application\Billing\GenerateInvoiceService;
use App\Application\Order\PlaceOrderService;
use App\Domain\Billing\Contracts\InvoiceRepository;
use App\Domain\Billing\Contracts\PaymentAdapterInterface;
use App\Domain\Catalog\Contracts\PlanRepository;
use App\Domain\Catalog\Contracts\ProductRepository;
use App\Domain\Customer\Contracts\CustomerRepository;
use App\Domain\Customer\Contracts\DomainRepository;
use App\Domain\Subscription\Contracts\SubscriptionRepository;
use App\Models\Domain\Billing\Invoice;
use App\Models\Domain\Subscription\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutHostingService
{
    public function __construct(
        private CustomerRepository $customerRepository,
        private ProductRepository $productRepository,
        private PlanRepository $planRepository,
        private DomainRepository $domainRepository,
        private SubscriptionRepository $subscriptionRepository,
        private PlaceOrderService $placeOrderService,
        private GenerateInvoiceService $generateInvoiceService,
        private PaymentAdapterInterface $paymentAdapter,
        private InvoiceRepository $invoiceRepository
    ) {}

    /**
     * Checkout shared hosting dengan domain yang sudah ada atau baru
     *
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, invoice?: Invoice, payment?: \App\Models\Domain\Billing\Payment, subscription?: Subscription}
     */
    public function execute(array $data): array
    {
        try {
            return DB::transaction(function () use ($data) {
                // Validasi customer
                $customer = $this->customerRepository->findByUlid($data['customer_id']);
                if (! $customer) {
                    return [
                        'success' => false,
                        'message' => 'Customer tidak ditemukan.',
                    ];
                }

                // Validasi product (harus shared hosting)
                $product = $this->productRepository->findByUlid($data['product_id']);
                if (! $product || $product->type !== 'hosting_shared') {
                    return [
                        'success' => false,
                        'message' => 'Product tidak valid atau bukan shared hosting.',
                    ];
                }

                // Validasi plan
                $plan = $this->planRepository->findByUlid($data['plan_id']);
                if (! $plan || $plan->product_id !== $product->id) {
                    return [
                        'success' => false,
                        'message' => 'Plan tidak valid.',
                    ];
                }

                // Handle domain
                $domain = null;
                $domainId = $data['domain_id'] ?? null;
                $domainName = $data['domain_name'] ?? null;

                if ($domainId) {
                    // Gunakan domain yang sudah ada
                    $domain = $this->domainRepository->findByUlid($domainId);
                    if (! $domain || $domain->customer_id !== $customer->id) {
                        return [
                            'success' => false,
                            'message' => 'Domain tidak ditemukan atau tidak dimiliki oleh customer.',
                        ];
                    }
                    $domainName = $domain->name;
                } elseif ($domainName) {
                    // Domain baru akan dibuat setelah payment (mirip dengan CheckoutDomainService)
                    // Untuk sekarang, kita hanya validasi format
                    if (! $this->isValidDomainName($domainName)) {
                        return [
                            'success' => false,
                            'message' => 'Format domain tidak valid.',
                        ];
                    }
                } else {
                    return [
                        'success' => false,
                        'message' => 'Domain harus dipilih atau diinput.',
                    ];
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
                            'product_id' => $product->id,
                            'plan_id' => $plan->id,
                            'qty' => 1,
                            'unit_price_cents' => $subtotalCents,
                            'total_cents' => $subtotalCents,
                            'meta' => [
                                'type' => 'hosting_shared',
                                'domain_name' => $domainName,
                                'domain_id' => $domain?->id,
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
                    'notes' => "Pembayaran untuk shared hosting: {$domainName}",
                ]);

                // Add invoice item
                $invoiceItem = $invoice->items()->create([
                    'description' => "Shared Hosting - {$plan->name} ({$plan->billing_cycle})",
                    'qty' => 1,
                    'unit_price_cents' => $subtotalCents,
                    'total_cents' => $subtotalCents,
                    'meta' => [
                        'type' => 'hosting_shared',
                        'product_id' => $product->id,
                        'plan_id' => $plan->id,
                        'domain_name' => $domainName,
                        'domain_id' => $domain?->id,
                        'setup_fee' => $setupFeeCents,
                    ],
                ]);

                // Create subscription (pending, akan diaktifkan setelah payment)
                $billingCycle = $plan->billing_cycle;
                $startAt = now();
                $endAt = $this->calculateEndDate($startAt, $billingCycle);

                $subscription = $this->subscriptionRepository->create([
                    'customer_id' => $customer->id,
                    'product_id' => $product->id,
                    'plan_id' => $plan->id,
                    'status' => 'trialing', // Akan diubah ke active setelah provisioning
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'next_renewal_at' => $endAt,
                    'auto_renew' => $data['auto_renew'] ?? true,
                    'provisioning_status' => 'pending',
                    'meta' => [
                        'domain' => $domainName,
                        'domain_id' => $domain?->id,
                    ],
                ]);

                // Update order item dengan subscription_id
                $order->items()->first()->update([
                    'subscription_id' => $subscription->id,
                ]);

                // Create payment
                $paymentOptions = [
                    'payment_method' => $data['payment_method'] ?? null,
                ];

                $payment = $this->paymentAdapter->createCharge($invoice, $paymentOptions);

                // Update invoice item dengan subscription_id
                $invoiceItem->update([
                    'meta' => array_merge($invoiceItem->meta, [
                        'subscription_id' => $subscription->id,
                    ]),
                ]);

                Log::info('Shared hosting checkout completed', [
                    'subscription_id' => $subscription->id,
                    'domain' => $domainName,
                    'invoice_id' => $invoice->id,
                    'payment_id' => $payment->id,
                ]);

                return [
                    'success' => true,
                    'message' => 'Checkout shared hosting berhasil. Silakan lakukan pembayaran.',
                    'invoice' => $invoice,
                    'payment' => $payment,
                    'subscription' => $subscription,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Shared hosting checkout failed', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal checkout shared hosting: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Validasi format domain name
     */
    private function isValidDomainName(string $domain): bool
    {
        // Basic validation: harus mengandung titik dan karakter valid
        return preg_match('/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?)*\.[a-z]{2,}$/i', $domain) === 1;
    }

    /**
     * Calculate end date berdasarkan billing cycle
     */
    private function calculateEndDate(\Carbon\Carbon $startAt, string $billingCycle): \Carbon\Carbon
    {
        return match ($billingCycle) {
            'monthly' => $startAt->copy()->addMonth(),
            'quarterly' => $startAt->copy()->addMonths(3),
            'semiannually' => $startAt->copy()->addMonths(6),
            'annually' => $startAt->copy()->addYear(),
            'biennially' => $startAt->copy()->addYears(2),
            'triennially' => $startAt->copy()->addYears(3),
            default => $startAt->copy()->addMonth(),
        };
    }
}
