<?php

namespace App\Application\Billing;

use App\Domain\Billing\Contracts\InvoiceRepository;
use App\Domain\Billing\Contracts\PaymentRepository;
use App\Models\Domain\Billing\Invoice;
use Illuminate\Support\Str;

class GenerateInvoiceService
{
    public function __construct(
        private InvoiceRepository $invoiceRepository
    ) {}

    public function execute(array $data): Invoice
    {
        $invoiceNumber = 'INV-' . strtoupper(Str::random(10));
        
        return $this->invoiceRepository->create([
            'order_id' => $data['order_id'] ?? null,
            'customer_id' => $data['customer_id'],
            'number' => $invoiceNumber,
            'status' => 'unpaid',
            'currency' => $data['currency'] ?? 'IDR',
            'subtotal_cents' => $data['subtotal_cents'],
            'discount_cents' => $data['discount_cents'] ?? 0,
            'tax_cents' => $data['tax_cents'] ?? 0,
            'total_cents' => $data['total_cents'],
            'due_at' => $data['due_at'] ?? now()->addDays(7),
            'notes' => $data['notes'] ?? null,
        ]);
    }
}

