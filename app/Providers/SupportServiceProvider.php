<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Support\Contracts\TicketRepository as TicketRepositoryContract;
use App\Infrastructure\Persistence\Eloquent\TicketRepository;

class SupportServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            TicketRepositoryContract::class,
            TicketRepository::class
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

