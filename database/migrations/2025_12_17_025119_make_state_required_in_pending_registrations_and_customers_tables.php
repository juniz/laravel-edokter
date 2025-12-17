<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing NULL values to empty string before making column NOT NULL
        // For pending_registrations table
        DB::table('pending_registrations')
            ->whereNull('state')
            ->update(['state' => '']);

        // For customers table
        DB::table('customers')
            ->whereNull('state')
            ->update(['state' => '']);

        // Make state column NOT NULL in pending_registrations table
        Schema::table('pending_registrations', function (Blueprint $table) {
            $table->string('state')->nullable(false)->change();
        });

        // Make state column NOT NULL in customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->string('state')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert state column back to nullable in pending_registrations table
        Schema::table('pending_registrations', function (Blueprint $table) {
            $table->string('state')->nullable()->change();
        });

        // Revert state column back to nullable in customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->string('state')->nullable()->change();
        });
    }
};
