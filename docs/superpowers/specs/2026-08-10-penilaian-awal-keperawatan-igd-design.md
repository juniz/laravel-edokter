# Design Spec: Riwayat Penilaian Awal Keperawatan IGD

**Date**: 2026-08-10  
**Feature**: Integration of `penilaian_awal_keperawatan_igd` into Patient Examination History (`pasien-tabs.blade.php`)

---

## 🎯 Goal

To render patient's initial emergency nursing assessment history (`penilaian_awal_keperawatan_igd`) in `pasien-tabs.blade.php` as a prominent standalone card at the top of the history tab (`#riwayat-pasien-content`), as well as providing a reusable Blade component `<x-ralan.penilaian-awal-medis-i-g-d :no-rawat="$no_rawat" />` that can be included per visit.

---

## 📐 Architecture & Components

### 1. Database Query & Helper Method (`app/Http/Livewire/Ralan/PasienTabs.php`)
- Method: `getPenilaianAwalKeperawatanIGD($noRawat)`
- Query:
  ```php
  DB::table('penilaian_awal_keperawatan_igd')
      ->leftJoin('petugas', 'penilaian_awal_keperawatan_igd.nip', '=', 'petugas.nip')
      ->where('penilaian_awal_keperawatan_igd.no_rawat', $noRawat)
      ->select('penilaian_awal_keperawatan_igd.*', 'petugas.nama as nama_petugas')
      ->first();
  ```

### 2. Blade Component Class (`app/View/Components/Ralan/PenilaianAwalMedisIGD.php`)
- Constructor accepts `$noRawat`
- Fetches data using `penilaian_awal_keperawatan_igd` query left-joined with `petugas` table
- Passes `$data` to view `components.ralan.penilaian-awal-medis-i-g-d`

### 3. Blade Component View (`resources/views/components/ralan/penilaian-awal-medis-i-g-d.blade.php`)
- Theme: `theme="info"` (using AdminLTE collapsible card)
- Icon: `fas fa-ambulance` or `fas fa-notes-medical`
- Title: **Penilaian Awal Keperawatan IGD**
- Organized into 6 sub-cards:
  1. **Informasi Umum & Anamnesis**: Tanggal, Informasi, Petugas (NIP/Nama), Keluhan Utama, RPD, RPO, Status Kehamilan (Gravida, Para, Abortus, HPHT)
  2. **Pemeriksaan Fisik & Eliminasi**: Tekanan, Pupil, Neurosensorik, Integumen, Turgor, Edema, Mukosa, Perdarahan (Jumlah & Warna), Intoksikasi, Eliminasi BAB (Frekuensi, Konsistensi, Warna) & BAK (Frekuensi, Warna, Lainnya)
  3. **Pengkajian Nyeri**: Status Nyeri, Skala Nyeri (0-10), Faktor Provokes, Quality, Lokasi, Menyebar, Durasi, Penanganan Nyeri, Dilaporkan ke Dokter
  4. **Pengkajian Risiko Jatuh & Fungsional**: Kemampuan, Aktivitas, Alat Bantu, Evaluation Screening Risiko Jatuh (Berjalan A, B, C, Hasil & Laporan)
  5. **Psikososial, Budaya & Edukasi**: Status Psikologis, Gangguan Jiwa, Perilaku, Hubungan Pasien, Tinggal Dengan, Budaya, Pendidikan Penanggung Jawab, Edukasi Pasien/Keluarga
  6. **Rencana Keperawatan**: Teks Rencana

### 4. Tab View Integration (`resources/views/livewire/ralan/pasien-tabs.blade.php`)
- In `#riwayat-pasien-content`, add a check for `penilaianIGD` from the active or latest visit in `$data`.
- Display top card using `<x-adminlte-card theme="info" title="Penilaian Awal Keperawatan IGD" ...>` or directly invoking component `<x-ralan.penilaian-awal-medis-i-g-d :no-rawat="$noRawatIGD" />`.

---

## 🧪 Verification Plan

1. Verify component rendering when `penilaian_awal_keperawatan_igd` data exists for a given `no_rawat`.
2. Verify graceful fallback (no error / hidden card) when no assessment data exists.
3. Check UI layout, responsive column display, and correct mapping of all database fields.
