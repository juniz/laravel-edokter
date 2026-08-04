# Surgical Site Marking Printout (Formulir Penandaan Area Operasi) Design Spec

**Date**: 2026-08-04  
**Module**: `penandaan-area-operasi` (Surgical Site Marking)  
**Target Files**:
- `resources/views/prints/penandaan-area-operasi.blade.php` (Print View Template)
- `app/Http/Controllers/PenandaanAreaOperasiController.php` (Print Controller)
- `app/Http/Livewire/Component/PenandaanAreaOperasi.php` (Livewire Component)
- `resources/views/livewire/component/penandaan-area-operasi.blade.php` (Livewire View)
- `routes/web.php` (Web Route for Printout)

---

## 1. Executive Summary

Implement an official A4 portrait single-page printout template matching 1:1 the hospital's official form layout (`FORMULIR PENANDAAN AREA OPERASI`). The printout dynamically adapts title according to patient gender (`PEREMPUAN` vs `LAKI-LAKI`), renders hospital header, patient barcode & metadata, a 2x3 combined diagram grid with anatomical directional labels ("Kanan" / "Kiri"), formal patient & surgeon approval declaration text, and dual signature blocks.

---

## 2. Technical Architecture & Component Flow

```
[ Dokter / Perawat ]
        │
        ▼ Click "Cetak" Button
[ Livewire View ] ──► Opens Window: GET /penandaan-area-operasi/cetak/{no_rawat}
                                             │
                                             ▼
                        [ PenandaanAreaOperasiController@cetak ]
                                             │
                                             ▼ Fetches reg_periksa, pasien, dokter, & penandaan_area_operasi
                        [ View: prints.penandaan-area-operasi ]
                                             │
                                             ▼ Render A4 Single-Page Template
                                    [ window.print() ]
```

---

## 3. Print Layout Structure (A4 Portrait 1:1 Match)

### 3.1 Header & Patient Information
- **Top Header Left**: Hospital Kop (`POLRI DAERAH JAWA TIMUR / BIDANG KEDOKTERAN DAN KESEHATAN / RUMAH SAKIT BHAYANGKARA TK. III NGANJUK`).
- **Top Header Right Box**: `RM. 41 / Halaman 2`.
- **Title Banner**: `FORMULIR PENANDAAN AREA OPERASI (PEREMPUAN)` or `FORMULIR PENANDAAN AREA OPERASI (LAKI-LAKI)`.
- **Patient & Operative Metadata Block**:
  - `NO. RM`: Patient Medical Record Number with HTML/SVG Barcode.
  - `NAMA`: Patient Name & Age (e.g., `SITI MUTMAINAH, NY. (66 Th)`).
  - `TGL. LAHIR`: Patient Birth Date (`DD/MM/YYYY`).
  - `NO. RAWAT`: Visit/Registration Number.
  - `PROSEDUR OPERASI`: Surgical Procedure Name.
  - `OPERATOR`: Operating Doctor Name & Specialty.

### 3.2 Diagram Grid Layout (2x3 Box Structure)
- **Top Left Box**:
  - Full Body Anterior (`Badan depan`: `Kanan` | `Kiri`)
  - Full Body Posterior (`Badan belakang`: `Kiri` | `Kanan`)
- **Top Right Box**: 2x2 Sub-grid containing:
  - Head Left Profile (`Kepala profil kiri`: `Kiri`) & Head Right Profile (`Kepala profil kanan`: `Kanan`)
  - Head Front (`Kepala depan`: `Kanan` | `Kiri`) & Head Back (`Kepala belakang`: `Kiri` | `Kanan`)
- **Bottom Left Box**:
  - Foot Plantar (`Plantar (posterior)`: `Kiri` | `Kanan`)
  - Foot Dorsal (`Dorsal (anterior)`: `Kiri` | `Kanan`)
- **Bottom Right Box**:
  - Hand Palm (`Palmar (anterior)`: `Kiri` | `Kanan`)
  - Hand Dorsal (`Dorsal (posterior)`: `Kiri` | `Kanan`)

### 3.3 Declaration & Signature Footer
- **Declaration Text**: *"Saya menyatakan bahwa lokasi operasi yang telah ditetapkan pada diagram adalah **benar***"
- **Date & Location**: `Nganjuk, [Tanggal Penandaan / Hari ini]`
- **Signatures Area**:
  - **Left**: `Pasien / Keluarga Pasien` (Name & Signature Line).
  - **Right**: `Dokter Operator` (`(dr. [Nama Operator])`).
- **Footer Code**: `VI/2018/Komite RM` at bottom left.

---

## 4. CSS @media print Specifications

```css
@page {
    size: A4 portrait;
    margin: 8mm 10mm;
}

@media print {
    .no-print {
        display: none !important;
    }
    body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .page-container {
        page-break-inside: avoid;
    }
}
```

---

## 5. UI Integration

- Add a **"Cetak"** button in `resources/views/livewire/component/penandaan-area-operasi.blade.php`:
  ```html
  <button type="button" id="cetak-penandaan" class="btn btn-info btn-sm ml-2" onclick="window.open('/penandaan-area-operasi/cetak/{{ str_replace('/', '-', $noRawat) }}', '_blank')">
      <i class="fas fa-print mr-1"></i> Cetak
  </button>
  ```
- Add route definition in `routes/web.php`:
  ```php
  Route::get('/penandaan-area-operasi/cetak/{no_rawat}', [PenandaanAreaOperasiController::class, 'cetak'])->name('penandaan-area-operasi.cetak');
  ```

---

## 6. Verification Plan

1. **Route Test**: Verify GET `/penandaan-area-operasi/cetak/{no_rawat}` loads without HTTP errors.
2. **PHP Syntax & View Compilation**: Verify Blade syntax compiles cleanly.
3. **Print Layout Verification**: Verify A4 single page fit and proper display of patient barcode, 14 diagram images, gender title, and signature blocks.
