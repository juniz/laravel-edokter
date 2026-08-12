# Penilaian Pre Induksi Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build and integrate the "Penilaian Pre Induksi" (Pre-Induction Assessment) form component for Ralan (outpatient) and Ranap (inpatient) examination views using table `penilaian_pre_induksi`.

**Architecture:** Create a reusable Livewire component (`App\Http\Livewire\Component\PenilaianPreInduksi`) with form input sections, auto-filled vital signs from `pemeriksaan_ralan`/`pemeriksaan_ranap`, and a historical datatable per patient encounter. Embed this component into `resources/views/ralan/pemeriksaan-ralan.blade.php` and `resources/views/ranap/pemeriksaan-ranap.blade.php`.

**Tech Stack:** Laravel 9, Livewire 2, AdminLTE 3, MySQL / MariaDB, Jantinnerezo LivewireAlert.

## Global Constraints
- `tanggal` defaults to current timestamp `Carbon::now()->format('Y-m-d H:i:s')` (or `Y-m-d\TH:i` for `<input type="datetime-local">`).
- `kd_dokter` defaults to logged-in user session `session()->get('username')`.
- Table name: `penilaian_pre_induksi`.
- Primary Key: (`no_rawat`, `tanggal`).

---

### Task 1: Create Livewire Component Class `PenilaianPreInduksi`

**Files:**
- Create: `app/Http/Livewire/Component/PenilaianPreInduksi.php`

**Interfaces:**
- Consumes: Database table `penilaian_pre_induksi`, `pemeriksaan_ralan`, `pemeriksaan_ranap`, `dokter`.
- Produces: Livewire component class registered as `component.penilaian-pre-induksi`.

- [ ] **Step 1: Create component class file**

Write `app/Http/Livewire/Component/PenilaianPreInduksi.php` with state properties and CRUD logic:

```php
<?php

namespace App\Http\Livewire\Component;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class PenilaianPreInduksi extends Component
{
    use LivewireAlert;

    public $no_rawat;
    public $no_rm;
    public $kd_dokter;

    // Fields
    public $tanggal;
    public $tensi = '';
    public $nadi = '';
    public $rr = '';
    public $suhu = '';
    public $ekg = '';
    public $lain_lain = '';
    public $asesmen = '';
    public $perencanaan = '';
    public $infus_perifier = '';
    public $cvc = '';
    public $posisi = '';
    public $premedikasi = '';
    public $premedikasi_keterangan = '';
    public $induksi = '';
    public $induksi_keterangan = '';
    public $face_mask_no = '';
    public $nasopharing_no = '';
    public $ett_no = '';
    public $ett_jenis = '';
    public $ett_viksasi = '';
    public $lma_no = '';
    public $lma_jenis = '';
    public $tracheostomi = '';
    public $bronchoscopi_fiberoptik = '';
    public $glidescopi = '';
    public $lain_lain_tatalaksana = '';
    public $intubasi_sesudah_tidur = '';
    public $intubasi_oral = '';
    public $intubasi_tracheostomi = '';
    public $intubasi_keterangan = '';
    public $sulit_ventilasi = '';
    public $sulit_intubasi = '';
    public $ventilasi = '';
    public $teknik_regional_jenis = '';
    public $teknik_regional_lokasi = '';
    public $teknik_regional_jenis_jarum = '';
    public $teknik_regional_kateter = '';
    public $teknik_regional_kateter_viksasi = '';
    public $teknik_regional_obat_obatan = '';
    public $teknik_regional_komplikasi = '';
    public $teknik_regional_hasil = '';

    public $data = [];
    public $modeEdit = false;
    public $tanggalEdit = null;

    protected $listeners = [
        'hapusPenilaianPreInduksi' => 'hapus',
    ];

    public function mount($noRawat, $noRm = null): void
    {
        $this->no_rawat = $noRawat;
        $this->no_rm = $noRm;
        $this->kd_dokter = session()->get('username');
        $this->tanggal = Carbon::now()->format('Y-m-d\TH:i');
        $this->getData();
        $this->autofillVitalSigns();
    }

    public function hydrate(): void
    {
        $this->getData();
    }

    public function getData(): void
    {
        if (empty($this->no_rawat)) {
            $this->data = [];
            return;
        }

        $this->data = DB::table('penilaian_pre_induksi')
            ->leftJoin('dokter', 'penilaian_pre_induksi.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('penilaian_pre_induksi.no_rawat', $this->no_rawat)
            ->select('penilaian_pre_induksi.*', 'dokter.nm_dokter')
            ->orderBy('penilaian_pre_induksi.tanggal', 'desc')
            ->get();
    }

    public function autofillVitalSigns(): void
    {
        if ($this->modeEdit || empty($this->no_rawat)) {
            return;
        }

        $pemeriksaan = DB::table('pemeriksaan_ralan')
            ->where('no_rawat', $this->no_rawat)
            ->orderBy('tgl_perawatan', 'desc')
            ->orderBy('jam_rawat', 'desc')
            ->first();

        if (! $pemeriksaan) {
            $pemeriksaan = DB::table('pemeriksaan_ranap')
                ->where('no_rawat', $this->no_rawat)
                ->orderBy('tgl_perawatan', 'desc')
                ->orderBy('jam_rawat', 'desc')
                ->first();
        }

        if ($pemeriksaan) {
            if (empty($this->tensi) && isset($pemeriksaan->tensi)) {
                $this->tensi = (string) $pemeriksaan->tensi;
            }
            if (empty($this->nadi) && isset($pemeriksaan->nadi)) {
                $this->nadi = (string) $pemeriksaan->nadi;
            }
            if (empty($this->rr) && isset($pemeriksaan->respirasi)) {
                $this->rr = (string) $pemeriksaan->respirasi;
            }
            if (empty($this->suhu) && isset($pemeriksaan->suhu_tubuh)) {
                $this->suhu = (string) $pemeriksaan->suhu_tubuh;
            }
        }
    }

    public function rules(): array
    {
        return [
            'tanggal' => 'required',
            'kd_dokter' => 'required',
            'tensi' => 'nullable|string|max:8',
            'nadi' => 'nullable|string|max:5',
            'rr' => 'nullable|string|max:5',
            'suhu' => 'nullable|string|max:5',
            'ekg' => 'nullable|string|max:50',
            'lain_lain' => 'nullable|string|max:50',
            'asesmen' => 'nullable|in:Sesuai Asesmen Pre Sedasi/Anestesi,Tidak Sesuai Asesmen Pre Sedasi/Anestesi',
            'perencanaan' => 'nullable|string|max:300',
            'infus_perifier' => 'nullable|string|max:300',
            'cvc' => 'nullable|string|max:70',
            'posisi' => 'nullable|in:Supine,Lithotomi,Lateral,Prone,Perlindungan Mata,Kanan,Kiri,Lain-lain',
            'premedikasi' => 'nullable|in:Oral,IM,IV',
            'premedikasi_keterangan' => 'nullable|string|max:50',
            'induksi' => 'nullable|in:Intravena,Inhalasi',
            'induksi_keterangan' => 'nullable|string|max:70',
            'face_mask_no' => 'nullable|string|max:20',
            'nasopharing_no' => 'nullable|string|max:20',
            'ett_no' => 'nullable|string|max:20',
            'ett_jenis' => 'nullable|string|max:20',
            'ett_viksasi' => 'nullable|string|max:25',
            'lma_no' => 'nullable|string|max:20',
            'lma_jenis' => 'nullable|string|max:20',
            'tracheostomi' => 'nullable|string|max:60',
            'bronchoscopi_fiberoptik' => 'nullable|string|max:60',
            'glidescopi' => 'nullable|string|max:60',
            'lain_lain_tatalaksana' => 'nullable|string|max:100',
            'intubasi_sesudah_tidur' => 'nullable|in:Ya,Tidak',
            'intubasi_oral' => 'nullable|in:Ya,Tidak',
            'intubasi_tracheostomi' => 'nullable|in:Ya,Tidak',
            'intubasi_keterangan' => 'nullable|string|max:200',
            'sulit_ventilasi' => 'nullable|string|max:100',
            'sulit_intubasi' => 'nullable|string|max:100',
            'ventilasi' => 'nullable|string|max:100',
            'teknik_regional_jenis' => 'nullable|string|max:100',
            'teknik_regional_lokasi' => 'nullable|string|max:40',
            'teknik_regional_jenis_jarum' => 'nullable|string|max:30',
            'teknik_regional_kateter' => 'nullable|in:Ya,Tidak',
            'teknik_regional_kateter_viksasi' => 'nullable|string|max:40',
            'teknik_regional_obat_obatan' => 'nullable|string|max:400',
            'teknik_regional_komplikasi' => 'nullable|string|max:200',
            'teknik_regional_hasil' => 'nullable|string|max:100',
        ];
    }

    public function simpan(): void
    {
        $this->validate();

        try {
            $formattedTanggal = Carbon::parse($this->tanggal)->format('Y-m-d H:i:s');

            $exists = DB::table('penilaian_pre_induksi')
                ->where('no_rawat', $this->no_rawat)
                ->where('tanggal', $formattedTanggal)
                ->exists();

            if ($exists) {
                $this->alert('error', 'Penilaian Pre Induksi pada tanggal tersebut sudah ada!');
                return;
            }

            DB::table('penilaian_pre_induksi')->insert([
                'no_rawat' => $this->no_rawat,
                'tanggal' => $formattedTanggal,
                'kd_dokter' => $this->kd_dokter,
                'tensi' => $this->tensi ?: null,
                'nadi' => $this->nadi ?: null,
                'rr' => $this->rr ?: null,
                'suhu' => $this->suhu ?: null,
                'ekg' => $this->ekg ?: null,
                'lain_lain' => $this->lain_lain ?: null,
                'asesmen' => $this->asesmen ?: null,
                'perencanaan' => $this->perencanaan ?: null,
                'infus_perifier' => $this->infus_perifier ?: null,
                'cvc' => $this->cvc ?: null,
                'posisi' => $this->posisi ?: null,
                'premedikasi' => $this->premedikasi ?: null,
                'premedikasi_keterangan' => $this->premedikasi_keterangan ?: null,
                'induksi' => $this->induksi ?: null,
                'induksi_keterangan' => $this->induksi_keterangan ?: null,
                'face_mask_no' => $this->face_mask_no ?: null,
                'nasopharing_no' => $this->nasopharing_no ?: null,
                'ett_no' => $this->ett_no ?: null,
                'ett_jenis' => $this->ett_jenis ?: null,
                'ett_viksasi' => $this->ett_viksasi ?: null,
                'lma_no' => $this->lma_no ?: null,
                'lma_jenis' => $this->lma_jenis ?: null,
                'tracheostomi' => $this->tracheostomi ?: null,
                'bronchoscopi_fiberoptik' => $this->bronchoscopi_fiberoptik ?: null,
                'glidescopi' => $this->glidescopi ?: null,
                'lain_lain_tatalaksana' => $this->lain_lain_tatalaksana ?: null,
                'intubasi_sesudah_tidur' => $this->intubasi_sesudah_tidur ?: null,
                'intubasi_oral' => $this->intubasi_oral ?: null,
                'intubasi_tracheostomi' => $this->intubasi_tracheostomi ?: null,
                'intubasi_keterangan' => $this->intubasi_keterangan ?: null,
                'sulit_ventilasi' => $this->sulit_ventilasi ?: null,
                'sulit_intubasi' => $this->sulit_intubasi ?: null,
                'ventilasi' => $this->ventilasi ?: null,
                'teknik_regional_jenis' => $this->teknik_regional_jenis ?: null,
                'teknik_regional_lokasi' => $this->teknik_regional_lokasi ?: null,
                'teknik_regional_jenis_jarum' => $this->teknik_regional_jenis_jarum ?: null,
                'teknik_regional_kateter' => $this->teknik_regional_kateter ?: null,
                'teknik_regional_kateter_viksasi' => $this->teknik_regional_kateter_viksasi ?: null,
                'teknik_regional_obat_obatan' => $this->teknik_regional_obat_obatan ?: null,
                'teknik_regional_komplikasi' => $this->teknik_regional_komplikasi ?: null,
                'teknik_regional_hasil' => $this->teknik_regional_hasil ?: null,
            ]);

            $this->alert('success', 'Penilaian Pre Induksi berhasil disimpan!');
            $this->batal();
            $this->getData();
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function edit($tanggal): void
    {
        $row = DB::table('penilaian_pre_induksi')
            ->where('no_rawat', $this->no_rawat)
            ->where('tanggal', $tanggal)
            ->first();

        if (! $row) {
            $this->alert('error', 'Data tidak ditemukan!');
            return;
        }

        $this->modeEdit = true;
        $this->tanggalEdit = $tanggal;
        $this->tanggal = Carbon::parse($row->tanggal)->format('Y-m-d\TH:i');
        $this->kd_dokter = $row->kd_dokter;

        $fields = [
            'tensi', 'nadi', 'rr', 'suhu', 'ekg', 'lain_lain', 'asesmen', 'perencanaan',
            'infus_perifier', 'cvc', 'posisi', 'premedikasi', 'premedikasi_keterangan',
            'induksi', 'induksi_keterangan', 'face_mask_no', 'nasopharing_no', 'ett_no',
            'ett_jenis', 'ett_viksasi', 'lma_no', 'lma_jenis', 'tracheostomi',
            'bronchoscopi_fiberoptik', 'glidescopi', 'lain_lain_tatalaksana',
            'intubasi_sesudah_tidur', 'intubasi_oral', 'intubasi_tracheostomi',
            'intubasi_keterangan', 'sulit_ventilasi', 'sulit_intubasi', 'ventilasi',
            'teknik_regional_jenis', 'teknik_regional_lokasi', 'teknik_regional_jenis_jarum',
            'teknik_regional_kateter', 'teknik_regional_kateter_viksasi',
            'teknik_regional_obat_obatan', 'teknik_regional_komplikasi', 'teknik_regional_hasil',
        ];

        foreach ($fields as $field) {
            $this->{$field} = $row->{$field} ?? '';
        }
    }

    public function update(): void
    {
        $this->validate();

        try {
            $formattedTanggal = Carbon::parse($this->tanggal)->format('Y-m-d H:i:s');

            DB::table('penilaian_pre_induksi')
                ->where('no_rawat', $this->no_rawat)
                ->where('tanggal', $this->tanggalEdit)
                ->update([
                    'tanggal' => $formattedTanggal,
                    'kd_dokter' => $this->kd_dokter,
                    'tensi' => $this->tensi ?: null,
                    'nadi' => $this->nadi ?: null,
                    'rr' => $this->rr ?: null,
                    'suhu' => $this->suhu ?: null,
                    'ekg' => $this->ekg ?: null,
                    'lain_lain' => $this->lain_lain ?: null,
                    'asesmen' => $this->asesmen ?: null,
                    'perencanaan' => $this->perencanaan ?: null,
                    'infus_perifier' => $this->infus_perifier ?: null,
                    'cvc' => $this->cvc ?: null,
                    'posisi' => $this->posisi ?: null,
                    'premedikasi' => $this->premedikasi ?: null,
                    'premedikasi_keterangan' => $this->premedikasi_keterangan ?: null,
                    'induksi' => $this->induksi ?: null,
                    'induksi_keterangan' => $this->induksi_keterangan ?: null,
                    'face_mask_no' => $this->face_mask_no ?: null,
                    'nasopharing_no' => $this->nasopharing_no ?: null,
                    'ett_no' => $this->ett_no ?: null,
                    'ett_jenis' => $this->ett_jenis ?: null,
                    'ett_viksasi' => $this->ett_viksasi ?: null,
                    'lma_no' => $this->lma_no ?: null,
                    'lma_jenis' => $this->lma_jenis ?: null,
                    'tracheostomi' => $this->tracheostomi ?: null,
                    'bronchoscopi_fiberoptik' => $this->bronchoscopi_fiberoptik ?: null,
                    'glidescopi' => $this->glidescopi ?: null,
                    'lain_lain_tatalaksana' => $this->lain_lain_tatalaksana ?: null,
                    'intubasi_sesudah_tidur' => $this->intubasi_sesudah_tidur ?: null,
                    'intubasi_oral' => $this->intubasi_oral ?: null,
                    'intubasi_tracheostomi' => $this->intubasi_tracheostomi ?: null,
                    'intubasi_keterangan' => $this->intubasi_keterangan ?: null,
                    'sulit_ventilasi' => $this->sulit_ventilasi ?: null,
                    'sulit_intubasi' => $this->sulit_intubasi ?: null,
                    'ventilasi' => $this->ventilasi ?: null,
                    'teknik_regional_jenis' => $this->teknik_regional_jenis ?: null,
                    'teknik_regional_lokasi' => $this->teknik_regional_lokasi ?: null,
                    'teknik_regional_jenis_jarum' => $this->teknik_regional_jenis_jarum ?: null,
                    'teknik_regional_kateter' => $this->teknik_regional_kateter ?: null,
                    'teknik_regional_kateter_viksasi' => $this->teknik_regional_kateter_viksasi ?: null,
                    'teknik_regional_obat_obatan' => $this->teknik_regional_obat_obatan ?: null,
                    'teknik_regional_komplikasi' => $this->teknik_regional_komplikasi ?: null,
                    'teknik_regional_hasil' => $this->teknik_regional_hasil ?: null,
                ]);

            $this->alert('success', 'Penilaian Pre Induksi berhasil diperbarui!');
            $this->batal();
            $this->getData();
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    public function confirmHapus($tanggal): void
    {
        $this->alert('warning', 'Apakah Anda yakin ingin menghapus data ini?', [
            'position' => 'center',
            'timer' => null,
            'toast' => false,
            'showConfirmButton' => true,
            'onConfirmed' => 'hapusPenilaianPreInduksi',
            'showCancelButton' => true,
            'onDismissed' => '',
            'data' => [
                'tanggal' => $tanggal,
            ],
        ]);
    }

    public function hapus($data): void
    {
        $tanggal = is_array($data) ? ($data['data']['tanggal'] ?? null) : $data;

        if ($tanggal) {
            DB::table('penilaian_pre_induksi')
                ->where('no_rawat', $this->no_rawat)
                ->where('tanggal', $tanggal)
                ->delete();

            $this->alert('success', 'Penilaian Pre Induksi berhasil dihapus!');
            $this->getData();
        }
    }

    public function batal(): void
    {
        $this->modeEdit = false;
        $this->tanggalEdit = null;
        $this->tanggal = Carbon::now()->format('Y-m-d\TH:i');

        $fields = [
            'tensi', 'nadi', 'rr', 'suhu', 'ekg', 'lain_lain', 'asesmen', 'perencanaan',
            'infus_perifier', 'cvc', 'posisi', 'premedikasi', 'premedikasi_keterangan',
            'induksi', 'induksi_keterangan', 'face_mask_no', 'nasopharing_no', 'ett_no',
            'ett_jenis', 'ett_viksasi', 'lma_no', 'lma_jenis', 'tracheostomi',
            'bronchoscopi_fiberoptik', 'glidescopi', 'lain_lain_tatalaksana',
            'intubasi_sesudah_tidur', 'intubasi_oral', 'intubasi_tracheostomi',
            'intubasi_keterangan', 'sulit_ventilasi', 'sulit_intubasi', 'ventilasi',
            'teknik_regional_jenis', 'teknik_regional_lokasi', 'teknik_regional_jenis_jarum',
            'teknik_regional_kateter', 'teknik_regional_kateter_viksasi',
            'teknik_regional_obat_obatan', 'teknik_regional_komplikasi', 'teknik_regional_hasil',
        ];

        foreach ($fields as $field) {
            $this->{$field} = '';
        }

        $this->autofillVitalSigns();
    }

    public function render()
    {
        return view('livewire.component.penilaian-pre-induksi');
    }
}
```

- [ ] **Step 2: Verify syntax**

Run: `php -l app/Http/Livewire/Component/PenilaianPreInduksi.php`
Expected: No syntax errors detected.

---

### Task 2: Create Livewire Component Blade View

**Files:**
- Create: `resources/views/livewire/component/penilaian-pre-induksi.blade.php`

**Interfaces:**
- Consumes: State variables & methods from `PenilaianPreInduksi.php`.
- Produces: Form HTML with Bootstrap 4 input fields & historical data table.

- [ ] **Step 1: Create blade view file**

Write `resources/views/livewire/component/penilaian-pre-induksi.blade.php`:

```html
<div>
    <form wire:submit.prevent="{{ $modeEdit ? 'update' : 'simpan' }}">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-1"></i>
                    {{ $modeEdit ? 'Edit Penilaian Pre Induksi' : 'Input Penilaian Pre Induksi' }}
                </h3>
            </div>
            <div class="card-body">
                <!-- Section 1: Informasi Waktu & Dokter -->
                <h5 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-clock mr-1"></i> Informasi Waktu Assessment</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tanggal & Jam Assessment</label>
                            <input type="datetime-local" class="form-control @error('tanggal') is-invalid @enderror" wire:model.defer="tanggal">
                            @error('tanggal') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Tanda Vital & Penunjang -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-heartbeat mr-1"></i> Tanda Vital & Penunjang</h5>
                <div class="row">
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>TD (mmHg)</label>
                            <input type="text" class="form-control @error('tensi') is-invalid @enderror" wire:model.defer="tensi" placeholder="120/80" maxlength="8">
                            @error('tensi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>Nadi (x/mnt)</label>
                            <input type="text" class="form-control @error('nadi') is-invalid @enderror" wire:model.defer="nadi" placeholder="80" maxlength="5">
                            @error('nadi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>RR (x/mnt)</label>
                            <input type="text" class="form-control @error('rr') is-invalid @enderror" wire:model.defer="rr" placeholder="20" maxlength="5">
                            @error('rr') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>Suhu (°C)</label>
                            <input type="text" class="form-control @error('suhu') is-invalid @enderror" wire:model.defer="suhu" placeholder="36.5" maxlength="5">
                            @error('suhu') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>EKG</label>
                            <input type="text" class="form-control @error('ekg') is-invalid @enderror" wire:model.defer="ekg" placeholder="Hasil EKG" maxlength="50">
                            @error('ekg') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Lain-lain Penunjang</label>
                            <input type="text" class="form-control @error('lain_lain') is-invalid @enderror" wire:model.defer="lain_lain" placeholder="Pemeriksaan penunjang lainnya" maxlength="50">
                            @error('lain_lain') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 3: Asesmen & Perencanaan -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-clipboard-check mr-1"></i> Asesmen & Perencanaan</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Asesmen</label>
                            <select class="form-control @error('asesmen') is-invalid @enderror" wire:model.defer="asesmen">
                                <option value="">-- Pilih Asesmen --</option>
                                <option value="Sesuai Asesmen Pre Sedasi/Anestesi">Sesuai Asesmen Pre Sedasi/Anestesi</option>
                                <option value="Tidak Sesuai Asesmen Pre Sedasi/Anestesi">Tidak Sesuai Asesmen Pre Sedasi/Anestesi</option>
                            </select>
                            @error('asesmen') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Perencanaan</label>
                            <textarea class="form-control @error('perencanaan') is-invalid @enderror" wire:model.defer="perencanaan" rows="2" placeholder="Catatan perencanaan" maxlength="300"></textarea>
                            @error('perencanaan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 4: Akses Vaskuler & Posisi -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-procedures mr-1"></i> Akses Vaskuler & Posisi</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Infus Perifer</label>
                            <input type="text" class="form-control @error('infus_perifier') is-invalid @enderror" wire:model.defer="infus_perifier" placeholder="Lokasi & ukuran infus" maxlength="300">
                            @error('infus_perifier') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>CVC</label>
                            <input type="text" class="form-control @error('cvc') is-invalid @enderror" wire:model.defer="cvc" placeholder="Keterangan CVC" maxlength="70">
                            @error('cvc') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Posisi Pasien</label>
                            <select class="form-control @error('posisi') is-invalid @enderror" wire:model.defer="posisi">
                                <option value="">-- Pilih Posisi --</option>
                                <option value="Supine">Supine</option>
                                <option value="Lithotomi">Lithotomi</option>
                                <option value="Lateral">Lateral</option>
                                <option value="Prone">Prone</option>
                                <option value="Perlindungan Mata">Perlindungan Mata</option>
                                <option value="Kanan">Kanan</option>
                                <option value="Kiri">Kiri</option>
                                <option value="Lain-lain">Lain-lain</option>
                            </select>
                            @error('posisi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 5: Premedikasi & Induksi -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-syringe mr-1"></i> Premedikasi & Induksi</h5>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Premedikasi</label>
                            <select class="form-control @error('premedikasi') is-invalid @enderror" wire:model.defer="premedikasi">
                                <option value="">-- Pilih Rute --</option>
                                <option value="Oral">Oral</option>
                                <option value="IM">IM</option>
                                <option value="IV">IV</option>
                            </select>
                            @error('premedikasi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ket. Premedikasi</label>
                            <input type="text" class="form-control @error('premedikasi_keterangan') is-invalid @enderror" wire:model.defer="premedikasi_keterangan" placeholder="Obat / Dosis" maxlength="50">
                            @error('premedikasi_keterangan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Induksi</label>
                            <select class="form-control @error('induksi') is-invalid @enderror" wire:model.defer="induksi">
                                <option value="">-- Pilih Jenis --</option>
                                <option value="Intravena">Intravena</option>
                                <option value="Inhalasi">Inhalasi</option>
                            </select>
                            @error('induksi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ket. Induksi</label>
                            <input type="text" class="form-control @error('induksi_keterangan') is-invalid @enderror" wire:model.defer="induksi_keterangan" placeholder="Keterangan induksi" maxlength="70">
                            @error('induksi_keterangan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 6: Tatalaksana Jalan Napas & Intubasi -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-head-side-mask mr-1"></i> Tatalaksana Jalan Napas & Intubasi</h5>
                <div class="row">
                    <div class="col-md-2 col-6">
                        <div class="form-group">
                            <label>Face Mask No</label>
                            <input type="text" class="form-control @error('face_mask_no') is-invalid @enderror" wire:model.defer="face_mask_no" placeholder="No" maxlength="20">
                            @error('face_mask_no') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="form-group">
                            <label>Nasopharing No</label>
                            <input type="text" class="form-control @error('nasopharing_no') is-invalid @enderror" wire:model.defer="nasopharing_no" placeholder="No" maxlength="20">
                            @error('nasopharing_no') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="form-group">
                            <label>ETT No</label>
                            <input type="text" class="form-control @error('ett_no') is-invalid @enderror" wire:model.defer="ett_no" placeholder="No" maxlength="20">
                            @error('ett_no') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>ETT Jenis</label>
                            <input type="text" class="form-control @error('ett_jenis') is-invalid @enderror" wire:model.defer="ett_jenis" placeholder="Jenis ETT" maxlength="20">
                            @error('ett_jenis') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>ETT Fiksasi</label>
                            <input type="text" class="form-control @error('ett_viksasi') is-invalid @enderror" wire:model.defer="ett_viksasi" placeholder="Fiksasi (cm)" maxlength="25">
                            @error('ett_viksasi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>LMA No</label>
                            <input type="text" class="form-control @error('lma_no') is-invalid @enderror" wire:model.defer="lma_no" placeholder="No" maxlength="20">
                            @error('lma_no') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>LMA Jenis</label>
                            <input type="text" class="form-control @error('lma_jenis') is-invalid @enderror" wire:model.defer="lma_jenis" placeholder="Jenis LMA" maxlength="20">
                            @error('lma_jenis') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>Tracheostomi</label>
                            <input type="text" class="form-control @error('tracheostomi') is-invalid @enderror" wire:model.defer="tracheostomi" placeholder="Tracheostomi" maxlength="60">
                            @error('tracheostomi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>Bronchoscopi Fiberoptik</label>
                            <input type="text" class="form-control @error('bronchoscopi_fiberoptik') is-invalid @enderror" wire:model.defer="bronchoscopi_fiberoptik" placeholder="Bronchoscopi" maxlength="60">
                            @error('bronchoscopi_fiberoptik') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>Glidescopi</label>
                            <input type="text" class="form-control @error('glidescopi') is-invalid @enderror" wire:model.defer="glidescopi" placeholder="Glidescopi" maxlength="60">
                            @error('glidescopi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-9 col-12">
                        <div class="form-group">
                            <label>Lain-lain Tatalaksana Jalan Napas</label>
                            <input type="text" class="form-control @error('lain_lain_tatalaksana') is-invalid @enderror" wire:model.defer="lain_lain_tatalaksana" placeholder="Keterangan tambahan" maxlength="100">
                            @error('lain_lain_tatalaksana') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Evaluasi Intubasi & Ventilasi -->
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>Intubasi Sesudah Tidur</label>
                            <select class="form-control @error('intubasi_sesudah_tidur') is-invalid @enderror" wire:model.defer="intubasi_sesudah_tidur">
                                <option value="">-- Pilih --</option>
                                <option value="Ya">Ya</option>
                                <option value="Tidak">Tidak</option>
                            </select>
                            @error('intubasi_sesudah_tidur') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>Intubasi Oral</label>
                            <select class="form-control @error('intubasi_oral') is-invalid @enderror" wire:model.defer="intubasi_oral">
                                <option value="">-- Pilih --</option>
                                <option value="Ya">Ya</option>
                                <option value="Tidak">Tidak</option>
                            </select>
                            @error('intubasi_oral') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>Intubasi Tracheostomi</label>
                            <select class="form-control @error('intubasi_tracheostomi') is-invalid @enderror" wire:model.defer="intubasi_tracheostomi">
                                <option value="">-- Pilih --</option>
                                <option value="Ya">Ya</option>
                                <option value="Tidak">Tidak</option>
                            </select>
                            @error('intubasi_tracheostomi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>Ventilasi</label>
                            <input type="text" class="form-control @error('ventilasi') is-invalid @enderror" wire:model.defer="ventilasi" placeholder="Ket. Ventilasi" maxlength="100">
                            @error('ventilasi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>Sulit Ventilasi</label>
                            <input type="text" class="form-control @error('sulit_ventilasi') is-invalid @enderror" wire:model.defer="sulit_ventilasi" placeholder="Catatan jika sulit" maxlength="100">
                            @error('sulit_ventilasi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>Sulit Intubasi</label>
                            <input type="text" class="form-control @error('sulit_intubasi') is-invalid @enderror" wire:model.defer="sulit_intubasi" placeholder="Catatan jika sulit" maxlength="100">
                            @error('sulit_intubasi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>Ket. Intubasi</label>
                            <input type="text" class="form-control @error('intubasi_keterangan') is-invalid @enderror" wire:model.defer="intubasi_keterangan" placeholder="Keterangan intubasi" maxlength="200">
                            @error('intubasi_keterangan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 7: Teknik Regional -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-band-aid mr-1"></i> Teknik Regional</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Jenis Teknik Regional</label>
                            <input type="text" class="form-control @error('teknik_regional_jenis') is-invalid @enderror" wire:model.defer="teknik_regional_jenis" placeholder="Jenis teknik regional" maxlength="100">
                            @error('teknik_regional_jenis') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Lokasi Regional</label>
                            <input type="text" class="form-control @error('teknik_regional_lokasi') is-invalid @enderror" wire:model.defer="teknik_regional_lokasi" placeholder="Lokasi anatomis" maxlength="40">
                            @error('teknik_regional_lokasi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Jenis Jarum</label>
                            <input type="text" class="form-control @error('teknik_regional_jenis_jarum') is-invalid @enderror" wire:model.defer="teknik_regional_jenis_jarum" placeholder="Jenis & ukuran jarum" maxlength="30">
                            @error('teknik_regional_jenis_jarum') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Kateter</label>
                            <select class="form-control @error('teknik_regional_kateter') is-invalid @enderror" wire:model.defer="teknik_regional_kateter">
                                <option value="">-- Pilih --</option>
                                <option value="Ya">Ya</option>
                                <option value="Tidak">Tidak</option>
                            </select>
                            @error('teknik_regional_kateter') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Fiksasi Kateter</label>
                            <input type="text" class="form-control @error('teknik_regional_kateter_viksasi') is-invalid @enderror" wire:model.defer="teknik_regional_kateter_viksasi" placeholder="Kedalaman fiksasi" maxlength="40">
                            @error('teknik_regional_kateter_viksasi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Obat-obatan Regional</label>
                            <input type="text" class="form-control @error('teknik_regional_obat_obatan') is-invalid @enderror" wire:model.defer="teknik_regional_obat_obatan" placeholder="Nama obat & dosis" maxlength="400">
                            @error('teknik_regional_obat_obatan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Komplikasi Regional</label>
                            <input type="text" class="form-control @error('teknik_regional_komplikasi') is-invalid @enderror" wire:model.defer="teknik_regional_komplikasi" placeholder="Komplikasi yang terjadi" maxlength="200">
                            @error('teknik_regional_komplikasi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Hasil Regional</label>
                            <input type="text" class="form-control @error('teknik_regional_hasil') is-invalid @enderror" wire:model.defer="teknik_regional_hasil" placeholder="Hasil blokade/anestesi" maxlength="100">
                            @error('teknik_regional_hasil') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer text-right">
                @if($modeEdit)
                    <button type="button" class="btn btn-secondary mr-2" wire:click="batal">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i> Update
                    </button>
                @else
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                @endif
            </div>
        </div>
    </form>

    <!-- Table Riwayat Penilaian Pre Induksi -->
    <div class="card card-outline card-info mt-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history mr-1"></i> Riwayat Penilaian Pre Induksi
            </h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Dokter</th>
                        <th>Tensi</th>
                        <th>Nadi</th>
                        <th>RR</th>
                        <th>Suhu</th>
                        <th>Asesmen</th>
                        <th>Posisi</th>
                        <th>Induksi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y H:i') }}</td>
                            <td>{{ $item->nm_dokter ?? $item->kd_dokter }}</td>
                            <td>{{ $item->tensi ?? '-' }}</td>
                            <td>{{ $item->nadi ?? '-' }}</td>
                            <td>{{ $item->rr ?? '-' }}</td>
                            <td>{{ $item->suhu ?? '-' }}</td>
                            <td>{{ $item->asesmen ?? '-' }}</td>
                            <td>{{ $item->posisi ?? '-' }}</td>
                            <td>{{ $item->induksi ?? '-' }}</td>
                            <td>
                                <button type="button" class="btn btn-xs btn-warning" wire:click="edit('{{ $item->tanggal }}')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button" class="btn btn-xs btn-danger" wire:click="confirmHapus('{{ $item->tanggal }}')">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-3">
                                Belum ada data penilaian pre induksi.
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
```

---

### Task 3: Integrate Component into Ralan & Ranap Examination Views

**Files:**
- Modify: `resources/views/ralan/pemeriksaan-ralan.blade.php:84`
- Modify: `resources/views/ranap/pemeriksaan-ranap.blade.php:44`

- [ ] **Step 1: Modify `resources/views/ralan/pemeriksaan-ralan.blade.php`**

Insert `<x-adminlte-card title="Penilaian Pre Induksi"...>` right after `Penilaian Pre Anestesi` card around line 84:

```blade
                <x-adminlte-card title="Penilaian Pre Induksi" icon="fas fa-syringe" theme="info" maximizable collapsible="collapsed">
                    <livewire:component.penilaian-pre-induksi :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" :key="'penilaian-pre-induksi-ralan-'.request()->get('no_rawat')" />
                </x-adminlte-card>
```

- [ ] **Step 2: Modify `resources/views/ranap/pemeriksaan-ranap.blade.php`**

Insert `<x-adminlte-card title="Penilaian Pre Induksi"...>` right after `Penilaian Pre Anestesi` card around line 44:

```blade
            <x-adminlte-card title="Penilaian Pre Induksi" icon="fas fa-syringe" theme="info" maximizable collapsible="collapsed">
                <livewire:component.penilaian-pre-induksi :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" :key="'penilaian-pre-induksi-ranap-'.request()->get('no_rawat')" />
            </x-adminlte-card>
```

---

### Task 4: Verification

- [ ] **Step 1: Check syntax of component files**

Run: `php -l app/Http/Livewire/Component/PenilaianPreInduksi.php`
Expected: `No syntax errors detected in app/Http/Livewire/Component/PenilaianPreInduksi.php`

- [ ] **Step 2: Commit implementation**

```bash
git add app/Http/Livewire/Component/PenilaianPreInduksi.php resources/views/livewire/component/penilaian-pre-induksi.blade.php resources/views/ralan/pemeriksaan-ralan.blade.php resources/views/ranap/pemeriksaan-ranap.blade.php
git commit -m "feat: add form penilaian pre induksi in ralan and ranap views"
```
