# Tampilan Riwayat Input Asesmen Medis IGD Dokter Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menampilkan data input asesmen medis dokter dari tabel `penilaian_medis_igd` secara terpadu (linier) ke dalam komponen `penilaian-awal-medis-i-g-d.blade.php` berdampingan dengan asesmen keperawatan.

**Architecture:** Component class `PenilaianAwalMedisIGD` mengambil data dokter dari `penilaian_medis_igd` (join `dokter` on `kd_dokter`) dan mengirim `$dataMedis` ke view. Parent `pasien-tabs.blade.php` merender component jika salah satu atau kedua data (keperawatan/medis) tersedia. Blade view menampilkan data terpadu (Header info dokter/perawat, RPS/RPD/RPO/Alergi, TTV & Pemeriksaan 8 Organ Dokter, Penunjang EKG/Rad/Lab, Diagnosis Medis & Tatalaksana Dokter).

**Tech Stack:** Laravel 9, Blade, Alpine.js v3, AdminLTE 3 / Bootstrap 4, MySQL.

## Global Constraints

- Kompatibel PHP 8.x dan Laravel 9
- Hindari breaking changes pada existing usage `<x-ralan.penilaian-awal-medis-i-g-d :no-rawat="$no_rawat" />`
- Null-safe rendering: harus tetap berjalan mulus jika data dokter kosong ATAU data perawat kosong
- Gunakan sanitasi output standar `{!! nl2br(e(...)) !!}` untuk multiline text

---

### Task 1: Tambahkan Query `getPenilaianMedisIGD` pada Component Class & Parent

**Files:**
- Modify: `app/View/Components/Ralan/PenilaianAwalMedisIGD.php`
- Modify: `app/Http/Livewire/Ralan/PasienTabs.php`
- Modify: `tests/Unit/PenilaianAwalMedisIGDTest.php`

**Interfaces:**
- Consumes: `$noRawat` string
- Produces: `getPenilaianMedisIGD(string $noRawat): ?object`
- View data: `dataMedis` passed ke view `components.ralan.penilaian-awal-medis-i-g-d`

- [ ] **Step 1: Update unit test**

Perbarui `tests/Unit/PenilaianAwalMedisIGDTest.php`:
```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\View\Components\Ralan\PenilaianAwalMedisIGD;

class PenilaianAwalMedisIGDTest extends TestCase
{
    public function test_component_instantiation_and_methods_exist()
    {
        $component = new PenilaianAwalMedisIGD('2026/01/01/000001');
        $this->assertTrue(method_exists($component, 'getPemeriksaanRalan'));
        $this->assertTrue(method_exists($component, 'getPenilaianMedisIGD'));
        
        $this->assertCount(0, $component->getPemeriksaanRalan(null));
        $this->assertNull($component->getPenilaianMedisIGD(null));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PenilaianAwalMedisIGDTest`
Expected: FAIL method `getPenilaianMedisIGD` does not exist.

- [ ] **Step 3: Implement `getPenilaianMedisIGD` in `PenilaianAwalMedisIGD.php`**

Edit `app/View/Components/Ralan/PenilaianAwalMedisIGD.php`:
```php
<?php

namespace App\View\Components\Ralan;

use Illuminate\View\Component;
use Illuminate\Support\Facades\DB;

class PenilaianAwalMedisIGD extends Component
{
    public $noRawat;

    public function __construct($noRawat = null)
    {
        $this->noRawat = $noRawat;
    }

    public function render()
    {
        return view('components.ralan.penilaian-awal-medis-i-g-d', [
            'data' => $this->getPenilaianIGD($this->noRawat),
            'dataMedis' => $this->getPenilaianMedisIGD($this->noRawat),
            'listPemeriksaan' => $this->getPemeriksaanRalan($this->noRawat),
        ]);
    }

    public function getPenilaianIGD($noRawat)
    {
        if (!$noRawat) return null;

        return DB::table('penilaian_awal_keperawatan_igd')
            ->leftJoin('petugas', 'penilaian_awal_keperawatan_igd.nip', '=', 'petugas.nip')
            ->where('penilaian_awal_keperawatan_igd.no_rawat', $noRawat)
            ->select('penilaian_awal_keperawatan_igd.*', 'petugas.nama as nama_petugas')
            ->first();
    }

    public function getPenilaianMedisIGD($noRawat)
    {
        if (!$noRawat) return null;

        return DB::table('penilaian_medis_igd')
            ->leftJoin('dokter', 'penilaian_medis_igd.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('penilaian_medis_igd.no_rawat', $noRawat)
            ->select('penilaian_medis_igd.*', 'dokter.nm_dokter')
            ->first();
    }

    public function getPemeriksaanRalan($noRawat)
    {
        if (!$noRawat) return collect();

        return DB::table('pemeriksaan_ralan')
            ->leftJoin('pegawai', 'pemeriksaan_ralan.nip', '=', 'pegawai.nik')
            ->where('pemeriksaan_ralan.no_rawat', $noRawat)
            ->select('pemeriksaan_ralan.*', 'pegawai.nama as nama_petugas')
            ->orderBy('pemeriksaan_ralan.tgl_perawatan', 'desc')
            ->orderBy('pemeriksaan_ralan.jam_rawat', 'desc')
            ->get();
    }
}
```

- [ ] **Step 4: Tambahkan `getPenilaianMedisIGD` di `PasienTabs.php`**

Tambahkan method di `app/Http/Livewire/Ralan/PasienTabs.php`:
```php
    public function getPenilaianMedisIGD($noRawat)
    {
        return DB::table('penilaian_medis_igd')
            ->where('no_rawat', $noRawat)
            ->exists();
    }
```

- [ ] **Step 5: Run unit test and syntax check**

Run: `php artisan test --filter=PenilaianAwalMedisIGDTest && php -l app/View/Components/Ralan/PenilaianAwalMedisIGD.php && php -l app/Http/Livewire/Ralan/PasienTabs.php`
Expected: PASS and syntax clean.

- [ ] **Step 6: Commit changes**

```bash
git add app/View/Components/Ralan/PenilaianAwalMedisIGD.php app/Http/Livewire/Ralan/PasienTabs.php tests/Unit/PenilaianAwalMedisIGDTest.php
git commit -m "feat: add getPenilaianMedisIGD method and pass dataMedis to component"
```

---

### Task 2: Update Parent Guard di `pasien-tabs.blade.php`

**Files:**
- Modify: `resources/views/livewire/ralan/pasien-tabs.blade.php:1078-1084`

**Interfaces:**
- Consumes: `$this->getPenilaianAwalKeperawatanIGD($no_rawat)` & `$this->getPenilaianMedisIGD($no_rawat)`
- Produces: Rendering component `<x-ralan.penilaian-awal-medis-i-g-d>` jika data keperawatan ATAU data medis tersedia

- [ ] **Step 1: Edit conditional check di `pasien-tabs.blade.php`**

Ubah:
```blade
                                        @php
                                        $penilaianIGDVisit = $this->getPenilaianAwalKeperawatanIGD($no_rawat);
                                        @endphp
                                        @if($penilaianIGDVisit)
                                            <x-ralan.penilaian-awal-medis-i-g-d :no-rawat="$no_rawat" />
                                        @endif
```
Menjadi:
```blade
                                        @php
                                        $penilaianIGDVisit = $this->getPenilaianAwalKeperawatanIGD($no_rawat);
                                        $penilaianMedisVisit = $this->getPenilaianMedisIGD($no_rawat);
                                        @endphp
                                        @if($penilaianIGDVisit || $penilaianMedisVisit)
                                            <x-ralan.penilaian-awal-medis-i-g-d :no-rawat="$no_rawat" />
                                        @endif
```

- [ ] **Step 2: Verify view compilation**

Run: `php artisan view:clear`
Expected: `Compiled views cleared successfully.`

- [ ] **Step 3: Commit changes**

```bash
git add resources/views/livewire/ralan/pasien-tabs.blade.php
git commit -m "feat: render penilaian-awal-medis-i-g-d if keperawatan or medis visit exists"
```

---

### Task 3: Implementasi Tampilan Linier Terpadu di `penilaian-awal-medis-i-g-d.blade.php`

**Files:**
- Modify: `resources/views/components/ralan/penilaian-awal-medis-i-g-d.blade.php`

**Interfaces:**
- Consumes: `$data` (keperawatan), `$dataMedis` (dokter), `$listPemeriksaan` (SOAP ralan)
- Produces: Tampilan linier terpadu mencakup:
  1. Main guard: `@if(!empty($data) || !empty($dataMedis))`
  2. Metadata Header: Dokter & Perawat
  3. I. Riwayat Kesehatan & Anamnesis (Dokter + Perawat + Kebidanan)
  4. II. Pemeriksaan Fisik: TTV Dokter, 8 Organ Sistemik Dokter, Keterangan Lokalis, Eliminasi & Fisik Perawat
  5. III. Pengkajian Khusus Keperawatan: Nyeri, Risiko Jatuh, Psikososial
  6. IV. Pemeriksaan Penunjang (Dokter): EKG, Radiologi, Lab
  7. V. Diagnosis / Asesmen & Tatalaksana Medis Dokter + Rencana Keperawatan

- [ ] **Step 1: Update main card wrapper & metadata header**

Update baris atas:
- Ubah title menjadi `Penilaian Awal IGD (Medis & Keperawatan)`.
- Tampilkan card header metadata:
  - Jika `$dataMedis`: Tanggal Asesmen Medis & Dokter Pemeriksa.
  - Jika `$data`: Tanggal Asesmen Keperawatan & Perawat.

- [ ] **Step 2: Integrasikan Section I Anamnesis & Riwayat Penyakit**

Tampilkan:
- Anamnesis Dokter: `keluhan_utama`, `anamnesis` (Auto/Alloanamnesis), `hubungan`.
- Riwayat Penyakit: `rps` (Riwayat Penyakit Sekarang), `rpk` (Riwayat Penyakit Keluarga), `rpd` (Riwayat Penyakit Dahulu), `rpo` (Riwayat Pengobatan), `alergi`.
- Kehamilan (jika perawat mencatat hamil).

- [ ] **Step 3: Integrasikan Section II Pemeriksaan Fisik**

Tampilkan:
- **TTV & Status Generalis Dokter**: Keadaan Umum, Kesadaran, GCS, TB, BB, TD, Nadi, RR, Suhu, SpO2.
- **Pemeriksaan 8 Organ Sistemik (Dokter)**: Kepala, Mata, Thoraks, Abdomen, Gigi, Genital, Leher, Ekstremitas.
- **Keterangan Fisik / Lokalis Dokter**: `ket_fisik`, `ket_lokalis`.
- **Fisik & Eliminasi Perawat**: Pupil, Neurosensorik, Integumen, Turgor, Edema, Perdarahan, BAB, BAK.

- [ ] **Step 4: Tambahkan Section IV Penunjang Medis Dokter**

Jika `$dataMedis` memiliki data penunjang (`ekg`, `rad`, `lab`):
- Tampilkan card "Pemeriksaan Penunjang (EKG, Radiologi, Lab)".

- [ ] **Step 5: Integrasikan Section V Diagnosis, Tatalaksana & Rencana**

Tampilkan:
- **Diagnosis / Asesmen Medis Dokter**: `{!! nl2br(e($dataMedis->diagnosis)) !!}`
- **Tatalaksana / Instruksi Medis Dokter**: `{!! nl2br(e($dataMedis->tata)) !!}`
- **Rencana Asuhan Keperawatan**: `{!! nl2br(e($data->rencana)) !!}`

- [ ] **Step 6: Clear view cache & test render**

Run: `php artisan view:clear`
Run: `php artisan tinker --execute="echo (new \App\View\Components\Ralan\PenilaianAwalMedisIGD('dummy'))->render();"`
Expected: Clean render, zero exceptions.

- [ ] **Step 7: Commit changes**

```bash
git add resources/views/components/ralan/penilaian-awal-medis-i-g-d.blade.php
git commit -m "feat: implement unified linear display of doctor and nurse assessments in penilaian-awal-medis-i-g-d"
```

---

### Task 4: Verifikasi Menyeluruh & Testing

**Files:**
- Test: PHPUnit suite & Tinker execution

- [ ] **Step 1: Jalankan PHPUnit test**

Run: `php artisan test --filter=PenilaianAwalMedisIGDTest`
Expected: PASS.

- [ ] **Step 2: Lakukan pengecekan render simulasi null-safety**

Run: `php artisan tinker --execute="echo (new \App\View\Components\Ralan\PenilaianAwalMedisIGD('nonexistent_rawat'))->render();"`
Expected: No errors, output template kosong/ter-render aman.

- [ ] **Step 3: Review git status & log**

Run: `git status -s`
Expected: Clean.
