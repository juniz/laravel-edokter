<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Subscription\Contracts\SubscriptionRepository as SubscriptionRepositoryContract;
use App\Infrastructure\Persistence\Eloquent\SubscriptionRepository;

class SubscriptionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            SubscriptionRepositoryContract::class,
            SubscriptionRepository::class
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

