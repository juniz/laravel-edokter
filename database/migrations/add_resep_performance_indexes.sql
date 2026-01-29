-- =====================================================
-- Script SQL untuk Optimasi Performa Resep
-- File: add_resep_performance_indexes.sql
-- Deskripsi: Menambahkan indeks untuk optimasi query resep
-- =====================================================

-- =====================================================
-- UP: Menambahkan Indeks
-- =====================================================

-- Indeks untuk tabel riwayat_barang_medis
-- Composite index untuk query stok terbaru
CREATE INDEX idx_stok_latest ON riwayat_barang_medis (kode_brng, kd_bangsal, tanggal, jam);

-- Index untuk query MAX tanggal dan jam
CREATE INDEX idx_stok_lookup ON riwayat_barang_medis (kode_brng, kd_bangsal);

-- Indeks untuk tabel resep_obat
-- Composite index untuk cek resep existing
CREATE INDEX idx_resep_existing ON resep_obat (no_rawat, tgl_peresepan, kd_dokter);

-- Index untuk generate nomor resep
CREATE INDEX idx_resep_number ON resep_obat (tgl_perawatan, tgl_peresepan);

-- Index untuk query response
CREATE INDEX idx_resep_lookup ON resep_obat (no_rawat, kd_dokter);

-- Indeks untuk tabel resep_dokter
-- Index untuk join dengan databarang
CREATE INDEX idx_resep_dokter_lookup ON resep_dokter (no_resep, kode_brng);

-- Indeks untuk tabel databarang
-- Index untuk join dengan resep_dokter
CREATE INDEX idx_databarang_lookup ON databarang (kode_brng, status);

-- Indeks untuk tabel set_depo_ralan
CREATE INDEX idx_depo_ralan ON set_depo_ralan (kd_poli);

-- Indeks untuk tabel set_depo_ranap
CREATE INDEX idx_depo_ranap ON set_depo_ranap (kd_bangsal);

-- Indeks untuk tabel gudangbarang
-- Composite index untuk query obat tersedia
CREATE INDEX idx_gudang_lookup ON gudangbarang (kode_brng, kd_bangsal, stok);

-- =====================================================
-- DOWN: Menghapus Indeks (Rollback)
-- =====================================================
-- Uncomment baris di bawah ini untuk rollback/undo

/*
DROP INDEX idx_stok_latest ON riwayat_barang_medis;
DROP INDEX idx_stok_lookup ON riwayat_barang_medis;

DROP INDEX idx_resep_existing ON resep_obat;
DROP INDEX idx_resep_number ON resep_obat;
DROP INDEX idx_resep_lookup ON resep_obat;

DROP INDEX idx_resep_dokter_lookup ON resep_dokter;

DROP INDEX idx_databarang_lookup ON databarang;

DROP INDEX idx_depo_ralan ON set_depo_ralan;

DROP INDEX idx_depo_ranap ON set_depo_ranap;

DROP INDEX idx_gudang_lookup ON gudangbarang;
*/

-- =====================================================
-- Catatan:
-- 1. Pastikan tabel-tabel sudah ada sebelum menjalankan script ini
-- 2. Untuk MySQL/MariaDB, gunakan syntax di atas
-- 3. Untuk PostgreSQL, ubah CREATE INDEX menjadi CREATE INDEX IF NOT EXISTS
-- 4. Untuk rollback, uncomment bagian DOWN di atas
-- =====================================================
