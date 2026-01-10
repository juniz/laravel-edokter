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
        Schema::create('product_features', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->char('product_id', 26);
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('label')->nullable(); // Label untuk display, misal "CPU", "RAM", "Bandwidth"
            $table->string('unit')->nullable(); // Unit untuk display, misal "core", "GB", "Mbps"
            $table->integer('display_order')->default(0); // Urutan tampilan
            $table->timestamps();

            $table->index('product_id');
            $table->unique(['product_id', 'key']);

            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_features');
    }
};
