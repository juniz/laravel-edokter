<?php

namespace App\Infrastructure\Payments\Adapters;

use App\Domain\Billing\Contracts\PaymentAdapterInterface;
use App\Domain\Billing\Contracts\PaymentRepository;
use App\Models\Domain\Billing\Invoice;
use App\Models\Domain\Billing\Payment;
use Illuminate\Support\Facades\Log;

class XenditAdapter implements PaymentAdapterInterface
{
    public function __construct(
        private PaymentRepository $paymentRepository
    ) {}

    public function createCharge(Invoice $invoice, array $options): Payment
    {
        // TODO: Implementasi Xendit API
        $apiKey = config('payment.xendit.api_key');
        $externalId = 'INV-' . $invoice->id;

        // Simulasi create payment
        $payment = $this->paymentRepository->create([
            'invoice_id' => $invoice->id,
            'provider' => 'xendit',
            'provider_ref' => $externalId,
            'amount_cents' => $invoice->total_cents,
            'status' => 'pending',
            'raw_payload' => [
                'external_id' => $externalId,
                'payment_url' => 'https://checkout.xendit.co/web/...',
            ],
        ]);

        Log::info("Xendit payment created for invoice: {$invoice->id}");

        return $payment;
    }

    public function handleWebhook(array $payload): ?Payment
    {
        // TODO: Implementasi webhook verification dan handling
        $externalId = $payload['external_id'] ?? null;
        
        if (!$externalId) {
            return null;
        }

        $payment = Payment::where('provider_ref', $externalId)->first();
        
        if ($payment && $payload['status'] === 'PAID') {
            $this->paymentRepository->markAsSucceeded($payment, $payload);
        }

        return $payment;
    }
}

