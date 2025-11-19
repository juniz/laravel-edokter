<?php

namespace App\Listeners;

use App\Events\InvoicePaid;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendInvoiceEmail
{
    public function handle(InvoicePaid $event): void
    {
        $invoice = $event->invoice;
        $customer = $invoice->customer;

        // TODO: Implementasi email notification
        Log::info("Sending invoice paid email to: {$customer->email}");

        // Contoh:
        // Mail::to($customer->email)->send(new InvoicePaidMail($invoice));
    }
}
