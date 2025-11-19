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
        Schema::create('plans', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->char('product_id', 26);
            $table->string('code')->unique();
            $table->enum('billing_cycle', [
                'monthly',
                'quarterly',
                'semiannually',
                'annually',
                'biennially',
                'triennially'
            ]);
            $table->unsignedBigInteger('price_cents');
            $table->char('currency', 3)->default('IDR');
            $table->unsignedInteger('trial_days')->nullable();
            $table->unsignedBigInteger('setup_fee_cents')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('product_id');
            $table->index('code');
            
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
