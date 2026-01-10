<?php

namespace App\Domain\Billing\Contracts;

use App\Models\Domain\Billing\Invoice;

interface InvoiceRepository
{
    public function create(array $data): Invoice;

    public function findByUlid(string $id): ?Invoice;

    public function findByNumber(string $number): ?Invoice;

    public function findByCustomer(string $customerId): array;

    public function markAsPaid(Invoice $invoice): void;
}
