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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->char('invoice_id', 26);
            $table->string('description');
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedBigInteger('unit_price_cents');
            $table->unsignedBigInteger('total_cents');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('invoice_id');
            
            $table->foreign('invoice_id')->references('id')->on('invoices')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
