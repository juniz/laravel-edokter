# TTE Doctor Signature QR Code & Printout Redesign Spec

**Date**: 2026-08-06  
**Module**: `penandaan-area-operasi` (Surgical Site Marking)  
**Target Files**:
- `resources/views/prints/penandaan-area-operasi.blade.php` (Print View Template)
- `resources/views/prints/verifikasi-penandaan.blade.php` (New Verification Page)
- `app/Http/Controllers/PenandaanAreaOperasiController.php` (Controller)
- `routes/web.php` (Routes)

---

## 1. Goal & Requirements

1. **Remove Patient Signature Block**: Eliminate `Pasien / Keluarga Pasien` column from the printout footer.
2. **Indonesian TTE Standard Doctor Signature**: Replace manual signature line with an Electronic Signature (TTE) block featuring:
   - QR Code embedding the Digital Verification URL: `https://[domain]/penandaan-area-operasi/verifikasi/[no_rawat]`.
   - Structured TTE Metadata: `Ditandatangani secara elektronik oleh: dr. [Nama Operator]`, `Kode Dokter: [kd_dokter]`, `Waktu TTD: [Tanggal & Waktu]`.
3. **Digital Verification Page**: Create a clean web page at `/penandaan-area-operasi/verifikasi/{no_rawat}` showing document authenticity certificate.

---

## 2. Layout Specifications (`prints/penandaan-area-operasi.blade.php`)

```
+-----------------------------------------------------------------------------------+
|                                                                                   |
|                           [ FORMULIR & DIAGRAM GRID ]                             |
|                                                                                   |
+-----------------------------------------------------------------------------------+
| Saya menyatakan bahwa lokasi operasi yang telah ditetapkan pada diagram adalah benar |
+-----------------------------------------------------------------------------------+
                                                      Nganjuk, 6 Agustus 2026
                                                      Dokter Operator

                                                      +-----------+  Ditandatangani secara elektronik:
                                                      |   QR      |  dr. TEGUH PRARTONO Sp. PD
                                                      |  CODE     |  Kode Dokter: D00001
                                                      +-----------+  Waktu: 06-08-2026 14:00:00

                                                      ( dr. TEGUH PRARTONO Sp. PD )
VI/2018/Komite RM
```

---

## 3. QR Code Generation & Verification Page Architecture

### 3.1 QR Code Rendering
- Render QR Code locally via client-side JavaScript (`qrcode.js` / canvas) or lightweight SVG generator to guarantee 100% offline hospital SIMRS compatibility without external network dependencies.

### 3.2 Verification Route & View
- **Route**: `GET /penandaan-area-operasi/verifikasi/{no_rawat}` (`name('penandaan-area-operasi.verifikasi')`)
- **Controller Method**: `PenandaanAreaOperasiController@verifikasi($no_rawat)`
- **View**: `resources/views/prints/verifikasi-penandaan.blade.php`
  - Displays:
    - ✅ **Status**: *Dokumen Penandaan Area Operasi Terverifikasi Sah secara Digital*.
    - **Detail Pasien**: No. RM, Nama Pasien, Tanggal Lahir, No. Rawat.
    - **Detail Dokter**: Nama Doctor, Kode Doctor, Tanggal & Jam TTD.
    - **Detail Operasi**: Prosedur Operasi.

---

## 4. Verification Plan

1. **Syntax & Compilation**: Validate PHP syntax of controller and Blade templates.
2. **Route Test**: Verify GET `/penandaan-area-operasi/verifikasi/{no_rawat}` resolves cleanly.
3. **Print Layout Verification**: Confirm removal of patient signature block and accurate right-aligned doctor TTE QR block rendering on A4 page.
