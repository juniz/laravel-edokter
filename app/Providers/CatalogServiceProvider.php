<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Catalog\Contracts\ProductRepository as ProductRepositoryContract;
use App\Domain\Catalog\Contracts\PlanRepository as PlanRepositoryContract;
use App\Infrastructure\Persistence\Eloquent\ProductRepository;
use App\Infrastructure\Persistence\Eloquent\PlanRepository;

class CatalogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            ProductRepositoryContract::class,
            ProductRepository::class
        );
        $this->app->bind(
            PlanRepositoryContract::class,
            PlanRepository::class
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

