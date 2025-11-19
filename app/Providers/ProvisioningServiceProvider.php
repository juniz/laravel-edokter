<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Provisioning\Contracts\ProvisioningAdapterInterface;
use App\Domain\Billing\Contracts\PaymentAdapterInterface;
use App\Infrastructure\Provisioning\Adapters\CpanelAdapter;
use App\Infrastructure\Payments\Adapters\ManualTransferAdapter;

class ProvisioningServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind provisioning adapter berdasarkan konfigurasi
        $defaultAdapter = config('provisioning.default', 'cpanel');
        
        $this->app->bind(
            ProvisioningAdapterInterface::class,
            match($defaultAdapter) {
                'cpanel' => CpanelAdapter::class,
                'directadmin' => \App\Infrastructure\Provisioning\Adapters\DirectAdminAdapter::class,
                'proxmox' => \App\Infrastructure\Provisioning\Adapters\ProxmoxAdapter::class,
                default => CpanelAdapter::class,
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

