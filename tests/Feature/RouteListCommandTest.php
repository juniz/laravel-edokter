<?php

namespace Tests\Feature;

use Tests\TestCase;

class RouteListCommandTest extends TestCase
{
    public function test_route_list_handles_routes_without_encrypted_query_payloads(): void
    {
        $this->artisan('route:list', ['--name' => 'ralan.rujuk-internal'])
            ->assertSuccessful();
    }
}
