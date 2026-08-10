# Penilaian Awal Keperawatan IGD Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Display `penilaian_awal_keperawatan_igd` history in `pasien-tabs.blade.php` with a top card and a reusable component `<x-ralan.penilaian-awal-medis-i-g-d>`.

**Architecture:** Create/update component logic in `App\View\Components\Ralan\PenilaianAwalMedisIGD.php` and `PasienTabs.php` to fetch `penilaian_awal_keperawatan_igd` joined with `petugas`, render using `resources/views/components/ralan/penilaian-awal-medis-i-g-d.blade.php`, and include the top card in `resources/views/livewire/ralan/pasien-tabs.blade.php`.

**Tech Stack:** Laravel, Livewire, Blade, AdminLTE 3 components, MySQL / MariaDB.

## Global Constraints

- Preserve all existing code and queries in `PasienTabs.php` and `pasien-tabs.blade.php`.
- Style theme for cards: `theme="info"`.
- Table source: `penilaian_awal_keperawatan_igd`.

---

### Task 1: Add Helper Method in `PasienTabs.php` & Update Component Class `PenilaianAwalMedisIGD.php`

**Files:**
- Modify: `app/Http/Livewire/Ralan/PasienTabs.php`
- Modify: `app/View/Components/Ralan/PenilaianAwalMedisIGD.php`

**Interfaces:**
- Produces: `getPenilaianAwalKeperawatanIGD($noRawat)` returning `object|null`

- [ ] **Step 1: Add `getPenilaianAwalKeperawatanIGD($noRawat)` to `PasienTabs.php`**

Add the method to `app/Http/Livewire/Ralan/PasienTabs.php`:
```php
public function getPenilaianAwalKeperawatanIGD($noRawat)
{
    return DB::table('penilaian_awal_keperawatan_igd')
        ->leftJoin('petugas', 'penilaian_awal_keperawatan_igd.nip', '=', 'petugas.nip')
        ->where('penilaian_awal_keperawatan_igd.no_rawat', $noRawat)
        ->select('penilaian_awal_keperawatan_igd.*', 'petugas.nama as nama_petugas')
        ->first();
}
```

- [ ] **Step 2: Update `PenilaianAwalMedisIGD.php` component class**

Update `app/View/Components/Ralan/PenilaianAwalMedisIGD.php`:
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
}
```

- [ ] **Step 3: Commit Task 1**

```bash
git add app/Http/Livewire/Ralan/PasienTabs.php app/View/Components/Ralan/PenilaianAwalMedisIGD.php
git commit -m "feat: add getPenilaianAwalKeperawatanIGD query and update PenilaianAwalMedisIGD component class"
```

---

### Task 2: Create Component View Template `penilaian-awal-medis-i-g-d.blade.php`

**Files:**
- Modify: `resources/views/components/ralan/penilaian-awal-medis-i-g-d.blade.php`

**Interfaces:**
- Consumes: `$data` object containing fields of `penilaian_awal_keperawatan_igd`

- [ ] **Step 1: Write Blade View with `theme="info"` card layout**

Replace content in `resources/views/components/ralan/penilaian-awal-medis-i-g-d.blade.php`:
```blade
<div>
    @if(!empty($data))
    <x-adminlte-card theme="info" title="Penilaian Awal Keperawatan IGD" icon="fas fa-ambulance" theme-mode="outline" collapsible class="mb-4">
        @php
        $tglPenilaian = date_create($data->tanggal ?? '0000-00-00');
        $datePenilaian = date_format($tglPenilaian, "d M Y H:i");
        @endphp

        <div class="mb-3 pb-3 border-bottom">
            <div class="row">
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
        </div>

        <!-- 1. Informasi Umum & Anamnesis -->
        <div class="card mb-3 border-left-info" style="border-left-width: 4px;">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-notes-medical"></i> Riwayat Kesehatan & Anamnesis</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted d-block">Keluhan Utama</small>
                    <div class="border rounded p-2 bg-light">{!! nl2br(e($data->keluhan_utama ?? '-')) !!}</div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Riwayat Penyakit Dahulu (RPD)</small>
                        <div class="border rounded p-2 bg-light">{!! nl2br(e($data->rpd ?? '-')) !!}</div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Riwayat Pengobatan (RPO)</small>
                        <div class="border rounded p-2 bg-light">{!! nl2br(e($data->rpo ?? '-')) !!}</div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-3 mb-2">
                        <small class="text-muted d-block">Status Kehamilan</small>
                        <strong>{{ $data->status_kehamilan ?? '-' }}</strong>
                    </div>
                    @if(($data->status_kehamilan ?? '') == 'Hamil')
                    <div class="col-md-2 mb-2">
                        <small class="text-muted d-block">Gravida</small>
                        <strong>{{ $data->gravida ?? '-' }}</strong>
                    </div>
                    <div class="col-md-2 mb-2">
                        <small class="text-muted d-block">Para</small>
                        <strong>{{ $data->para ?? '-' }}</strong>
                    </div>
                    <div class="col-md-2 mb-2">
                        <small class="text-muted d-block">Abortus</small>
                        <strong>{{ $data->abortus ?? '-' }}</strong>
                    </div>
                    <div class="col-md-3 mb-2">
                        <small class="text-muted d-block">HPHT</small>
                        <strong>{{ $data->hpht ?? '-' }}</strong>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 2. Pemeriksaan Fisik & Eliminasi -->
        <div class="card mb-3 border-left-primary" style="border-left-width: 4px;">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-stethoscope"></i> Pemeriksaan Fisik & Eliminasi</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Tekanan</small>
                        <strong>{{ $data->tekanan ?? '-' }}</strong>
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Pupil</small>
                        <strong>{{ $data->pupil ?? '-' }}</strong>
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Neurosensorik</small>
                        <strong>{{ $data->neurosensorik ?? '-' }}</strong>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 mb-2">
                        <small class="text-muted d-block">Integumen</small>
                        <strong>{{ $data->integumen ?? '-' }}</strong>
                    </div>
                    <div class="col-md-3 mb-2">
                        <small class="text-muted d-block">Turgor</small>
                        <strong>{{ $data->turgor ?? '-' }}</strong>
                    </div>
                    <div class="col-md-3 mb-2">
                        <small class="text-muted d-block">Edema</small>
                        <strong>{{ $data->edema ?? '-' }}</strong>
                    </div>
                    <div class="col-md-3 mb-2">
                        <small class="text-muted d-block">Mukosa</small>
                        <strong>{{ $data->mukosa ?? '-' }}</strong>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Perdarahan</small>
                        <strong>{{ $data->perdarahan ?? '-' }}</strong>
                        @if(($data->perdarahan ?? '') == 'Ada')
                        <span class="ml-2">({{ $data->jumlah_perdarahan ?? '0' }} cc, Warna: {{ $data->warna_perdarahan ?? '-' }})</span>
                        @endif
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Intoksikasi</small>
                        <strong>{{ $data->intoksikasi ?? '-' }}</strong>
                    </div>
                </div>
                <div class="row border-top pt-2">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block"><i class="fas fa-toilet"></i> BAB</small>
                        <strong>{{ $data->bab ?? '-' }} x/hari</strong>
                        @if($data->xbab || $data->kbab || $data->wbab)
                        <div><small>Ket: {{ $data->xbab ?? '-' }}, Konsistensi: {{ $data->kbab ?? '-' }}, Warna: {{ $data->wbab ?? '-' }}</small></div>
                        @endif
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block"><i class="fas fa-restroom"></i> BAK</small>
                        <strong>{{ $data->bak ?? '-' }} x/hari</strong>
                        @if($data->xbak || $data->wbak || $data->lbak)
                        <div><small>Ket: {{ $data->xbak ?? '-' }}, Warna: {{ $data->wbak ?? '-' }}, Lain: {{ $data->lbak ?? '-' }}</small></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Pengkajian Nyeri -->
        <div class="card mb-3 border-left-warning" style="border-left-width: 4px;">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Pengkajian Nyeri</h6>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Status Nyeri</small>
                        <strong>{{ $data->nyeri ?? '-' }}</strong>
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Skala Nyeri</small>
                        <span class="badge badge-{{ ($data->skala_nyeri ?? 0) > 4 ? 'danger' : 'warning' }} p-2">
                            Skala {{ $data->skala_nyeri ?? '0' }} / 10
                        </span>
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Lokasi Nyeri</small>
                        <strong>{{ $data->lokasi ?? '-' }}</strong> (Menyebar: {{ $data->menyebar ?? '-' }})
                    </div>
                </div>
                @if(($data->nyeri ?? '') != 'Tidak Ada Nyeri')
                <div class="row mb-2">
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Penyebab (Provokes)</small>
                        <strong>{{ $data->provokes ?? '-' }}</strong> @if($data->ket_provokes) ({{ $data->ket_provokes }}) @endif
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Rasa Nyeri (Quality)</small>
                        <strong>{{ $data->quality ?? '-' }}</strong> @if($data->ket_quality) ({{ $data->ket_quality }}) @endif
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Durasi</small>
                        <strong>{{ $data->durasi ?? '-' }}</strong>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Nyeri Hilang Bila</small>
                        <strong>{{ $data->nyeri_hilang ?? '-' }}</strong> @if($data->ket_nyeri) ({{ $data->ket_nyeri }}) @endif
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Dilaporkan ke Dokter</small>
                        <strong>{{ $data->pada_dokter ?? '-' }}</strong> @if($data->ket_dokter) ({{ $data->ket_dokter }}) @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- 4. Pengkajian Risiko Jatuh & Fungsional -->
        <div class="card mb-3 border-left-danger" style="border-left-width: 4px;">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0"><i class="fas fa-user-shield"></i> Risiko Jatuh & Fungsional</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Kemampuan Mandiri</small>
                        <strong>{{ $data->kemampuan ?? '-' }}</strong>
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Aktivitas</small>
                        <strong>{{ $data->aktifitas ?? '-' }}</strong>
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Alat Bantu</small>
                        <strong>{{ $data->alat_bantu ?? '-' }}</strong> @if($data->ket_bantu) ({{ $data->ket_bantu }}) @endif
                    </div>
                </div>
                <div class="border-top pt-2">
                    <small class="text-muted d-block mb-1"><strong>Screening Risiko Jatuh:</strong></small>
                    <div class="row">
                        <div class="col-md-4 mb-1">Cara berjalan tak seimbang (a): <strong>{{ $data->berjalan_a ?? '-' }}</strong></div>
                        <div class="col-md-4 mb-1">Memegang penopang (b): <strong>{{ $data->berjalan_b ?? '-' }}</strong></div>
                        <div class="col-md-4 mb-1">Penopang/lainnya (c): <strong>{{ $data->berjalan_c ?? '-' }}</strong></div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted d-block">Hasil Screening</small>
                        <span class="badge badge-{{ str_contains($data->hasil ?? '', 'Resiko tinggi') ? 'danger' : (str_contains($data->hasil ?? '', 'Resiko rendah') ? 'warning' : 'success') }} p-2">
                            {{ $data->hasil ?? '-' }}
                        </span>
                        @if(($data->lapor ?? '') == 'Ya')
                        <span class="ml-2 text-muted">(Dilaporkan: {{ $data->ket_lapor ?? '-' }})</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Psikososial, Budaya & Edukasi -->
        <div class="card mb-3 border-left-secondary" style="border-left-width: 4px;">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="fas fa-brain"></i> Psikososial, Budaya & Edukasi</h6>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Status Psikologis</small>
                        <strong>{{ $data->psikologis ?? '-' }}</strong>
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Gangguan Jiwa</small>
                        <strong>{{ $data->jiwa ?? '-' }}</strong>
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Perilaku</small>
                        <strong>{{ $data->perilaku ?? '-' }}</strong> @if($data->sebutkan) ({{ $data->sebutkan }}) @endif
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Hubungan Pasien</small>
                        <strong>{{ $data->hubungan ?? '-' }}</strong>
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Tinggal Dengan</small>
                        <strong>{{ $data->tinggal_dengan ?? '-' }}</strong> @if($data->ket_tinggal) ({{ $data->ket_tinggal }}) @endif
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Budaya / Nilai Khusus</small>
                        <strong>{{ $data->budaya ?? '-' }}</strong> @if($data->ket_budaya) ({{ $data->ket_budaya }}) @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Pendidikan Penanggung Jawab</small>
                        <strong>{{ $data->pendidikan_pj ?? '-' }}</strong> @if($data->ket_pendidikan_pj) ({{ $data->ket_pendidikan_pj }}) @endif
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Edukasi Kepada</small>
                        <strong>{{ $data->edukasi ?? '-' }}</strong> @if($data->ket_edukasi) ({{ $data->ket_edukasi }}) @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Rencana Keperawatan -->
        @if($data->rencana)
        <div class="card mb-2 border-left-success" style="border-left-width: 4px;">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-tasks"></i> Rencana Keperawatan</h6>
            </div>
            <div class="card-body">
                <div class="border rounded p-2 bg-light">{!! nl2br(e($data->rencana)) !!}</div>
            </div>
        </div>
        @endif
    </x-adminlte-card>
    @endif
</div>
```

- [ ] **Step 2: Commit Task 2**

```bash
git add resources/views/components/ralan/penilaian-awal-medis-i-g-d.blade.php
git commit -m "feat: implement penilaian-awal-medis-i-g-d blade component template"
```

---

### Task 3: Render Top Card in `pasien-tabs.blade.php`

**Files:**
- Modify: `resources/views/livewire/ralan/pasien-tabs.blade.php:140-160`

**Interfaces:**
- Consumes: `$data` list of visits in `pasien-tabs.blade.php`

- [ ] **Step 1: Add IGD Assessment detection and top card rendering in `pasien-tabs.blade.php`**

In `resources/views/livewire/ralan/pasien-tabs.blade.php`, right after the `penilaianKebidananRanap` block (~line 154):
```blade
@php
// Ambil data Penilaian Awal Keperawatan IGD dari kunjungan mana saja yang memiliki data
$penilaianKeperawatanIGD = null;
$noRawatIGD = null;
foreach($data as $row) {
    $no_rawat_temp = is_array($row) ? ($row['no_rawat'] ?? null) : ($row->no_rawat ?? null);
    if ($no_rawat_temp) {
        $checkIGD = $this->getPenilaianAwalKeperawatanIGD($no_rawat_temp);
        if ($checkIGD) {
            $penilaianKeperawatanIGD = $checkIGD;
            $noRawatIGD = $no_rawat_temp;
            break;
        }
    }
}
@endphp

@if($noRawatIGD)
    <x-ralan.penilaian-awal-medis-i-g-d :no-rawat="$noRawatIGD" />
@endif
```

- [ ] **Step 2: Commit Task 3**

```bash
git add resources/views/livewire/ralan/pasien-tabs.blade.php
git commit -m "feat: render top card for Penilaian Awal Keperawatan IGD in pasien-tabs.blade.php"
```
