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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->char('cart_id', 26);
            $table->char('product_id', 26);
            $table->char('plan_id', 26)->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedBigInteger('unit_price_cents');
            $table->json('meta')->nullable(); // domain name, notes
            $table->timestamps();

            $table->index('cart_id');
            
            $table->foreign('cart_id')->references('id')->on('carts')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
