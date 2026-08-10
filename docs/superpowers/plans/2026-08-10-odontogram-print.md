# Odontogram Print Feature Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create a printout page for Odontogram with official hospital Kop header, patient info grid, summary counters, 1:1 tooth chart grid, findings history table, and TTE QR code signature matching the design style of `prints/penandaan-area-operasi.blade.php`.

**Architecture:**
- `App\Http\Controllers\OdontogramPrintController` fetches patient registration metadata, patient info, hospital settings, `pemeriksaan_odontogram` records, and generates TTE HMAC verification token.
- `resources/views/prints/odontogram.blade.php` renders A4 printable template with auto-print script `window.print()`.
- Route: `Route::get('/odontogram/cetak', [OdontogramPrintController::class, 'cetak'])->name('odontogram.cetak');`
- Print button in `resources/views/livewire/ralan/odontogram.blade.php` opens print page in a new window.

**Tech Stack:** Laravel, Blade, CSS `@page A4 portrait`, QR Code JS, HMAC SHA256.

## Global Constraints

- Match exact Kop header styling (`POLRI DAERAH JAWA TIMUR`, `BIDANG KEDOKTERAN DAN KESEHATAN`, `RUMAH SAKIT BHAYANGKARA TK. III NGANJUK`).
- Document box code: `RM. 06.a / Odontogram`.
- Auto trigger `window.print()` on load.

---

### Task 1: Create `OdontogramPrintController` & Register Route

**Files:**
- Create: `app/Http/Controllers/OdontogramPrintController.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create `OdontogramPrintController.php`**

Create `app/Http/Controllers/OdontogramPrintController.php` with HMAC token generator and `cetak(Request $request)` method querying `pemeriksaan_odontogram`, `reg_periksa`, `pasien`, `dokter`, `petugas`, and `setting`:
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OdontogramPrintController extends Controller
{
    public static function generateToken(string $noRawat): string
    {
        $normalized = str_replace('-', '/', $noRawat);
        $key = config('app.key', 'laravel-edokter-secret-key-2026');
        return substr(hash_hmac('sha256', $normalized, $key), 0, 32);
    }

    public function cetak(Request $request)
    {
        $noRawat = $request->input('no_rawat') ?? $request->route('no_rawat');

        if (!$noRawat || !is_string($noRawat)) {
            abort(400, 'Parameter no_rawat is required.');
        }

        $regData = DB::table('reg_periksa')
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('reg_periksa.no_rawat', $noRawat)
            ->select([
                'reg_periksa.*',
                'pasien.nm_pasien',
                'pasien.tgl_lahir as pasien_tgl_lahir',
                'pasien.jk',
                'dokter.nm_dokter',
            ])
            ->first();

        if (!$regData) {
            abort(404, 'Data registrasi pasien tidak ditemukan.');
        }

        $listPemeriksaan = DB::table('pemeriksaan_odontogram')
            ->where('no_rawat', $noRawat)
            ->get();

        $petugasFirst = DB::table('pemeriksaan_odontogram')
            ->leftJoin('petugas', 'pemeriksaan_odontogram.nip', '=', 'petugas.nip')
            ->where('pemeriksaan_odontogram.no_rawat', $noRawat)
            ->whereNotNull('petugas.nama')
            ->select('petugas.nama as nama_petugas', 'petugas.nip')
            ->first();

        $namaDokterPetugas = $regData->nm_dokter ?? ($petugasFirst->nama_petugas ?? '-');
        $kodeDokterPetugas = $regData->kd_dokter ?? ($petugasFirst->nip ?? '-');

        $setting = DB::table('setting')->first();
        if (!$setting) {
            $setting = (object)[
                'nama_instansi' => 'RUMAH SAKIT BHAYANGKARA TK. III NGANJUK',
                'alamat_instansi' => '',
                'kabupaten' => 'NGANJUK',
                'propinsi' => 'JAWA TIMUR',
                'kontak' => '',
                'email' => '',
                'logo' => null,
            ];
        }

        $setting->header_title_1 = 'POLRI DAERAH JAWA TIMUR';
        $setting->header_title_2 = 'BIDANG KEDOKTERAN DAN KESEHATAN';
        $setting->hospital_name = $setting->nama_instansi ?? 'RUMAH SAKIT BHAYANGKARA TK. III NGANJUK';
        $setting->location = $setting->kabupaten ?? 'NGANJUK';

        $tglLahir = $regData->pasien_tgl_lahir ?? null;
        if ($tglLahir) {
            $umurPasien = Carbon::parse($tglLahir)->age . ' Th';
        } elseif (isset($regData->umurdaftar) && isset($regData->sttsumur)) {
            $umurPasien = $regData->umurdaftar . ' ' . $regData->sttsumur;
        } else {
            $umurPasien = '-';
        }

        $jenisKelaminTitle = (isset($regData->jk) && $regData->jk === 'L') ? 'LAKI-LAKI' : 'PEREMPUAN';
        $tanggalPemeriksaan = Carbon::now()->isoFormat('D MMMM Y');
        $verificationToken = self::generateToken($noRawat);

        return view('prints.odontogram', compact(
            'regData',
            'listPemeriksaan',
            'setting',
            'umurPasien',
            'jenisKelaminTitle',
            'tanggalPemeriksaan',
            'verificationToken',
            'namaDokterPetugas',
            'kodeDokterPetugas'
        ));
    }
}
```

- [ ] **Step 2: Add route in `routes/web.php`**

Add the route to `routes/web.php`:
```php
use App\Http\Controllers\OdontogramPrintController;

Route::get('/odontogram/cetak', [OdontogramPrintController::class, 'cetak'])->name('odontogram.cetak');
```

- [ ] **Step 3: Verify syntax & commit Task 1**

Run `php -l app/Http/Controllers/OdontogramPrintController.php` and commit:
```bash
git add app/Http/Controllers/OdontogramPrintController.php routes/web.php
git commit -m "feat: add OdontogramPrintController and route /odontogram/cetak"
```

---

### Task 2: Create A4 Print Template `resources/views/prints/odontogram.blade.php`

**Files:**
- Create: `resources/views/prints/odontogram.blade.php`

- [ ] **Step 1: Create `resources/views/prints/odontogram.blade.php`**

Create printable template featuring:
- Hospital Kop (`POLRI DAERAH JAWA TIMUR`, `BIDANG KEDOKTERAN DAN KESEHATAN`, `RUMAH SAKIT BHAYANGKARA TK. III NGANJUK`, logo, `RM. 06.a / Odontogram`)
- Form title: `FORMULIR PEMERIKSAAN ODONTOGRAM`
- Patient metadata table (NO. RM, NAMA PASIEN, TGL. LAHIR, NO. RAWAT, DOKTER / PETUGAS, BARCODE)
- Summary counter boxes (Total Periksa, Tanggal, Karies, Sisa Akar, Tumpatan, Fraktur Mahkota)
- Full 1:1 Tooth Diagram Grid (Adult 18-11, 21-28, 48-41, 31-38 & Child 55-51, 61-65, 85-81, 71-75) with PNG images
- Legenda & History Table
- Declaration statement & TTE QR Code signature footer
- Auto-print script (`window.print()`)

- [ ] **Step 2: Commit Task 2**

```bash
git add resources/views/prints/odontogram.blade.php
git commit -m "feat: create 1:1 A4 Odontogram print template with hospital Kop and TTE footer"
```

---

### Task 3: Add Cetak Button to `odontogram.blade.php` Card Header

**Files:**
- Modify: `resources/views/livewire/ralan/odontogram.blade.php`

- [ ] **Step 1: Add Cetak Button in `odontogram.blade.php` header tools**

Add print link next to collapse button in card header:
```blade
<div class="card-tools ml-auto d-flex align-items-center">
    <a href="{{ route('odontogram.cetak', ['no_rawat' => $noRawat]) }}" target="_blank" class="btn btn-tool text-white mr-1" title="Cetak Odontogram">
        <i class="fas fa-print fa-lg"></i>
    </a>
    <button type="button" wire:click="collapsed" class="btn btn-tool text-white" data-card-widget="collapse">
        <i wire:ignore class="fas fa-lg @if($isCollapsed) fa-plus @else fa-minus @endif"></i>
    </button>
</div>
```

- [ ] **Step 2: Commit Task 3**

```bash
git add resources/views/livewire/ralan/odontogram.blade.php
git commit -m "feat: add print button link in Odontogram card header"
```
