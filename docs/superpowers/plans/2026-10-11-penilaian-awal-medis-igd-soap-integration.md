# Penilaian Awal Medis IGD SOAP Integration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambahkan function untuk mengambil data SOAP dari tabel `pemeriksaan_ralan` ke dalam komponen Penilaian Awal Medis IGD serta antarmuka pemilih (modal selector dengan tombol clip) berbasis Alpine.js untuk menampilkan riwayat SOAP yang dipilih.

**Architecture:** Component class `App\View\Components\Ralan\PenilaianAwalMedisIGD` mengambil data riwayat `pemeriksaan_ralan` yang berelasi dengan pegawai (`nik = nip`). View Blade `penilaian-awal-medis-i-g-d.blade.php` menerima daftar SOAP, menginisialisasi state Alpine.js (`selectedSoap`), menyediakan modal selector via tombol klip, dan merender detail TTV serta catatan SOAP secara reaktif di kartu penilaian.

**Tech Stack:** Laravel 9, Blade, Alpine.js v3, AdminLTE 3 / Bootstrap 4, MySQL / Query Builder.

## Global Constraints

- PHP 8.x kompatibel dengan Laravel 9
- Hindari breaking change pada interface component `<x-ralan.penilaian-awal-medis-i-g-d :no-rawat="$no_rawat" />`
- ID Modal harus unik berbasis hash `no_rawat` untuk menghindari konflik modal di halaman riwayat multi-kunjungan
- Gunakan `@js()` untuk serialisasi data aman ke Alpine.js

---

### Task 1: Tambahkan Query `getPemeriksaanRalan` pada Component Class

**Files:**
- Modify: `app/View/Components/Ralan/PenilaianAwalMedisIGD.php:17-34`
- Test: `tests/Unit/PenilaianAwalMedisIGDTest.php` atau PHP CLI syntax & query check

**Interfaces:**
- Consumes: `$noRawat` string (nomor rawat pasien)
- Produces: `getPemeriksaanRalan(string $noRawat): \Illuminate\Support\Collection`
- View data: `listPemeriksaan` passed ke view `components.ralan.penilaian-awal-medis-i-g-d`

- [ ] **Step 1: Write verification test / script for query method**

Buat file test `tests/Unit/PenilaianAwalMedisIGDTest.php`:
```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\View\Components\Ralan\PenilaianAwalMedisIGD;

class PenilaianAwalMedisIGDTest extends TestCase
{
    public function test_component_instantiation_and_get_pemeriksaan_ralan_method_exists()
    {
        $component = new PenilaianAwalMedisIGD('2026/01/01/000001');
        $this->assertTrue(method_exists($component, 'getPemeriksaanRalan'));
        $data = $component->getPemeriksaanRalan(null);
        $this->assertCount(0, $data);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=PenilaianAwalMedisIGDTest`
Expected: FAIL dengan pesan method `getPemeriksaanRalan` tidak ditemukan atau assertion failure.

- [ ] **Step 3: Implement `getPemeriksaanRalan` and update `render`**

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

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=PenilaianAwalMedisIGDTest`
Expected: PASS (1 test, 2 assertions).

- [ ] **Step 5: Verify PHP syntax**

Run: `php -l app/View/Components/Ralan/PenilaianAwalMedisIGD.php`
Expected: `No syntax errors detected in app/View/Components/Ralan/PenilaianAwalMedisIGD.php`

- [ ] **Step 6: Commit changes**

```bash
git add app/View/Components/Ralan/PenilaianAwalMedisIGD.php tests/Unit/PenilaianAwalMedisIGDTest.php
git commit -m "feat: add getPemeriksaanRalan method to PenilaianAwalMedisIGD component"
```

---

### Task 2: Implementasi Tombol Clip, Modal Selector, dan Tampilan SOAP Terpilih di Blade

**Files:**
- Modify: `resources/views/components/ralan/penilaian-awal-medis-i-g-d.blade.php`

**Interfaces:**
- Consumes: `$data` (object penilaian_awal_keperawatan_igd), `$listPemeriksaan` (collection pemeriksaan_ralan)
- Produces: UI interaktif Alpine.js dengan tombol clip, modal selector, dan rendering reaktif TTV + SOAP

- [ ] **Step 1: Tambahkan Alpine.js wrapper dan Tombol Klip di Header**

Di awal card atau container `resources/views/components/ralan/penilaian-awal-medis-i-g-d.blade.php`:
Bungkus root div dengan `x-data`:
```blade
@php
    $modalId = 'modalPilihSoap-' . md5($data->no_rawat ?? ($noRawat ?? uniqid()));
    $soapCount = isset($listPemeriksaan) ? count($listPemeriksaan) : 0;
@endphp
<div x-data="{
    listSoap: @js($listPemeriksaan ?? []),
    selectedSoap: @js(isset($listPemeriksaan) && count($listPemeriksaan) > 0 ? $listPemeriksaan->first() : null),
    selectSoap(soap) {
        this.selectedSoap = soap;
        $('#{{ $modalId }}').modal('hide');
    }
}">
```

Tambahkan tombol klip dan badge di baris informasi atas (atau tools slot pada `x-adminlte-card`):
```blade
<div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
    <div class="row flex-grow-1">
        <div class="col-md-4">
            <small class="text-muted d-block">Tanggal Penilaian</small>
            <strong><i class="fas fa-calendar"></i> {{ $datePenilaian }}</strong>
        </div>
        <div class="col-md-4">
            <small class="text-muted d-block">Informasi</small>
            <strong><i class="fas fa-info-circle"></i> {{ $data->informasi ?? '-' }}</strong>
        </div>
        <div class="col-md-4">
            <small class="text-muted d-block">Petugas / Perawat</small>
            <strong><i class="fas fa-user-nurse"></i> {{ $data->nama_petugas ?? $data->nip ?? '-' }}</strong>
        </div>
    </div>
    <div>
        <button type="button" class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#{{ $modalId }}" title="Pilih Data SOAP / Pemeriksaan Ralan">
            <i class="fas fa-paperclip"></i> SOAP Ralan
            <span class="badge badge-info ml-1">{{ $soapCount }}</span>
        </button>
    </div>
</div>
```

- [ ] **Step 2: Tambahkan Section Card Tanda Vital & SOAP Terpilih**

Tambahkan section sebelum atau sesudah "1. Informasi Umum & Anamnesis":
```blade
<!-- Data SOAP Terpilih (Pemeriksaan Ralan) -->
<template x-if="selectedSoap">
    <div class="card mb-3 border-left-success" style="border-left-width: 4px;">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0">
                <i class="fas fa-clipboard-check"></i> Data SOAP Terpilih (Pemeriksaan Ralan)
            </h6>
            <small>
                <i class="fas fa-calendar-day"></i> <span x-text="selectedSoap.tgl_perawatan"></span>
                <i class="fas fa-clock ml-2"></i> <span x-text="selectedSoap.jam_rawat"></span>
                <span class="ml-2 font-weight-bold" x-text="'| ' + (selectedSoap.nama_petugas || selectedSoap.nip)"></span>
            </small>
        </div>
        <div class="card-body">
            <!-- Tanda-tanda Vital -->
            <div class="row mb-3 pb-2 border-bottom">
                <div class="col-6 col-md-2 mb-2">
                    <small class="text-muted d-block">Tensi</small>
                    <strong x-text="(selectedSoap.tensi || '-') + ' mmHg'"></strong>
                </div>
                <div class="col-6 col-md-2 mb-2">
                    <small class="text-muted d-block">Nadi</small>
                    <strong x-text="(selectedSoap.nadi || '-') + ' x/m'"></strong>
                </div>
                <div class="col-6 col-md-2 mb-2">
                    <small class="text-muted d-block">Suhu</small>
                    <strong x-text="(selectedSoap.suhu_tubuh || '-') + ' °C'"></strong>
                </div>
                <div class="col-6 col-md-2 mb-2">
                    <small class="text-muted d-block">Respirasi</small>
                    <strong x-text="(selectedSoap.respirasi || '-') + ' x/m'"></strong>
                </div>
                <div class="col-6 col-md-2 mb-2">
                    <small class="text-muted d-block">SpO2</small>
                    <strong x-text="(selectedSoap.spo2 || '-') + ' %'"></strong>
                </div>
                <div class="col-6 col-md-2 mb-2">
                    <small class="text-muted d-block">Kesadaran / GCS</small>
                    <strong><span x-text="selectedSoap.kesadaran || '-'"></span> (<span x-text="selectedSoap.gcs || '-'"></span>)</strong>
                </div>
                <div class="col-6 col-md-2 mb-2">
                    <small class="text-muted d-block">TB / BB</small>
                    <strong><span x-text="selectedSoap.tinggi || '-'"></span> cm / <span x-text="selectedSoap.berat || '-'"></span> kg</strong>
                </div>
                <div class="col-6 col-md-2 mb-2">
                    <small class="text-muted d-block">Lingkar Perut</small>
                    <strong x-text="(selectedSoap.lingkar_perut || '-') + ' cm'"></strong>
                </div>
                <div class="col-12 col-md-4 mb-2" x-show="selectedSoap.alergi && selectedSoap.alergi !== '-' && selectedSoap.alergi !== 'Tidak Ada'">
                    <small class="text-muted d-block">Alergi</small>
                    <span class="badge badge-danger p-1"><i class="fas fa-exclamation-triangle"></i> <span x-text="selectedSoap.alergi"></span></span>
                </div>
            </div>

            <!-- Catatan S-O-A-P -->
            <div class="row">
                <div class="col-md-6 mb-2">
                    <small class="text-muted d-block font-weight-bold">Subjektif (Keluhan)</small>
                    <div class="border rounded p-2 bg-light" style="white-space: pre-wrap;" x-text="selectedSoap.keluhan || '-'"></div>
                </div>
                <div class="col-md-6 mb-2">
                    <small class="text-muted d-block font-weight-bold">Objektif (Pemeriksaan)</small>
                    <div class="border rounded p-2 bg-light" style="white-space: pre-wrap;" x-text="selectedSoap.pemeriksaan || '-'"></div>
                </div>
                <div class="col-md-6 mb-2">
                    <small class="text-muted d-block font-weight-bold">Asesmen (Penilaian)</small>
                    <div class="border rounded p-2 bg-light" style="white-space: pre-wrap;" x-text="selectedSoap.penilaian || '-'"></div>
                </div>
                <div class="col-md-6 mb-2">
                    <small class="text-muted d-block font-weight-bold">Plan (Instruksi / RTL)</small>
                    <div class="border rounded p-2 bg-light">
                        <div x-show="selectedSoap.instruksi"><strong>Instruksi:</strong> <span style="white-space: pre-wrap;" x-text="selectedSoap.instruksi"></span></div>
                        <div x-show="selectedSoap.rtl" class="mt-1"><strong>RTL:</strong> <span style="white-space: pre-wrap;" x-text="selectedSoap.rtl"></span></div>
                        <div x-show="selectedSoap.evaluasi" class="mt-1"><strong>Evaluasi:</strong> <span style="white-space: pre-wrap;" x-text="selectedSoap.evaluasi"></span></div>
                        <div x-show="!selectedSoap.instruksi && !selectedSoap.rtl && !selectedSoap.evaluasi">-</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
```

- [ ] **Step 3: Tambahkan Modal Selector SOAP**

Tambahkan kode modal Bootstrap di bagian bawah template:
```blade
<!-- Modal Selector SOAP Pemeriksaan Ralan -->
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    <i class="fas fa-clipboard-list"></i> Pilih Data SOAP Pemeriksaan Ralan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                @if(isset($listPemeriksaan) && count($listPemeriksaan) > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 text-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Waktu</th>
                                <th>Petugas / Dokter</th>
                                <th>TTV (Td/Nd/Sh/Rr)</th>
                                <th>Keluhan</th>
                                <th class="text-center" style="width: 90px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($listPemeriksaan as $idx => $soap)
                            <tr>
                                <td>
                                    <strong>{{ date('d-m-Y', strtotime($soap->tgl_perawatan)) }}</strong><br>
                                    <small class="text-muted">{{ $soap->jam_rawat }}</small>
                                </td>
                                <td>{{ $soap->nama_petugas ?? $soap->nip ?? '-' }}</td>
                                <td>
                                    <small>
                                        TD: {{ $soap->tensi ?: '-' }} | N: {{ $soap->nadi ?: '-' }}<br>
                                        S: {{ $soap->suhu_tubuh ?: '-' }} | RR: {{ $soap->respirasi ?: '-' }}
                                    </small>
                                </td>
                                <td>
                                    <span class="d-inline-block text-truncate" style="max-width: 250px;" title="{{ $soap->keluhan }}">
                                        {{ $soap->keluhan ?: '-' }}
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    <button type="button"
                                        class="btn btn-xs btn-primary"
                                        @click="selectSoap(listSoap[{{ $idx }}])">
                                        <i class="fas fa-check"></i> Pilih
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                    Belum ada riwayat data SOAP pemeriksaan ralan pada nomor rawat ini.
                </div>
                @endif
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
```

- [ ] **Step 4: Verify Blade view syntax & rendering**

Run: `php artisan view:clear`
Expected: `Compiled views cleared!`

- [ ] **Step 5: Commit changes**

```bash
git add resources/views/components/ralan/penilaian-awal-medis-i-g-d.blade.php
git commit -m "feat: add clip button, modal selector, and reactive SOAP card in penilaian-awal-medis-i-g-d"
```

---

### Task 3: Verifikasi Menyeluruh & Testing

**Files:**
- Modify: N/A
- Test: PHPUnit & artisan tinker verification

- [ ] **Step 1: Jalankan PHPUnit test suite**

Run: `php artisan test --filter=PenilaianAwalMedisIGDTest`
Expected: PASS

- [ ] **Step 2: Lakukan pengecekan render component via tinker / scratch script**

Run: `php artisan tinker --execute="echo (new \App\View\Components\Ralan\PenilaianAwalMedisIGD('dummy'))->render();"`
Expected: Output view template ter-render tanpa error Exception.

- [ ] **Step 3: Lakukan git diff review**

Run: `git diff HEAD~2`
Expected: Perubahan hanya pada `PenilaianAwalMedisIGD.php`, template blade terkait, dan unit test.
