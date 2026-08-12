# Design Spec: Pilihan Item Lab & Radiologi pada Penilaian Pre Operasi

## Overview
Memungkinkan dokter memilih parameter **Laboratorium** atau pemeriksaan **Radiologi** secara individual (per item/per bacaan) untuk dimasukkan ke field **Pemeriksaan Diagnostik** pada komponen `PenilaianPreOperasi`.

---

## Technical Architecture & Design

### 1. Livewire Component: `App\Http\Livewire\Component\PenilaianPreOperasi`

#### Method Modification / Addition:
- `public function importDiagnostikLabItem($index = null): void`
  - Jika `$index === null`: Mengambil seluruh hasil Lab (fitur existing).
  - Jika `$index` diberikan (integer): Mengambil item lab spesifik pada `$listLab[$index]`, menyusun teks:
    `{Pemeriksaan}: {nilai} {satuan}`
    lalu menambahkan (append) ke field `$this->pemeriksaan_diagnostik`.
- `public function importDiagnostikRadiologiItem($index = null): void`
  - Jika `$index === null`: Mengambil seluruh bacaan Radiologi (fitur existing).
  - Jika `$index` diberikan (integer): Mengambil item radiologi pada `$listRadiologi[$index]`, menyusun teks:
    `Radiologi ({tgl_periksa}): {hasil}`
    lalu menambahkan (append) ke field `$this->pemeriksaan_diagnostik`.

---

### 2. View Template: `resources/views/livewire/component/penilaian-pre-operasi.blade.php`

#### Elemen UI:
1. **Dropdown Pilih Hasil Lab**:
   - Diubah dari tombol biasa menjadi `<div class="dropdown">`.
   - Menampilkan opsi pertama **"Semua Hasil Lab"**.
   - Menampilkan daftar per item lab: `[d/m/Y] {Pemeriksaan}: {nilai} {satuan}`.
2. **Dropdown Pilih Hasil Radiologi**:
   - Diubah dari tombol biasa menjadi `<div class="dropdown">`.
   - Menampilkan opsi pertama **"Semua Hasil Radiologi"**.
   - Menampilkan daftar per pemeriksaan radiologi: `[d/m/Y H:i] {potongan_hasil}`.

---

## Verification Plan
1. Verifikasi sintaks PHP dengan `php -l`.
2. Uji pemilihan item spesifik Lab & Radiologi dan pastikan teks yang dipilih bertambah secara rapi pada textarea **Pemeriksaan Diagnostik**.
