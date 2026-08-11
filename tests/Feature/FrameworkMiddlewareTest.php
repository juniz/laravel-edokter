<?php

namespace Tests\Feature;

use Tests\TestCase;

class FrameworkMiddlewareTest extends TestCase
{
    public function test_web_group_uses_the_laravel_13_csrf_middleware(): void
    {
        $this->assertContains(
            \App\Http\Middleware\PreventRequestForgery::class,
            app('router')->getMiddlewareGroups()['web']
        );
    }

    public function test_sanctum_uses_the_same_csrf_middleware(): void
    {
        $this->assertSame(
            \App\Http\Middleware\PreventRequestForgery::class,
            config('sanctum.middleware.verify_csrf_token')
        );
    }
}
