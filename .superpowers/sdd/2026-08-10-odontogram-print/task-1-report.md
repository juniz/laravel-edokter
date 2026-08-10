# Task 1 Report: Create OdontogramPrintController and Route /odontogram/cetak

## Status: COMPLETED

### Summary of Changes:
1. **Created `app/Http/Controllers/OdontogramPrintController.php`**:
   - `generateToken(string $noRawat): string`: Generates SHA-256 HMAC token (truncated to 32 chars) based on normalized `no_rawat` and `config('app.key')`.
   - `cetak(Request $request)`:
     - Validates and retrieves `no_rawat` parameter.
     - Fetches registration data (`reg_periksa`), patient info (`pasien`), and doctor info (`dokter`).
     - Queries `pemeriksaan_odontogram` records for `no_rawat`.
     - Queries fallback petugas info if `dokter.nm_dokter` is null.
     - Retrieves hospital/instansi metadata (`setting`) and formats headers.
     - Computes patient age (`umurPasien`) and gender label (`jenisKelaminTitle`).
     - Formats examination date using Carbon isoFormat `D MMMM Y`.
     - Returns `prints.odontogram` view with required context.

2. **Registered Route in `routes/web.php`**:
   - Imported `App\Http\Controllers\OdontogramPrintController`.
   - Added `Route::get('/odontogram/cetak', [OdontogramPrintController::class, 'cetak'])->name('odontogram.cetak');`.

### Verification & Testing:
- PHP Syntax Lint (`php -l`):
  - `app/Http/Controllers/OdontogramPrintController.php`: No syntax errors detected.
  - `routes/web.php`: No syntax errors detected.

### Git Commit:
- Commit message: `feat: add OdontogramPrintController and route /odontogram/cetak`
