<?php

namespace App\Providers;

use App\Domain\Provisioning\Contracts\ProvisioningAdapterInterface;
use App\Infrastructure\Provisioning\AdapterResolver;
use App\Infrastructure\Provisioning\Adapters\CpanelAdapter;
use Illuminate\Support\ServiceProvider;

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
            match ($defaultAdapter) {
                'cpanel' => CpanelAdapter::class,
                'directadmin' => \App\Infrastructure\Provisioning\Adapters\DirectAdminAdapter::class,
                'proxmox' => \App\Infrastructure\Provisioning\Adapters\ProxmoxAdapter::class,
                'aapanel' => \App\Infrastructure\Provisioning\Adapters\AaPanelAdapter::class,
                default => CpanelAdapter::class,
            }
        );

        // Bind adapter berdasarkan server type
        $this->app->bind('provisioning.adapter.cpanel', CpanelAdapter::class);
        $this->app->bind('provisioning.adapter.directadmin', \App\Infrastructure\Provisioning\Adapters\DirectAdminAdapter::class);
        $this->app->bind('provisioning.adapter.proxmox', \App\Infrastructure\Provisioning\Adapters\ProxmoxAdapter::class);
        $this->app->bind('provisioning.adapter.aapanel', \App\Infrastructure\Provisioning\Adapters\AaPanelAdapter::class);

        // Bind AdapterResolver
        $this->app->singleton(AdapterResolver::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
