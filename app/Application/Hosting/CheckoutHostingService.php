<?php

namespace App\Application\Hosting;

use App\Application\Billing\GenerateInvoiceService;
use App\Application\Order\PlaceOrderService;
use App\Domain\Billing\Contracts\InvoiceRepository;
use App\Domain\Billing\Contracts\PaymentAdapterInterface;
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
                if (! $product || $product->productType?->slug !== 'hosting_shared') {
                    return [
                        'success' => false,
                        'message' => 'Product tidak valid atau bukan shared hosting.',
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

                // Validasi durasi
                $durationMonths = $data['duration_months'] ?? 1;
                if ($durationMonths === 1 && ! ($product->duration_1_month_enabled ?? true)) {
                    return [
                        'success' => false,
                        'message' => 'Durasi 1 bulan tidak tersedia untuk product ini.',
                    ];
                }
                if ($durationMonths === 12 && ! ($product->duration_12_months_enabled ?? true)) {
                    return [
                        'success' => false,
                        'message' => 'Durasi 12 bulan tidak tersedia untuk product ini.',
                    ];
                }

                // Calculate totals berdasarkan durasi
                // price_cents sudah dalam bentuk per bulan
                $subtotalCents = $product->price_cents * $durationMonths;
                $setupFeeCents = $product->setup_fee_cents ?? 0;
                $totalCents = $subtotalCents + $setupFeeCents;

                // Create order
                $order = $this->placeOrderService->execute([
                    'customer_id' => $customer->id,
                    'currency' => $product->currency ?? 'IDR',
                    'subtotal_cents' => $subtotalCents,
                    'discount_cents' => 0,
                    'tax_cents' => 0,
                    'total_cents' => $totalCents,
                    'items' => [
                        [
                            'product_id' => $product->id,
                            'qty' => 1,
                            'unit_price_cents' => $subtotalCents,
                            'total_cents' => $subtotalCents,
                            'meta' => [
                                'type' => 'hosting_shared',
                                'domain_name' => $domainName,
                                'domain_id' => $domain?->id,
                                'setup_fee' => $setupFeeCents,
                                'duration_months' => $durationMonths,
                            ],
                        ],
                    ],
                ]);

                // Generate invoice
                $invoice = $this->generateInvoiceService->execute([
                    'order_id' => $order->id,
                    'customer_id' => $customer->id,
                    'currency' => $product->currency ?? 'IDR',
                    'subtotal_cents' => $subtotalCents + $setupFeeCents,
                    'discount_cents' => 0,
                    'tax_cents' => 0,
                    'total_cents' => $totalCents,
                    'due_at' => now()->addDays(1),
                    'notes' => "Pembayaran untuk shared hosting: {$domainName}",
                ]);

                // Add invoice item
                $durationLabel = $durationMonths === 1 ? '1 bulan' : '12 bulan';
                $invoiceItem = $invoice->items()->create([
                    'description' => "Shared Hosting - {$product->name} ({$durationLabel})",
                    'qty' => 1,
                    'unit_price_cents' => $subtotalCents,
                    'total_cents' => $subtotalCents,
                    'meta' => [
                        'type' => 'hosting_shared',
                        'product_id' => $product->id,
                        'domain_name' => $domainName,
                        'domain_id' => $domain?->id,
                        'setup_fee' => $setupFeeCents,
                        'duration_months' => $durationMonths,
                    ],
                ]);

                // Create subscription (pending, akan diaktifkan setelah payment)
                $startAt = now();
                $endAt = $startAt->copy()->addMonths($durationMonths);

                $subscription = $this->subscriptionRepository->create([
                    'customer_id' => $customer->id,
                    'product_id' => $product->id,
                    'status' => 'trialing', // Akan diubah ke active setelah provisioning
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'next_renewal_at' => $endAt,
                    'auto_renew' => $data['auto_renew'] ?? true,
                    'provisioning_status' => 'pending',
                    'meta' => [
                        'domain' => $domainName,
                        'domain_id' => $domain?->id,
                        'duration_months' => $durationMonths,
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
}
