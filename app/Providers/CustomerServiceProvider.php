<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Customer\Contracts\CustomerRepository as CustomerRepositoryContract;
use App\Infrastructure\Persistence\Eloquent\CustomerRepository;

class CustomerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            CustomerRepositoryContract::class,
            CustomerRepository::class
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

