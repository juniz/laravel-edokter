<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Billing\Contracts\PaymentAdapterInterface;
use App\Infrastructure\Payments\Adapters\ManualTransferAdapter;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind payment adapter berdasarkan konfigurasi
        $defaultAdapter = config('payment.default', 'manual');
        
        $this->app->bind(
            PaymentAdapterInterface::class,
            match($defaultAdapter) {
                'manual' => ManualTransferAdapter::class,
                'midtrans' => \App\Infrastructure\Payments\Adapters\MidtransAdapter::class,
                'xendit' => \App\Infrastructure\Payments\Adapters\XenditAdapter::class,
                'tripay' => \App\Infrastructure\Payments\Adapters\TripayAdapter::class,
                default => ManualTransferAdapter::class,
            }
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

