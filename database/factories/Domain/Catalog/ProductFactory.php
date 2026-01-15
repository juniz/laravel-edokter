<?php

namespace Database\Factories\Domain\Catalog;

use App\Models\Domain\Catalog\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Domain\Catalog\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $types = ['hosting_shared', 'vps', 'addon', 'domain'];
        $type = $this->faker->randomElement($types);

        return [
            'type' => $type,
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->unique()->slug(),
            'status' => $this->faker->randomElement(['active', 'draft', 'archived']),
            'price_cents' => $this->faker->numberBetween(50000, 500000),
            'currency' => 'IDR',
            'setup_fee_cents' => $this->faker->numberBetween(0, 100000),
            'trial_days' => $this->faker->randomElement([null, 7, 14, 30]),
            'duration_1_month_enabled' => true,
            'duration_12_months_enabled' => true,
            'metadata' => [
                'description' => $this->faker->paragraph(),
                'features' => $this->faker->words(5),
            ],
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function hostingShared(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'hosting_shared',
            'name' => 'Shared Hosting',
        ]);
    }

    public function vps(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'vps',
            'name' => 'VPS Hosting',
        ]);
    }
}
