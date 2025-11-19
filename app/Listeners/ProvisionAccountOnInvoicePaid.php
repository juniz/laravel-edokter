<?php

namespace App\Listeners;

use App\Events\InvoicePaid;
use App\Jobs\ProvisionAccountJob;
use App\Models\Domain\Order\OrderItem;
use Illuminate\Support\Facades\Log;

class ProvisionAccountOnInvoicePaid
{
    public function handle(InvoicePaid $event): void
    {
        $invoice = $event->invoice;

        // Cari order items yang memiliki subscription
        if ($invoice->order) {
            $orderItems = OrderItem::where('order_id', $invoice->order->id)
                ->whereNotNull('subscription_id')
                ->get();

            foreach ($orderItems as $item) {
                if ($item->subscription_id) {
                    // Dispatch job untuk provisioning
                    ProvisionAccountJob::dispatch($item->subscription_id);
                    Log::info("Provisioning job dispatched for subscription: {$item->subscription_id}");
                }
            }
        }
    }
}
