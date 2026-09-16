# Penilaian Pre Induksi Auto Upload Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Automate digital PDF generation and upload for Penilaian Pre Induksi under master berkas code `A07` with safe replace on update.

**Architecture:** Dedicated `App\Services\PenilaianPreInduksiService` generates A4 PDF (`prints.penilaian-pre-induksi-pdf`), uploads to SIMRS webapps endpoint (`upload-berkas-edokter.php`), and cleans older `PENILAIAN_PRE_INDUKSI_{no_rawat}_...` entries in `berkas_digital_perawatan`. Livewire component `PenilaianPreInduksi` triggers this on save/update/delete.

**Tech Stack:** Laravel 9, Livewire 2, DomPDF, GD QrCodeGenerator, PHPUnit.

## Global Constraints
- Target Master Berkas Code: `A07`
- Target Endpoint: `https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php`
- Filename Prefix: `PENILAIAN_PRE_INDUKSI_{sanitized_no_rawat}_`
- Layout: DomPDF table layout (no CSS flex/grid, Kop RS on left, clean borderless logo, no doc box).

---

### Task 1: Create DomPDF Blade Template for Penilaian Pre Induksi

**Files:**
- Create: `resources/views/prints/penilaian-pre-induksi-pdf.blade.php`

- [ ] **Step 1: Write Blade template**
  Create `resources/views/prints/penilaian-pre-induksi-pdf.blade.php` with Kop on left, barcode SVG, metadata, vital signs, assessment, airway, regional technique, and TTE QR code box.
- [ ] **Step 2: Validate syntax**
  Run `php -l resources/views/prints/penilaian-pre-induksi-pdf.blade.php`.
- [ ] **Step 3: Commit**
  Run `git add resources/views/prints/penilaian-pre-induksi-pdf.blade.php && git commit -m "feat(penilaian-pre-induksi): create DomPDF blade template"`.

---

### Task 2: Implement PenilaianPreInduksiService

**Files:**
- Create: `app/Services/PenilaianPreInduksiService.php`

- [ ] **Step 1: Implement methods in service**
  Implement `generatePdf()`, `uploadPdf()`, `generateAndUploadPdf()`, and `deleteDigitalFile()` with safe replace query on `berkas_digital_perawatan` (`kode = 'A07'`).
- [ ] **Step 2: Validate syntax**
  Run `php -l app/Services/PenilaianPreInduksiService.php`.
- [ ] **Step 3: Commit**
  Run `git add app/Services/PenilaianPreInduksiService.php && git commit -m "feat(penilaian-pre-induksi): create PenilaianPreInduksiService"`.

---

### Task 3: Controller & Routes for Download & Upload PDF

**Files:**
- Create: `app/Http/Controllers/PenilaianPreInduksiController.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create controller with `downloadPdf` and `uploadPdf`**
- [ ] **Step 2: Add web routes**
- [ ] **Step 3: Validate syntax**
  Run `php -l app/Http/Controllers/PenilaianPreInduksiController.php && php -l routes/web.php`.
- [ ] **Step 4: Commit**
  Run `git add app/Http/Controllers/PenilaianPreInduksiController.php routes/web.php && git commit -m "feat(penilaian-pre-induksi): add controller and routes for PDF download and upload"`.

---

### Task 4: Integrate Auto Upload in Livewire Component and UI

**Files:**
- Modify: `app/Http/Livewire/Component/PenilaianPreInduksi.php`
- Modify: `resources/views/livewire/component/penilaian-pre-induksi.blade.php`

- [ ] **Step 1: Update `simpan()` and `update()` in `PenilaianPreInduksi.php` to call service**
- [ ] **Step 2: Update `hapus()` in `PenilaianPreInduksi.php` to delete digital file**
- [ ] **Step 3: Add Cetak / Download PDF button in `penilaian-pre-induksi.blade.php` history table actions**
- [ ] **Step 4: Validate syntax**
  Run `php -l app/Http/Livewire/Component/PenilaianPreInduksi.php && php -l resources/views/livewire/component/penilaian-pre-induksi.blade.php`.
- [ ] **Step 5: Commit**
  Run `git add app/Http/Livewire/Component/PenilaianPreInduksi.php resources/views/livewire/component/penilaian-pre-induksi.blade.php && git commit -m "feat(penilaian-pre-induksi): integrate auto upload and print action in livewire component"`.

---

### Task 5: Automated Testing & Verification

**Files:**
- Create: `tests/Feature/PenilaianPreInduksiAutoUploadTest.php`

- [ ] **Step 1: Write test suite covering PDF generation, upload A07, safe replacement, preservation of other A07 documents, deletion, and controller endpoints**
- [ ] **Step 2: Run test suite**
  Run `./vendor/bin/phpunit tests/Feature/PenilaianPreInduksiAutoUploadTest.php`.
- [ ] **Step 3: Commit**
  Run `git add tests/Feature/PenilaianPreInduksiAutoUploadTest.php && git commit -m "test(penilaian-pre-induksi): add automated feature tests for auto upload and replacement"`.

---

## Verification Plan
- Run `./vendor/bin/phpunit tests/Feature/PenilaianPreInduksiAutoUploadTest.php`.
- Run all related test suites to verify zero regressions.
