<?php

namespace App\Listeners;

use App\Events\InvoicePaid;

class UpdateOrderStatusOnInvoicePaid
{
    public function handle(InvoicePaid $event): void
    {
        $invoice = $event->invoice;

        $order = $invoice->order;

        if (! $order) {
            return;
        }

        if ($order->status !== 'paid') {
            $order->update(['status' => 'paid']);
        }
    }
}

