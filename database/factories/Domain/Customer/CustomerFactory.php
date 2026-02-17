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
        $state = $this->faker->state();

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'city' => $this->faker->city(),
            'state' => $state,
            'phone' => $this->faker->phoneNumber(),
            'tax_number' => $this->faker->optional()->numerify('##########'),
            'billing_address_json' => [
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'province' => $state,
                'postal_code' => $this->faker->postcode(),
                'country' => 'Indonesia',
            ],
        ];
    }
}
