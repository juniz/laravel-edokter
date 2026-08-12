# Design Spec: Impor & Pilih Data Pasien pada Penilaian Pre Operasi

## Overview
Menambahkan fitur pada komponen `PenilaianPreOperasi` yang memungkinkan dokter/tenaga medis mengambil dan memilih data rekam medis pasien yang sudah tercatat sebelumnya tanpa perlu input ulang secara manual.

Data yang dapat diambil meliputi:
1. **Ringkasan Klinik** (`ringkasan_klinik`): Mengambil data `keluhan` dari SOAP Ranap (`pemeriksaan_ranap`) atau Ralan (`pemeriksaan_ralan`).
2. **Pemeriksaan Fisik** (`pemeriksaan_fisik`): Mengambil data fisik dan tanda-tanda vital (`pemeriksaan`, `suhu_tubuh`, `tensi`, `nadi`, `respirasi`, `spo2`, `gcs`) dari SOAP Ranap/Ralan.
3. **Pemeriksaan Diagnostik** (`pemeriksaan_diagnostik`): Mengambil ringkasan data Penunjang **Laboratorium** (`detail_periksa_lab` + `template_laboratorium`) dan **Radiologi** (`hasil_radiologi`).

---

## Technical Architecture & Design

### 1. Livewire Component: `App\Http\Livewire\Component\PenilaianPreOperasi`

#### State & Properties Baru:
- `public $listSoap = [];` : Menyimpan daftar riwayat SOAP (keluhan, pemeriksaan fisik, vital signs) pasien dari `pemeriksaan_ranap` dan `pemeriksaan_ralan`.
- `public $listLab = [];` : Menyimpan data hasil laboratorium pasien.
- `public $listRadiologi = [];` : Menyimpan data hasil radiologi pasien.
- `public $showImportModal = false;` : Status modal pemilih data pasien (opsional jika modal digunakan) atau state dropdown.

#### Helper & Action Methods Baru:
- `public function loadDataImportPasien(): void`
  - Mengambil data SOAP (`pemeriksaan_ranap` di-union / fallback ke `pemeriksaan_ralan`) berdasarkan `no_rawat`.
  - Mengambil data Lab (`detail_periksa_lab` joined with `template_laboratorium`) berdasarkan `no_rawat`.
  - Mengambil data Radiologi (`hasil_radiologi`) berdasarkan `no_rawat`.
- `public function importKeluhan($keluhan): void`
  - Mengisi atau menambahkan `$this->ringkasan_klinik` dengan keluhan yang dipilih.
- `public function importPemeriksaanFisik($index): void`
  - Menyusun dan mengeset `$this->pemeriksaan_fisik` dari item SOAP yang dipilih dengan format:
    `Pemeriksaan: {pemeriksaan}\nSuhu: {suhu}°C | TD: {tensi} mmHg | Nadi: {nadi}x/m | RR: {respirasi}x/m | SpO2: {spo2}% | GCS: {gcs}`
- `public function importDiagnostikLab(): void`
  - Menyusun dan mengisi `$this->pemeriksaan_diagnostik` dari daftar hasil Lab.
- `public function importDiagnostikRadiologi(): void`
  - Menyusun dan mengisi `$this->pemeriksaan_diagnostik` dari hasil Radiologi.
- `public function autoFillSemua(): void`
  - Mengisi ketiga field sekaligus secara instan menggunakan data SOAP, Lab, dan Radiologi terbaru.

---

### 2. View Template: `resources/views/livewire/component/penilaian-pre-operasi.blade.php`

#### Elemen UI Baru:
1. **Header Action Bar**:
   - Di bagian atas form, ditambahkan tombol **"Ambil Data Pasien Terbaru"** (Auto-Fill 1-Click) dengan ikon `<i class="fas fa-file-download"></i>`.

2. **Quick Action per Field**:
   - **Atas Field Ringkasan Klinik**: Tombol/Dropdown "Pilih Keluhan SOAP" menampilkan daftar riwayat keluhan pasien berdasarkan tanggal/jam.
   - **Atas Field Pemeriksaan Fisik**: Tombol/Dropdown "Pilih Fisik & Vital" menampilkan daftar riwayat SOAP beserta ringkasan TTV.
   - **Atas Field Pemeriksaan Diagnostik**: Tombol "Ambil Lab", "Ambil Radiologi", atau "Ambil Semua Penunjang".

3. **Modal Pilih Data Pasien (Opsional / Interactive Selector)**:
   - Apabila dokter mengeklik "Pilih dari Riwayat", modal/dropdown rapi akan terbuka untuk memilih tanggal SOAP atau jenis hasil penunjang yang spesifik.

---

## Verification Plan

### Manual Verification
1. Buka halaman Penilaian Pre Operasi untuk pasien yang memiliki data SOAP Ranap, Lab, dan Radiologi.
2. Klik tombol **"Ambil Data Pasien Terbaru"** dan pastikan field Ringkasan Klinik, Pemeriksaan Fisik, dan Pemeriksaan Diagnostik terisi secara otomatis.
3. Coba pilih secara manual dari dropdown/modal pilih item SOAP & Lab/Radiologi per field.
4. Pastikan teks yang diimpor tidak melebihi batas 500 karakter atau disesuaikan dengan kapasitas kolom.
5. Simpan Penilaian Pre Operasi dan verifikasi data tersimpan dengan benar di basis data.
