<?php

namespace Database\Seeders;

use App\Models\Domain\Catalog\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks to allow truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Product::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Starter Package
        Product::create([
            'type' => 'hosting_shared',
            'name' => 'Starter',
            'slug' => 'starter',
            'status' => 'active',
            'price_cents' => 29900, 
            'currency' => 'IDR',
            'setup_fee_cents' => 0,
            'trial_days' => 0,
            'duration_1_month_enabled' => true,
            'duration_12_months_enabled' => true,
            'metadata' => [
                'description' => 'Cocok untuk blog dan website pribadi',
                'popular' => false,
                'features' => [
                    '1 Website',
                    '10 GB SSD Storage',
                    'Free SSL Certificate',
                    '1 Email Account',
                    'Weekly Backups',
                ],
            ],
        ]);

        // Professional Package (Popular)
        Product::create([
            'type' => 'hosting_shared',
            'name' => 'Professional',
            'slug' => 'professional',
            'status' => 'active',
            'price_cents' => 59900, 
            'currency' => 'IDR',
            'setup_fee_cents' => 0,
            'trial_days' => 0,
            'duration_1_month_enabled' => true,
            'duration_12_months_enabled' => true,
            'metadata' => [
                'description' => 'Ideal untuk bisnis dan toko online',
                'popular' => true,
                'features' => [
                    '100 Website',
                    '100 GB NVMe Storage',
                    'Free SSL Certificate',
                    'Unlimited Email',
                    'Daily Backups',
                    'Free Domain 1 Tahun',
                    'Priority Support',
                ],
            ],
        ]);


        // Enterprise Package
        Product::create([
            'type' => 'hosting_shared',
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'status' => 'active',
            'price_cents' => 149900, 
            'currency' => 'IDR',
            'setup_fee_cents' => 0,
            'trial_days' => 0,
            'duration_1_month_enabled' => true,
            'duration_12_months_enabled' => true,
            'metadata' => [
                'description' => 'Untuk website dengan traffic tinggi',
                'popular' => false,
                'features' => [
                    'Unlimited Website',
                    '200 GB NVMe Storage',
                    'Free SSL Certificate',
                    'Unlimited Email',
                    'Real-time Backups',
                    'Free Domain Selamanya',
                    'Dedicated Support',
                    'CDN Premium',
                ],
            ],
        ]);

        $this->command->info('Dashboard products populated successfully (Schema: Products has Price)!');
    }
}
