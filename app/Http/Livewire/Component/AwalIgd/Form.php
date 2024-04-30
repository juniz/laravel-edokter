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

    protected $listeners = ['hapusMedisIgd' => 'hapus'];

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
