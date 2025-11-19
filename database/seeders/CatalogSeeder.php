<?php

namespace Database\Seeders;

use App\Models\Domain\Catalog\Product;
use App\Models\Domain\Catalog\Plan;
use App\Models\Domain\Catalog\PlanFeature;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Shared Hosting Product
        $sharedHosting = Product::create([
            'type' => 'hosting_shared',
            'name' => 'Shared Hosting',
            'slug' => 'shared-hosting',
            'status' => 'active',
            'metadata' => [
                'description' => 'Perfect untuk website pribadi dan bisnis kecil',
                'features' => ['Unlimited Bandwidth', 'Free SSL', 'cPanel', 'Email Accounts'],
            ],
        ]);

        // Plans untuk Shared Hosting
        $planBasic = Plan::create([
            'product_id' => $sharedHosting->id,
            'code' => 'SHARED-BASIC-1Y',
            'billing_cycle' => 'annually',
            'price_cents' => 500000, // Rp 5.000.000
            'currency' => 'IDR',
            'trial_days' => 7,
            'setup_fee_cents' => 0,
        ]);

        PlanFeature::create(['plan_id' => $planBasic->id, 'key' => 'disk_space', 'value' => '10 GB']);
        PlanFeature::create(['plan_id' => $planBasic->id, 'key' => 'bandwidth', 'value' => 'Unlimited']);
        PlanFeature::create(['plan_id' => $planBasic->id, 'key' => 'domains', 'value' => '1']);
        PlanFeature::create(['plan_id' => $planBasic->id, 'key' => 'email_accounts', 'value' => '5']);

        $planPremium = Plan::create([
            'product_id' => $sharedHosting->id,
            'code' => 'SHARED-PREMIUM-1Y',
            'billing_cycle' => 'annually',
            'price_cents' => 1000000, // Rp 10.000.000
            'currency' => 'IDR',
            'trial_days' => 7,
            'setup_fee_cents' => 0,
        ]);

        PlanFeature::create(['plan_id' => $planPremium->id, 'key' => 'disk_space', 'value' => '50 GB']);
        PlanFeature::create(['plan_id' => $planPremium->id, 'key' => 'bandwidth', 'value' => 'Unlimited']);
        PlanFeature::create(['plan_id' => $planPremium->id, 'key' => 'domains', 'value' => '5']);
        PlanFeature::create(['plan_id' => $planPremium->id, 'key' => 'email_accounts', 'value' => 'Unlimited']);

        // VPS Product
        $vpsHosting = Product::create([
            'type' => 'vps',
            'name' => 'VPS Hosting',
            'slug' => 'vps-hosting',
            'status' => 'active',
            'metadata' => [
                'description' => 'Virtual Private Server untuk kebutuhan lebih besar',
                'features' => ['Full Root Access', 'SSD Storage', '24/7 Support'],
            ],
        ]);

        Plan::create([
            'product_id' => $vpsHosting->id,
            'code' => 'VPS-STARTER-1M',
            'billing_cycle' => 'monthly',
            'price_cents' => 250000, // Rp 2.500.000
            'currency' => 'IDR',
            'trial_days' => null,
            'setup_fee_cents' => 0,
        ]);

        Plan::create([
            'product_id' => $vpsHosting->id,
            'code' => 'VPS-STARTER-1Y',
            'billing_cycle' => 'annually',
            'price_cents' => 2500000, // Rp 25.000.000 (discount)
            'currency' => 'IDR',
            'trial_days' => null,
            'setup_fee_cents' => 0,
        ]);

        $this->command->info('Catalog seeded successfully!');
    }
}
