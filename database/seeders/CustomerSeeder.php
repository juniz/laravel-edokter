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
        // Create admin user
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $admin->assignRole('admin');

        // Create 10 customers
        Customer::factory(10)->create();

        $this->command->info('Customers seeded successfully!');
    }
}
