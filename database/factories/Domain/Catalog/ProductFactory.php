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
