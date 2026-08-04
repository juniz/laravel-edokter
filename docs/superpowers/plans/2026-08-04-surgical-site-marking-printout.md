# Surgical Site Marking Printout Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create an official A4 portrait single-page printout template matching 1:1 the hospital's official form layout (`FORMULIR PENANDAAN AREA OPERASI`) with dynamic gender title (`PEREMPUAN` vs `LAKI-LAKI`), hospital Kop header, patient barcode & metadata, 2x3 diagram grid with anatomical labels ("Kanan"/"Kiri"), declaration text, dual signature blocks, and a "Cetak" button in the Livewire UI.

**Architecture:** 
- Controller: `app/Http/Controllers/PenandaanAreaOperasiController.php`
- View: `resources/views/prints/penandaan-area-operasi.blade.php`
- Component View: `resources/views/livewire/component/penandaan-area-operasi.blade.php`
- Route: `routes/web.php` (`route('penandaan-area-operasi.cetak')`)

**Tech Stack:** Laravel, Blade, CSS `@media print` A4.

## Global Constraints
- Target files:
  - `app/Http/Controllers/PenandaanAreaOperasiController.php`
  - `resources/views/prints/penandaan-area-operasi.blade.php`
  - `resources/views/livewire/component/penandaan-area-operasi.blade.php`
  - `routes/web.php`
- Print page size: A4 portrait (`@page { size: A4 portrait; margin: 6mm 10mm; }`)
- Output: Single page fit without page overflows.

---

### Task 1: Update `PenandaanAreaOperasiController.php`

**Files:**
- Modify: `app/Http/Controllers/PenandaanAreaOperasiController.php`

**Interfaces:**
- Consumes: `no_rawat` query param or request input.
- Produces: Data payload (`marking`, `images`, `setting`, `umurPasien`, `jenisKelaminTitle`) to `prints.penandaan-area-operasi`.

- [ ] **Step 1: Update `PenandaanAreaOperasiController.php`**

Update `cetak(Request $request)` method:
1. Validate `no_rawat`.
2. Query `penandaan_area_operasi` record joined with `reg_periksa`, `pasien`, and `dokter`.
3. Fetch `setting` table for hospital name & header info (fallback to RS Bhayangkara TK. III Nganjuk).
4. Decode `gambar_penandaan_base64`.
5. Compute patient age (`umurPasien`).
6. Compute title suffix (`PEREMPUAN` vs `LAKI-LAKI`).
7. Pass data to view `prints.penandaan-area-operasi`.

- [ ] **Step 2: Validate PHP syntax**

Run: `php -l app/Http/Controllers/PenandaanAreaOperasiController.php`
Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/PenandaanAreaOperasiController.php
git commit -m "feat(penandaan-area-operasi): update controller to provide full printout data model"
```

---

### Task 2: Create A4 1:1 Print Template (`prints/penandaan-area-operasi.blade.php`)

**Files:**
- Modify: `resources/views/prints/penandaan-area-operasi.blade.php`

**Interfaces:**
- Consumes: Data payload from controller.
- Produces: Pixel-perfect A4 print layout matching hospital photo 1:1.

- [ ] **Step 1: Write `resources/views/prints/penandaan-area-operasi.blade.php`**

Implement HTML/CSS matching reference photo 1:1:
1. **Header & Kop**:
   - POLRI / RS Bhayangkara Nganjuk Kop.
   - Right top box `RM. 41 / Halaman 2`.
   - Title: `FORMULIR PENANDAAN AREA OPERASI ({{ $jenisKelaminTitle }})`.
2. **Patient Info**:
   - `NO. RM` with barcode representation.
   - `NAMA`, `TGL. LAHIR`, `NO. RAWAT`, `PROSEDUR OPERASI`, `OPERATOR`.
3. **2x3 Combined Diagram Layout**:
   - Box 1 (Top Left): Full Body Anterior (`Badan depan`: `Kanan` | `Kiri`) & Posterior (`Badan belakang`: `Kiri` | `Kanan`).
   - Box 2 (Top Right): 2x2 grid of Head Profile Left & Right, Head Front & Back.
   - Box 3 (Bottom Left): Foot Plantar (`Plantar (posterior)`: `Kiri` | `Kanan`) & Dorsal (`Dorsal (anterior)`: `Kiri` | `Kanan`).
   - Box 4 (Bottom Right): Hand Palm (`Palmar (anterior)`: `Kiri` | `Kanan`) & Dorsal (`Dorsal (posterior)`: `Kiri` | `Kanan`).
4. **Declaration & Signatures**:
   - Statement: *"Saya menyatakan bahwa lokasi operasi yang telah ditetapkan pada diagram adalah **benar***".
   - Left signature: `Pasien / Keluarga Pasien`.
   - Right signature: `Nganjuk, [Tanggal]` & `(dr. [Nama Operator])`.
   - Bottom Left: `VI/2018/Komite RM`.
5. **Auto-Print**: `<script>window.addEventListener('load', function() { window.print(); });</script>`.

- [ ] **Step 2: Commit**

```bash
git add resources/views/prints/penandaan-area-operasi.blade.php
git commit -m "feat(penandaan-area-operasi): create 1:1 A4 printout template matching official hospital form"
```

---

### Task 3: Add "Cetak" Button in Livewire View & Route Configuration

**Files:**
- Modify: `resources/views/livewire/component/penandaan-area-operasi.blade.php`
- Modify: `routes/web.php`

**Interfaces:**
- Consumes: `$noRawat` property in Blade view.
- Produces: Clickable "Cetak" button opening print page in a new tab.

- [ ] **Step 1: Ensure route in `routes/web.php` accepts query parameters or path params**

Ensure `Route::get('/penandaan-area-operasi/cetak', ...)->name('penandaan-area-operasi.cetak')` is configured properly.

- [ ] **Step 2: Add "Cetak" button to `penandaan-area-operasi.blade.php`**

Add button next to "Simpan":
```html
<a href="{{ route('penandaan-area-operasi.cetak', ['no_rawat' => $noRawat]) }}" target="_blank" class="btn btn-info ml-2" id="cetak-penandaan" title="Cetak Formulir Penandaan Area Operasi (A4)">
    <i class="fas fa-print mr-1" aria-hidden="true"></i>
    Cetak
</a>
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/livewire/component/penandaan-area-operasi.blade.php routes/web.php
git commit -m "feat(penandaan-area-operasi): add Cetak button linking to A4 printout route"
```

---

### Task 4: Integration Verification

**Files:**
- Verify: `app/Http/Controllers/PenandaanAreaOperasiController.php`
- Verify: `resources/views/prints/penandaan-area-operasi.blade.php`
- Verify: `resources/views/livewire/component/penandaan-area-operasi.blade.php`

**Interfaces:**
- Consumes: Full application setup.
- Produces: Verified print workflow.

- [ ] **Step 1: Run PHP syntax linting**

Run: `php -l app/Http/Controllers/PenandaanAreaOperasiController.php`
Expected: `No syntax errors detected`

- [ ] **Step 2: Verify git status clean**

Run: `git status`
Expected: Clean working tree
