<?php

namespace Database\Factories\Domain\Customer;

use App\Models\Domain\Customer\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Domain\Customer\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'tax_number' => $this->faker->optional()->numerify('##########'),
            'billing_address_json' => [
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'province' => $this->faker->state(),
                'postal_code' => $this->faker->postcode(),
                'country' => 'Indonesia',
            ],
        ];
    }
}
