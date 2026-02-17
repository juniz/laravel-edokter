<?php

namespace App\Infrastructure\Payments\Adapters;

use App\Domain\Billing\Contracts\PaymentAdapterInterface;
use App\Domain\Billing\Contracts\PaymentRepository;
use App\Models\Domain\Billing\Invoice;
use App\Models\Domain\Billing\Payment;

class ManualTransferAdapter implements PaymentAdapterInterface
{
    public function __construct(
        private PaymentRepository $paymentRepository
    ) {}

    public function createCharge(Invoice $invoice, array $options): Payment
    {
        return $this->paymentRepository->create([
            'invoice_id' => $invoice->id,
            'provider' => 'manual',
            'provider_ref' => null,
            'amount_cents' => $invoice->total_cents,
            'status' => 'pending',
        ]);
    }

    public function handleWebhook(array $payload): ?Payment
    {
        // Manual transfer tidak memiliki webhook
        return null;
    }

    public function checkStatus(Payment $payment): ?Payment
    {
        $payment->refresh();

        return $payment;
    }
}
