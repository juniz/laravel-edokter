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
        Schema::create('domains', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->char('customer_id', 26);
            $table->string('name')->unique();
            $table->enum('status', ['active', 'pending', 'expired'])->default('pending');
            $table->json('whois_json')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('status');
            
            $table->foreign('customer_id')->references('id')->on('customers')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
