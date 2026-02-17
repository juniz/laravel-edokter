<?php

namespace Database\Seeders;

use App\Models\Domain\Customer\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        $customerUser = User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Customer Demo',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $customerUser->assignRole('customer');

        Customer::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'user_id' => $customerUser->id,
                'name' => 'Customer Demo',
                'phone' => '081234567890',
                'city' => 'Jakarta',
                'state' => 'DKI Jakarta',
                'country_code' => 'ID',
                'postal_code' => '10110',
                'billing_address_json' => [
                    'street' => 'Jl. Demo No. 1',
                    'city' => 'Jakarta',
                    'province' => 'DKI Jakarta',
                    'postal_code' => '10110',
                    'country' => 'Indonesia',
                ],
            ]
        );

        Customer::factory(10)->create();

        $this->command->info('Customers seeded successfully!');
    }
}
