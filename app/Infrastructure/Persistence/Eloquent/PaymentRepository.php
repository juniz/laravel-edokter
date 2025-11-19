<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Billing\Contracts\PaymentRepository as PaymentRepositoryContract;
use App\Models\Domain\Billing\Payment;

class PaymentRepository implements PaymentRepositoryContract
{
    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    public function findByUlid(string $id): ?Payment
    {
        return Payment::find($id);
    }

    public function findByInvoice(string $invoiceId): array
    {
        return Payment::where('invoice_id', $invoiceId)->get()->all();
    }

    public function markAsSucceeded(Payment $payment, array $payload): void
    {
        $payment->update([
            'status' => 'succeeded',
            'paid_at' => now(),
            'raw_payload' => $payload,
        ]);
    }
}

