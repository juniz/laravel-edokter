# Automated PDF Upload for Penandaan Area Operasi (Kode A05) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Automatically generate a single-page DomPDF document for Penandaan Area Operasi upon saving and upload it directly to the digital medical records webapps server with master berkas code `A05`.

**Architecture:** A dedicated service `App\Services\PenandaanAreaOperasiService` compiles marking data, encodes diagrams and TTE QR verification code to Base64 data URIs, generates a DomPDF stream using a table-based template `prints.penandaan-area-operasi-pdf`, and uploads it via Laravel `Http::post` to `https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php`. The Livewire component `PenandaanAreaOperasi` triggers this service in `simpan()` and alerts the user.

**Tech Stack:** PHP 8.x, Laravel 9, Livewire 2, Barryvdh\DomPDF, Guzzle/Laravel HTTP Client.

## Global Constraints
- Target files:
  - `app/Services/PenandaanAreaOperasiService.php` (NEW)
  - `resources/views/prints/penandaan-area-operasi-pdf.blade.php` (NEW)
  - `app/Http/Livewire/Component/PenandaanAreaOperasi.php` (MODIFY)
  - `app/Http/Controllers/PenandaanAreaOperasiController.php` (MODIFY)
  - `tests/Feature/PenandaanAreaOperasiAutoUploadTest.php` (NEW)
- Master berkas code: `A05`
- External upload endpoint: `https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php`
- Layout: Single-page A4 portrait PDF using table markup compatible with DomPDF.

---

### Task 1: Create DomPDF-Compatible Blade View for Penandaan Area Operasi

**Files:**
- Create: `resources/views/prints/penandaan-area-operasi-pdf.blade.php`

**Interfaces:**
- Consumes:
  - `$marking`: Object containing `no_rawat`, `no_rm`, `nama_pasien`, `tgl_lahir`, `prosedur_operasi`, `operator_id`, `nama_operator`, `tanggal_penandaan`, `gambar_penandaan_base64`.
  - `$diagrams`: Array of 14 key-value pairs (`body_front`, `body_back`, `head_left_profile`, etc.) containing Base64 data URIs.
  - `$setting`: Object with hospital metadata and Base64 logo.
  - `$umurPasien`: String (e.g. "35 Th").
  - `$jenisKelaminTitle`: String ("LAKI-LAKI" / "PEREMPUAN").
  - `$tanggalPenandaan`: Formatted string.
  - `$qrBase64`: Base64 PNG/SVG data URI for TTE QR verification.
- Produces: Clean HTML/CSS table-based template renderable by DomPDF on 1 A4 page.

- [ ] **Step 1: Create `resources/views/prints/penandaan-area-operasi-pdf.blade.php`**

Write table-based layout strictly avoiding CSS `display: grid` and `display: flex` (which DomPDF does not support):
- Header Kop with hospital info & logo Base64.
- Patient metadata table (No. RM, Nama Pasien, Umur, Tgl Lahir, Prosedur Operasi).
- 2x2 table layout containing the 4 anatomical groups (Badan, Kepala, Kaki, Tangan) displaying the 14 diagrams.
- TTE signature block on the bottom right containing the QR code Base64 image and electronic signature metadata.

- [ ] **Step 2: Commit**

```bash
git add resources/views/prints/penandaan-area-operasi-pdf.blade.php
git commit -m "feat(penandaan): add dompdf compatible blade view for penandaan area operasi"
```

---

### Task 2: Implement `PenandaanAreaOperasiService` for PDF Generation and Webapps Upload

**Files:**
- Create: `app/Services/PenandaanAreaOperasiService.php`

**Interfaces:**
- Consumes:
  - `$noRawat` (string)
- Produces:
  - `PenandaanAreaOperasiService::generateAndUploadPdf(string $noRawat): array` returning `['success' => bool, 'message' => string, 'pdf_url' => ?string]`

- [ ] **Step 1: Create `app/Services/PenandaanAreaOperasiService.php`**

Implement `PenandaanAreaOperasiService` with methods:
1. `generatePdf(string $noRawat): string`:
   - Retrieves marking record joined with `pasien`, `reg_periksa`, `dokter`, and `setting`.
   - Prepares all 14 diagram Base64 images (falling back to file-read Base64 for untouched SVG files in `public/img/penandaan-area-operasi/`).
   - Converts hospital logo to Base64 data URI.
   - Generates TTE verification URL & embedded QR code data URI.
   - Loads `prints.penandaan-area-operasi-pdf` into `Barryvdh\DomPDF\Facade\Pdf::loadView(...)` with `setPaper('a4', 'portrait')`.
   - Returns binary PDF content (`$pdf->output()`).
2. `uploadPdf(string $noRawat, string $pdfBinary): array`:
   - Encodes binary to base64 (`base64_encode($pdfBinary)`).
   - Generates `nama_file`: `'PENANDAAN_OPERASI_' . str_replace('/', '-', $noRawat) . '_' . date('YmdHis')`.
   - Sends `Http::post` to `https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php` with:
     ```php
     [
         'file' => $pdfBase64,
         'no_rawat' => $noRawat,
         'kd_berkas' => 'A05',
         'nama_file' => $namaFile,
     ]
     ```
   - Checks `$response->successful()` and JSON `success` key.
   - Returns result array.
3. `generateAndUploadPdf(string $noRawat): array`:
   - Wrapper orchestrating `generatePdf` and `uploadPdf`.

- [ ] **Step 2: Check PHP syntax**

Run: `php -l app/Services/PenandaanAreaOperasiService.php`
Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
git add app/Services/PenandaanAreaOperasiService.php
git commit -m "feat(penandaan): add PenandaanAreaOperasiService for pdf generation and webapps upload"
```

---

### Task 3: Integrate Automated Upload in Livewire Component and Controller

**Files:**
- Modify: `app/Http/Livewire/Component/PenandaanAreaOperasi.php`
- Modify: `app/Http/Controllers/PenandaanAreaOperasiController.php`

**Interfaces:**
- Consumes: `PenandaanAreaOperasiService::generateAndUploadPdf($this->noRawat)`
- Produces: Immediate PDF upload upon saving penandaan area operasi and informative user feedback.

- [ ] **Step 1: Update `app/Http/Livewire/Component/PenandaanAreaOperasi.php`**

In `simpan()` method:
- Keep the `DB::table('penandaan_area_operasi')->updateOrInsert(...)` call.
- After saving to database, call `PenandaanAreaOperasiService::generateAndUploadPdf($this->noRawat)`.
- If upload is successful, alert: `'Penandaan area operasi berhasil disimpan dan diunggah ke berkas digital.'`.
- If upload fails with an exception or unsuccessful response, log the error and alert: `'Penandaan area operasi berhasil disimpan, namun gagal diunggah otomatis: ' . $uploadResult['message']`.

- [ ] **Step 2: Update `app/Http/Controllers/PenandaanAreaOperasiController.php`**

Add an endpoint/method `uploadPdf(Request $request)` that allows re-triggering the upload for a given `no_rawat` if needed:
- Accepts `no_rawat` parameter.
- Calls `PenandaanAreaOperasiService::generateAndUploadPdf($noRawat)`.
- Returns JSON response matching `MasterEkgController::cetakEkg`.

- [ ] **Step 3: Check PHP syntax**

Run: `php -l app/Http/Livewire/Component/PenandaanAreaOperasi.php && php -l app/Http/Controllers/PenandaanAreaOperasiController.php`
Expected: `No syntax errors detected`

- [ ] **Step 4: Commit**

```bash
git add app/Http/Livewire/Component/PenandaanAreaOperasi.php app/Http/Controllers/PenandaanAreaOperasiController.php
git commit -m "feat(penandaan): integrate automated upload into Livewire component and controller"
```

---

### Task 4: Automated Testing & Verification

**Files:**
- Create: `tests/Feature/PenandaanAreaOperasiAutoUploadTest.php`

**Interfaces:**
- Tests `PenandaanAreaOperasiService::generateAndUploadPdf()` with mock `Http::fake()`.
- Tests Livewire `PenandaanAreaOperasi` component saving and uploading.

- [ ] **Step 1: Create `tests/Feature/PenandaanAreaOperasiAutoUploadTest.php`**

Write test methods:
1. `test_service_generates_pdf_and_uploads_to_webapps_with_code_A05`:
   - Setup mock patient, registration, doctor, and marking records.
   - Use `Http::fake()` to intercept `https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php`.
   - Call `PenandaanAreaOperasiService::generateAndUploadPdf('2026/08/19/000001')`.
   - Assert `Http::assertSent` verified payload has `kd_berkas == 'A05'`, non-empty `file`, correct `no_rawat`.
2. `test_livewire_simpan_triggers_automated_upload`:
   - Mount Livewire `PenandaanAreaOperasi` component with test visit.
   - Set diagram payload and procedure name.
   - `Http::fake()` returning `['success' => true]`.
   - Call `simpan()`.
   - Assert `Http::assertSent` called.

- [ ] **Step 2: Run test suite**

Run: `php -l tests/Feature/PenandaanAreaOperasiAutoUploadTest.php`
Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/PenandaanAreaOperasiAutoUploadTest.php
git commit -m "test(penandaan): add feature tests for automated upload with code A05"
```
