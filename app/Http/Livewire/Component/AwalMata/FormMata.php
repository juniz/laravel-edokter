<?php

namespace App\Http\Livewire\Component\AwalMata;

use Jantinnerezo\LivewireAlert\LivewireAlert;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\Component;

class FormMata extends Component
{
    use LivewireAlert;
    public $no_rawat, $editMode = false;
    public $tanggal_awal, $informasi, $ket_informasi, $keluhan_utama, $penyakit_sekarang, $penyakit_dahulu, $riwayat_pengobatan, $riwayat_alergi;
    public $bb, $td, $nadi, $rr, $suhu, $nyeri, $nutrisi;
    public $visus_od, $visus_os, $cc_od, $cc_os, $palpebra_od, $palpebra_os, $conjungtiva_od, $conjungtiva_os, $cornea_od, $cornea_os, $coa_od, $coa_os, $pupil_od, $pupil_os, $lensa_od, $lensa_os, $fundus_od, $fundus_os, $papil_od, $papil_os, $retina_od, $retina_os, $makula_od, $makula_os, $tio_od, $tio_os, $mbo_od, $mbo_os;
    public $lab, $rad, $penunjang_lain, $tes_penglihatan, $pemeriksaan_lain;
    public $assesment_kerja, $assesment_banding;
    public $permasalahan, $terapi, $tindakan;
    public $edukasi;
    protected $listeners = ['setNoRawatMata' => 'setNoRawat', 'hapusMedisRalanMata' => 'hapus'];

    public function render()
    {
        return view('livewire.component.awal-mata.form-mata');
    }

    public function mount()
    {
        $this->tanggal_awal = Carbon::now()->format('Y-m-d H:i:s');
        $this->informasi = 'Autoanamnesis';
    }

    public function setNoRawat($no_rawat)
    {
        $this->no_rawat = $no_rawat;
    }

    public function updatedNoRawat()
    {
        // dd($this->no_rawat);
        try {
            $this->resetInput();
            $data = DB::table('penilaian_medis_ralan_mata')
                ->where('no_rawat', $this->no_rawat)
                ->first();

            if ($data) {
                $this->tanggal_awal = $data->tanggal;
                $this->informasi = $data->anamnesis;
                $this->ket_informasi = $data->hubungan;
                $this->keluhan_utama = $data->keluhan_utama;
                $this->penyakit_sekarang = $data->rps;
                $this->penyakit_dahulu = $data->rpd;
                $this->riwayat_pengobatan = $data->rpo;
                $this->riwayat_alergi = $data->alergi;
                $this->bb = $data->bb;
                $this->td = $data->td;
                $this->nadi = $data->nadi;
                $this->rr = $data->rr;
                $this->suhu = $data->suhu;
                $this->nyeri = $data->nyeri;
                $this->nutrisi = $data->status;
                $this->visus_od = $data->visuskanan;
                $this->visus_os = $data->visuskiri;
                $this->cc_od = $data->cckanan;
                $this->cc_os = $data->cckiri;
                $this->palpebra_od = $data->palkanan;
                $this->palpebra_os = $data->palkiri;
                $this->conjungtiva_od = $data->conkanan;
                $this->conjungtiva_os = $data->conkiri;
                $this->cornea_od = $data->corneakanan;
                $this->cornea_os = $data->corneakiri;
                $this->coa_od = $data->coakanan;
                $this->coa_os = $data->coakiri;
                $this->pupil_od = $data->pupilkanan;
                $this->pupil_os = $data->pupilkiri;
                $this->lensa_od = $data->lensakanan;
                $this->lensa_os = $data->lensakiri;
                $this->fundus_od = $data->funduskanan;
                $this->fundus_os = $data->funduskiri;
                $this->papil_od = $data->papilkanan;
                $this->papil_os = $data->papilkiri;
                $this->retina_od = $data->retinakanan;
                $this->retina_os = $data->retinakiri;
                $this->makula_od = $data->makulakanan;
                $this->makula_os = $data->makulakiri;
                $this->tio_od = $data->tiokanan;
                $this->tio_os = $data->tiokiri;
                $this->mbo_od = $data->mbokanan;
                $this->mbo_os = $data->mbokiri;
                $this->lab = $data->lab;
                $this->rad = $data->rad;
                $this->penunjang_lain = $data->penunjang;
                $this->tes_penglihatan = $data->tes;
                $this->pemeriksaan_lain = $data->pemeriksaan;
                $this->assesment_kerja = $data->diagnosis;
                $this->assesment_banding = $data->diagnosisbdg;
                $this->permasalahan = $data->permasalahan;
                $this->terapi = $data->terapi;
                $this->tindakan = $data->tindakan;
                $this->edukasi = $data->edukasi;
            }
        } catch (\Exception $e) {
        }
    }

    public function resetInput()
    {
        $this->resetExcept(['tanggal_awal', 'informasi', 'no_rawat']);
        $this->tanggal_awal = Carbon::now()->format('Y-m-d H:i:s');
        $this->informasi = 'Autoanamnesis';
    }

    public function simpan()
    {
        // dd($this->no_rawat);
        try {

            DB::table('penilaian_medis_ralan_mata')
                ->upsert([
                    'no_rawat' => $this->no_rawat,
                    'kd_dokter' => session()->get('username'),
                    'tanggal' => $this->tanggal_awal,
                    'anamnesis' => $this->informasi,
                    'hubungan' => $this->ket_informasi ?? '',
                    'keluhan_utama' => $this->keluhan_utama ?? '',
                    'rps' => $this->penyakit_sekarang ?? '',
                    'rpd' => $this->penyakit_dahulu ?? '',
                    'rpo' => $this->riwayat_pengobatan ?? '',
                    'alergi' => $this->riwayat_alergi ?? '',
                    'bb' => $this->bb ?? '',
                    'td' => $this->td ?? '',
                    'nadi' => $this->nadi ?? '',
                    'rr' => $this->rr ?? '',
                    'suhu' => $this->suhu ?? '',
                    'nyeri' => $this->nyeri ?? '',
                    'status' => $this->nutrisi ?? '',
                    'visuskanan' => $this->visus_od ?? '',
                    'visuskiri' => $this->visus_os ?? '',
                    'cckanan' => $this->cc_od ?? '',
                    'cckiri' => $this->cc_os ?? '',
                    'palkanan' => $this->palpebra_od ?? '',
                    'palkiri' => $this->palpebra_os ?? '',
                    'conkanan' => $this->conjungtiva_od ?? '',
                    'conkiri' => $this->conjungtiva_os ?? '',
                    'corneakanan' => $this->cornea_od ?? '',
                    'corneakiri' => $this->cornea_os ?? '',
                    'coakanan' => $this->coa_od ?? '',
                    'coakiri' => $this->coa_os ?? '',
                    'pupilkanan' => $this->pupil_od ?? '',
                    'pupilkiri' => $this->pupil_os ?? '',
                    'lensakanan' => $this->lensa_od ?? '',
                    'lensakiri' => $this->lensa_os ?? '',
                    'funduskanan' => $this->fundus_od ?? '',
                    'funduskiri' => $this->fundus_os ?? '',
                    'papilkanan' => $this->papil_od ?? '',
                    'papilkiri' => $this->papil_os ?? '',
                    'retinakanan' => $this->retina_od ?? '',
                    'retinakiri' => $this->retina_os ?? '',
                    'makulakanan' => $this->makula_od ?? '',
                    'makulakiri' => $this->makula_os ?? '',
                    'tiokanan' => $this->tio_od ?? '',
                    'tiokiri' => $this->tio_os ?? '',
                    'mbokanan' => $this->mbo_od ?? '',
                    'mbokiri' => $this->mbo_os ?? '',
                    'lab' => $this->lab ?? '',
                    'rad' => $this->rad ?? '',
                    'penunjang' => $this->penunjang_lain ?? '',
                    'tes' => $this->tes_penglihatan ?? '',
                    'pemeriksaan' => $this->pemeriksaan_lain ?? '',
                    'diagnosis' => $this->assesment_kerja ?? '',
                    'diagnosisbdg' => $this->assesment_banding ?? '',
                    'permasalahan' => $this->permasalahan ?? '',
                    'terapi' => $this->terapi ?? '',
                    'tindakan' => $this->tindakan ?? '',
                    'edukasi' => $this->edukasi ?? '',
                ], 'no_rawat');

            $this->resetInput();
            $this->alert('success', 'Data berhasil disimpan');
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
        try {

            $data = DB::table('penilaian_medis_ralan_mata')
                ->where('no_rawat', $this->no_rawat)
                ->first();

            if ($data) {
                if ($data->kd_dokter == session()->get('username')) {
                    $this->confirm('Apakah anda yakin ingin menghapus data ini?', [
                        'onConfirmed' => 'hapusMedisRalanMata',
                        'cancelButtonText' => 'Batal',
                        'confirmButtonText' => 'Hapus',
                    ]);
                } else {
                    $this->alert('warning', 'Gagal', [
                        'position' =>  'center',
                        'timer' =>  '',
                        'toast' =>  false,
                        'text' =>  'Anda tidak memiliki akses untuk menghapus data ini',
                        'showConfirmButton' =>  true,
                        'confirmButtonText' =>  'Oke'
                    ]);
                }
            } else {
                $this->alert('warning', 'Gagal', [
                    'position' =>  'center',
                    'timer' =>  '',
                    'toast' =>  false,
                    'text' =>  'Data tidak ditemukan',
                    'showConfirmButton' =>  true,
                    'confirmButtonText' =>  'Oke'
                ]);
            }
        } catch (\Exception $e) {
            $this->alert('error', 'Gagal Hapus', [
                'position' =>  'center',
                'timer' =>  '',
                'toast' =>  false,
                'text' =>  App::environment('local') ? $e->getMessage() : 'Terjadi Kesalahan',
                'confirmButtonText' =>  'Oke'
            ]);
        }
    }

    public function hapus()
    {
        try {
            DB::table('penilaian_medis_ralan_mata')
                ->where('no_rawat', $this->no_rawat)
                ->delete();

            $this->resetInput();
            $this->editMode = false;
            $this->alert('success', 'Berhasil hapus data');
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
