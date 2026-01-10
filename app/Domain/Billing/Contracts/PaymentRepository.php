<?php

namespace App\Domain\Billing\Contracts;

use App\Models\Domain\Billing\Payment;

interface PaymentRepository
{
    public function create(array $data): Payment;

    public function findByUlid(string $id): ?Payment;

    public function findByInvoice(string $invoiceId): array;

    public function markAsSucceeded(Payment $payment, array $payload): void;
}
