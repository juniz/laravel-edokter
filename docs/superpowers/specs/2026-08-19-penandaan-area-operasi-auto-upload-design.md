# Design Specification: Automated PDF Upload for Penandaan Area Operasi (Kode A05)

## 1. Overview
Automate the digital document archiving process for **Penandaan Area Operasi** (Surgical Site Marking) by rendering a print-ready PDF upon saving and uploading it directly to the hospital's electronic medical record document server (`webapps/upload-berkas-edokter.php`) under document code `A05`.

## 2. Background & Reference
- **Reference**: `MasterEkgController.php` (`cetakEkg` method) generates DomPDF output, encodes to Base64, and sends an HTTP POST request to the webapps server.
- **Current Penandaan Flow**:
  - `PenandaanAreaOperasi.php` Livewire component saves diagram markings and procedure description into `penandaan_area_operasi`.
  - `PenandaanAreaOperasiController.php` provides a browser print view via `route('penandaan-area-operasi.cetak')`.
- **Master Berkas Code**: `A05` is assigned to surgical documentation (`master_berkas_digital.kode = 'A05'`).

## 3. Architecture & Data Flow

### 3.1 Flow Diagram
1. User marks canvas & inputs surgical procedure in `PenandaanAreaOperasi` component.
2. User clicks **Simpan**.
3. Livewire component validates input and executes `updateOrInsert` into `penandaan_area_operasi`.
4. Livewire component invokes `PenandaanAreaOperasiService::generateAndUploadPdf($noRawat)`.
5. The service:
   - Queries database for marking record, patient metadata, doctor info, and hospital settings.
   - Converts hospital logo and SVG fallback diagrams into Base64 data URIs.
   - Generates TTE verification token and QR code data URI.
   - Compiles DomPDF view `prints.penandaan-area-operasi-pdf` with `A4 portrait` paper geometry.
   - Posts payload to `https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php`:
     ```json
     {
       "file": "<Base64 encoded PDF>",
       "no_rawat": "<no_rawat>",
       "kd_berkas": "A05",
       "nama_file": "PENANDAAN_OPERASI_<no_rawat_slug>_<timestamp>"
     }
     ```
   - Checks response JSON (`success: true`).
6. Livewire component displays success alert and updates UI state.

### 3.2 Error Handling & Resilience
- If the HTTP upload fails (e.g. timeout or server error), the database record remains safely saved, and a descriptive warning or error alert is displayed to the user.
- All HTTP errors are logged using Laravel `Log::error` for traceability.

## 4. Components & File Changes

| Component | Target File | Action | Description |
|---|---|---|---|
| **Service / Logic** | `app/Services/PenandaanAreaOperasiService.php` | NEW | Encapsulates PDF rendering, Base64 compilation, and Webapps HTTP upload |
| **Livewire Component** | `app/Http/Livewire/Component/PenandaanAreaOperasi.php` | MODIFY | Integrates automated upload trigger inside `simpan()` |
| **Controller** | `app/Http/Controllers/PenandaanAreaOperasiController.php` | MODIFY | Exposes service method if direct controller trigger is required |
| **PDF Blade View** | `resources/views/prints/penandaan-area-operasi-pdf.blade.php` | NEW | DomPDF-optimized A4 portrait single-page layout using tables |

## 5. Verification Plan
- Unit/Feature testing with mock HTTP responses for `upload-berkas-edokter.php`.
- Test DomPDF generation to ensure clean 1-page A4 rendering without page overflow.
- Test fallback behavior if remote server is unreachable.
