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
        if (! Schema::hasTable('customers')) {
            return;
        }

        if (! Schema::hasColumn('customers', 'rdash_customer_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->unsignedInteger('rdash_customer_id')->nullable()->after('billing_address_json');
                $table->index('rdash_customer_id');
            });
        }

        if (! Schema::hasColumn('customers', 'rdash_synced_at')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->timestamp('rdash_synced_at')->nullable()->after('rdash_customer_id');
            });
        }

        if (! Schema::hasColumn('customers', 'rdash_sync_status')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->enum('rdash_sync_status', ['pending', 'synced', 'failed'])->default('pending')->after('rdash_synced_at');
                $table->index('rdash_sync_status');
            });
        }

        if (! Schema::hasColumn('customers', 'rdash_sync_error')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->text('rdash_sync_error')->nullable()->after('rdash_sync_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        if (Schema::hasColumn('customers', 'rdash_customer_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropIndex(['rdash_customer_id']);
                $table->dropColumn('rdash_customer_id');
            });
        }

        if (Schema::hasColumn('customers', 'rdash_synced_at')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('rdash_synced_at');
            });
        }

        if (Schema::hasColumn('customers', 'rdash_sync_status')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropIndex(['rdash_sync_status']);
                $table->dropColumn('rdash_sync_status');
            });
        }

        if (Schema::hasColumn('customers', 'rdash_sync_error')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('rdash_sync_error');
            });
        }
    }
};
