<?php

namespace App\Listeners;

use App\Application\Rdash\User\SyncUserToRdashService;
use App\Events\UserCreated;
use App\Events\UserUpdated;

class SyncUserToRdashListener
{
    /**
     * Handle the event.
     */
    public function handle(UserCreated|UserUpdated $event): void
    {
        // Only sync if auto sync is enabled
        if (!config('rdash.auto_sync_on_user_create', false)) {
            return;
        }

        // Only sync if user has customer or if we want to create customer
        // Check if user has customer profile
        if ($event->user->customer || config('rdash.auto_sync_on_user_create', false)) {
            $syncService = app(SyncUserToRdashService::class);
            $syncService->execute($event->user, true);
        }
    }
}

