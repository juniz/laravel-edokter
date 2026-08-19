# Design Spec: Pengecekan & Replace Berkas Digital Penandaan Area Operasi

## Background & Objectives
Kode master berkas digital `A05` tidak hanya digunakan untuk berkas Penandaan Area Operasi, tetapi juga dapat digunakan untuk berkas medis tindakan operasi lainnya.
Ketika pengguna (dokter/operator) melakukan proses update/simpan ulang pada formulir penandaan area operasi, sistem harus dapat:
1. Mengunggah berkas PDF penandaan operasi baru ke server webapps.
2. Mengecek dan mereplace (menghapus) berkas digital penandaan area operasi yang **lama** pada `berkas_digital_perawatan` khusus untuk nomor rawat tersebut yang memiliki pola nama file penandaan operasi (`PENANDAAN_OPERASI_...`), tanpa menghapus berkas `A05` lainnya yang bukan berkas penandaan area operasi.

## Architectural Changes

### 1. PenandaanAreaOperasiService
- **File**: `app/Services/PenandaanAreaOperasiService.php`
- **Method**: `uploadPdf(string $noRawat, string $pdfBinary): array`
  - Pola nama file:
    `$sanitizedNoRawat = str_replace('/', '-', $noRawat);`
    `$filePrefix = 'PENANDAAN_OPERASI_' . $sanitizedNoRawat . '_';`
    `$namaFile = $filePrefix . date('YmdHis');`
    `$newLocation = 'pages/upload/' . $namaFile . '.pdf';`
  - Kirim HTTP POST upload ke server webapps `upload-berkas-edokter.php`.
  - Jika upload berhasil (`$resJson['success'] === true`):
    Jalankan cleanup pada database `berkas_digital_perawatan` untuk menggantikan file penandaan operasi lama:
    ```php
    DB::table('berkas_digital_perawatan')
        ->where('no_rawat', $noRawat)
        ->where('kode', 'A05')
        ->where('lokasi_file', 'like', '%PENANDAAN_OPERASI_' . $sanitizedNoRawat . '_%')
        ->where('lokasi_file', '<>', $newLocation)
        ->delete();
    ```
  - Kembalikan status sukses, URL file PDF, dan flag apakah berkas lama telah digantikan (`is_replaced => true/false`).

### 2. Livewire Component
- **File**: `app/Http/Livewire/Component/PenandaanAreaOperasi.php`
- Di dalam method `simpan()`:
  - Panggil `PenandaanAreaOperasiService::generateAndUploadPdf($this->noRawat)`.
  - Tampilkan pesan alert sukses:
    - Jika baru pertama kali: `'Penandaan area operasi berhasil disimpan dan diunggah ke berkas digital.'`
    - Jika update/replace: `'Penandaan area operasi berhasil disimpan dan berkas digital diperbarui.'`

## Verification Plan
1. **Unit & Feature Test**:
   - `tests/Feature/PenandaanAreaOperasiAutoUploadTest.php`:
     - Test upload baru menghasilkan 1 baris `berkas_digital_perawatan` dengan `PENANDAAN_OPERASI_...`.
     - Test simpan ulang (update) membersihkan baris lama `PENANDAAN_OPERASI_...` dan hanya menyisakan baris baru.
     - Test memastikan berkas `A05` lain (misal berkas dengan lokasi `pages/upload/OPERASI_LAIN_...`) **TIDAK** ikut terhapus saat replace.
