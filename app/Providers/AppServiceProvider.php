<?php

namespace App\Providers;

use App\Models\Menu;
use App\Models\User;
use App\Models\SettingApp;
use Spatie\Permission\Models\Role;
use App\Observers\GlobalActivityLogger;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Event;
use App\Events\InvoicePaid;
use App\Events\AccountProvisioned;
use App\Listeners\ProvisionAccountOnInvoicePaid;
use App\Listeners\SendInvoiceEmail;
use App\Listeners\SendWelcomeEmail;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(GlobalActivityLogger::class);
        Role::observe(GlobalActivityLogger::class);
        Permission::observe(GlobalActivityLogger::class);
        Menu::observe(GlobalActivityLogger::class);
        SettingApp::observe(GlobalActivityLogger::class);

        // Register event listeners
        Event::listen(
            InvoicePaid::class,
            [ProvisionAccountOnInvoicePaid::class, 'handle']
        );

        Event::listen(
            InvoicePaid::class,
            [SendInvoiceEmail::class, 'handle']
        );

        Event::listen(
            AccountProvisioned::class,
            [SendWelcomeEmail::class, 'handle']
        );
    }
}
