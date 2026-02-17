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
        Schema::create('customers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('organization')->nullable();
            $table->string('street_1')->nullable();
            $table->string('street_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->char('country_code', 2)->default('ID');
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('tax_number')->nullable();
            $table->json('billing_address_json')->nullable();
            $table->unsignedInteger('rdash_customer_id')->nullable();
            $table->timestamp('rdash_synced_at')->nullable();
            $table->enum('rdash_sync_status', ['pending', 'synced', 'failed'])->default('pending');
            $table->text('rdash_sync_error')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
            $table->index('user_id');
            $table->index('country_code');
            $table->index('rdash_customer_id');
            $table->index('rdash_sync_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
