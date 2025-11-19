<?php

namespace App\Infrastructure\Payments\Adapters;

use App\Domain\Billing\Contracts\PaymentAdapterInterface;
use App\Domain\Billing\Contracts\PaymentRepository;
use App\Models\Domain\Billing\Invoice;
use App\Models\Domain\Billing\Payment;
use Illuminate\Support\Facades\Log;

class TripayAdapter implements PaymentAdapterInterface
{
    public function __construct(
        private PaymentRepository $paymentRepository
    ) {}

    public function createCharge(Invoice $invoice, array $options): Payment
    {
        // TODO: Implementasi Tripay API
        $apiKey = config('payment.tripay.api_key');
        $merchantRef = 'INV-' . $invoice->id;

        // Simulasi create payment
        $payment = $this->paymentRepository->create([
            'invoice_id' => $invoice->id,
            'provider' => 'tripay',
            'provider_ref' => $merchantRef,
            'amount_cents' => $invoice->total_cents,
            'status' => 'pending',
            'raw_payload' => [
                'merchant_ref' => $merchantRef,
                'payment_url' => 'https://tripay.co.id/checkout/...',
            ],
        ]);

        Log::info("Tripay payment created for invoice: {$invoice->id}");

        return $payment;
    }

    public function handleWebhook(array $payload): ?Payment
    {
        // TODO: Implementasi webhook verification dan handling
        $merchantRef = $payload['merchant_ref'] ?? null;
        
        if (!$merchantRef) {
            return null;
        }

        $payment = Payment::where('provider_ref', $merchantRef)->first();
        
        if ($payment && $payload['status'] === 'PAID') {
            $this->paymentRepository->markAsSucceeded($payment, $payload);
        }

        return $payment;
    }
}

