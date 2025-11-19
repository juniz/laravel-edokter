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
        Schema::create('subscription_cycles', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->char('subscription_id', 26);
            $table->unsignedInteger('cycle_no');
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->char('invoice_id', 26)->nullable();
            $table->char('payment_id', 26)->nullable();
            $table->timestamps();

            $table->index('subscription_id');
            $table->index('period_end');
            
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->restrictOnDelete();
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
            $table->foreign('payment_id')->references('id')->on('payments')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_cycles');
    }
};
