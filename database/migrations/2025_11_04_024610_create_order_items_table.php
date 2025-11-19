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
        Schema::create('order_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->char('order_id', 26);
            $table->char('product_id', 26);
            $table->char('plan_id', 26)->nullable();
            // subscription_id akan ditambahkan setelah subscriptions table dibuat
            $table->char('subscription_id', 26)->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedBigInteger('unit_price_cents');
            $table->unsignedBigInteger('total_cents');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('order_id');
            
            $table->foreign('order_id')->references('id')->on('orders')->restrictOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
