<?php

namespace App\Http\Livewire\Component\AwalIgd;

use Livewire\Component;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class Form extends Component
{
    use LivewireAlert;
    public $no_rawat;
    public $tanggal;
    public $anamnesis;
    public $hubungan;
    public $keluhan_utama;
    public $rps;
    public $rpk;
    public $rpd;
    public $rpo;
    public $alergi;
    public $keadaan_umum;
    public $kesadaran;
    public $gcs;
    public $tb;
    public $bb;
    public $td;
    public $nadi;
    public $rr;
    public $suhu;
    public $spo2;
    public $kepala;
    public $mata;
    public $thoraks;
    public $abdomen;
    public $gigi;
    public $genital;
    public $leher;
    public $ekstremitas;
    public $ket_fisik;
    public $ket_lokalis;
    public $ekg;
    public $radiologi;
    public $laborat;
    public $diagnosis;
    public $tatalaksana;
    public $listSoap = [];
    public $dataPerawat = null;
    public $dataRiwayat = [];

    protected $listeners = ['hapusMedisIgd' => 'hapus'];

    public function openModalSoap()
    {
        if (!$this->no_rawat) {
            $this->alert('warning', 'Nomor rawat belum dipilih');
            return;
        }

        $this->listSoap = DB::table('pemeriksaan_ralan')
            ->leftJoin('pegawai', 'pemeriksaan_ralan.nip', '=', 'pegawai.nik')
            ->where('pemeriksaan_ralan.no_rawat', $this->no_rawat)
            ->select('pemeriksaan_ralan.*', 'pegawai.nama as nama_petugas')
            ->orderByDesc('pemeriksaan_ralan.tgl_perawatan')
            ->orderByDesc('pemeriksaan_ralan.jam_rawat')
            ->get()
            ->toArray();

        if (empty($this->listSoap)) {
            $this->alert('info', 'Belum ada data pemeriksaan SOAP ralan pada nomor rawat ini');
            return;
        }

        $this->dispatchBrowserEvent('open-modal-soap-igd');
    }

    public function pilihSoap($tgl_perawatan, $jam_rawat)
    {
        $soap = DB::table('pemeriksaan_ralan')
            ->where('no_rawat', $this->no_rawat)
            ->where('tgl_perawatan', $tgl_perawatan)
            ->where('jam_rawat', $jam_rawat)
            ->first();

        if ($soap) {
            if (!empty($soap->keluhan)) $this->keluhan_utama = $soap->keluhan;
            if (!empty($soap->alergi)) $this->alergi = $soap->alergi;
            if (!empty($soap->kesadaran)) $this->kesadaran = $soap->kesadaran;
            if (!empty($soap->gcs)) $this->gcs = $soap->gcs;
            if (!empty($soap->tinggi)) $this->tb = $soap->tinggi;
            if (!empty($soap->berat)) $this->bb = $soap->berat;
            if (!empty($soap->tensi)) $this->td = $soap->tensi;
            if (!empty($soap->nadi)) $this->nadi = $soap->nadi;
            if (!empty($soap->respirasi)) $this->rr = $soap->respirasi;
            if (!empty($soap->suhu_tubuh)) $this->suhu = $soap->suhu_tubuh;
            if (!empty($soap->spo2)) $this->spo2 = $soap->spo2;
            if (!empty($soap->pemeriksaan)) $this->ket_fisik = $soap->pemeriksaan;

            $this->dispatchBrowserEvent('close-modal-soap-igd');
            $this->alert('success', 'Data SOAP berhasil disalin ke form Penilaian Awal Medis IGD');
        }
    }

    public function salinDariPerawat()
    {
        if (empty($this->dataPerawat)) {
            $this->alert('warning', 'Data asesmen keperawatan tidak ditemukan');
            return;
        }

        $keluhan = data_get($this->dataPerawat, 'keluhan_utama');
        $rpd = data_get($this->dataPerawat, 'rpd');
        $rpo = data_get($this->dataPerawat, 'rpo');

        if (!empty($keluhan)) {
            $this->keluhan_utama = $keluhan;
        }
        if (!empty($rpd)) {
            $this->rpd = $rpd;
        }
        if (!empty($rpo)) {
            $this->rpo = $rpo;
        }

        $this->alert('success', 'Data keluhan & riwayat berhasil disalin dari perawat');
    }

    public function loadRiwayat()
    {
        if (!$this->no_rawat) {
            $this->dataRiwayat = [];
            return;
        }

        $noRm = DB::table('reg_periksa')->where('no_rawat', $this->no_rawat)->value('no_rkm_medis');
        if (!$noRm) {
            $this->dataRiwayat = [];
            return;
        }

        $this->dataRiwayat = DB::table('penilaian_medis_igd')
            ->join('reg_periksa', 'penilaian_medis_igd.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('dokter', 'penilaian_medis_igd.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('reg_periksa.no_rkm_medis', $noRm)
            ->select('penilaian_medis_igd.*', 'dokter.nm_dokter', 'reg_periksa.tgl_registrasi')
            ->orderByDesc('penilaian_medis_igd.tanggal')
            ->get()
            ->map(fn($item) => (array) $item)
            ->toArray();
    }

    public function editRiwayat($noRawat)
    {
        $this->no_rawat = $noRawat;
        $this->updatedNoRawat();
        $this->alert('info', 'Data penilaian medis IGD dimuat ke form');
    }

    public function salinDariRiwayat($noRawat)
    {
        $data = DB::table('penilaian_medis_igd')->where('no_rawat', $noRawat)->first();
        if (!$data) {
            $this->alert('warning', 'Data riwayat tidak ditemukan');
            return;
        }

        if (!empty($data->keluhan_utama)) $this->keluhan_utama = $data->keluhan_utama;
        if (!empty($data->rps)) $this->rps = $data->rps;
        if (!empty($data->rpk)) $this->rpk = $data->rpk;
        if (!empty($data->rpd)) $this->rpd = $data->rpd;
        if (!empty($data->rpo)) $this->rpo = $data->rpo;
        if (!empty($data->alergi)) $this->alergi = $data->alergi;
        if (!empty($data->keadaan)) $this->keadaan_umum = $data->keadaan;
        if (!empty($data->kesadaran)) $this->kesadaran = $data->kesadaran;
        if (!empty($data->gcs)) $this->gcs = $data->gcs;
        if (!empty($data->tb)) $this->tb = $data->tb;
        if (!empty($data->bb)) $this->bb = $data->bb;
        if (!empty($data->td)) $this->td = $data->td;
        if (!empty($data->nadi)) $this->nadi = $data->nadi;
        if (!empty($data->rr)) $this->rr = $data->rr;
        if (!empty($data->suhu)) $this->suhu = $data->suhu;
        if (!empty($data->spo)) $this->spo2 = $data->spo;
        if (!empty($data->kepala)) $this->kepala = $data->kepala;
        if (!empty($data->mata)) $this->mata = $data->mata;
        if (!empty($data->thoraks)) $this->thoraks = $data->thoraks;
        if (!empty($data->abdomen)) $this->abdomen = $data->abdomen;
        if (!empty($data->gigi)) $this->gigi = $data->gigi;
        if (!empty($data->genital)) $this->genital = $data->genital;
        if (!empty($data->leher)) $this->leher = $data->leher;
        if (!empty($data->ekstremitas)) $this->ekstremitas = $data->ekstremitas;
        if (!empty($data->ket_fisik)) $this->ket_fisik = $data->ket_fisik;
        if (!empty($data->ket_lokalis)) $this->ket_lokalis = $data->ket_lokalis;
        if (!empty($data->ekg)) $this->ekg = $data->ekg;
        if (!empty($data->rad)) $this->radiologi = $data->rad;
        if (!empty($data->lab)) $this->laborat = $data->lab;
        if (!empty($data->diagnosis)) $this->diagnosis = $data->diagnosis;
        if (!empty($data->tata)) $this->tatalaksana = $data->tata;

        $this->alert('success', 'Data berhasil disalin dari riwayat kunjungan lampau');
    }

    public function mount()
    {
        $this->tanggal = Carbon::now()->format('Y-m-d H:i:s');
        $this->keadaan_umum = 'Sehat';
        $this->kesadaran = 'Compos Mentis';
        $this->kepala = 'Normal';
        $this->thoraks = 'Normal';
        $this->mata = 'Normal';
        $this->abdomen = 'Normal';
        $this->gigi = 'Normal';
        $this->genital = 'Normal';
        $this->leher = 'Normal';
        $this->ekstremitas = 'Normal';
    }

    public function render()
    {
        return view('livewire.component.awal-igd.form');
    }

    public function resetInput()
    {
        $this->dataPerawat = null;
        $this->dataRiwayat = [];
        $this->tanggal = Carbon::now()->format('Y-m-d H:i:s');
        $this->anamnesis = 'Autoanamnesis';
        $this->hubungan = '';
        $this->keluhan_utama = '';
        $this->rps = '';
        $this->rpk = '';
        $this->rpd = '';
        $this->rpo = '';
        $this->alergi = '';
        $this->keadaan_umum = 'Sehat';
        $this->kesadaran = 'Compos Mentis';
        $this->gcs = '';
        $this->tb = '';
        $this->bb = '';
        $this->td = '';
        $this->nadi = '';
        $this->rr = '';
        $this->suhu = '';
        $this->spo2 = '';
        $this->kepala = 'Normal';
        $this->mata = 'Normal';
        $this->thoraks = 'Normal';
        $this->abdomen = 'Normal';
        $this->gigi = 'Normal';
        $this->genital = 'Normal';
        $this->leher = 'Normal';
        $this->ekstremitas = 'Normal';
        $this->ket_fisik = '';
        $this->ket_lokalis = '';
        $this->ekg = '';
        $this->radiologi = '';
        $this->laborat = '';
        $this->diagnosis = '';
        $this->tatalaksana = '';
    }

    public function updatedNoRawat()
    {

        // dd($this->no_rawat);
        try {
            $this->resetInput();

            $rawatPerawat = DB::table('penilaian_awal_keperawatan_igd')
                ->leftJoin('petugas', 'penilaian_awal_keperawatan_igd.nip', '=', 'petugas.nip')
                ->where('penilaian_awal_keperawatan_igd.no_rawat', $this->no_rawat)
                ->select('penilaian_awal_keperawatan_igd.*', 'petugas.nama as nama_petugas')
                ->first();

            $this->dataPerawat = $rawatPerawat ? (array) $rawatPerawat : null;

            $data = DB::table('penilaian_medis_igd')
                ->where('no_rawat', $this->no_rawat)
                ->first();

            if ($data) {
                $this->tanggal = $data->tanggal;
                $this->anamnesis = $data->anamnesis;
                $this->hubungan = $data->hubungan;
                $this->keluhan_utama = $data->keluhan_utama;
                $this->rps = $data->rps;
                $this->rpk = $data->rpk;
                $this->rpd = $data->rpd;
                $this->rpo = $data->rpo;
                $this->alergi = $data->alergi;
                $this->keadaan_umum = $data->keadaan;
                $this->kesadaran = $data->kesadaran;
                $this->gcs = $data->gcs;
                $this->tb = $data->tb;
                $this->bb = $data->bb;
                $this->td = $data->td;
                $this->nadi = $data->nadi;
                $this->rr = $data->rr;
                $this->suhu = $data->suhu;
                $this->spo2 = $data->spo;
                $this->kepala = $data->kepala;
                $this->mata = $data->mata;
                $this->thoraks = $data->thoraks;
                $this->abdomen = $data->abdomen;
                $this->gigi = $data->gigi;
                $this->genital = $data->genital;
                $this->leher = $data->leher;
                $this->ekstremitas = $data->ekstremitas;
                $this->ket_fisik = $data->ket_fisik;
                $this->ket_lokalis = $data->ket_lokalis;
                $this->ekg = $data->ekg;
                $this->radiologi = $data->rad;
                $this->laborat = $data->lab;
                $this->diagnosis = $data->diagnosis;
                $this->tatalaksana = $data->tata;
            }

            $this->loadRiwayat();
        } catch (\Exception $e) {
            //
        }
    }

    public function simpan()
    {
        try {
            DB::table('penilaian_medis_igd')
                ->upsert([
                    'kd_dokter' => session()->get('username'),
                    'no_rawat' => $this->no_rawat,
                    'tanggal' => $this->tanggal,
                    'anamnesis' => $this->anamnesis,
                    'hubungan' => $this->hubungan ?? '',
                    'keluhan_utama' => $this->keluhan_utama ?? '',
                    'rps' => $this->rps ?? '',
                    'rpk' => $this->rpk ?? '',
                    'rpd' => $this->rpd ?? '',
                    'rpo' => $this->rpo ?? '',
                    'alergi' => $this->alergi ?? '',
                    'keadaan' => $this->keadaan_umum ?? '',
                    'kesadaran' => $this->kesadaran ?? '',
                    'gcs' => $this->gcs ?? '',
                    'tb' => $this->tb ?? '',
                    'bb' => $this->bb ?? '',
                    'td' => $this->td ?? '',
                    'nadi' => $this->nadi ?? '',
                    'rr' => $this->rr ?? '',
                    'suhu' => $this->suhu ?? '',
                    'spo' => $this->spo2 ?? '',
                    'kepala' => $this->kepala ?? '',
                    'mata' => $this->mata ?? '',
                    'thoraks' => $this->thoraks ?? '',
                    'abdomen' => $this->abdomen ?? '',
                    'gigi' => $this->gigi ?? '',
                    'genital' => $this->genital ?? '',
                    'leher' => $this->leher ?? '',
                    'ekstremitas' => $this->ekstremitas ?? '',
                    'ket_fisik' => $this->ket_fisik ?? '',
                    'ket_lokalis' => $this->ket_lokalis ?? '',
                    'ekg' => $this->ekg ?? '',
                    'rad' => $this->radiologi ?? '',
                    'lab' => $this->laborat ?? '',
                    'diagnosis' => $this->diagnosis ?? '',
                    'tata' => $this->tatalaksana ?? '',
                ], ['no_rawat'], ['tanggal', 'kd_dokter', 'anamnesis', 'hubungan', 'keluhan_utama', 'rps', 'rpk', 'rpd', 'rpo', 'alergi', 'keadaan', 'kesadaran', 'gcs', 'tb', 'bb', 'td', 'nadi', 'rr', 'suhu', 'spo', 'kepala', 'mata', 'thoraks', 'abdomen', 'gigi', 'genital', 'leher', 'ekstremitas', 'ket_fisik', 'ket_lokalis', 'ekg', 'rad', 'lab', 'diagnosis', 'tata']);

            $this->loadRiwayat();
            $this->alert('success', 'Data berhasil disimpan');
            $this->emit('closeModalMedisIgd');
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal', [
                'position' =>  'center',
                'timer' =>  '',
                'toast' =>  false,
                'text' =>  App::environment('local') ? $e->getMessage() : 'Terjadi Kesalahan',
                'confirmButtonText' =>  'Oke'
            ]);
        }
    }

    public function confirmHapus()
    {
        $this->confirm('Apakah Anda yakin ingin menghapus data ?', [
            'toast' => false,
            'position' => 'center',
            'showConfirmButton' => true,
            'cancelButtonText' => 'Tidak',
            'onConfirmed' => 'hapusMedisIgd'
        ]);
    }

    public function hapus()
    {
        try {
            $cek = DB::table('penilaian_medis_igd')
                ->where('no_rawat', $this->no_rawat)
                ->delete();
            if (!$cek) {
                $this->alert('error', 'Data tidak ditemukan');
            } else {
                $this->resetInput();
                $this->loadRiwayat();
                $this->alert('success', 'Data berhasil dihapus');
                // $this->emit('closeModalMedisIgd');
            }
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal', [
                'position' =>  'center',
                'timer' =>  '',
                'toast' =>  false,
                'text' =>  App::environment('local') ? $e->getMessage() : 'Terjadi Kesalahan',
                'confirmButtonText' =>  'Oke'
            ]);
        }
    }
}
