<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Billing\Contracts\InvoiceRepository as InvoiceRepositoryContract;
use App\Models\Domain\Billing\Invoice;

class InvoiceRepository implements InvoiceRepositoryContract
{
    public function create(array $data): Invoice
    {
        return Invoice::create($data);
    }

    public function findByUlid(string $id): ?Invoice
    {
        return Invoice::find($id);
    }

    public function findByNumber(string $number): ?Invoice
    {
        return Invoice::where('number', $number)->first();
    }

    public function findByCustomer(string $customerId): array
    {
        return Invoice::where('customer_id', $customerId)->get()->all();
    }

    public function markAsPaid(Invoice $invoice): void
    {
        $invoice->update(['status' => 'paid']);
    }
}
