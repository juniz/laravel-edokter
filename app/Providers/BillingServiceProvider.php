<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Billing\Contracts\InvoiceRepository as InvoiceRepositoryContract;
use App\Domain\Billing\Contracts\PaymentRepository as PaymentRepositoryContract;
use App\Infrastructure\Persistence\Eloquent\InvoiceRepository;
use App\Infrastructure\Persistence\Eloquent\PaymentRepository;

class BillingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            InvoiceRepositoryContract::class,
            InvoiceRepository::class
        );
        $this->app->bind(
            PaymentRepositoryContract::class,
            PaymentRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

