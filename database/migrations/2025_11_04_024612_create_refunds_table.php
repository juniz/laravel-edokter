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
        Schema::create('refunds', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->char('payment_id', 26);
            $table->unsignedBigInteger('amount_cents');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'succeeded', 'failed'])->default('pending');
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index('payment_id');
            $table->index('status');
            
            $table->foreign('payment_id')->references('id')->on('payments')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
