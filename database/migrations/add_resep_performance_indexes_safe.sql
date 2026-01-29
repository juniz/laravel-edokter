-- =====================================================
-- Script SQL untuk Optimasi Performa Resep (Safe Version)
-- File: add_resep_performance_indexes_safe.sql
-- Deskripsi: Menambahkan indeks dengan pengecekan keamanan
-- =====================================================

-- =====================================================
-- UP: Menambahkan Indeks (dengan pengecekan)
-- =====================================================

-- Fungsi helper untuk membuat indeks jika belum ada (MySQL/MariaDB)
-- Catatan: MySQL tidak support IF NOT EXISTS untuk CREATE INDEX
-- Gunakan stored procedure atau hapus manual jika indeks sudah ada

-- Indeks untuk tabel riwayat_barang_medis
-- Hapus indeks jika sudah ada, lalu buat ulang
DROP INDEX IF EXISTS idx_stok_latest ON riwayat_barang_medis;
CREATE INDEX idx_stok_latest ON riwayat_barang_medis (kode_brng, kd_bangsal, tanggal, jam);

DROP INDEX IF EXISTS idx_stok_lookup ON riwayat_barang_medis;
CREATE INDEX idx_stok_lookup ON riwayat_barang_medis (kode_brng, kd_bangsal);

-- Indeks untuk tabel resep_obat
DROP INDEX IF EXISTS idx_resep_existing ON resep_obat;
CREATE INDEX idx_resep_existing ON resep_obat (no_rawat, tgl_peresepan, kd_dokter);

DROP INDEX IF EXISTS idx_resep_number ON resep_obat;
CREATE INDEX idx_resep_number ON resep_obat (tgl_perawatan, tgl_peresepan);

DROP INDEX IF EXISTS idx_resep_lookup ON resep_obat;
CREATE INDEX idx_resep_lookup ON resep_obat (no_rawat, kd_dokter);

-- Indeks untuk tabel resep_dokter
DROP INDEX IF EXISTS idx_resep_dokter_lookup ON resep_dokter;
CREATE INDEX idx_resep_dokter_lookup ON resep_dokter (no_resep, kode_brng);

-- Indeks untuk tabel databarang
DROP INDEX IF EXISTS idx_databarang_lookup ON databarang;
CREATE INDEX idx_databarang_lookup ON databarang (kode_brng, status);

-- Indeks untuk tabel set_depo_ralan
DROP INDEX IF EXISTS idx_depo_ralan ON set_depo_ralan;
CREATE INDEX idx_depo_ralan ON set_depo_ralan (kd_poli);

-- Indeks untuk tabel set_depo_ranap
DROP INDEX IF EXISTS idx_depo_ranap ON set_depo_ranap;
CREATE INDEX idx_depo_ranap ON set_depo_ranap (kd_bangsal);

-- Indeks untuk tabel gudangbarang
DROP INDEX IF EXISTS idx_gudang_lookup ON gudangbarang;
CREATE INDEX idx_gudang_lookup ON gudangbarang (kode_brng, kd_bangsal, stok);

-- =====================================================
-- DOWN: Menghapus Indeks (Rollback)
-- =====================================================

DROP INDEX IF EXISTS idx_stok_latest ON riwayat_barang_medis;
DROP INDEX IF EXISTS idx_stok_lookup ON riwayat_barang_medis;

DROP INDEX IF EXISTS idx_resep_existing ON resep_obat;
DROP INDEX IF EXISTS idx_resep_number ON resep_obat;
DROP INDEX IF EXISTS idx_resep_lookup ON resep_obat;

DROP INDEX IF EXISTS idx_resep_dokter_lookup ON resep_dokter;

DROP INDEX IF EXISTS idx_databarang_lookup ON databarang;

DROP INDEX IF EXISTS idx_depo_ralan ON set_depo_ralan;

DROP INDEX IF EXISTS idx_depo_ranap ON set_depo_ranap;

DROP INDEX IF EXISTS idx_gudang_lookup ON gudangbarang;

-- =====================================================
-- Catatan:
-- 1. DROP INDEX IF EXISTS hanya tersedia di MySQL 5.7+ dan MariaDB 10.2.1+
-- 2. Untuk versi MySQL/MariaDB yang lebih lama, gunakan versi tanpa IF EXISTS
-- 3. Untuk PostgreSQL, gunakan: DROP INDEX IF EXISTS nama_index;
-- 4. Pastikan backup database sebelum menjalankan script ini
-- =====================================================
