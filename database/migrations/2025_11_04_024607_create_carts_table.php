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
        Schema::create('carts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->char('customer_id', 26);
            $table->char('coupon_id', 26)->nullable();
            $table->char('currency', 3)->default('IDR');
            $table->json('totals_json')->nullable(); // subtotal, discount, tax, total
            $table->timestamps();

            $table->index('customer_id');
            
            $table->foreign('customer_id')->references('id')->on('customers')->restrictOnDelete();
            $table->foreign('coupon_id')->references('id')->on('coupons')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
