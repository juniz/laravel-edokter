<?php

namespace Tests\Feature;

use App\Domain\Rdash\Account\Contracts\AccountRepository;
use App\Domain\Rdash\Account\ValueObjects\DomainPrice;
use App\Models\User;
use Database\Seeders\MenuSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery;
use Tests\TestCase;

class DomainPricesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/domain-prices')->assertRedirect('/login');
    }

    public function test_admin_can_view_domain_prices_page(): void
    {
        $this->seed(MenuSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $mock = Mockery::mock(AccountRepository::class);
        $mock->shouldReceive('getPrices')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturn([
                new DomainPrice(
                    id: 1,
                    extension: '.co.id',
                    price: 135000,
                    renewPrice: 275000,
                    transferPrice: 270000,
                    currency: 'IDR',
                    promo: true,
                    metadata: ['redemption_price' => 1475000]
                ),
            ]);

        $this->app->instance(AccountRepository::class, $mock);

        $response = $this->actingAs($admin)->get('/admin/domain-prices');

        $response->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('admin/domain-prices/Index')
            ->has('prices', 1)
            ->where('prices.0.extension', '.co.id')
        );
    }
}


