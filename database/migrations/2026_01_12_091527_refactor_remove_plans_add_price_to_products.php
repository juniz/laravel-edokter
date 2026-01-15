<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom harga ke products
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('price_cents')->default(0)->after('status');
            $table->char('currency', 3)->default('IDR')->after('price_cents');
            $table->unsignedBigInteger('setup_fee_cents')->default(0)->after('currency');
            $table->unsignedInteger('trial_days')->nullable()->after('setup_fee_cents');
            $table->boolean('duration_1_month_enabled')->default(true)->after('trial_days');
            $table->boolean('duration_12_months_enabled')->default(true)->after('duration_1_month_enabled');
        });

        // 2. Migrasi data dari plans ke products
        // Ambil harga dari plan pertama untuk setiap product
        if (Schema::hasTable('plans')) {
            \DB::statement('
                UPDATE products p
                INNER JOIN (
                    SELECT product_id, 
                           MIN(price_cents) as price_cents,
                           MIN(currency) as currency,
                           MIN(setup_fee_cents) as setup_fee_cents,
                           MIN(trial_days) as trial_days,
                           MAX(duration_1_month_enabled) as duration_1_month_enabled,
                           MAX(duration_12_months_enabled) as duration_12_months_enabled
                    FROM plans
                    GROUP BY product_id
                ) pl ON p.id = pl.product_id
                SET p.price_cents = pl.price_cents,
                    p.currency = pl.currency,
                    p.setup_fee_cents = pl.setup_fee_cents,
                    p.trial_days = pl.trial_days,
                    p.duration_1_month_enabled = pl.duration_1_month_enabled,
                    p.duration_12_months_enabled = pl.duration_12_months_enabled
            ');
        }

        // 3. Hapus foreign key constraints untuk plan_id
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            if ($this->hasForeignKey('order_items', 'order_items_plan_id_foreign')) {
                $table->dropForeign(['plan_id']);
            }
        });

        Schema::table('cart_items', function (Blueprint $table) {
            if ($this->hasForeignKey('cart_items', 'cart_items_plan_id_foreign')) {
                $table->dropForeign(['plan_id']);
            }
        });

        // 4. Hapus kolom plan_id dari subscriptions, order_items, cart_items
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('plan_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('plan_id');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn('plan_id');
        });

        // 5. Hapus tabel plan_features dan plans
        Schema::dropIfExists('plan_features');
        Schema::dropIfExists('plans');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate plans table
        Schema::create('plans', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->char('product_id', 26);
            $table->string('code')->unique();
            $table->unsignedBigInteger('price_cents');
            $table->char('currency', 3)->default('IDR');
            $table->unsignedInteger('trial_days')->nullable();
            $table->unsignedBigInteger('setup_fee_cents')->default(0);
            $table->boolean('duration_1_month_enabled')->default(true);
            $table->boolean('duration_12_months_enabled')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('product_id');
            $table->index('code');

            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
        });

        // Recreate plan_features table
        Schema::create('plan_features', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->char('plan_id', 26);
            $table->string('key');
            $table->text('value');
            $table->timestamps();

            $table->index('plan_id');
            $table->unique(['plan_id', 'key']);

            $table->foreign('plan_id')->references('id')->on('plans')->cascadeOnDelete();
        });

        // Add plan_id columns back
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->char('plan_id', 26)->nullable()->after('product_id');
            $table->foreign('plan_id')->references('id')->on('plans')->restrictOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->char('plan_id', 26)->nullable()->after('product_id');
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->char('plan_id', 26)->nullable()->after('product_id');
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
        });

        // Remove columns from products
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'price_cents',
                'currency',
                'setup_fee_cents',
                'trial_days',
                'duration_1_month_enabled',
                'duration_12_months_enabled',
            ]);
        });
    }

    /**
     * Check if foreign key exists
     */
    private function hasForeignKey(string $table, string $foreignKey): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        $result = $connection->selectOne(
            'SELECT COUNT(*) as count 
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = ? 
             AND CONSTRAINT_NAME = ?',
            [$database, $table, $foreignKey]
        );

        return $result->count > 0;
    }
};
