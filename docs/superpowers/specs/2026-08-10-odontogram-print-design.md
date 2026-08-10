# Design Spec: Odontogram Print Template & Controller

**Date**: 2026-08-10  
**Feature**: Odontogram Printout (`prints.odontogram`) with Hospital Kop & Penandaan Area Operasi styling

---

## 🎯 Goal

Create a printout template for dental examination (Odontogram) featuring official hospital Kop header, patient metadata table with barcode, summary counters, complete tooth chart grid (Adult & Child), legend, history table, and TTE QR code signature matching the layout style of `prints/penandaan-area-operasi.blade.php`.

---

## 📐 Architecture & Components

### 1. Controller & Route
- File: `app/Http/Controllers/OdontogramPrintController.php`
- Route: `Route::get('/odontogram/cetak', [OdontogramPrintController::class, 'cetak'])->name('odontogram.cetak');`
- Data retrieval:
  - `pemeriksaan_odontogram` records for `$noRawat`
  - `reg_periksa` joined with `pasien` and `dokter` / `petugas`
  - `setting` table for hospital logo & header info
  - Verification token for TTE QR code

### 2. View Template (`resources/views/prints/odontogram.blade.php`)
- **Header Kop**: POLRI DAERAH JAWA TIMUR, BIDANG KEDOKTERAN DAN KESEHATAN, RUMAH SAKIT BHAYANGKARA TK. III NGANJUK, logo, and RM code `RM. 06.a / Odontogram`.
- **Title**: `FORMULIR PEMERIKSAAN ODONTOGRAM`
- **Patient Info Table & Barcode**: NO. RM, NAMA PASIEN, TGL. LAHIR, NO. RAWAT, DOKTER / PETUGAS.
- **Summary Counter Cards**: Total Periksa, Tanggal (Hilang), Karies, Sisa Akar, Tumpatan, Fraktur Mahkota.
- **Tooth Chart Grid**: Complete 1:1 tooth diagram with condition PNG images.
- **Legend & History Table**: Table of findings.
- **Signature Footer**: TTE QR code container with doctor/petugas details.
- **Print Trigger**: Auto `window.print()` on load.

### 3. Print Button in `odontogram.blade.php`
- Add print button `<a href="{{ route('odontogram.cetak', ['no_rawat' => $noRawat]) }}" target="_blank" class="btn btn-tool text-white" title="Cetak Odontogram"><i class="fas fa-print fa-lg"></i></a>` in card tools header.

---

## 🧪 Verification Plan

1. Verify route `/odontogram/cetak?no_rawat=...` renders clean A4 layout.
2. Verify Kop header, patient metadata, barcode, tooth grid, and TTE QR code display correctly.
3. Verify print button in `odontogram.blade.php` opens print page in new tab.
