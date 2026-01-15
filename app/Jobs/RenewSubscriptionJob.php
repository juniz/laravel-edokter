<?php

namespace App\Jobs;

use App\Application\Billing\GenerateInvoiceService;
use App\Domain\Billing\Contracts\InvoiceRepository;
use App\Domain\Subscription\Contracts\SubscriptionRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RenewSubscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(
        SubscriptionRepository $subscriptionRepository,
        InvoiceRepository $invoiceRepository,
        GenerateInvoiceService $generateInvoiceService
    ): void {
        $subscriptions = $subscriptionRepository->findDueForRenewal();

        foreach ($subscriptions as $subscription) {
            try {
                // Generate invoice untuk renewal
                // Hitung harga berdasarkan durasi dari meta atau default 1 bulan
                $durationMonths = $subscription->meta['duration_months'] ?? 1;
                $subtotalCents = $subscription->product->price_cents * $durationMonths;

                $invoice = $generateInvoiceService->execute([
                    'customer_id' => $subscription->customer_id,
                    'currency' => $subscription->product->currency ?? 'IDR',
                    'subtotal_cents' => $subtotalCents,
                    'total_cents' => $subtotalCents,
                    'due_at' => $subscription->next_renewal_at,
                ]);

                Log::info("Invoice generated for subscription renewal: {$subscription->id}");

                // TODO: Charge payment gateway jika auto-renew aktif
                // TODO: Update subscription dates setelah payment berhasil
            } catch (\Exception $e) {
                Log::error("Failed to renew subscription {$subscription->id}: {$e->getMessage()}");
            }
        }
    }
}
