# Selective Lab & Radiologi Import Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambahkan opsi dropdown agar dokter dapat memilih item hasil laboratorium atau radiologi secara spesifik per item ke field Pemeriksaan Diagnostik.

**Architecture:** Memperbarui method `importDiagnostikLab` dan `importDiagnostikRadiologi` di `PenilaianPreOperasi.php` agar mendukung parameter `$index`, serta mengubah tombol action di `penilaian-pre-operasi.blade.php` menjadi Dropdown UI.

**Tech Stack:** Laravel 9, Livewire, Bootstrap 4 / FontAwesome.

## Global Constraints
- Tetap mematuhi batas maksimal 500 karakter per field (`mb_substr`).
- Mempertahankan opsi "Semua Hasil Lab" & "Semua Hasil Radiologi".

---

### Task 1: Update Livewire Component Methods di `PenilaianPreOperasi.php`

**Files:**
- Modify: `app/Http/Livewire/Component/PenilaianPreOperasi.php:350-475`

- [ ] **Step 1: Perbarui `loadDataImportPasien()` untuk menyertakan atribut tanggal/jam**

Pastikan query `$listLab` memuat `detail_periksa_lab.tgl_periksa` dan `detail_periksa_lab.jam` jika belum ada.

- [ ] **Step 2: Update `importDiagnostikLab($index = null)`**

Dukung parameter `$index`:
- Jika `$index === null`, impor seluruh hasil lab.
- Jika `$index !== null`, impor hanya item pada `$listLab[$index]` dan tambahkan (append) ke `$pemeriksaan_diagnostik`.

- [ ] **Step 3: Update `importDiagnostikRadiologi($index = null)`**

Dukung parameter `$index`:
- Jika `$index === null`, impor seluruh hasil radiologi.
- Jika `$index !== null`, impor hanya item pada `$listRadiologi[$index]` dan tambahkan (append) ke `$pemeriksaan_diagnostik`.

---

### Task 2: Update Blade Template `penilaian-pre-operasi.blade.php` dengan UI Dropdown Selectors

**Files:**
- Modify: `resources/views/livewire/component/penilaian-pre-operasi.blade.php:67-85`

- [ ] **Step 1: Ubah Tombol Lab & Radiologi Menjadi Dropdown Selector**

- Ubah tombol **"Ambil Hasil Lab"** menjadi Dropdown yang menampilkan:
  - Opsi "Semua Hasil Lab" (`wire:click.prevent="importDiagnostikLab"`)
  - Loop `@foreach($listLab as $idx => $lab)`: item individual (`wire:click.prevent="importDiagnostikLab({{ $idx }})"`)
- Ubah tombol **"Ambil Hasil Radiologi"** menjadi Dropdown yang menampilkan:
  - Opsi "Semua Hasil Radiologi" (`wire:click.prevent="importDiagnostikRadiologi"`)
  - Loop `@foreach($listRadiologi as $idx => $rad)`: item individual (`wire:click.prevent="importDiagnostikRadiologi({{ $idx }})"`)

---

### Task 3: Verifikasi Sintaks & Kode

- [ ] **Step 1: Jalankan PHP linting check**

Run: `php -l app/Http/Livewire/Component/PenilaianPreOperasi.php`
Expected: `No syntax errors detected`
