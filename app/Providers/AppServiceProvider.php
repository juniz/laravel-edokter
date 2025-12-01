<?php

namespace App\Providers;

use App\Events\AccountProvisioned;
use App\Events\InvoicePaid;
use App\Listeners\ProvisionAccountOnInvoicePaid;
use App\Listeners\RegisterDomainOnInvoicePaid;
use App\Listeners\SendInvoiceEmail;
use App\Listeners\SendWelcomeEmail;
use App\Models\Menu;
use App\Models\SettingApp;
use App\Models\User;
use App\Observers\GlobalActivityLogger;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
            [RegisterDomainOnInvoicePaid::class, 'handle']
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
