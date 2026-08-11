# Penilaian Pre Anestesi Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create a new assessment form for Pre-Anesthesia ("Penilaian Pre Anestesi") based on existing DB table `penilaian_pre_anestesi` and embed it in Outpatient (Ralan) and Inpatient (Ranap) examination pages.

**Architecture:** A reusable Livewire component (`App\Http\Livewire\Component\PenilaianPreAnestesi`) with Blade view (`resources/views/livewire/component/penilaian-pre-anestesi.blade.php`) managing CRUD operations on `penilaian_pre_anestesi`. Embeds in `pemeriksaan-ralan.blade.php` and `pemeriksaan-ranap.blade.php` inside an AdminLTE collapsible card. Auto-fills vital signs from existing examination records when creating a new assessment.

**Tech Stack:** Laravel 9, Livewire 2, AdminLTE 3, Bootstrap 4, Jantinnerezo LivewireAlert.

## Global Constraints

- Table `penilaian_pre_anestesi` composite primary key: `(no_rawat, tanggal)`.
- Doctor ID extracted from `session('username')`.
- Edit and Delete operations restricted to `$record->kd_dokter == session('username')`.

---

### Task 1: Create Livewire Component (`App\Http\Livewire\Component\PenilaianPreAnestesi`)

**Files:**
- Create: `app/Http/Livewire/Component/PenilaianPreAnestesi.php`

**Interfaces:**
- Consumes: DB table `penilaian_pre_anestesi`, `pemeriksaan_ralan`, `pemeriksaan_ranap`, `dokter`
- Produces: `Livewire\Component` class handling CRUD & auto-fill logic.

- [ ] **Step 1: Write `app/Http/Livewire/Component/PenilaianPreAnestesi.php`**

```php
<?php

namespace App\Http\Livewire\Component;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class PenilaianPreAnestesi extends Component
{
    use LivewireAlert;

    public $no_rawat;
    public $no_rm;
    public $kd_dokter;

    // Form fields
    public $tanggal_operasi;
    public $diagnosa;
    public $rencana_tindakan;

    // Vital signs
    public $tb = '';
    public $bb = '';
    public $td = '';
    public $io2 = '';
    public $nadi = '';
    public $pernapasan = '';
    public $suhu = '';

    // Physical exam
    public $fisik_cardiovasculer;
    public $fisik_paru;
    public $fisik_abdomen;
    public $fisik_extrimitas;
    public $fisik_endokrin;
    public $fisik_ginjal;
    public $fisik_obatobatan;
    public $fisik_laborat;
    public $fisik_penunjang;

    // Disease history & habits
    public $riwayat_penyakit_alergiobat;
    public $riwayat_penyakit_alergilainnya;
    public $riwayat_penyakit_terapi;
    public $riwayat_kebiasaan_merokok = 'Tidak';
    public $riwayat_kebiasaan_ket_merokok = '';
    public $riwayat_kebiasaan_alkohol = 'Tidak';
    public $riwayat_kebiasaan_ket_alkohol = '';
    public $riwayat_kebiasaan_obat = '-';
    public $riwayat_kebiasaan_ket_obat = '';

    // Medical conditions
    public $riwayat_medis_cardiovasculer;
    public $riwayat_medis_respiratory;
    public $riwayat_medis_endocrine;
    public $riwayat_medis_lainnya;

    // Assessment & Plan
    public $asa;
    public $puasa;
    public $rencana_anestesi;
    public $rencana_perawatan;
    public $catatan_khusus;

    public $data = [];
    public $modeEdit = false;
    public $tanggalEdit = null;

    protected $listeners = [
        'hapusPenilaianPreAnestesi' => 'hapus',
    ];

    public function mount($noRawat, $noRm = null): void
    {
        $this->no_rawat = $noRawat;
        $this->no_rm = $noRm;
        $this->kd_dokter = session()->get('username');
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

        $this->data = DB::table('penilaian_pre_anestesi')
            ->leftJoin('dokter', 'penilaian_pre_anestesi.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('penilaian_pre_anestesi.no_rawat', $this->no_rawat)
            ->select('penilaian_pre_anestesi.*', 'dokter.nm_dokter')
            ->orderBy('penilaian_pre_anestesi.tanggal', 'desc')
            ->get();
    }

    public function autofillVitalSigns(): void
    {
        if ($this->modeEdit || empty($this->no_rawat)) {
            return;
        }

        // Try pemeriksaan_ralan first
        $pemeriksaan = DB::table('pemeriksaan_ralan')
            ->where('no_rawat', $this->no_rawat)
            ->orderBy('tgl_perawatan', 'desc')
            ->orderBy('jam_rawat', 'desc')
            ->first();

        // If not found in ralan, try pemeriksaan_ranap
        if (! $pemeriksaan) {
            $pemeriksaan = DB::table('pemeriksaan_ranap')
                ->where('no_rawat', $this->no_rawat)
                ->orderBy('tgl_perawatan', 'desc')
                ->orderBy('jam_rawat', 'desc')
                ->first();
        }

        if ($pemeriksaan) {
            if (empty($this->tb) && isset($pemeriksaan->tinggi)) {
                $this->tb = (string) $pemeriksaan->tinggi;
            }
            if (empty($this->bb) && isset($pemeriksaan->berat)) {
                $this->bb = (string) $pemeriksaan->berat;
            }
            if (empty($this->td) && isset($pemeriksaan->tensi)) {
                $this->td = (string) $pemeriksaan->tensi;
            }
            if (empty($this->nadi) && isset($pemeriksaan->nadi)) {
                $this->nadi = (string) $pemeriksaan->nadi;
            }
            if (empty($this->pernapasan) && isset($pemeriksaan->respirasi)) {
                $this->pernapasan = (string) $pemeriksaan->respirasi;
            }
            if (empty($this->suhu) && isset($pemeriksaan->suhu_tubuh)) {
                $this->suhu = (string) $pemeriksaan->suhu_tubuh;
            }
            if (empty($this->io2) && isset($pemeriksaan->spo2)) {
                $this->io2 = (string) $pemeriksaan->spo2;
            }
        }
    }

    public function edit($tanggal): void
    {
        $data = DB::table('penilaian_pre_anestesi')
            ->where('no_rawat', $this->no_rawat)
            ->where('tanggal', $tanggal)
            ->first();

        if (! $data) {
            $this->alert('warning', 'Gagal', [
                'position' => 'center',
                'timer' => '',
                'toast' => false,
                'text' => 'Data tidak ditemukan',
                'confirmButtonText' => 'Ok',
                'showConfirmButton' => true,
            ]);
            return;
        }

        $userDokter = session()->get('username') ?? $this->kd_dokter;
        if ($data->kd_dokter != $userDokter) {
            $this->alert('warning', 'Gagal', [
                'position' => 'center',
                'timer' => '',
                'toast' => false,
                'text' => 'Anda tidak memiliki akses untuk mengubah data ini',
                'confirmButtonText' => 'Ok',
                'showConfirmButton' => true,
            ]);
            return;
        }

        $this->tanggalEdit = $data->tanggal;
        $this->tanggal_operasi = $data->tanggal_operasi ? Carbon::parse($data->tanggal_operasi)->format('Y-m-d\TH:i') : null;
        $this->diagnosa = $data->diagnosa;
        $this->rencana_tindakan = $data->rencana_tindakan;

        $this->tb = $data->tb;
        $this->bb = $data->bb;
        $this->td = $data->td;
        $this->io2 = $data->io2;
        $this->nadi = $data->nadi;
        $this->pernapasan = $data->pernapasan;
        $this->suhu = $data->suhu;

        $this->fisik_cardiovasculer = $data->fisik_cardiovasculer;
        $this->fisik_paru = $data->fisik_paru;
        $this->fisik_abdomen = $data->fisik_abdomen;
        $this->fisik_extrimitas = $data->fisik_extrimitas;
        $this->fisik_endokrin = $data->fisik_endokrin;
        $this->fisik_ginjal = $data->fisik_ginjal;
        $this->fisik_obatobatan = $data->fisik_obatobatan;
        $this->fisik_laborat = $data->fisik_laborat;
        $this->fisik_penunjang = $data->fisik_penunjang;

        $this->riwayat_penyakit_alergiobat = $data->riwayat_penyakit_alergiobat;
        $this->riwayat_penyakit_alergilainnya = $data->riwayat_penyakit_alergilainnya;
        $this->riwayat_penyakit_terapi = $data->riwayat_penyakit_terapi;
        $this->riwayat_kebiasaan_merokok = $data->riwayat_kebiasaan_merokok;
        $this->riwayat_kebiasaan_ket_merokok = $data->riwayat_kebiasaan_ket_merokok;
        $this->riwayat_kebiasaan_alkohol = $data->riwayat_kebiasaan_alkohol;
        $this->riwayat_kebiasaan_ket_alkohol = $data->riwayat_kebiasaan_ket_alkohol;
        $this->riwayat_kebiasaan_obat = $data->riwayat_kebiasaan_obat;
        $this->riwayat_kebiasaan_ket_obat = $data->riwayat_kebiasaan_ket_obat;

        $this->riwayat_medis_cardiovasculer = $data->riwayat_medis_cardiovasculer;
        $this->riwayat_medis_respiratory = $data->riwayat_medis_respiratory;
        $this->riwayat_medis_endocrine = $data->riwayat_medis_endocrine;
        $this->riwayat_medis_lainnya = $data->riwayat_medis_lainnya;

        $this->asa = $data->asa;
        $this->puasa = $data->puasa ? Carbon::parse($data->puasa)->format('Y-m-d\TH:i') : null;
        $this->rencana_anestesi = $data->rencana_anestesi;
        $this->rencana_perawatan = $data->rencana_perawatan;
        $this->catatan_khusus = $data->catatan_khusus;

        $this->modeEdit = true;
    }

    public function resetInput(): void
    {
        $this->reset([
            'tanggal_operasi',
            'diagnosa',
            'rencana_tindakan',
            'tb',
            'bb',
            'td',
            'io2',
            'nadi',
            'pernapasan',
            'suhu',
            'fisik_cardiovasculer',
            'fisik_paru',
            'fisik_abdomen',
            'fisik_extrimitas',
            'fisik_endokrin',
            'fisik_ginjal',
            'fisik_obatobatan',
            'fisik_laborat',
            'fisik_penunjang',
            'riwayat_penyakit_alergiobat',
            'riwayat_penyakit_alergilainnya',
            'riwayat_penyakit_terapi',
            'riwayat_kebiasaan_merokok',
            'riwayat_kebiasaan_ket_merokok',
            'riwayat_kebiasaan_alkohol',
            'riwayat_kebiasaan_ket_alkohol',
            'riwayat_kebiasaan_obat',
            'riwayat_kebiasaan_ket_obat',
            'riwayat_medis_cardiovasculer',
            'riwayat_medis_respiratory',
            'riwayat_medis_endocrine',
            'riwayat_medis_lainnya',
            'asa',
            'puasa',
            'rencana_anestesi',
            'rencana_perawatan',
            'catatan_khusus',
        ]);
        $this->riwayat_kebiasaan_merokok = 'Tidak';
        $this->riwayat_kebiasaan_ket_merokok = '';
        $this->riwayat_kebiasaan_alkohol = 'Tidak';
        $this->riwayat_kebiasaan_ket_alkohol = '';
        $this->riwayat_kebiasaan_obat = '-';
        $this->riwayat_kebiasaan_ket_obat = '';
        $this->modeEdit = false;
        $this->tanggalEdit = null;
        $this->resetErrorBag();

        $this->autofillVitalSigns();
    }

    public function simpan(): void
    {
        $this->validate([
            'tanggal_operasi' => 'nullable|date',
            'diagnosa' => 'nullable|string|max:100',
            'rencana_tindakan' => 'nullable|string|max:100',
            'tb' => 'nullable|string|max:5',
            'bb' => 'nullable|string|max:5',
            'td' => 'nullable|string|max:8',
            'io2' => 'nullable|string|max:5',
            'nadi' => 'nullable|string|max:5',
            'pernapasan' => 'nullable|string|max:5',
            'suhu' => 'nullable|string|max:5',
            'fisik_cardiovasculer' => 'nullable|string|max:100',
            'fisik_paru' => 'nullable|string|max:100',
            'fisik_abdomen' => 'nullable|string|max:100',
            'fisik_extrimitas' => 'nullable|string|max:100',
            'fisik_endokrin' => 'nullable|string|max:100',
            'fisik_ginjal' => 'nullable|string|max:100',
            'fisik_obatobatan' => 'nullable|string|max:100',
            'fisik_laborat' => 'nullable|string|max:100',
            'fisik_penunjang' => 'nullable|string|max:100',
            'riwayat_penyakit_alergiobat' => 'nullable|string|max:50',
            'riwayat_penyakit_alergilainnya' => 'nullable|string|max:50',
            'riwayat_penyakit_terapi' => 'nullable|string|max:100',
            'riwayat_kebiasaan_merokok' => 'required|in:Tidak,Ya',
            'riwayat_kebiasaan_ket_merokok' => 'nullable|string|max:5',
            'riwayat_kebiasaan_alkohol' => 'required|in:Tidak,Ya',
            'riwayat_kebiasaan_ket_alkohol' => 'nullable|string|max:5',
            'riwayat_kebiasaan_obat' => 'required|in:-,Obat Obatan,Vitamin,Jamu Jamuan',
            'riwayat_kebiasaan_ket_obat' => 'nullable|string|max:100',
            'riwayat_medis_cardiovasculer' => 'nullable|string|max:100',
            'riwayat_medis_respiratory' => 'nullable|string|max:100',
            'riwayat_medis_endocrine' => 'nullable|string|max:100',
            'riwayat_medis_lainnya' => 'nullable|string|max:100',
            'asa' => 'nullable|in:1,2,3,4,5,E',
            'puasa' => 'nullable|date',
            'rencana_anestesi' => 'nullable|in:GA,RA Spinal,RA Epidural,RA Combined,Blok Syaraf',
            'rencana_perawatan' => 'nullable|string|max:40',
            'catatan_khusus' => 'nullable|string|max:100',
        ]);

        $userDokter = session()->get('username') ?? $this->kd_dokter;

        // Guard against rapid duplicate submission (< 3s)
        $recentDuplicate = DB::table('penilaian_pre_anestesi')
            ->where('no_rawat', $this->no_rawat)
            ->where('kd_dokter', $userDokter)
            ->where('tanggal', '>=', Carbon::now()->subSeconds(3)->format('Y-m-d H:i:s'))
            ->first();

        if ($recentDuplicate) {
            $this->resetInput();
            $this->getData();
            return;
        }

        try {
            DB::table('penilaian_pre_anestesi')->insert([
                'no_rawat' => $this->no_rawat,
                'tanggal' => Carbon::now()->format('Y-m-d H:i:s'),
                'kd_dokter' => $userDokter,
                'tanggal_operasi' => $this->tanggal_operasi ? Carbon::parse($this->tanggal_operasi)->format('Y-m-d H:i:s') : null,
                'diagnosa' => $this->diagnosa,
                'rencana_tindakan' => $this->rencana_tindakan,
                'tb' => $this->tb ?? '',
                'bb' => $this->bb ?? '',
                'td' => $this->td ?? '',
                'io2' => $this->io2 ?? '',
                'nadi' => $this->nadi ?? '',
                'pernapasan' => $this->pernapasan ?? '',
                'suhu' => $this->suhu ?? '',
                'fisik_cardiovasculer' => $this->fisik_cardiovasculer,
                'fisik_paru' => $this->fisik_paru,
                'fisik_abdomen' => $this->fisik_abdomen,
                'fisik_extrimitas' => $this->fisik_extrimitas,
                'fisik_endokrin' => $this->fisik_endokrin,
                'fisik_ginjal' => $this->fisik_ginjal,
                'fisik_obatobatan' => $this->fisik_obatobatan,
                'fisik_laborat' => $this->fisik_laborat,
                'fisik_penunjang' => $this->fisik_penunjang,
                'riwayat_penyakit_alergiobat' => $this->riwayat_penyakit_alergiobat,
                'riwayat_penyakit_alergilainnya' => $this->riwayat_penyakit_alergilainnya,
                'riwayat_penyakit_terapi' => $this->riwayat_penyakit_terapi,
                'riwayat_kebiasaan_merokok' => $this->riwayat_kebiasaan_merokok ?? 'Tidak',
                'riwayat_kebiasaan_ket_merokok' => $this->riwayat_kebiasaan_ket_merokok ?? '',
                'riwayat_kebiasaan_alkohol' => $this->riwayat_kebiasaan_alkohol ?? 'Tidak',
                'riwayat_kebiasaan_ket_alkohol' => $this->riwayat_kebiasaan_ket_alkohol ?? '',
                'riwayat_kebiasaan_obat' => $this->riwayat_kebiasaan_obat ?? '-',
                'riwayat_kebiasaan_ket_obat' => $this->riwayat_kebiasaan_ket_obat ?? '',
                'riwayat_medis_cardiovasculer' => $this->riwayat_medis_cardiovasculer,
                'riwayat_medis_respiratory' => $this->riwayat_medis_respiratory,
                'riwayat_medis_endocrine' => $this->riwayat_medis_endocrine,
                'riwayat_medis_lainnya' => $this->riwayat_medis_lainnya,
                'asa' => $this->asa,
                'puasa' => $this->puasa ? Carbon::parse($this->puasa)->format('Y-m-d H:i:s') : null,
                'rencana_anestesi' => $this->rencana_anestesi,
                'rencana_perawatan' => $this->rencana_perawatan,
                'catatan_khusus' => $this->catatan_khusus,
            ]);

            $this->resetInput();
            $this->getData();
            $this->alert('success', 'Berhasil simpan penilaian pre anestesi');
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal', [
                'position' => 'center',
                'timer' => '',
                'toast' => false,
                'text' => App::environment('local') ? $e->getMessage() : 'Terjadi kesalahan saat menyimpan data',
                'confirmButtonText' => 'Ok',
                'showConfirmButton' => true,
            ]);
        }
    }

    public function update(): void
    {
        if (empty($this->tanggalEdit)) {
            $this->alert('warning', 'Gagal', [
                'position' => 'center',
                'timer' => '',
                'toast' => false,
                'text' => 'Data yang diubah tidak ditemukan',
                'confirmButtonText' => 'Ok',
                'showConfirmButton' => true,
            ]);
            return;
        }

        $this->validate([
            'tanggal_operasi' => 'nullable|date',
            'diagnosa' => 'nullable|string|max:100',
            'rencana_tindakan' => 'nullable|string|max:100',
            'tb' => 'nullable|string|max:5',
            'bb' => 'nullable|string|max:5',
            'td' => 'nullable|string|max:8',
            'io2' => 'nullable|string|max:5',
            'nadi' => 'nullable|string|max:5',
            'pernapasan' => 'nullable|string|max:5',
            'suhu' => 'nullable|string|max:5',
            'fisik_cardiovasculer' => 'nullable|string|max:100',
            'fisik_paru' => 'nullable|string|max:100',
            'fisik_abdomen' => 'nullable|string|max:100',
            'fisik_extrimitas' => 'nullable|string|max:100',
            'fisik_endokrin' => 'nullable|string|max:100',
            'fisik_ginjal' => 'nullable|string|max:100',
            'fisik_obatobatan' => 'nullable|string|max:100',
            'fisik_laborat' => 'nullable|string|max:100',
            'fisik_penunjang' => 'nullable|string|max:100',
            'riwayat_penyakit_alergiobat' => 'nullable|string|max:50',
            'riwayat_penyakit_alergilainnya' => 'nullable|string|max:50',
            'riwayat_penyakit_terapi' => 'nullable|string|max:100',
            'riwayat_kebiasaan_merokok' => 'required|in:Tidak,Ya',
            'riwayat_kebiasaan_ket_merokok' => 'nullable|string|max:5',
            'riwayat_kebiasaan_alkohol' => 'required|in:Tidak,Ya',
            'riwayat_kebiasaan_ket_alkohol' => 'nullable|string|max:5',
            'riwayat_kebiasaan_obat' => 'required|in:-,Obat Obatan,Vitamin,Jamu Jamuan',
            'riwayat_kebiasaan_ket_obat' => 'nullable|string|max:100',
            'riwayat_medis_cardiovasculer' => 'nullable|string|max:100',
            'riwayat_medis_respiratory' => 'nullable|string|max:100',
            'riwayat_medis_endocrine' => 'nullable|string|max:100',
            'riwayat_medis_lainnya' => 'nullable|string|max:100',
            'asa' => 'nullable|in:1,2,3,4,5,E',
            'puasa' => 'nullable|date',
            'rencana_anestesi' => 'nullable|in:GA,RA Spinal,RA Epidural,RA Combined,Blok Syaraf',
            'rencana_perawatan' => 'nullable|string|max:40',
            'catatan_khusus' => 'nullable|string|max:100',
        ]);

        try {
            DB::table('penilaian_pre_anestesi')
                ->where('no_rawat', $this->no_rawat)
                ->where('tanggal', $this->tanggalEdit)
                ->update([
                    'tanggal_operasi' => $this->tanggal_operasi ? Carbon::parse($this->tanggal_operasi)->format('Y-m-d H:i:s') : null,
                    'diagnosa' => $this->diagnosa,
                    'rencana_tindakan' => $this->rencana_tindakan,
                    'tb' => $this->tb ?? '',
                    'bb' => $this->bb ?? '',
                    'td' => $this->td ?? '',
                    'io2' => $this->io2 ?? '',
                    'nadi' => $this->nadi ?? '',
                    'pernapasan' => $this->pernapasan ?? '',
                    'suhu' => $this->suhu ?? '',
                    'fisik_cardiovasculer' => $this->fisik_cardiovasculer,
                    'fisik_paru' => $this->fisik_paru,
                    'fisik_abdomen' => $this->fisik_abdomen,
                    'fisik_extrimitas' => $this->fisik_extrimitas,
                    'fisik_endokrin' => $this->fisik_endokrin,
                    'fisik_ginjal' => $this->fisik_ginjal,
                    'fisik_obatobatan' => $this->fisik_obatobatan,
                    'fisik_laborat' => $this->fisik_laborat,
                    'fisik_penunjang' => $this->fisik_penunjang,
                    'riwayat_penyakit_alergiobat' => $this->riwayat_penyakit_alergiobat,
                    'riwayat_penyakit_alergilainnya' => $this->riwayat_penyakit_alergilainnya,
                    'riwayat_penyakit_terapi' => $this->riwayat_penyakit_terapi,
                    'riwayat_kebiasaan_merokok' => $this->riwayat_kebiasaan_merokok ?? 'Tidak',
                    'riwayat_kebiasaan_ket_merokok' => $this->riwayat_kebiasaan_ket_merokok ?? '',
                    'riwayat_kebiasaan_alkohol' => $this->riwayat_kebiasaan_alkohol ?? 'Tidak',
                    'riwayat_kebiasaan_ket_alkohol' => $this->riwayat_kebiasaan_ket_alkohol ?? '',
                    'riwayat_kebiasaan_obat' => $this->riwayat_kebiasaan_obat ?? '-',
                    'riwayat_kebiasaan_ket_obat' => $this->riwayat_kebiasaan_ket_obat ?? '',
                    'riwayat_medis_cardiovasculer' => $this->riwayat_medis_cardiovasculer,
                    'riwayat_medis_respiratory' => $this->riwayat_medis_respiratory,
                    'riwayat_medis_endocrine' => $this->riwayat_medis_endocrine,
                    'riwayat_medis_lainnya' => $this->riwayat_medis_lainnya,
                    'asa' => $this->asa,
                    'puasa' => $this->puasa ? Carbon::parse($this->puasa)->format('Y-m-d H:i:s') : null,
                    'rencana_anestesi' => $this->rencana_anestesi,
                    'rencana_perawatan' => $this->rencana_perawatan,
                    'catatan_khusus' => $this->catatan_khusus,
                ]);

            $this->resetInput();
            $this->getData();
            $this->alert('success', 'Berhasil ubah penilaian pre anestesi');
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal', [
                'position' => 'center',
                'timer' => '',
                'toast' => false,
                'text' => App::environment('local') ? $e->getMessage() : 'Terjadi kesalahan saat mengubah data',
                'confirmButtonText' => 'Ok',
                'showConfirmButton' => true,
            ]);
        }
    }

    public function confirmHapus($tanggal): void
    {
        $data = DB::table('penilaian_pre_anestesi')
            ->where('no_rawat', $this->no_rawat)
            ->where('tanggal', $tanggal)
            ->first();

        if (! $data) {
            $this->alert('warning', 'Gagal', [
                'position' => 'center',
                'timer' => '',
                'toast' => false,
                'text' => 'Data tidak ditemukan',
                'confirmButtonText' => 'Ok',
                'showConfirmButton' => true,
            ]);
            return;
        }

        $userDokter = session()->get('username') ?? $this->kd_dokter;
        if ($data->kd_dokter != $userDokter) {
            $this->alert('warning', 'Gagal', [
                'position' => 'center',
                'timer' => '',
                'toast' => false,
                'text' => 'Anda tidak memiliki akses untuk menghapus data ini',
                'confirmButtonText' => 'Ok',
                'showConfirmButton' => true,
            ]);
            return;
        }

        $this->tanggalEdit = $tanggal;
        $this->confirm('Apakah anda yakin ingin menghapus data ini?', [
            'onConfirmed' => 'hapusPenilaianPreAnestesi',
            'cancelButtonText' => 'Batal',
        ]);
    }

    public function hapus(): void
    {
        if (! $this->tanggalEdit) {
            return;
        }

        try {
            $data = DB::table('penilaian_pre_anestesi')
                ->where('no_rawat', $this->no_rawat)
                ->where('tanggal', $this->tanggalEdit)
                ->first();

            $userDokter = session()->get('username') ?? $this->kd_dokter;
            if (! $data || $data->kd_dokter != $userDokter) {
                $this->alert('warning', 'Gagal', [
                    'position' => 'center',
                    'timer' => '',
                    'toast' => false,
                    'text' => 'Anda tidak memiliki akses untuk menghapus data ini',
                    'confirmButtonText' => 'Ok',
                    'showConfirmButton' => true,
                ]);
                return;
            }

            DB::table('penilaian_pre_anestesi')
                ->where('no_rawat', $this->no_rawat)
                ->where('tanggal', $this->tanggalEdit)
                ->delete();

            $this->resetInput();
            $this->getData();
            $this->alert('success', 'Berhasil hapus penilaian pre anestesi');
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal', [
                'position' => 'center',
                'timer' => '',
                'toast' => false,
                'text' => App::environment('local') ? $e->getMessage() : 'Terjadi kesalahan saat menghapus data',
                'confirmButtonText' => 'Ok',
                'showConfirmButton' => true,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.component.penilaian-pre-anestesi');
    }
}
```

- [ ] **Step 2: Run syntax check**

Run: `php -l app/Http/Livewire/Component/PenilaianPreAnestesi.php`  
Expected: `No syntax errors detected in app/Http/Livewire/Component/PenilaianPreAnestesi.php`

- [ ] **Step 3: Commit Task 1**

```bash
git add app/Http/Livewire/Component/PenilaianPreAnestesi.php
git commit -m "feat(penilaian-pre-anestesi): create Livewire component for pre-anesthesia assessment"
```

---

### Task 2: Create Blade View (`resources/views/livewire/component/penilaian-pre-anestesi.blade.php`)

**Files:**
- Create: `resources/views/livewire/component/penilaian-pre-anestesi.blade.php`

**Interfaces:**
- Consumes: Livewire component properties (`$modeEdit`, `$data`, form fields)
- Produces: HTML layout with form inputs & history table formatted in AdminLTE/Bootstrap card styles.

- [ ] **Step 1: Write `resources/views/livewire/component/penilaian-pre-anestesi.blade.php`**

```blade
<div>
    <form wire:submit.prevent="{{ $modeEdit ? 'update' : 'simpan' }}">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-1"></i>
                    {{ $modeEdit ? 'Edit Penilaian Pre Anestesi' : 'Input Penilaian Pre Anestesi' }}
                </h3>
            </div>
            <div class="card-body">
                <!-- Section 1: Informasi Operasi & Diagnosa -->
                <h5 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-notes-medical mr-1"></i> Informasi Operasi & Diagnosa</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tanggal Operasi</label>
                            <input type="datetime-local" class="form-control @error('tanggal_operasi') is-invalid @enderror" wire:model.defer="tanggal_operasi">
                            @error('tanggal_operasi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Diagnosa</label>
                            <input type="text" class="form-control @error('diagnosa') is-invalid @enderror" wire:model.defer="diagnosa" placeholder="Diagnosa pre anestesi" maxlength="100">
                            @error('diagnosa') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Rencana Tindakan</label>
                            <input type="text" class="form-control @error('rencana_tindakan') is-invalid @enderror" wire:model.defer="rencana_tindakan" placeholder="Rencana tindakan" maxlength="100">
                            @error('rencana_tindakan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Vital Signs (Tanda Vital) -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-heartbeat mr-1"></i> Tanda Vital</h5>
                <div class="row">
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>TB (cm)</label>
                            <input type="text" class="form-control @error('tb') is-invalid @enderror" wire:model.defer="tb" placeholder="cm" maxlength="5">
                            @error('tb') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>BB (kg)</label>
                            <input type="text" class="form-control @error('bb') is-invalid @enderror" wire:model.defer="bb" placeholder="kg" maxlength="5">
                            @error('bb') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>TD (mmHg)</label>
                            <input type="text" class="form-control @error('td') is-invalid @enderror" wire:model.defer="td" placeholder="120/80" maxlength="8">
                            @error('td') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>SpO2 / IO2 (%)</label>
                            <input type="text" class="form-control @error('io2') is-invalid @enderror" wire:model.defer="io2" placeholder="%" maxlength="5">
                            @error('io2') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="form-group">
                            <label>Nadi (x/mnt)</label>
                            <input type="text" class="form-control @error('nadi') is-invalid @enderror" wire:model.defer="nadi" placeholder="x/menit" maxlength="5">
                            @error('nadi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="form-group">
                            <label>Pernapasan (x/mnt)</label>
                            <input type="text" class="form-control @error('pernapasan') is-invalid @enderror" wire:model.defer="pernapasan" placeholder="x/menit" maxlength="5">
                            @error('pernapasan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>Suhu (°C)</label>
                            <input type="text" class="form-control @error('suhu') is-invalid @enderror" wire:model.defer="suhu" placeholder="°C" maxlength="5">
                            @error('suhu') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 3: Pemeriksaan Fisik -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-stethoscope mr-1"></i> Pemeriksaan Fisik</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Kardiovaskuler</label>
                            <input type="text" class="form-control @error('fisik_cardiovasculer') is-invalid @enderror" wire:model.defer="fisik_cardiovasculer" maxlength="100">
                            @error('fisik_cardiovasculer') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Paru</label>
                            <input type="text" class="form-control @error('fisik_paru') is-invalid @enderror" wire:model.defer="fisik_paru" maxlength="100">
                            @error('fisik_paru') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Abdomen</label>
                            <input type="text" class="form-control @error('fisik_abdomen') is-invalid @enderror" wire:model.defer="fisik_abdomen" maxlength="100">
                            @error('fisik_abdomen') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Ekstrimitas</label>
                            <input type="text" class="form-control @error('fisik_extrimitas') is-invalid @enderror" wire:model.defer="fisik_extrimitas" maxlength="100">
                            @error('fisik_extrimitas') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Endokrin</label>
                            <input type="text" class="form-control @error('fisik_endokrin') is-invalid @enderror" wire:model.defer="fisik_endokrin" maxlength="100">
                            @error('fisik_endokrin') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Ginjal</label>
                            <input type="text" class="form-control @error('fisik_ginjal') is-invalid @enderror" wire:model.defer="fisik_ginjal" maxlength="100">
                            @error('fisik_ginjal') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Obat-obatan</label>
                            <input type="text" class="form-control @error('fisik_obatobatan') is-invalid @enderror" wire:model.defer="fisik_obatobatan" maxlength="100">
                            @error('fisik_obatobatan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Laboratorium</label>
                            <input type="text" class="form-control @error('fisik_laborat') is-invalid @enderror" wire:model.defer="fisik_laborat" maxlength="100">
                            @error('fisik_laborat') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Penunjang Lainnya</label>
                            <input type="text" class="form-control @error('fisik_penunjang') is-invalid @enderror" wire:model.defer="fisik_penunjang" maxlength="100">
                            @error('fisik_penunjang') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 4: Riwayat Penyakit & Kebiasaan -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-history mr-1"></i> Riwayat Penyakit & Kebiasaan</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Alergi Obat</label>
                            <input type="text" class="form-control @error('riwayat_penyakit_alergiobat') is-invalid @enderror" wire:model.defer="riwayat_penyakit_alergiobat" maxlength="50">
                            @error('riwayat_penyakit_alergiobat') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Alergi Lainnya</label>
                            <input type="text" class="form-control @error('riwayat_penyakit_alergilainnya') is-invalid @enderror" wire:model.defer="riwayat_penyakit_alergilainnya" maxlength="50">
                            @error('riwayat_penyakit_alergilainnya') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Terapi Saat Ini</label>
                            <input type="text" class="form-control @error('riwayat_penyakit_terapi') is-invalid @enderror" wire:model.defer="riwayat_penyakit_terapi" maxlength="100">
                            @error('riwayat_penyakit_terapi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Merokok -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Merokok</label>
                            <select class="form-control @error('riwayat_kebiasaan_merokok') is-invalid @enderror" wire:model.defer="riwayat_kebiasaan_merokok">
                                <option value="Tidak">Tidak</option>
                                <option value="Ya">Ya</option>
                            </select>
                            @error('riwayat_kebiasaan_merokok') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Ket. Merokok</label>
                            <input type="text" class="form-control @error('riwayat_kebiasaan_ket_merokok') is-invalid @enderror" wire:model.defer="riwayat_kebiasaan_ket_merokok" placeholder="Ket" maxlength="5">
                            @error('riwayat_kebiasaan_ket_merokok') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Alkohol -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Alkohol</label>
                            <select class="form-control @error('riwayat_kebiasaan_alkohol') is-invalid @enderror" wire:model.defer="riwayat_kebiasaan_alkohol">
                                <option value="Tidak">Tidak</option>
                                <option value="Ya">Ya</option>
                            </select>
                            @error('riwayat_kebiasaan_alkohol') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Ket. Alkohol</label>
                            <input type="text" class="form-control @error('riwayat_kebiasaan_ket_alkohol') is-invalid @enderror" wire:model.defer="riwayat_kebiasaan_ket_alkohol" placeholder="Ket" maxlength="5">
                            @error('riwayat_kebiasaan_ket_alkohol') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Kebiasaan Obat -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Konsumsi Obat</label>
                            <select class="form-control @error('riwayat_kebiasaan_obat') is-invalid @enderror" wire:model.defer="riwayat_kebiasaan_obat">
                                <option value="-">-</option>
                                <option value="Obat Obatan">Obat Obatan</option>
                                <option value="Vitamin">Vitamin</option>
                                <option value="Jamu Jamuan">Jamu Jamuan</option>
                            </select>
                            @error('riwayat_kebiasaan_obat') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Ket. Obat</label>
                            <input type="text" class="form-control @error('riwayat_kebiasaan_ket_obat') is-invalid @enderror" wire:model.defer="riwayat_kebiasaan_ket_obat" placeholder="Ket obat" maxlength="100">
                            @error('riwayat_kebiasaan_ket_obat') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 5: Riwayat Medis Spesifik -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-user-md mr-1"></i> Riwayat Medis</h5>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Kardiovaskuler</label>
                            <input type="text" class="form-control @error('riwayat_medis_cardiovasculer') is-invalid @enderror" wire:model.defer="riwayat_medis_cardiovasculer" maxlength="100">
                            @error('riwayat_medis_cardiovasculer') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Respiratory</label>
                            <input type="text" class="form-control @error('riwayat_medis_respiratory') is-invalid @enderror" wire:model.defer="riwayat_medis_respiratory" maxlength="100">
                            @error('riwayat_medis_respiratory') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Endocrine</label>
                            <input type="text" class="form-control @error('riwayat_medis_endocrine') is-invalid @enderror" wire:model.defer="riwayat_medis_endocrine" maxlength="100">
                            @error('riwayat_medis_endocrine') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Lainnya</label>
                            <input type="text" class="form-control @error('riwayat_medis_lainnya') is-invalid @enderror" wire:model.defer="riwayat_medis_lainnya" maxlength="100">
                            @error('riwayat_medis_lainnya') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 6: Rencana Anestesi & Penilaian ASA -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-syringe mr-1"></i> Rencana Anestesi & Penilaian ASA</h5>
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>ASA Score</label>
                            <select class="form-control @error('asa') is-invalid @enderror" wire:model.defer="asa">
                                <option value="">-- Pilih --</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="E">E</option>
                            </select>
                            @error('asa') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Waktu Puasa</label>
                            <input type="datetime-local" class="form-control @error('puasa') is-invalid @enderror" wire:model.defer="puasa">
                            @error('puasa') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Rencana Anestesi</label>
                            <select class="form-control @error('rencana_anestesi') is-invalid @enderror" wire:model.defer="rencana_anestesi">
                                <option value="">-- Pilih --</option>
                                <option value="GA">GA</option>
                                <option value="RA Spinal">RA Spinal</option>
                                <option value="RA Epidural">RA Epidural</option>
                                <option value="RA Combined">RA Combined</option>
                                <option value="Blok Syaraf">Blok Syaraf</option>
                            </select>
                            @error('rencana_anestesi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Rencana Perawatan</label>
                            <input type="text" class="form-control @error('rencana_perawatan') is-invalid @enderror" wire:model.defer="rencana_perawatan" placeholder="Rencana perawatan" maxlength="40">
                            @error('rencana_perawatan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Catatan Khusus</label>
                            <input type="text" class="form-control @error('catatan_khusus') is-invalid @enderror" wire:model.defer="catatan_khusus" placeholder="Catatan khusus pre anestesi" maxlength="100">
                            @error('catatan_khusus') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-right">
                @if($modeEdit)
                    <button type="button" class="btn btn-secondary mr-1" wire:click="resetInput">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save mr-1"></i> Update Penilaian</button>
                @else
                    <button type="button" class="btn btn-default mr-1" wire:click="resetInput">Reset</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Penilaian</button>
                @endif
            </div>
        </div>
    </form>

    <!-- Table Riwayat Penilaian Pre Anestesi -->
    <div class="card card-outline card-info mt-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history mr-1"></i>
                Riwayat Penilaian Pre Anestesi
            </h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 150px;">Tanggal Input</th>
                        <th>Dokter Penilai</th>
                        <th>Tgl Operasi</th>
                        <th>Diagnosa & Rencana</th>
                        <th>ASA</th>
                        <th>Anestesi</th>
                        <th style="width: 120px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y H:i') }}</td>
                            <td>{{ $item->nm_dokter ?? $item->kd_dokter }}</td>
                            <td>{{ $item->tanggal_operasi ? \Carbon\Carbon::parse($item->tanggal_operasi)->format('d-m-Y H:i') : '-' }}</td>
                            <td>
                                <strong>Diag:</strong> {{ $item->diagnosa ?? '-' }}<br>
                                <small class="text-muted"><strong>Tindakan:</strong> {{ $item->rencana_tindakan ?? '-' }}</small>
                            </td>
                            <td><span class="badge badge-info">{{ $item->asa ?? '-' }}</span></td>
                            <td>{{ $item->rencana_anestesi ?? '-' }}</td>
                            <td class="text-center">
                                @if($item->kd_dokter == session('username'))
                                    <button class="btn btn-xs btn-warning" wire:click="edit('{{ $item->tanggal }}')" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-xs btn-danger" wire:click="confirmHapus('{{ $item->tanggal }}')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @else
                                    <span class="badge badge-secondary" title="Tidak memiliki akses edit/hapus">Read Only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada data penilaian pre anestesi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
```

- [ ] **Step 2: Commit Task 2**

```bash
git add resources/views/livewire/component/penilaian-pre-anestesi.blade.php
git commit -m "feat(penilaian-pre-anestesi): create Blade view for pre-anesthesia assessment form"
```

---

### Task 3: Embed Component into Ralan and Ranap Examination Views

**Files:**
- Modify: `resources/views/ralan/pemeriksaan-ralan.blade.php`
- Modify: `resources/views/ranap/pemeriksaan-ranap.blade.php`

**Interfaces:**
- Consumes: Livewire tag `<livewire:component.penilaian-pre-anestesi :no-rawat="..." :no-rm="..." />`
- Produces: Collapsible AdminLTE card on both Ralan and Ranap pages.

- [ ] **Step 1: Embed in `resources/views/ralan/pemeriksaan-ralan.blade.php`**

Add after `penilaian-pre-operasi` card (around line 81):

```blade
                <x-adminlte-card title="Penilaian Pre Anestesi" icon="fas fa-syringe" theme="info" maximizable collapsible="collapsed">
                    <livewire:component.penilaian-pre-anestesi :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" :key="'penilaian-pre-anestesi-ralan-'.request()->get('no_rawat')" />
                </x-adminlte-card>
```

- [ ] **Step 2: Embed in `resources/views/ranap/pemeriksaan-ranap.blade.php`**

Add after `penilaian-pre-operasi` card (around line 41):

```blade
            <x-adminlte-card title="Penilaian Pre Anestesi" icon="fas fa-syringe" theme="info" maximizable collapsible="collapsed">
                <livewire:component.penilaian-pre-anestesi :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" :key="'penilaian-pre-anestesi-ranap-'.request()->get('no_rawat')" />
            </x-adminlte-card>
```

- [ ] **Step 3: Commit Task 3**

```bash
git add resources/views/ralan/pemeriksaan-ralan.blade.php resources/views/ranap/pemeriksaan-ranap.blade.php
git commit -m "feat(penilaian-pre-anestesi): embed component in ralan and ranap examination views"
```

---

### Task 4: Verification and Cache Testing

- [ ] **Step 1: Check PHP syntax for all modified files**

Run: `php -l app/Http/Livewire/Component/PenilaianPreAnestesi.php`  
Expected: `No syntax errors detected`

- [ ] **Step 2: Run view compilation test**

Run: `php artisan view:cache`  
Expected: `Blade templates cached successfully!` followed by `php artisan view:clear`

- [ ] **Step 3: Commit final verification state if needed**
