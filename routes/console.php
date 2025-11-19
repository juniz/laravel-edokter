<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\RenewSubscriptionJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule subscription renewal job to run daily
Schedule::job(new RenewSubscriptionJob)->daily()->at('02:00');

// Schedule queue worker (if not using supervisor)
// Schedule::command('queue:work --stop-when-empty')->hourly();
