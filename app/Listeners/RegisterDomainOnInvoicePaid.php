<?php

namespace App\Listeners;

use App\Application\Rdash\Domain\RegisterDomainViaRdashService;
use App\Domain\Billing\Contracts\InvoiceRepository;
use App\Events\InvoicePaid;
use App\Models\Domain\Customer\Domain;
use Illuminate\Support\Facades\Log;

class RegisterDomainOnInvoicePaid
{
    public function __construct(
        private InvoiceRepository $invoiceRepository,
        private RegisterDomainViaRdashService $registerDomainService
    ) {}

    public function handle(InvoicePaid $event): void
    {
        $invoice = $event->invoice;

        // Cek apakah invoice ini untuk domain purchase
        $domainItem = $invoice->items()
            ->whereJsonContains('meta->type', 'domain')
            ->first();

        if (! $domainItem) {
            // Bukan invoice untuk domain, skip
            return;
        }

        $domainMeta = $domainItem->meta;
        $domainName = $domainMeta['domain_name'] ?? null;
        $domainId = null;

        // Cari domain dari invoice notes atau dari meta
        if (isset($domainMeta['domain_id'])) {
            $domainId = $domainMeta['domain_id'];
        } else {
            // Cari domain berdasarkan name dan customer
            $domain = Domain::where('name', $domainName)
                ->where('customer_id', $invoice->customer_id)
                ->where('status', 'pending')
                ->first();

            if ($domain) {
                $domainId = $domain->id;
            }
        }

        if (! $domainId || ! $domainName) {
            Log::warning('Domain not found for invoice', [
                'invoice_id' => $invoice->id,
                'domain_name' => $domainName,
            ]);

            return;
        }

        $domain = Domain::find($domainId);
        if (! $domain) {
            Log::warning('Domain not found by ID', [
                'domain_id' => $domainId,
                'invoice_id' => $invoice->id,
            ]);

            return;
        }

        // Pastikan domain belum ter-register
        if ($domain->rdash_sync_status === 'synced' && $domain->rdash_domain_id) {
            Log::info('Domain already registered', [
                'domain_id' => $domain->id,
                'rdash_domain_id' => $domain->rdash_domain_id,
            ]);

            return;
        }

        try {
            // Prepare data untuk register domain
            $registerData = [
                'name' => $domainName,
                'period' => $domainMeta['period'] ?? 1,
                'customer_id' => $invoice->customer_id,
                'auto_renew' => $domain->auto_renew,
            ];

            // Add nameservers jika ada
            if (isset($domainMeta['nameserver']) && is_array($domainMeta['nameserver'])) {
                foreach ($domainMeta['nameserver'] as $index => $ns) {
                    $registerData["nameserver[{$index}]"] = $ns;
                }
            }

            // Add optional fields
            if (isset($domainMeta['buy_whois_protection'])) {
                $registerData['buy_whois_protection'] = $domainMeta['buy_whois_protection'];
            }

            if (isset($domainMeta['include_premium_domains'])) {
                $registerData['include_premium_domains'] = $domainMeta['include_premium_domains'];
            }

            if (isset($domainMeta['registrant_contact_id'])) {
                $registerData['registrant_contact_id'] = $domainMeta['registrant_contact_id'];
            }

            // Register domain ke RDASH
            $result = $this->registerDomainService->execute($registerData);

            if ($result['success'] && isset($result['domain'])) {
                Log::info('Domain registered successfully after payment', [
                    'domain_id' => $domain->id,
                    'invoice_id' => $invoice->id,
                    'rdash_domain_id' => $result['domain']->rdash_domain_id ?? null,
                ]);
            } else {
                Log::error('Failed to register domain after payment', [
                    'domain_id' => $domain->id,
                    'invoice_id' => $invoice->id,
                    'message' => $result['message'] ?? 'Unknown error',
                ]);

                // Update domain status to failed
                $domain->update([
                    'rdash_sync_status' => 'failed',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception while registering domain after payment', [
                'domain_id' => $domain->id,
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Update domain status to failed
            $domain->update([
                'rdash_sync_status' => 'failed',
            ]);
        }
    }
}
