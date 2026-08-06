# TTE Doctor Signature QR Printout Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove the patient signature block from the surgical marking printout and implement a standard Indonesian Electronic Signature (TTE) block for the doctor containing a dynamic QR Code (linked to document verification page) and structured TTE metadata.

**Architecture:**
- Controller: `app/Http/Controllers/PenandaanAreaOperasiController.php` (`cetak` & new `verifikasi` method)
- Views:
  - `resources/views/prints/penandaan-area-operasi.blade.php` (Updated Print View)
  - `resources/views/prints/verifikasi-penandaan.blade.php` (New Digital Verification Certificate Page)
- Routes: `routes/web.php` (`penandaan-area-operasi.verifikasi`)

**Tech Stack:** Laravel, Blade, JavaScript (QRCode generator / SVG).

## Global Constraints
- Target files:
  - `app/Http/Controllers/PenandaanAreaOperasiController.php`
  - `resources/views/prints/penandaan-area-operasi.blade.php`
  - `resources/views/prints/verifikasi-penandaan.blade.php`
  - `routes/web.php`
- Print layout: A4 portrait single page.

---

### Task 1: Create Document Verification Page & Controller Method

**Files:**
- Modify: `app/Http/Controllers/PenandaanAreaOperasiController.php`
- Create: `resources/views/prints/verifikasi-penandaan.blade.php`
- Modify: `routes/web.php`

**Interfaces:**
- Consumes: `no_rawat` parameter.
- Produces: Digital Verification Certificate page view.

- [ ] **Step 1: Add `verifikasi()` method to `PenandaanAreaOperasiController.php`**

Add `verifikasi(Request $request, string $no_rawat = null)` method:
1. Normalize `no_rawat` (replace `-` back to `/`).
2. Query `penandaan_area_operasi` joined with `reg_periksa`, `pasien`, `dokter`.
3. Compute `jenisKelaminTitle`, `umurPasien`, and format timestamps.
4. Pass data to view `prints.verifikasi-penandaan`.

- [ ] **Step 2: Create `resources/views/prints/verifikasi-penandaan.blade.php`**

Create a clean Bootstrap/responsive verification certificate view:
- Badge: ✅ *Dokumen Penandaan Area Operasi Terverifikasi Sah secara Digital*.
- Table displaying Patient Name, RM Number, Visit Number, Surgical Procedure, Doctor Name, Doctor Code/SIP, and Signed Timestamp.

- [ ] **Step 3: Register route in `routes/web.php`**

Add: `Route::get('/penandaan-area-operasi/verifikasi/{no_rawat?}', [PenandaanAreaOperasiController::class, 'verifikasi'])->name('penandaan-area-operasi.verifikasi');`

- [ ] **Step 4: Validate PHP syntax**

Run: `php -l app/Http/Controllers/PenandaanAreaOperasiController.php`
Expected: `No syntax errors detected`

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/PenandaanAreaOperasiController.php resources/views/prints/verifikasi-penandaan.blade.php routes/web.php
git commit -m "feat(penandaan-area-operasi): add digital document verification route and view"
```

---

### Task 2: Redesign Printout Footer with Doctor TTE QR Signature Block

**Files:**
- Modify: `resources/views/prints/penandaan-area-operasi.blade.php`

**Interfaces:**
- Consumes: `$marking`, `$tanggalPenandaan`, `$verificationUrl`.
- Produces: Updated print view without patient signature block and with right-aligned doctor TTE QR block.

- [ ] **Step 1: Update `resources/views/prints/penandaan-area-operasi.blade.php`**

1. Remove `Pasien / Keluarga Pasien` column from signature table.
2. Align signature section to right side:
   - Header: `Nganjuk, {{ $tanggalPenandaan }}`
   - Subheader: `Dokter Operator`
   - TTE Container:
     - QR Code canvas/SVG rendering verification URL: `{{ route('penandaan-area-operasi.verifikasi', ['no_rawat' => str_replace('/', '-', $marking->no_rawat)]) }}`
     - Side Metadata text:
       - `Tersertifikasi Elektronik:`
       - `dr. {{ $marking->nama_operator }}`
       - `Kode: {{ $marking->operator_id }}`
       - `Waktu TTD: {{ $tanggalPenandaan }}`
   - Footer name: `( dr. {{ $marking->nama_operator }} )`
3. Embed inline client-side QR generator or SVG generator so it renders 100% offline cleanly.

- [ ] **Step 2: Commit**

```bash
git add resources/views/prints/penandaan-area-operasi.blade.php
git commit -m "feat(penandaan-area-operasi): replace patient signature with doctor TTE QR signature block"
```

---

### Task 3: Integration Verification & Syntax Checks

**Files:**
- Verify: `app/Http/Controllers/PenandaanAreaOperasiController.php`
- Verify: `resources/views/prints/penandaan-area-operasi.blade.php`
- Verify: `resources/views/prints/verifikasi-penandaan.blade.php`

**Interfaces:**
- Consumes: All modified files.
- Produces: Verification status.

- [ ] **Step 1: Run PHP syntax check**

Run: `php -l app/Http/Controllers/PenandaanAreaOperasiController.php`
Expected: `No syntax errors detected`

- [ ] **Step 2: Check git status**

Run: `git status`
Expected: Clean working tree
