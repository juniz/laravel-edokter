<?php

namespace App\Application\Domain;

use App\Application\Billing\GenerateInvoiceService;
use App\Application\Order\PlaceOrderService;
use App\Domain\Billing\Contracts\InvoiceRepository;
use App\Domain\Billing\Contracts\PaymentAdapterInterface;
use App\Domain\Customer\Contracts\CustomerRepository;
use App\Domain\Rdash\Account\Contracts\AccountRepository;
use App\Models\Domain\Billing\Invoice;
use App\Models\Domain\Customer\Domain;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutDomainService
{
    public function __construct(
        private CustomerRepository $customerRepository,
        private AccountRepository $accountRepository,
        private PlaceOrderService $placeOrderService,
        private GenerateInvoiceService $generateInvoiceService,
        private PaymentAdapterInterface $paymentAdapter,
        private InvoiceRepository $invoiceRepository
    ) {}

    /**
     * Checkout domain dengan payment
     *
     * @param  array<string, mixed>  $data
     * @return array{success: bool, message: string, invoice?: Invoice, payment?: \App\Models\Domain\Billing\Payment, domain?: Domain}
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

                // Pastikan customer sudah sync ke RDASH
                if (! $customer->rdash_customer_id) {
                    return [
                        'success' => false,
                        'message' => 'Customer belum di-sync ke RDASH. Silakan sync customer terlebih dahulu.',
                    ];
                }

                // Get domain price dari RDASH
                $domainName = $data['name'];
                $extension = $this->extractExtension($domainName);
                $domainPrice = $this->getDomainPrice($extension);

                if (! $domainPrice) {
                    return [
                        'success' => false,
                        'message' => 'Harga domain tidak ditemukan untuk extension: ' . $extension,
                    ];
                }

                // Calculate total price from registration array
                $period = $data['period'] ?? 1;
                $periodKey = (string) $period;

                // Validate registration array structure
                if (! is_array($domainPrice->registration) || empty($domainPrice->registration)) {
                    Log::error('Domain price registration array is invalid or empty', [
                        'domain_price_id' => $domainPrice->id,
                        'registration' => $domainPrice->registration,
                        'period' => $period,
                    ]);

                    return [
                        'success' => false,
                        'message' => 'Harga domain tidak valid. Silakan hubungi administrator.',
                    ];
                }

                // Try to get price for the specific period first
                $price = null;
                if (isset($domainPrice->registration[$periodKey]) && is_numeric($domainPrice->registration[$periodKey])) {
                    $price = (int) $domainPrice->registration[$periodKey];
                } elseif (isset($domainPrice->registration['1']) && is_numeric($domainPrice->registration['1'])) {
                    // Fallback: calculate from 1-year price
                    $oneYearPrice = (int) $domainPrice->registration['1'];
                    $price = $oneYearPrice * $period;
                } else {
                    // No valid price found - fail the transaction
                    Log::error('Domain price not found for period', [
                        'domain_price_id' => $domainPrice->id,
                        'registration' => $domainPrice->registration,
                        'period' => $period,
                        'period_key' => $periodKey,
                    ]);

                    return [
                        'success' => false,
                        'message' => 'Harga domain tidak ditemukan untuk periode yang dipilih. Silakan pilih periode lain atau hubungi administrator.',
                    ];
                }

                // Validate price is greater than zero
                if ($price <= 0) {
                    Log::error('Domain price is zero or negative', [
                        'domain_price_id' => $domainPrice->id,
                        'price' => $price,
                        'period' => $period,
                        'registration' => $domainPrice->registration,
                    ]);

                    return [
                        'success' => false,
                        'message' => 'Harga domain tidak valid. Silakan hubungi administrator.',
                    ];
                }

                $subtotalCents = $price * 100; // Convert to cents
                $totalCents = $subtotalCents; // No tax/discount for now

                // Create order
                $order = $this->placeOrderService->execute([
                    'customer_id' => $customer->id,
                    'currency' => $domainPrice->currency ?? 'IDR',
                    'subtotal_cents' => $subtotalCents,
                    'discount_cents' => 0,
                    'tax_cents' => 0,
                    'total_cents' => $totalCents,
                    'items' => [
                        [
                            'product_id' => null, // Domain tidak punya product
                            'plan_id' => null,
                            'qty' => 1,
                            'unit_price_cents' => $subtotalCents,
                            'total_cents' => $subtotalCents,
                            'meta' => [
                                'type' => 'domain',
                                'domain_name' => $domainName,
                                'period' => $period,
                                'extension' => $extension,
                                'rdash_price_id' => $domainPrice->id,
                            ],
                        ],
                    ],
                ]);

                // Generate invoice
                $invoice = $this->generateInvoiceService->execute([
                    'order_id' => $order->id,
                    'customer_id' => $customer->id,
                    'currency' => $domainPrice->currency ?? 'IDR',
                    'subtotal_cents' => $subtotalCents,
                    'discount_cents' => 0,
                    'tax_cents' => 0,
                    'total_cents' => $totalCents,
                    'due_at' => now()->addDays(1), // Due date 1 hari
                    'notes' => "Pembayaran untuk domain: {$domainName}",
                ]);

                // Add invoice item (domain_id will be added after domain creation)
                $invoiceItem = $invoice->items()->create([
                    'description' => "Domain {$domainName} ({$period} tahun)",
                    'qty' => 1,
                    'unit_price_cents' => $subtotalCents,
                    'total_cents' => $subtotalCents,
                    'meta' => [
                        'type' => 'domain',
                        'domain_name' => $domainName,
                        'period' => $period,
                        'extension' => $extension,
                        'rdash_price_id' => $domainPrice->id,
                        'nameserver' => $data['nameserver'] ?? null,
                        'buy_whois_protection' => $data['buy_whois_protection'] ?? false,
                        'include_premium_domains' => $data['include_premium_domains'] ?? false,
                        'registrant_contact_id' => $data['registrant_contact_id'] ?? null,
                        'auto_renew' => $data['auto_renew'] ?? false,
                    ],
                ]);

                // Create payment via Midtrans
                $paymentOptions = [
                    'payment_method' => $data['payment_method'] ?? null,
                ];

                $payment = $this->paymentAdapter->createCharge($invoice, $paymentOptions);

                // Create domain record setelah payment berhasil (dalam transaction)
                // Jika payment gagal, transaction akan di-rollback termasuk domain
                $domain = Domain::create([
                    'customer_id' => $customer->id,
                    'name' => $domainName,
                    'status' => 'pending',
                    'auto_renew' => $data['auto_renew'] ?? false,
                    'rdash_sync_status' => 'pending',
                ]);

                // Update invoice item dengan domain_id
                $invoiceItem->update([
                    'meta' => array_merge($invoiceItem->meta, [
                        'domain_id' => $domain->id,
                    ]),
                ]);

                // Link domain ke invoice via meta
                $invoice->update([
                    'notes' => $invoice->notes . "\nDomain ID: {$domain->id}",
                ]);

                Log::info('Domain checkout completed', [
                    'domain' => $domainName,
                    'invoice_id' => $invoice->id,
                    'payment_id' => $payment->id,
                ]);

                return [
                    'success' => true,
                    'message' => 'Checkout domain berhasil. Silakan lakukan pembayaran.',
                    'invoice' => $invoice,
                    'payment' => $payment,
                    'domain' => $domain,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Domain checkout failed', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal checkout domain: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Extract extension dari domain name
     */
    private function extractExtension(string $domainName): string
    {
        $parts = explode('.', $domainName);
        if (count($parts) < 2) {
            return '';
        }

        // Ambil extension (bagian terakhir)
        return '.' . end($parts);
    }

    /**
     * Get domain price dari RDASH
     */
    private function getDomainPrice(string $extension): ?\App\Domain\Rdash\Account\ValueObjects\DomainPrice
    {
        try {
            $result = $this->accountRepository->getPrices([
                'domainExtension[extension]' => $extension,
            ]);

            if (empty($result['data'])) {
                return null;
            }

            // Return price pertama yang ditemukan
            return $result['data'][0];
        } catch (\Exception $e) {
            Log::error('Failed to get domain price from RDASH', [
                'extension' => $extension,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
