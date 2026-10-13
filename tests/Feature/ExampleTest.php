<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite') {
            \Illuminate\Support\Facades\Schema::create('poliklinik', function ($table) {
                $table->string('kd_poli')->primary();
                $table->string('nm_poli')->default('Poli Umum');
                $table->string('status')->default('1');
            });
        }

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
