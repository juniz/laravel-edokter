# Tombol Debug Download PDF Penandaan Area Operasi Implementation Plan

Implement a dedicated debug download/preview button for DomPDF generation of Penandaan Area Operasi.

## User Review Required

> [!NOTE]
> Menambahkan tombol **Download PDF** di sebelah tombol **Cetak** pada tampilan livewire penandaan area operasi yang membuka tab baru untuk preview/download PDF DomPDF secara instan.

## Proposed Changes

### Routes & Controller

#### [MODIFY] `routes/web.php`
- Register `GET /penandaan-area-operasi/download-pdf` route named `penandaan-area-operasi.download-pdf` pointing to `PenandaanAreaOperasiController@downloadPdf`.

#### [MODIFY] `app/Http/Controllers/PenandaanAreaOperasiController.php`
- Add `downloadPdf(Request $request, ?PenandaanAreaOperasiService $service = null)` method returning binary PDF response with `Content-Type: application/pdf` and `Content-Disposition: inline`.

### Views

#### [MODIFY] `resources/views/livewire/component/penandaan-area-operasi.blade.php`
- Add `Download PDF` button next to `#cetak-penandaan` in `.penandaan-submit-group`.

### Tests

#### [MODIFY] `tests/Feature/PenandaanAreaOperasiAutoUploadTest.php`
- Add feature test verifying `penandaan-area-operasi.download-pdf` endpoint returns 200 with PDF content type and header.

## Verification Plan

### Automated Tests
- Run `./vendor/bin/phpunit tests/Feature/PenandaanAreaOperasiAutoUploadTest.php`

### Manual Verification
- Test URL `/penandaan-area-operasi/download-pdf?no_rawat=...` returns valid PDF stream.
