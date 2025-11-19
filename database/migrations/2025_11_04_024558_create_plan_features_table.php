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
        Schema::create('plan_features', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->char('plan_id', 26);
            $table->string('key');
            $table->text('value')->nullable(); // bisa JSON string
            $table->timestamps();

            $table->index('plan_id');
            $table->unique(['plan_id', 'key']);
            
            $table->foreign('plan_id')->references('id')->on('plans')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_features');
    }
};
