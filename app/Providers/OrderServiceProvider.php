<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Order\Contracts\OrderRepository as OrderRepositoryContract;
use App\Infrastructure\Persistence\Eloquent\OrderRepository;

class OrderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            OrderRepositoryContract::class,
            OrderRepository::class
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

