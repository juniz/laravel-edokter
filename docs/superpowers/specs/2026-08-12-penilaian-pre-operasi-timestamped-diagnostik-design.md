# Design Spec: Tanggal dan Jam Pemeriksaan pada Form Diagnostik Pre Operasi

## Overview
Menyertakan tanggal dan jam pemeriksaan (`tgl_periksa` & `jam`) secara otomatis pada teks hasil Laboratorium dan Radiologi saat dimasukkan ke field **Pemeriksaan Diagnostik** di komponen `PenilaianPreOperasi`.

---

## Technical Architecture & Design

### 1. Livewire Component: `App\Http\Livewire\Component\PenilaianPreOperasi`

#### Method Modification:
- `importDiagnostikLab($index = null)`:
  - Format tanggal: `d/m/Y H:i` dari `tgl_periksa` dan `jam`.
  - Item individual: `[{tgl} {jam}] {Pemeriksaan}: {nilai} {satuan}`.
  - Semua item lab (dikelompokkan per tanggal/jam):
    `Lab ({tgl} {jam}):\n- {Pemeriksaan}: {nilai} {satuan}`.
- `importDiagnostikRadiologi($index = null)`:
  - Format tanggal: `d/m/Y H:i` dari `tgl_periksa` dan `jam`.
  - Item individual: `[{tgl} {jam}] Radiologi: {hasil}`.
  - Semua item radiologi:
    `Radiologi ({tgl} {jam}):\n{hasil}`.

---

## Verification Plan
1. Verifikasi sintaks PHP `php -l`.
2. Pastikan teks yang dihasilkan mengandung timestamp `[d/m/Y H:i]`.
