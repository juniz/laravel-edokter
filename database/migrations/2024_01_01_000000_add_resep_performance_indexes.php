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
        // Indeks untuk tabel riwayat_barang_medis
        Schema::table('riwayat_barang_medis', function (Blueprint $table) {
            // Composite index untuk query stok terbaru
            $table->index(['kode_brng', 'kd_bangsal', 'tanggal', 'jam'], 'idx_stok_latest');

            // Index untuk query MAX tanggal dan jam
            $table->index(['kode_brng', 'kd_bangsal'], 'idx_stok_lookup');
        });

        // Indeks untuk tabel resep_obat
        Schema::table('resep_obat', function (Blueprint $table) {
            // Composite index untuk cek resep existing
            $table->index(['no_rawat', 'tgl_peresepan', 'kd_dokter'], 'idx_resep_existing');

            // Index untuk generate nomor resep
            $table->index(['tgl_perawatan', 'tgl_peresepan'], 'idx_resep_number');

            // Index untuk query response
            $table->index(['no_rawat', 'kd_dokter'], 'idx_resep_lookup');
        });

        // Indeks untuk tabel resep_dokter
        Schema::table('resep_dokter', function (Blueprint $table) {
            // Index untuk join dengan databarang
            $table->index(['no_resep', 'kode_brng'], 'idx_resep_dokter_lookup');
        });

        // Indeks untuk tabel databarang
        Schema::table('databarang', function (Blueprint $table) {
            // Index untuk join dengan resep_dokter
            $table->index(['kode_brng', 'status'], 'idx_databarang_lookup');
        });

        // Indeks untuk tabel set_depo_ralan
        Schema::table('set_depo_ralan', function (Blueprint $table) {
            $table->index('kd_poli', 'idx_depo_ralan');
        });

        // Indeks untuk tabel set_depo_ranap
        Schema::table('set_depo_ranap', function (Blueprint $table) {
            $table->index('kd_bangsal', 'idx_depo_ranap');
        });

        // Indeks untuk tabel gudangbarang
        Schema::table('gudangbarang', function (Blueprint $table) {
            // Composite index untuk query obat tersedia
            $table->index(['kode_brng', 'kd_bangsal', 'stok'], 'idx_gudang_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_barang_medis', function (Blueprint $table) {
            $table->dropIndex('idx_stok_latest');
            $table->dropIndex('idx_stok_lookup');
        });

        Schema::table('resep_obat', function (Blueprint $table) {
            $table->dropIndex('idx_resep_existing');
            $table->dropIndex('idx_resep_number');
            $table->dropIndex('idx_resep_lookup');
        });

        Schema::table('resep_dokter', function (Blueprint $table) {
            $table->dropIndex('idx_resep_dokter_lookup');
        });

        Schema::table('databarang', function (Blueprint $table) {
            $table->dropIndex('idx_databarang_lookup');
        });

        Schema::table('set_depo_ralan', function (Blueprint $table) {
            $table->dropIndex('idx_depo_ralan');
        });

        Schema::table('set_depo_ranap', function (Blueprint $table) {
            $table->dropIndex('idx_depo_ranap');
        });

        Schema::table('gudangbarang', function (Blueprint $table) {
            $table->dropIndex('idx_gudang_lookup');
        });
    }
};

