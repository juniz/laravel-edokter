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
        Schema::table('plans', function (Blueprint $table) {
            // Tambah kolom durasi enabled terlebih dahulu
            $table->boolean('duration_1_month_enabled')->default(true)->after('code');
            $table->boolean('duration_12_months_enabled')->default(true)->after('duration_1_month_enabled');
        });

        // Set semua plan existing untuk enable kedua durasi
        \DB::table('plans')->update([
            'duration_1_month_enabled' => true,
            'duration_12_months_enabled' => true,
        ]);

        Schema::table('plans', function (Blueprint $table) {
            // Hapus kolom billing_cycle setelah data di-migrate
            $table->dropColumn('billing_cycle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // Kembalikan kolom billing_cycle
            $table->enum('billing_cycle', [
                'monthly',
                'quarterly',
                'semiannually',
                'annually',
                'biennially',
                'triennially',
            ])->after('code');
        });

        // Set default billing_cycle untuk data existing
        \DB::table('plans')->update([
            'billing_cycle' => 'monthly',
        ]);

        Schema::table('plans', function (Blueprint $table) {
            // Hapus kolom durasi enabled
            $table->dropColumn(['duration_1_month_enabled', 'duration_12_months_enabled']);
        });
    }
};
