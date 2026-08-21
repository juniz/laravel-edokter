<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penandaan_area_operasi', function (Blueprint $table) {
            // Drop unique constraint if exists
            $table->dropUnique('penandaan_area_operasi_no_rawat_unique');
            $table->index('no_rawat', 'penandaan_area_operasi_no_rawat_index');
        });
    }

    public function down(): void
    {
        Schema::table('penandaan_area_operasi', function (Blueprint $table) {
            $table->dropIndex('penandaan_area_operasi_no_rawat_index');
            $table->unique('no_rawat', 'penandaan_area_operasi_no_rawat_unique');
        });
    }
};
