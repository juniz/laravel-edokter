# Penilaian Pre Operasi Auto Upload Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Automate digital PDF generation and upload for Penilaian Pre Operasi under master berkas code `A05` with safe replace on update.

**Architecture:** Dedicated `App\Services\PenilaianPreOperasiService` generates single-page A4 PDF (`prints.penilaian-pre-operasi-pdf`), uploads to SIMRS webapps endpoint (`upload-berkas-edokter.php`), and cleans older `PENILAIAN_PRE_OPERASI_{no_rawat}_...` entries in `berkas_digital_perawatan`. Livewire component `PenilaianPreOperasi` triggers this on save/update/delete.

**Tech Stack:** Laravel 9, Livewire 2, DomPDF, GD QrCodeGenerator, PHPUnit.

## Global Constraints
- Target Master Berkas Code: `A05`
- Target Endpoint: `https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php`
- Filename Prefix: `PENILAIAN_PRE_OPERASI_{sanitized_no_rawat}_`
- Layout: Single-page A4 portrait DomPDF table layout (no CSS flex/grid, Kop RS on left, clean borderless logo).

---

### Task 1: Create DomPDF Blade Template for Penilaian Pre Operasi

**Files:**
- Create: `resources/views/prints/penilaian-pre-operasi-pdf.blade.php`

- [ ] **Step 1: Write Blade template**
  Create `resources/views/prints/penilaian-pre-operasi-pdf.blade.php` matching design with Kop on left, barcode SVG, metadata, 7 assessment sections, and TTE QR code box.
- [ ] **Step 2: Validate syntax**
  Run `php -l resources/views/prints/penilaian-pre-operasi-pdf.blade.php`.
- [ ] **Step 3: Commit**
  Run `git add resources/views/prints/penilaian-pre-operasi-pdf.blade.php && git commit -m "feat(penilaian-pre-op): create DomPDF blade template"`.

---

### Task 2: Implement PenilaianPreOperasiService

**Files:**
- Create: `app/Services/PenilaianPreOperasiService.php`

- [ ] **Step 1: Implement methods in service**
  Implement `generatePdf()`, `uploadPdf()`, `generateAndUploadPdf()`, and `deleteDigitalFile()` with safe replace query on `berkas_digital_perawatan`.
- [ ] **Step 2: Validate syntax**
  Run `php -l app/Services/PenilaianPreOperasiService.php`.
- [ ] **Step 3: Commit**
  Run `git add app/Services/PenilaianPreOperasiService.php && git commit -m "feat(penilaian-pre-op): create PenilaianPreOperasiService"`.

---

### Task 3: Controller & Routes for Download & Upload PDF

**Files:**
- Create: `app/Http/Controllers/PenilaianPreOperasiController.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create controller with `downloadPdf` and `uploadPdf`**
- [ ] **Step 2: Add web routes**
- [ ] **Step 3: Validate syntax**
  Run `php -l app/Http/Controllers/PenilaianPreOperasiController.php && php -l routes/web.php`.
- [ ] **Step 4: Commit**
  Run `git add app/Http/Controllers/PenilaianPreOperasiController.php routes/web.php && git commit -m "feat(penilaian-pre-op): add controller and routes for PDF download and upload"`.

---

### Task 4: Integrate Auto Upload in Livewire Component and UI

**Files:**
- Modify: `app/Http/Livewire/Component/PenilaianPreOperasi.php`
- Modify: `resources/views/livewire/component/penilaian-pre-operasi.blade.php`

- [ ] **Step 1: Update `simpan()` and `update()` in `PenilaianPreOperasi.php` to call service**
- [ ] **Step 2: Update `hapus()` in `PenilaianPreOperasi.php` to delete digital file**
- [ ] **Step 3: Add Cetak / Download PDF button in `penilaian-pre-operasi.blade.php` history table actions**
- [ ] **Step 4: Validate syntax**
  Run `php -l app/Http/Livewire/Component/PenilaianPreOperasi.php && php -l resources/views/livewire/component/penilaian-pre-operasi.blade.php`.
- [ ] **Step 5: Commit**
  Run `git add app/Http/Livewire/Component/PenilaianPreOperasi.php resources/views/livewire/component/penilaian-pre-operasi.blade.php && git commit -m "feat(penilaian-pre-op): integrate auto upload and print action in livewire component"`.

---

### Task 5: Automated Testing & Verification

**Files:**
- Create: `tests/Feature/PenilaianPreOperasiAutoUploadTest.php`

- [ ] **Step 1: Write test suite covering PDF generation, upload A05, safe replacement, preservation of other A05 documents, deletion, and controller endpoints**
- [ ] **Step 2: Run test suite**
  Run `./vendor/bin/phpunit tests/Feature/PenilaianPreOperasiAutoUploadTest.php`.
- [ ] **Step 3: Commit**
  Run `git add tests/Feature/PenilaianPreOperasiAutoUploadTest.php && git commit -m "test(penilaian-pre-op): add automated feature tests for auto upload and replacement"`.

---

## Verification Plan
- Run `./vendor/bin/phpunit tests/Feature/PenilaianPreOperasiAutoUploadTest.php`.
- Run all penandaan and penilaian tests to verify zero regressions.
