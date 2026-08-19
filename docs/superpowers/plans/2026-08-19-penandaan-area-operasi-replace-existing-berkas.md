# Implementation Plan: Pengecekan & Replace Berkas Digital Berdasarkan Nama File

Implementasi mekanisme pengecekan berkas digital saat simpan/update penandaan area operasi dengan menghapus berkas lama berdasarkan pola nama file `PENANDAAN_OPERASI_...` agar tidak menghapus berkas berkode `A05` lainnya.

## Proposed Changes

### 1. Service Layer

#### [MODIFY] `app/Services/PenandaanAreaOperasiService.php`
- Di dalam method `uploadPdf(string $noRawat, string $pdfBinary)`:
  1. Identifikasi prefix nama file spesifik penandaan operasi: `$sanitizedNoRawat = str_replace('/', '-', $noRawat);` `$filePrefix = 'PENANDAAN_OPERASI_' . $sanitizedNoRawat . '_';`
  2. Cek apakah ada berkas lama di database:
     ```php
     $existingOldFiles = DB::table('berkas_digital_perawatan')
         ->where('no_rawat', $noRawat)
         ->where('kode', 'A05')
         ->where('lokasi_file', 'like', '%' . $filePrefix . '%')
         ->exists();
     ```
  3. Setelah upload ke webapps server berhasil (`$resJson['success'] === true`), hapus entri lama yang cocok dengan prefix tanpa menyentuh file baru dan tanpa menyentuh file `A05` lainnya:
     ```php
     $newLocation = 'pages/upload/' . $namaFile . '.pdf';
     DB::table('berkas_digital_perawatan')
         ->where('no_rawat', $noRawat)
         ->where('kode', 'A05')
         ->where('lokasi_file', 'like', '%' . $filePrefix . '%')
         ->where('lokasi_file', '<>', $newLocation)
         ->delete();
     ```
  4. Tambahkan `'is_replaced' => $existingOldFiles` pada array kembalian.

---

### 2. Livewire Component

#### [MODIFY] `app/Http/Livewire/Component/PenandaanAreaOperasi.php`
- Di dalam method `simpan()`:
  - Berikan feedback yang sesuai bila berkas diperbarui:
    - Jika `$uploadResult['is_replaced'] ?? false` adalah true: `'Penandaan area operasi berhasil disimpan dan berkas digital diperbarui.'`
    - Jika baru: `'Penandaan area operasi berhasil disimpan dan diunggah ke berkas digital.'`

---

### 3. Controller Layer

#### [MODIFY] `app/Http/Controllers/PenandaanAreaOperasiController.php`
- Sertakan atribut `is_replaced` pada response JSON endpoint `uploadPdf()`.

---

### 4. Automated Tests

#### [MODIFY] `tests/Feature/PenandaanAreaOperasiAutoUploadTest.php`
- Tambahkan test case:
  - `test_upload_replaces_only_old_penandaan_operasi_files_and_preserves_other_a05_documents()`:
    1. Seed `berkas_digital_perawatan` dengan file lama penandaan: `pages/upload/PENANDAAN_OPERASI_2026-08-12-000303_20260819111111.pdf`.
    2. Seed `berkas_digital_perawatan` dengan file A05 lain milik pasien: `pages/upload/DOKUMEN_OPERASI_LAIN_2026-08-12-000303.pdf`.
    3. Jalankan `uploadPdf()`.
    4. Assert bahwa file lama `PENANDAAN_OPERASI_...` terhapus, file baru `PENANDAAN_OPERASI_...` tersimpan, dan file `DOKUMEN_OPERASI_LAIN_...` tetap ada (tidak terhapus).

## Verification Plan

### Automated Tests
- Jalankan test suite:
  ```bash
  ./vendor/bin/phpunit tests/Feature/PenandaanAreaOperasiAutoUploadTest.php
  ```
