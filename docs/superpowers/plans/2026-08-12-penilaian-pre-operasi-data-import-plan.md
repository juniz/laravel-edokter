# Penilaian Pre Operasi Data Import Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambahkan opsi impor data pasien dari SOAP Ranap (keluhan & fisik/TTV) serta Data Penunjang (Lab & Radiologi) ke dalam form Penilaian Pre Operasi.

**Architecture:** Menambahkan metode pembacaan data rekam medis di Livewire Component `PenilaianPreOperasi.php` dan komponen UI berupa tombol Quick-Import per field serta tombol 1-Click Auto-Fill pada view Blade `penilaian-pre-operasi.blade.php`.

**Tech Stack:** Laravel 9, Livewire, AlpineJS / Bootstrap 4, FontAwesome.

## Global Constraints
- Menggunakan `DB::table` konsisten dengan pola yang sudah ada di file `PenilaianPreOperasi.php`.
- Batas maksimal karakter tiap textarea adalah 500 karakter.
- Preservasi data yang sudah diisi manual (menggunakan opsi append atau replace jika dikonfirmasi).

---

### Task 1: Tambahkan Data Retrieval & Action Methods pada Livewire Component `PenilaianPreOperasi.php`

**Files:**
- Modify: `app/Http/Livewire/Component/PenilaianPreOperasi.php:15-110`

**Interfaces:**
- Produces: `$listSoap`, `$listLab`, `$listRadiologi`, `loadDataImportPasien()`, `importKeluhan()`, `importPemeriksaanFisik()`, `importDiagnostikLab()`, `importDiagnostikRadiologi()`, `autoFillSemua()`.

- [ ] **Step 1: Modifikasi `PenilaianPreOperasi.php` untuk menambah properties & method pembacaan data**

Edit `app/Http/Livewire/Component/PenilaianPreOperasi.php` untuk menambahkan:
- `$listSoap`, `$listLab`, `$listRadiologi`
- Method `loadDataImportPasien()`
- Method `importKeluhan($text)`
- Method `importPemeriksaanFisik($index)`
- Method `importDiagnostikLab()`
- Method `importDiagnostikRadiologi()`
- Method `autoFillSemua()`

- [ ] **Step 2: Panggil `loadDataImportPasien()` pada `mount()` dan `getData()`**

Memastikan data pasien siap digunakan saat komponen di-mount atau diperbarui.

---

### Task 2: Modifikasi View Template `penilaian-pre-operasi.blade.php` dengan UI Impor Data

**Files:**
- Modify: `resources/views/livewire/component/penilaian-pre-operasi.blade.php:1-70`

- [ ] **Step 1: Tambahkan Tombol Header "Ambil Data Pasien Terbaru" (Auto-Fill 1-Click)**

Menambahkan action bar di bagian paling atas form dengan tombol "Ambil Data Pasien Terbaru".

- [ ] **Step 2: Tambahkan UI Selector / Quick-Action di atas masing-masing field textarea**

- Untuk **Ringkasan Klinik**: Tombol Dropdown / Modal pilihan list keluhan dari SOAP.
- Untuk **Pemeriksaan Fisik**: Tombol Dropdown / Modal pilihan SOAP + TTV (Suhu, Tensi, Nadi, Respirasi, SpO2, GCS).
- Untuk **Pemeriksaan Diagnostik**: Tombol "Ambil Lab" & "Ambil Radiologi".

---

### Task 3: Verifikasi Pengisian & Penyimpanan Data

- [ ] **Step 1: Lakukan sintaks check pada PHP file & Blade view**

Jalankan `php -l app/Http/Livewire/Component/PenilaianPreOperasi.php` untuk memastikan tidak ada kesalahan sintaks PHP.

- [ ] **Step 2: Manual Check / Verification**

Konfirmasi komponen dapat merender dengan benar dan tidak menyebabkan error pada aplikasi.
