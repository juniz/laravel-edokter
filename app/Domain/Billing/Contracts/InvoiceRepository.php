<?php

namespace App\Domain\Billing\Contracts;

use App\Models\Domain\Billing\Invoice;
use App\Models\Domain\Billing\Payment;

interface InvoiceRepository
{
    public function create(array $data): Invoice;
    public function findByUlid(string $id): ?Invoice;
    public function findByNumber(string $number): ?Invoice;
    public function findByCustomer(string $customerId): array;
    public function markAsPaid(Invoice $invoice): void;
}

interface PaymentRepository
{
    public function create(array $data): Payment;
    public function findByUlid(string $id): ?Payment;
    public function findByInvoice(string $invoiceId): array;
    public function markAsSucceeded(Payment $payment, array $payload): void;
}

