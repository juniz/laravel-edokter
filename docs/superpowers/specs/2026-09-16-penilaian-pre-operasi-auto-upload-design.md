# Design Spec: Auto Upload Berkas Digital Penilaian Pre Operasi (Kode A05)

## Overview
Automate PDF generation and digital upload for **Penilaian Pre Operasi** (`penilaian_pre_operasi` table) using master berkas code `A05` (`05.1.BERKAS LAPORAN OPERASI`).
When assessment saved or updated in `PenilaianPreOperasi` Livewire component, system renders A4 PDF, uploads to `upload-berkas-edokter.php`, and safely replaces existing digital file based on filename pattern `PENILAIAN_PRE_OPERASI_{sanitized_no_rawat}_...`.

## Architecture & Components

### 1. PDF Template (`resources/views/prints/penilaian-pre-operasi-pdf.blade.php`)
- **Engine**: DomPDF (pure HTML `<table>`, CSS inline/table-based, zero flex/grid).
- **Page Setup**: `@page { size: A4 portrait; margin: 0; }` with `body { margin: 5mm 10mm; font-size: 11px; }`.
- **Header**:
  - Left column: Hospital logo on top, centered above Polri & hospital text (`border: none; outline: none;`).
  - Right column: `RM. ... / Hal ...` box.
- **Patient Info Table**:
  - No. RM, Barcode graphic, Nama Pasien, Tgl Lahir / Umur, Jenis Kelamin, No. Rawat, Dokter Operator, Tanggal Penilaian.
- **Assessment Content Table**:
  - 1. Ringkasan Klinik
  - 2. Pemeriksaan Fisik
  - 3. Pemeriksaan Diagnostik
  - 4. Diagnosa Pre Operasi
  - 5. Rencana Tindakan Bedah
  - 6. Hal-hal Yang Perlu Dipersiapkan
  - 7. Terapi Pre Operasi
- **Doctor TTE Signature**:
  - Bottom right box with offline QR code data URI generated via `QrCodeGenerator::pngDataUri()`.

### 2. Service Layer (`app/Services/PenilaianPreOperasiService.php`)
- **`generatePdf(string $noRawat, ?string $tanggal = null): string`**:
  - Query `penilaian_pre_operasi` joined with `reg_periksa`, `pasien`, `dokter`, `setting`.
  - Format patient age, gender title, dates.
  - Generate TTE QR code offline.
  - Render `prints.penilaian-pre-operasi-pdf` via DomPDF `setPaper('a4', 'portrait')`.
  - Return binary PDF string.
- **`uploadPdf(string $noRawat, string $pdfBinary, ?string $tanggal = null): array`**:
  - Filename: `$filePrefix = 'PENILAIAN_PRE_OPERASI_' . str_replace('/', '-', $noRawat) . '_';`
  - Filename: `$namaFile = $filePrefix . date('YmdHis');`
  - Upload via `Http::post('https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php', ...)` with `kd_berkas = 'A05'`.
  - On success:
    ```php
    DB::table('berkas_digital_perawatan')
        ->where('no_rawat', $noRawat)
        ->where('kode', 'A05')
        ->where('lokasi_file', 'like', '%' . $filePrefix . '%')
        ->where('lokasi_file', '<>', 'pages/upload/' . $namaFile . '.pdf')
        ->delete();
    ```
  - Return `['success' => bool, 'message' => string, 'pdf_url' => ?string, 'is_replaced' => bool]`.
- **`generateAndUploadPdf(string $noRawat, ?string $tanggal = null): array`**:
  - Coordinates PDF generation and upload.
- **`deleteDigitalFile(string $noRawat): void`**:
  - Remove `PENILAIAN_PRE_OPERASI` records from `berkas_digital_perawatan` when assessment deleted.

### 3. Livewire Component (`app/Http/Livewire/Component/PenilaianPreOperasi.php`)
- In `simpan()`:
  - After DB insert, call `PenilaianPreOperasiService::generateAndUploadPdf($this->no_rawat, $formattedTanggal)`.
  - Show alert: `"Penilaian pre operasi berhasil disimpan dan diunggah ke berkas digital."`.
- In `update()`:
  - After DB update, call `PenilaianPreOperasiService::generateAndUploadPdf($this->no_rawat, $formattedTanggal)`.
  - Show alert: `"Penilaian pre operasi berhasil diubah dan berkas digital diperbarui."`.
- In `hapus()`:
  - After DB delete, call `PenilaianPreOperasiService::deleteDigitalFile($this->no_rawat)`.
- In View (`resources/views/livewire/component/penilaian-pre-operasi.blade.php`):
  - Add "Cetak / PDF" button in Riwayat table actions opening `/penilaian-pre-operasi/download-pdf?no_rawat=...&tanggal=...`.

### 4. Controller & Routes (`app/Http/Controllers/PenilaianPreOperasiController.php` + `routes/web.php`)
- `GET /penilaian-pre-operasi/download-pdf`: Stream PDF binary inline.
- `POST /penilaian-pre-operasi/upload-pdf`: API upload endpoint returning JSON.

### 5. Automated Tests (`tests/Feature/PenilaianPreOperasiAutoUploadTest.php`)
- Test PDF generation & upload with `Http::fake()`.
- Test Livewire `simpan()` and `update()` trigger auto upload.
- Test safe replace deletes old `PENILAIAN_PRE_OPERASI` file and preserves other `A05` documents.
- Test `hapus()` cleans digital berkas entry.
- Test controller endpoints.
