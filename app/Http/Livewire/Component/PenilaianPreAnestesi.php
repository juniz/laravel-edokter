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
