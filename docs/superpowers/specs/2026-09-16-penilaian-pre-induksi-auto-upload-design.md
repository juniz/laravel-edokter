# Design Spec: Auto Upload Berkas Digital Penilaian Pre Induksi (Kode A07)

## Overview
Automate PDF generation and digital upload for **Penilaian Pre Induksi** (`penilaian_pre_induksi` table) using master berkas code `A07` (`06.BERKAS LAPORAN ANESTESI`).
When assessment saved or updated in `PenilaianPreInduksi` Livewire component, system renders A4 PDF, uploads to `upload-berkas-edokter.php`, and safely replaces existing digital file based on filename pattern `PENILAIAN_PRE_INDUKSI_{sanitized_no_rawat}_...`.

## Architecture & Components

### 1. PDF Template (`resources/views/prints/penilaian-pre-induksi-pdf.blade.php`)
- **Engine**: DomPDF (pure HTML `<table>`, inline/table-based CSS, zero flex/grid).
- **Page Setup**: `@page { size: A4 portrait; margin: 0; }` with `body { margin: 5mm 10mm; font-size: 10px; }`.
- **Header**:
  - Left: Hospital logo on top of Polri & hospital text (`border: none; outline: none;`).
  - Right: Empty column (no doc box).
- **Patient Info Table**:
  - No. RM, Barcode graphic, Nama Pasien, Tgl Lahir / Umur, Jenis Kelamin, No. Rawat, Tanggal Assessment, Dokter Anestesi.
- **Content Sections**:
  - 1. Tanda Vital & Penunjang (TD, Nadi, RR, Suhu, EKG, Lain-lain)
  - 2. Asesmen & Perencanaan
  - 3. Akses Vena & Posisi (Infus Perifier, CVC, Posisi)
  - 4. Premedikasi & Induksi
  - 5. Tata Laksana Jalan Nafas & Intubasi (Face mask, ETT, LMA, dll)
  - 6. Teknik Regional
- **Doctor TTE Signature**:
  - Bottom right box with offline QR code data URI generated via `QrCodeGenerator::pngDataUri()`.

### 2. Service Layer (`app/Services/PenilaianPreInduksiService.php`)
- **`generatePdf(string $noRawat, ?string $tanggal = null): string`**:
  - Query `penilaian_pre_induksi` joined with `reg_periksa`, `pasien`, `dokter`, `setting`.
  - Format patient age, gender title, dates.
  - Generate TTE QR code offline.
  - Render `prints.penilaian-pre-induksi-pdf` via DomPDF `setPaper('a4', 'portrait')`.
  - Return binary PDF string.
- **`uploadPdf(string $noRawat, string $pdfBinary, ?string $tanggal = null): array`**:
  - Filename: `$filePrefix = 'PENILAIAN_PRE_INDUKSI_' . str_replace('/', '-', $noRawat) . '_';`
  - Filename: `$namaFile = $filePrefix . date('YmdHis');`
  - Upload via `Http::post('https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php', ...)` with `kd_berkas = 'A07'`.
  - On success:
    ```php
    DB::table('berkas_digital_perawatan')
        ->where('no_rawat', $noRawat)
        ->where('kode', 'A07')
        ->where('lokasi_file', 'like', '%' . $filePrefix . '%')
        ->where('lokasi_file', '<>', 'pages/upload/' . $namaFile . '.pdf')
        ->delete();
    ```
  - Return `['success' => bool, 'message' => string, 'pdf_url' => ?string, 'is_replaced' => bool]`.
- **`generateAndUploadPdf(string $noRawat, ?string $tanggal = null): array`**:
  - Coordinates PDF generation and upload.
- **`deleteDigitalFile(string $noRawat): void`**:
  - Remove `PENILAIAN_PRE_INDUKSI` records from `berkas_digital_perawatan` where `kode = 'A07'`.

### 3. Livewire Component (`app/Http/Livewire/Component/PenilaianPreInduksi.php`)
- In `simpan()`:
  - After DB insert, call `PenilaianPreInduksiService::generateAndUploadPdf($this->no_rawat, $formattedTanggal)`.
  - Show alert: `"Penilaian Pre Induksi berhasil disimpan dan diunggah ke berkas digital."`.
- In `update()`:
  - After DB update, call `PenilaianPreInduksiService::generateAndUploadPdf($this->no_rawat, $formattedTanggal)`.
  - Show alert: `"Penilaian Pre Induksi berhasil diperbarui dan berkas digital diperbarui."`.
- In `hapus()`:
  - After DB delete, call `PenilaianPreInduksiService::deleteDigitalFile($this->no_rawat)`.
- In View (`resources/views/livewire/component/penilaian-pre-induksi.blade.php`):
  - Add "Cetak / PDF" button in Riwayat table actions opening `/penilaian-pre-induksi/download-pdf?no_rawat=...&tanggal=...`.

### 4. Controller & Routes (`app/Http/Controllers/PenilaianPreInduksiController.php` + `routes/web.php`)
- `GET /penilaian-pre-induksi/download-pdf`: Stream PDF binary inline.
- `POST /penilaian-pre-induksi/upload-pdf`: API upload endpoint returning JSON.

### 5. Automated Tests (`tests/Feature/PenilaianPreInduksiAutoUploadTest.php`)
- Test PDF generation & upload with `kd_berkas = 'A07'`.
- Test Livewire `simpan()` and `update()` trigger auto upload.
- Test safe replace deletes old `PENILAIAN_PRE_INDUKSI` file and preserves other `A07` documents.
- Test `hapus()` cleans digital berkas entry.
- Test controller endpoints.
