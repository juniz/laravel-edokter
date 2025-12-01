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
        Schema::create('ssl_products', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->unsignedInteger('rdash_ssl_product_id')->unique(); // ID dari RDASH API
            $table->string('provider'); // gogetssl
            $table->string('brand'); // Comodo, Geotrust, Digicert
            $table->string('name'); // Sectigo PositiveSSL, RapidSSL, dll
            $table->enum('ssl_type', ['DV', 'OV', 'EV'])->default('DV'); // Domain Validation, Organization Validation, Extended Validation
            $table->boolean('is_wildcard')->default(false);
            $table->boolean('is_refundable')->default(true);
            $table->unsignedInteger('max_period')->default(1); // Maksimal periode dalam tahun
            $table->unsignedTinyInteger('status')->default(1); // 0 = inactive, 1 = active
            $table->json('features')->nullable(); // Features dari RDASH API
            $table->unsignedBigInteger('price_cents')->nullable(); // Harga dalam cents (jika ada)
            $table->char('currency', 3)->default('IDR');
            $table->timestamp('rdash_synced_at')->nullable(); // Waktu terakhir sync dari RDASH
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('rdash_ssl_product_id');
            $table->index('provider');
            $table->index('brand');
            $table->index('ssl_type');
            $table->index('status');
            $table->index(['is_wildcard', 'ssl_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ssl_products');
    }
};
