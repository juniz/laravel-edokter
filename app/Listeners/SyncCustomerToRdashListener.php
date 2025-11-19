<?php

namespace App\Listeners;

use App\Application\Rdash\User\SyncUserToRdashService;
use App\Events\CustomerCreated;
use App\Events\CustomerUpdated;

class SyncCustomerToRdashListener
{
    /**
     * Handle the event.
     */
    public function handle(CustomerCreated|CustomerUpdated $event): void
    {
        // Only sync if auto sync is enabled
        if (!config('rdash.auto_sync_on_customer_create', true) && !config('rdash.auto_sync_on_customer_update', true)) {
            return;
        }

        // Sync customer to RDASH via user sync (synchronous)
        if ($event->customer->user) {
            $syncService = app(SyncUserToRdashService::class);
            $syncService->execute($event->customer->user, false);
        }
    }
}

