<?php

namespace Database\Seeders;

use App\Models\Domain\Provisioning\Server;
use Illuminate\Database\Seeder;

class HostingSeeder extends Seeder
{
    public function run(): void
    {
        // Create cPanel server
        Server::create([
            'name' => 'cPanel Server 1',
            'type' => 'cpanel',
            'endpoint' => 'https://cpanel.example.com:2083',
            'auth_secret_ref' => 'cpanel_api_key_123',
            'status' => 'active',
            'meta' => [
                'max_accounts' => 500,
                'packages' => ['basic', 'premium', 'enterprise'],
            ],
        ]);

        // Create DirectAdmin server
        Server::create([
            'name' => 'DirectAdmin Server 1',
            'type' => 'directadmin',
            'endpoint' => 'https://directadmin.example.com:2222',
            'auth_secret_ref' => 'directadmin_api_key_123',
            'status' => 'active',
            'meta' => [
                'max_accounts' => 300,
                'packages' => ['starter', 'business'],
            ],
        ]);

        $this->command->info('Hosting servers seeded successfully!');
    }
}
