<?php

namespace App\Console\Commands;

use App\Jobs\ProvisionAccountJob;
use App\Models\Domain\Order\OrderItem;
use App\Models\Domain\Subscription\Subscription;
use Illuminate\Console\Command;

class BackfillCatalogSubscriptions extends Command
{
    protected $signature = 'subscriptions:backfill-catalog {--dry-run : Only show what will be changed}';

    protected $description = 'Backfill subscriptions for catalog orders without subscriptions';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $query = OrderItem::whereNull('subscription_id')
            ->where('meta->type', 'catalog')
            ->with(['order.customer', 'order.invoices', 'product']);

        $total = $query->count();

        if ($total === 0) {
            $this->info('No catalog order items without subscription found.');

            return Command::SUCCESS;
        }

        $this->info("Found {$total} catalog order items without subscription.");

        $created = 0;
        $skipped = 0;

        $items = $query->get();

        foreach ($items as $item) {
            $order = $item->order;
            $customer = $order?->customer;
            $product = $item->product;

            if (! $order || ! $customer || ! $product) {
                $skipped++;
                $this->warn("Skipping item {$item->id} because order, customer, or product is missing.");

                continue;
            }

            if (Subscription::where('customer_id', $customer->id)->where('product_id', $product->id)->whereHas('cycles', function ($q) use ($order) {
                $q->where('invoice_id', $order->invoices->first()?->id);
            })->exists()) {
                $skipped++;
                $this->warn("Skipping item {$item->id} because related subscription already exists.");

                continue;
            }

            $durationMonths = $item->meta['duration_months'] ?? 1;

            $startAt = now();
            $endAt = $startAt->copy()->addMonths($durationMonths);

            if ($dryRun) {
                $created++;
                $this->line("Would create subscription for customer {$customer->id} product {$product->id} from order {$order->id}.");

                continue;
            }

            $subscription = Subscription::create([
                'customer_id' => $customer->id,
                'product_id' => $product->id,
                'status' => 'trialing',
                'start_at' => $startAt,
                'end_at' => $endAt,
                'next_renewal_at' => $endAt,
                'auto_renew' => true,
                'provisioning_status' => 'pending',
                'meta' => [
                    'duration_months' => $durationMonths,
                ],
            ]);

            $item->update([
                'subscription_id' => $subscription->id,
            ]);

            $paidInvoice = $order->invoices->firstWhere('status', 'paid');

            if ($paidInvoice) {
                ProvisionAccountJob::dispatch($subscription->id);
            }

            $created++;

            $this->info("Created subscription {$subscription->id} for order item {$item->id}.");
        }

        $this->newLine();
        $this->info("Done. Created: {$created}, Skipped: {$skipped}.");

        return Command::SUCCESS;
    }
}

