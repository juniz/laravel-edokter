# Tampilan Riwayat Asesmen Perawat IGD di Form Medis Dokter Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menampilkan riwayat hasil pengkajian awal keperawatan IGD (`penilaian_awal_keperawatan_igd`) pada form input dokter `App\Http\Livewire\Component\AwalIgd\Form` beserta tombol salin data keluhan dan riwayat penyakit.

**Architecture:** Tambahkan property `$dataPerawat` dan method `salinDariPerawat` pada Livewire Form component, muat data saat `updatedNoRawat()`, dan tampilkan collapsible AdminLTE card di atas Section I `form.blade.php`.

**Tech Stack:** Laravel 9, Livewire 2, AdminLTE 3 / Bootstrap 4, FontAwesome, PHPUnit.

## Global Constraints
- PHP 8.x compatible dengan Laravel 9.
- Tidak merusak fungsionalitas input dan penyimpanan asesmen dokter yang sudah ada.
- Penanganan null-safe pada setiap property `$dataPerawat`.
- Menggunakan LivewireAlert untuk feedback penyalinan data.

---

### Task 1: Backend Update di Component Class `Form.php` & Unit Test

**Files:**
- Create: `tests/Unit/AwalIgdFormTest.php`
- Modify: `app/Http/Livewire/Component/AwalIgd/Form.php`

**Interfaces:**
- Produces:
  - `public $dataPerawat = null`
  - `public function salinDariPerawat(): void`
  - In `updatedNoRawat()`: fetch `dataPerawat` from `penilaian_awal_keperawatan_igd` join `petugas`

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Livewire\Component\AwalIgd\Form;

class AwalIgdFormTest extends TestCase
{
    public function test_component_has_data_perawat_property_and_salin_method()
    {
        $component = new Form();
        $this->assertTrue(property_exists($component, 'dataPerawat'));
        $this->assertTrue(method_exists($component, 'salinDariPerawat'));
    }

    public function test_salin_dari_perawat_copies_fields_to_model()
    {
        $component = new Form();
        $component->dataPerawat = (object) [
            'keluhan_utama' => 'Nyeri perut kanan bawah',
            'rpd' => 'Gastritis',
            'rpo' => 'Antasida',
        ];

        $component->salinDariPerawat();

        $this->assertEquals('Nyeri perut kanan bawah', $component->keluhan_utama);
        $this->assertEquals('Gastritis', $component->rpd);
        $this->assertEquals('Antasida', $component->rpo);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AwalIgdFormTest`
Expected: FAIL (property `dataPerawat` or method `salinDariPerawat` does not exist).

- [ ] **Step 3: Write minimal implementation**

Di `app/Http/Livewire/Component/AwalIgd/Form.php`:
1. Tambah property `public $dataPerawat = null;`
2. Di `resetInput()`, tambah `$this->dataPerawat = null;`
3. Di `updatedNoRawat()`, query data perawat:
```php
$this->dataPerawat = DB::table('penilaian_awal_keperawatan_igd')
    ->leftJoin('petugas', 'penilaian_awal_keperawatan_igd.nip', '=', 'petugas.nip')
    ->where('penilaian_awal_keperawatan_igd.no_rawat', $this->no_rawat)
    ->select('penilaian_awal_keperawatan_igd.*', 'petugas.nama as nama_petugas')
    ->first();
```
4. Tambah method `salinDariPerawat()`:
```php
public function salinDariPerawat()
{
    if (!$this->dataPerawat) {
        $this->alert('warning', 'Data asesmen keperawatan tidak ditemukan');
        return;
    }

    if (!empty($this->dataPerawat->keluhan_utama)) {
        $this->keluhan_utama = $this->dataPerawat->keluhan_utama;
    }
    if (!empty($this->dataPerawat->rpd)) {
        $this->rpd = $this->dataPerawat->rpd;
    }
    if (!empty($this->dataPerawat->rpo)) {
        $this->rpo = $this->dataPerawat->rpo;
    }

    $this->alert('success', 'Data keluhan & riwayat berhasil disalin dari perawat');
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=AwalIgdFormTest`
Expected: PASS (2 tests, assertions pass).

- [ ] **Step 5: Commit**

```bash
git add app/Http/Livewire/Component/AwalIgd/Form.php tests/Unit/AwalIgdFormTest.php
git commit -m "feat: add dataPerawat property and salinDariPerawat method in AwalIgd Form"
```

---

### Task 2: Implementasi Card Tampilan Asesmen Perawat di Blade View

**Files:**
- Modify: `resources/views/livewire/component/awal-igd/form.blade.php`

**Interfaces:**
- Consumes: `$dataPerawat` and `wire:click="salinDariPerawat"` from `Form` component.

- [ ] **Step 1: Tambahkan card di atas Section I pada form.blade.php**

Tepat sebelum `<div class="d-flex justify-content-between align-items-center mt-2 mb-2"> <h6 class="text-bold mb-0">I. RIWAYAT KESEHATAN</h6>`:
```blade
        @if(!empty($dataPerawat))
        <div class="card card-outline card-success mb-3 shadow-sm">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="card-title text-sm mb-0">
                    <i class="fas fa-user-nurse text-success mr-1"></i> <strong>Asesmen Awal Keperawatan IGD</strong>
                    <span class="badge badge-light border ml-1">{{ $dataPerawat->nama_petugas ?? $dataPerawat->nip }}</span>
                    <small class="text-muted ml-2">({{ !empty($dataPerawat->tanggal) ? date('d/m/Y H:i', strtotime($dataPerawat->tanggal)) : '-' }})</small>
                </h6>
                <div class="card-tools">
                    <button type="button" class="btn btn-xs btn-outline-success mr-2" wire:click="salinDariPerawat" title="Salin Keluhan, RPD & RPO ke Form Dokter">
                        <i class="fas fa-copy mr-1"></i> Salin ke Form Dokter
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body py-2 px-3 text-sm">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Keluhan Utama (Perawat):</small>
                        <div class="font-weight-bold text-dark">{{ $dataPerawat->keluhan_utama ?: '-' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <small class="text-muted d-block">Riwayat Penyakit Dahulu:</small>
                        <div>{{ $dataPerawat->rpd ?: '-' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <small class="text-muted d-block">Riwayat Pengobatan:</small>
                        <div>{{ $dataPerawat->rpo ?: '-' }}</div>
                    </div>
                </div>
                <div class="row pt-2 border-top">
                    <div class="col-6 col-md-3 mb-1">
                        <small class="text-muted d-block">Pengkajian Nyeri:</small>
                        <span>{{ $dataPerawat->nyeri ?: 'Tidak' }} (Skala: {{ $dataPerawat->skala_nyeri ?: '0' }})</span>
                    </div>
                    <div class="col-6 col-md-3 mb-1">
                        <small class="text-muted d-block">Risiko Jatuh:</small>
                        <span>{{ $dataPerawat->hasil ?: '-' }}</span>
                    </div>
                    <div class="col-6 col-md-3 mb-1">
                        <small class="text-muted d-block">Status Kehamilan:</small>
                        <span>{{ $dataPerawat->status_kehamilan ?: '-' }}</span>
                    </div>
                    <div class="col-6 col-md-3 mb-1">
                        <small class="text-muted d-block">Rencana Keperawatan:</small>
                        <span>{{ $dataPerawat->rencana ?: '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
```

- [ ] **Step 2: Clear view cache**

Run: `php artisan view:clear`
Expected: `Compiled views cleared!`

- [ ] **Step 3: Test render via tinker**

Run:
```bash
php artisan tinker --execute="echo app(\Illuminate\View\Factory::class)->make('livewire.component.awal-igd.form', ['dataPerawat' => null])->render();"
```
Expected: Tidak error, output HTML render normal.

- [ ] **Step 4: Commit**

```bash
git add resources/views/livewire/component/awal-igd/form.blade.php
git commit -m "feat: add nurse assessment collapsible reference card in awal igd form"
```

---

### Task 3: Verifikasi Menyeluruh & Testing

**Files:**
- Test execution & verification

- [ ] **Step 1: Jalankan unit test PHPUnit**

Run: `php artisan test --filter=AwalIgdFormTest`
Expected: PASS

- [ ] **Step 2: Test simulasi Livewire dengan no_rawat riil di Tinker**

Run:
```bash
php artisan tinker --execute="
\$c = new \App\Http\Livewire\Component\AwalIgd\Form();
\$c->no_rawat = '2026/10/11/000027';
\$c->updatedNoRawat();
echo 'Perawat: ' . (\$c->dataPerawat->nama_petugas ?? 'null') . PHP_EOL;
\$c->salinDariPerawat();
echo 'Keluhan: ' . \$c->keluhan_utama . PHP_EOL;
"
```
Expected: Output data perawat terbaca dan `salinDariPerawat()` berhasil menyalin keluhan utama.

- [ ] **Step 3: Cek git status**

Run: `git status -s`
Expected: Bersih, tidak ada file uncommitted selain artefak pelacak.

---
