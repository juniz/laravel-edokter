# Design Spec: Tombol Debug Generate & Download PDF Penandaan Area Operasi

## Background & Objectives
Untuk mempermudah proses debugging layout, margin, dan visualisasi PDF DomPDF tanpa bergantung pada koneksi/upload ke server berkas digital SIMRS eksternal, dibutuhkan tombol dedicated pada halaman penandaan area operasi yang memungkinkan dokter / developer langsung men-generate dan mengunduh/melihat (preview) file PDF hasil DomPDF.

## Architectural Changes

### 1. Route Registration
- **Route**: `GET /penandaan-area-operasi/download-pdf`
- **Name**: `penandaan-area-operasi.download-pdf`
- **Handler**: `App\Http\Controllers\PenandaanAreaOperasiController@downloadPdf`
- **Parameters**: `no_rawat` (via query string or route parameter)

### 2. Controller Endpoint
- **File**: `app/Http/Controllers/PenandaanAreaOperasiController.php`
- **Method**: `downloadPdf(Request $request, ?PenandaanAreaOperasiService $service = null)`
  - Memvalidasi parameter `no_rawat`.
  - Memanggil `PenandaanAreaOperasiService::generatePdf($noRawat)`.
  - Mengembalikan response binary PDF dengan headers:
    - `Content-Type: application/pdf`
    - `Content-Disposition: inline; filename="PENANDAAN_OPERASI_{sanitized_no_rawat}_{timestamp}.pdf"`

### 3. UI Button Placement
- **File**: `resources/views/livewire/component/penandaan-area-operasi.blade.php`
- **Position**: Di dalam `.penandaan-submit-group` tepat di sebelah tombol **Cetak** (`#cetak-penandaan`).
- **Button Component**:
  ```html
  <a href="{{ route('penandaan-area-operasi.download-pdf', ['no_rawat' => $noRawat]) }}"
     target="_blank"
     class="btn btn-secondary ml-2"
     id="download-pdf-penandaan"
     title="Generate dan Download / Preview PDF DomPDF (A4)">
      <i class="fas fa-file-pdf mr-1" aria-hidden="true"></i>
      Download PDF
  </a>
  ```

## Verification Plan
1. **Feature Test**:
   - Menambahkan test case di `tests/Feature/PenandaanAreaOperasiAutoUploadTest.php` untuk memverifikasi endpoint `penandaan-area-operasi.download-pdf` mengembalikan HTTP 200 dengan `Content-Type: application/pdf` dan binary PDF valid (`%PDF-`).
2. **Manual / CLI Verification**:
   - Menjalankan test suite PHPUnit untuk memastikan seluruh tes passing.
