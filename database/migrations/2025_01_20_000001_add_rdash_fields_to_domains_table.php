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
        if (! Schema::hasTable('domains')) {
            return;
        }

        if (! Schema::hasColumn('domains', 'rdash_domain_id')) {
            Schema::table('domains', function (Blueprint $table) {
                $table->unsignedInteger('rdash_domain_id')->nullable()->after('auto_renew');
                $table->index('rdash_domain_id');
            });
        }

        if (! Schema::hasColumn('domains', 'rdash_synced_at')) {
            Schema::table('domains', function (Blueprint $table) {
                $table->timestamp('rdash_synced_at')->nullable()->after('rdash_domain_id');
            });
        }

        if (! Schema::hasColumn('domains', 'rdash_sync_status')) {
            Schema::table('domains', function (Blueprint $table) {
                $table->enum('rdash_sync_status', ['pending', 'synced', 'failed'])->default('pending')->after('rdash_synced_at');
                $table->index('rdash_sync_status');
            });
        }

        if (! Schema::hasColumn('domains', 'rdash_verification_status')) {
            Schema::table('domains', function (Blueprint $table) {
                $table->integer('rdash_verification_status')->nullable()->after('rdash_sync_status')->comment('0. Waiting, 1. Verifying, 2. Document Validating, 3. Active');
            });
        }

        if (! Schema::hasColumn('domains', 'rdash_required_document')) {
            Schema::table('domains', function (Blueprint $table) {
                $table->boolean('rdash_required_document')->default(false)->after('rdash_verification_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('domains')) {
            return;
        }

        if (Schema::hasColumn('domains', 'rdash_domain_id')) {
            Schema::table('domains', function (Blueprint $table) {
                $table->dropIndex(['rdash_domain_id']);
                $table->dropColumn('rdash_domain_id');
            });
        }

        if (Schema::hasColumn('domains', 'rdash_sync_status')) {
            Schema::table('domains', function (Blueprint $table) {
                $table->dropIndex(['rdash_sync_status']);
            });
        }

        $columnsToDrop = [
            'rdash_synced_at',
            'rdash_sync_status',
            'rdash_verification_status',
            'rdash_required_document',
        ];

        $existingColumnsToDrop = array_values(array_filter(
            $columnsToDrop,
            fn (string $column) => Schema::hasColumn('domains', $column)
        ));

        if ($existingColumnsToDrop !== []) {
            Schema::table('domains', function (Blueprint $table) use ($existingColumnsToDrop) {
                $table->dropColumn($existingColumnsToDrop);
            });
        }
    }
};
