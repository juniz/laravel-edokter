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
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedInteger('rdash_customer_id')->nullable()->after('billing_address_json');
            $table->timestamp('rdash_synced_at')->nullable()->after('rdash_customer_id');
            $table->enum('rdash_sync_status', ['pending', 'synced', 'failed'])->default('pending')->after('rdash_synced_at');
            $table->text('rdash_sync_error')->nullable()->after('rdash_sync_status');
            
            $table->index('rdash_customer_id');
            $table->index('rdash_sync_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['rdash_customer_id']);
            $table->dropIndex(['rdash_sync_status']);
            $table->dropColumn([
                'rdash_customer_id',
                'rdash_synced_at',
                'rdash_sync_status',
                'rdash_sync_error',
            ]);
        });
    }
};

