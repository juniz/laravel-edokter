<?php

namespace App\Providers;

use App\Domain\Rdash\Account\Contracts\AccountRepository;
use App\Domain\Rdash\BareMetal\Contracts\BareMetalRepository;
use App\Domain\Rdash\Contact\Contracts\ContactRepository;
use App\Domain\Rdash\Customer\Contracts\RdashCustomerRepository;
use App\Domain\Rdash\Dns\Contracts\DnsRepository;
use App\Domain\Rdash\Domain\Contracts\RdashDomainRepository;
use App\Domain\Rdash\ObjectStorage\Contracts\ObjectStorageRepository;
use App\Domain\Rdash\Ssl\Contracts\SslRepository;
use App\Infrastructure\Rdash\HttpClient;
use App\Infrastructure\Rdash\Repositories\AccountRepository as InfrastructureAccountRepository;
use App\Infrastructure\Rdash\Repositories\BareMetalRepository as InfrastructureBareMetalRepository;
use App\Infrastructure\Rdash\Repositories\ContactRepository as InfrastructureContactRepository;
use App\Infrastructure\Rdash\Repositories\DnsRepository as InfrastructureDnsRepository;
use App\Infrastructure\Rdash\Repositories\ObjectStorageRepository as InfrastructureObjectStorageRepository;
use App\Infrastructure\Rdash\Repositories\RdashCustomerRepository as InfrastructureRdashCustomerRepository;
use App\Infrastructure\Rdash\Repositories\RdashDomainRepository as InfrastructureRdashDomainRepository;
use App\Infrastructure\Rdash\Repositories\SslRepository as InfrastructureSslRepository;
use Illuminate\Support\ServiceProvider;

class RdashServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register HTTP Client
        $this->app->singleton(HttpClient::class, function ($app) {
            return new HttpClient();
        });

        // Register Repositories
        $this->app->bind(
            AccountRepository::class,
            InfrastructureAccountRepository::class
        );

        $this->app->bind(
            RdashCustomerRepository::class,
            InfrastructureRdashCustomerRepository::class
        );

        $this->app->bind(
            ContactRepository::class,
            InfrastructureContactRepository::class
        );

        $this->app->bind(
            RdashDomainRepository::class,
            InfrastructureRdashDomainRepository::class
        );

        $this->app->bind(
            DnsRepository::class,
            InfrastructureDnsRepository::class
        );

        $this->app->bind(
            SslRepository::class,
            InfrastructureSslRepository::class
        );

        $this->app->bind(
            ObjectStorageRepository::class,
            InfrastructureObjectStorageRepository::class
        );

        $this->app->bind(
            BareMetalRepository::class,
            InfrastructureBareMetalRepository::class
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

