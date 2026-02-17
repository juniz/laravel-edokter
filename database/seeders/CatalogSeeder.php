<?php

namespace Database\Seeders;

use App\Models\Domain\Catalog\Product;
use App\Models\Domain\Catalog\ProductType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $driver = DB::connection()->getDriverName();

        $productTypeIds = [
            'hosting_shared' => ProductType::firstOrCreate(
                ['slug' => 'hosting_shared'],
                [
                    'name' => 'Shared Hosting',
                    'status' => 'active',
                    'icon' => 'HardDrive',
                    'display_order' => 1,
                    'metadata' => null,
                ]
            )->id,
            'app' => ProductType::firstOrCreate(
                ['slug' => 'app'],
                [
                    'name' => 'Aplikasi (SaaS)',
                    'status' => 'active',
                    'icon' => 'LayoutGrid',
                    'display_order' => 2,
                    'metadata' => null,
                ]
            )->id,
        ];

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        Product::truncate();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // Paket Hosting Faskesku.id
        Product::create([
            'product_type_id' => $productTypeIds['hosting_shared'],
            'name' => 'Faskesku Personal',
            'slug' => 'faskesku-personal',
            'status' => 'active',
            'price_cents' => 60000,
            'currency' => 'IDR',
            'setup_fee_cents' => 0,
            'trial_days' => 0,
            'duration_1_month_enabled' => true,
            'duration_12_months_enabled' => true,
            'metadata' => [
                'description' => 'Hosting khusus sistem kesehatan & aplikasi klinik (Faskesku.id).',
                'popular' => false,
                'features' => [
                    'Untuk Dokter Praktek Mandiri',
                    'SSD 10 GB Storage',
                    '1 Domain',
                    'Unlimited Bandwidth',
                    '2 MySQL Database',
                    '10 Email Account',
                    'Free SSL & Backup Mingguan',
                    'Cocok untuk 1 User',
                ],
            ],
        ]);

        Product::create([
            'product_type_id' => $productTypeIds['hosting_shared'],
            'name' => 'Faskesku Klinik',
            'slug' => 'faskesku-klinik',
            'status' => 'active',
            'price_cents' => 150000,
            'currency' => 'IDR',
            'setup_fee_cents' => 0,
            'trial_days' => 0,
            'duration_1_month_enabled' => true,
            'duration_12_months_enabled' => true,
            'metadata' => [
                'description' => 'Hosting khusus sistem kesehatan & aplikasi klinik (Faskesku.id).',
                'popular' => true,
                'features' => [
                    'Untuk Klinik Rawat Jalan',
                    'SSD 30 GB Storage',
                    '3 Domain',
                    'Unlimited Bandwidth',
                    '10 Database',
                    '50 Email Account',
                    'Free SSL & Backup Harian',
                    'Multi User (Dokter & Staf)',
                ],
            ],
        ]);

        Product::create([
            'product_type_id' => $productTypeIds['hosting_shared'],
            'name' => 'Faskesku Enterprise',
            'slug' => 'faskesku-enterprise',
            'status' => 'active',
            'price_cents' => 250000,
            'currency' => 'IDR',
            'setup_fee_cents' => 0,
            'trial_days' => 0,
            'duration_1_month_enabled' => true,
            'duration_12_months_enabled' => true,
            'metadata' => [
                'description' => 'Hosting khusus sistem kesehatan & aplikasi klinik (Faskesku.id).',
                'popular' => false,
                'features' => [
                    'Untuk Klinik Besar & Rawat Inap',
                    'SSD 80 GB Storage',
                    'Unlimited Domain',
                    'Prioritas Bandwidth',
                    'Unlimited Database',
                    'Unlimited Email',
                    'Free SSL & Backup + Restore',
                    'Dedicated Resource',
                ],
            ],
        ]);

        // Aplikasi Sistem Klinik (SaaS)
        Product::create([
            'product_type_id' => $productTypeIds['app'],
            'name' => 'faskesku.id',
            'slug' => 'faskesku-id',
            'status' => 'active',
            'price_cents' => 199000,
            'currency' => 'IDR',
            'setup_fee_cents' => 0,
            'trial_days' => 7,
            'duration_1_month_enabled' => true,
            'duration_12_months_enabled' => true,
            'metadata' => [
                'description' => 'Aplikasi sistem klinik (SaaS) untuk operasional faskes',
                'popular' => false,
                'features' => [
                    'Pendaftaran & data pasien',
                    'Antrian & jadwal dokter',
                    'Rekam medis',
                    'Kasir & billing',
                    'Laporan operasional',
                    'Multi user & role',
                ],
            ],
        ]);

        $this->command->info('Catalog products populated successfully!');
    }
}
