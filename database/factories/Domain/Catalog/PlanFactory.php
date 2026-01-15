<?php

namespace Database\Factories\Domain\Catalog;

use App\Models\Domain\Catalog\Plan;
use App\Models\Domain\Catalog\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Domain\Catalog\Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'code' => strtoupper($this->faker->unique()->bothify('###-???')),
            'price_cents' => $this->faker->numberBetween(50000, 500000),
            'currency' => 'IDR',
            'trial_days' => $this->faker->randomElement([null, 7, 14, 30]),
            'setup_fee_cents' => $this->faker->numberBetween(0, 100000),
            'duration_1_month_enabled' => true,
            'duration_12_months_enabled' => true,
        ];
    }

    public function monthlyOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'duration_1_month_enabled' => true,
            'duration_12_months_enabled' => false,
            'code' => 'MONTHLY-'.strtoupper($this->faker->bothify('???')),
        ]);
    }

    public function annualOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'duration_1_month_enabled' => false,
            'duration_12_months_enabled' => true,
            'code' => 'ANNUAL-'.strtoupper($this->faker->bothify('???')),
        ]);
    }
}
