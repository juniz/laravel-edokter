<?php

namespace App\Domain\Billing\Contracts;

use App\Models\Domain\Billing\Invoice;
use App\Models\Domain\Billing\Payment;

interface PaymentAdapterInterface
{
    public function createCharge(Invoice $invoice, array $options): Payment;
    public function handleWebhook(array $payload): ?Payment;
    public function checkStatus(Payment $payment): ?Payment;
}

