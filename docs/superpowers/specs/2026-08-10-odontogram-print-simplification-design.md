# Design Spec: Odontogram Print Simplification

**Date**: 2026-08-10  
**Feature**: Odontogram Print Template Simplification (`resources/views/prints/odontogram.blade.php`)

---

## 🎯 Goal

Simplify the Odontogram printout template by removing the 6 summary statistic cards and the history table, retaining only the Kop header, patient info grid with barcode, 1:1 tooth chart grid diagram, legend bar, declaration statement box, and TTE QR code signature footer.

---

## 📐 Modifications (`resources/views/prints/odontogram.blade.php`)

1. **Remove Statistic Cards**: Remove `<table class="stats-table">` and its associated CSS styling.
2. **Remove History Table**: Remove the `<table class="history-table">` section displaying examination logs.
3. **Retain Core Print Elements**:
   - Hospital Kop Header (`POLRI DAERAH JAWA TIMUR`, `BIDANG KEDOKTERAN DAN KESEHATAN`, `RUMAH SAKIT BHAYANGKARA TK. III NGANJUK`, logo, `RM. 06.a / Odontogram`).
   - Form Title `FORMULIR PEMERIKSAAN ODONTOGRAM`.
   - Patient Info Metadata Table with Barcode.
   - 1:1 Odontogram Chart Grid (Adult & Child teeth diagram with overlay condition PNG images).
   - Legend Bar (`Tanggal (Hilang)`, `Karies`, `Sisa Akar`, `Tumpatan`, `Fraktur Mahkota`).
   - Declaration Statement Box.
   - Signature Footer with TTE QR code generation and auto-print script (`window.print()`).

---

## 🧪 Verification Plan

1. Open `/odontogram/cetak?no_rawat=...` in browser.
2. Verify statistic cards and history table are absent.
3. Verify Kop header, patient metadata, tooth diagram grid, legend bar, declaration box, and TTE signature footer are rendered cleanly on A4 print preview.
