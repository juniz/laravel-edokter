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
        Schema::table('domains', function (Blueprint $table) {
            // RDASH Integration Fields
            $table->unsignedInteger('rdash_domain_id')->nullable()->after('auto_renew');
            $table->timestamp('rdash_synced_at')->nullable()->after('rdash_domain_id');
            $table->enum('rdash_sync_status', ['pending', 'synced', 'failed'])->default('pending')->after('rdash_synced_at');
            $table->integer('rdash_verification_status')->nullable()->after('rdash_sync_status')->comment('0. Waiting, 1. Verifying, 2. Document Validating, 3. Active');
            $table->boolean('rdash_required_document')->default(false)->after('rdash_verification_status');

            // Indexes
            $table->index('rdash_domain_id');
            $table->index('rdash_sync_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropIndex(['rdash_domain_id']);
            $table->dropIndex(['rdash_sync_status']);
            $table->dropColumn([
                'rdash_domain_id',
                'rdash_synced_at',
                'rdash_sync_status',
                'rdash_verification_status',
                'rdash_required_document',
            ]);
        });
    }
};

