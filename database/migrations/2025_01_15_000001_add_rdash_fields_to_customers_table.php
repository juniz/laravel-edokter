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

        if (! Schema::hasColumn('customers', 'organization')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('organization')->nullable()->after('email');
            });
        }

        if (! Schema::hasColumn('customers', 'street_1')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('street_1')->nullable()->after('organization');
            });
        }

        if (! Schema::hasColumn('customers', 'street_2')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('street_2')->nullable()->after('street_1');
            });
        }

        if (! Schema::hasColumn('customers', 'city')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('city')->nullable()->after('street_2');
            });
        }

        if (! Schema::hasColumn('customers', 'state')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('state')->nullable()->after('city');
            });
        }

        if (! Schema::hasColumn('customers', 'country_code')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->char('country_code', 2)->default('ID')->after('state');
                $table->index('country_code');
            });
        }

        if (! Schema::hasColumn('customers', 'postal_code')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('postal_code')->nullable()->after('country_code');
            });
        }

        if (! Schema::hasColumn('customers', 'fax')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('fax')->nullable()->after('phone');
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

        if (Schema::hasColumn('customers', 'country_code')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropIndex(['country_code']);
            });
        }

        $columnsToDrop = [
            'organization',
            'street_1',
            'street_2',
            'city',
            'state',
            'country_code',
            'postal_code',
            'fax',
        ];

        $existingColumnsToDrop = array_values(array_filter(
            $columnsToDrop,
            fn (string $column) => Schema::hasColumn('customers', $column)
        ));

        if ($existingColumnsToDrop !== []) {
            Schema::table('customers', function (Blueprint $table) use ($existingColumnsToDrop) {
                $table->dropColumn($existingColumnsToDrop);
            });
        }
    }
};
