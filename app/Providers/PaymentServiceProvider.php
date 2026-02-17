<?php

namespace App\Providers;

use App\Domain\Billing\Contracts\PaymentAdapterInterface;
use App\Infrastructure\Payments\Adapters\ManualTransferAdapter;
use App\Models\Domain\Shared\Setting;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentAdapterInterface::class, function ($app) {
            $defaultAdapter = config('payment.default', 'manual');

            try {
                $setting = Setting::where('key', 'payment_gateway_settings')->first();
                $value = $setting?->value ?? [];

                $defaultAdapter = $value['default_gateway'] ?? $defaultAdapter;

                if (($value['midtrans_enabled'] ?? true) === false && $defaultAdapter === 'midtrans') {
                    $defaultAdapter = 'manual';
                }
            } catch (\Throwable) {
            }

            $adapterClass = match ($defaultAdapter) {
                'manual' => ManualTransferAdapter::class,
                'midtrans' => \App\Infrastructure\Payments\Adapters\MidtransAdapter::class,
                'xendit' => \App\Infrastructure\Payments\Adapters\XenditAdapter::class,
                'tripay' => \App\Infrastructure\Payments\Adapters\TripayAdapter::class,
                default => ManualTransferAdapter::class,
            };

            return $app->make($adapterClass);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
