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
        Schema::create('payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->char('invoice_id', 26);
            $table->enum('provider', ['midtrans', 'xendit', 'tripay', 'manual'])->default('manual');
            $table->string('provider_ref')->nullable(); // payment reference dari gateway
            $table->unsignedBigInteger('amount_cents');
            $table->enum('status', ['pending', 'succeeded', 'failed'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->json('raw_payload')->nullable(); // raw response dari gateway
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('provider');
            $table->index('status');
            $table->index(['invoice_id', 'provider', 'status']);
            
            $table->foreign('invoice_id')->references('id')->on('invoices')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
