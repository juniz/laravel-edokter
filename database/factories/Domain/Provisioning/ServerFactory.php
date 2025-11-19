<?php

namespace Database\Factories\Domain\Provisioning;

use App\Models\Domain\Provisioning\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Domain\Provisioning\Server>
 */
class ServerFactory extends Factory
{
    protected $model = Server::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true) . ' Server',
            'type' => $this->faker->randomElement(['cpanel', 'directadmin', 'proxmox']),
            'endpoint' => 'https://' . $this->faker->domainName(),
            'auth_secret_ref' => 'secret_' . $this->faker->uuid(),
            'status' => 'active',
            'meta' => [
                'max_accounts' => $this->faker->numberBetween(100, 1000),
                'packages' => ['basic', 'premium', 'enterprise'],
            ],
        ];
    }

    public function cpanel(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'cpanel',
            'name' => 'cPanel Server',
        ]);
    }

    public function directadmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'directadmin',
            'name' => 'DirectAdmin Server',
        ]);
    }
}
