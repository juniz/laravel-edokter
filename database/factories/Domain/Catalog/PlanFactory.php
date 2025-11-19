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
        $cycles = ['monthly', 'quarterly', 'semiannually', 'annually', 'biennially', 'triennially'];
        
        return [
            'product_id' => Product::factory(),
            'code' => strtoupper($this->faker->unique()->bothify('###-???')),
            'billing_cycle' => $this->faker->randomElement($cycles),
            'price_cents' => $this->faker->numberBetween(50000, 500000),
            'currency' => 'IDR',
            'trial_days' => $this->faker->randomElement([null, 7, 14, 30]),
            'setup_fee_cents' => $this->faker->numberBetween(0, 100000),
        ];
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_cycle' => 'monthly',
            'code' => 'MONTHLY-' . strtoupper($this->faker->bothify('???')),
        ]);
    }

    public function annually(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_cycle' => 'annually',
            'code' => 'ANNUAL-' . strtoupper($this->faker->bothify('???')),
            'price_cents' => $this->faker->numberBetween(500000, 5000000),
        ]);
    }
}
