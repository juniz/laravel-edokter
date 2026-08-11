<?php

namespace Tests\Feature;

use Tests\TestCase;

class FrameworkMiddlewareTest extends TestCase
{
    public function test_web_group_uses_the_laravel_13_csrf_middleware(): void
    {
        $this->assertSame(
            [
                \App\Http\Middleware\EncryptCookies::class,
                \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \App\Http\Middleware\PreventRequestForgery::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
            ],
            app('router')->getMiddlewareGroups()['web']
        );
    }

    public function test_sanctum_uses_the_same_csrf_middleware(): void
    {
        $this->assertSame(
            \App\Http\Middleware\PreventRequestForgery::class,
            config('sanctum.middleware.validate_csrf_token')
        );
    }
}
