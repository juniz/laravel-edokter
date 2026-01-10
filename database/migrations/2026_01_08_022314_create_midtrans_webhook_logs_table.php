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
        Schema::create('midtrans_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->nullable()->index();
            $table->char('payment_id', 26)->nullable()->index();
            $table->string('transaction_status')->nullable();
            $table->string('fraud_status')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('status_code')->nullable();
            $table->string('status_message')->nullable();
            $table->enum('processing_status', ['pending', 'success', 'failed', 'skipped'])->default('pending');
            $table->text('error_message')->nullable();
            $table->json('payload'); // Full webhook payload
            $table->json('response')->nullable(); // Response yang dikirim ke Midtrans
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'processing_status']);
            $table->index(['payment_id', 'processing_status']);
            $table->index('created_at');
            $table->index(['transaction_status', 'created_at']);

            $table->foreign('payment_id')->references('id')->on('payments')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('midtrans_webhook_logs');
    }
};
