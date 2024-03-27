<?php

namespace App\Http\Livewire\Component;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Riwayat extends Component
{
    public $noRawat;
    protected $pasien;
    public $data = [];
    public $selectDokter = "";

    public function mount($noRawat)
    {
        $this->noRawat = $noRawat;
        $this->pasien = $this->getPasien($noRawat);
    }

    public function hydrate()
    {
        $this->pasien = $this->getPasien($this->noRawat);
    }

    public function render()
    {
        return view('livewire.component.riwayat', [
            'dokter' => $this->getListDokter(),
        ]);
    }

    public function init()
    {
        $this->data = $this->getRiwayatPemeriksaan($this->pasien->no_rkm_medis);
    }

    public function getListDokter()
    {
        return DB::table('dokter')->where('status', '1')->select('kd_dokter', 'nm_dokter')->get();
    }

    public function updatedSelectDokter()
    {
        // dd($this->pasien);
        if ($this->selectDokter != "") {
            $this->data = DB::table('reg_periksa')
                ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
                ->join('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
                ->where('no_rkm_medis', $this->pasien->no_rkm_medis)
                ->where('reg_periksa.stts', '<>', 'Batal')
                ->where('reg_periksa.kd_dokter', $this->selectDokter)
                ->select(
                    'reg_periksa.tgl_registrasi',
                    'reg_periksa.no_rawat',
                    'dokter.nm_dokter',
                    'reg_periksa.status_lanjut',
                    'poliklinik.nm_poli',
                    'reg_periksa.no_reg'
                )
                ->orderBy('reg_periksa.tgl_registrasi', 'desc')
                ->limit(10)
                ->get();
        } else {
            $this->data = $this->getRiwayatPemeriksaan($this->pasien->no_rkm_medis);
        }
    }

    public function getPasien($noRawat)
    {
        $data = DB::table('reg_periksa')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->where('reg_periksa.no_rawat', $noRawat)
            ->select('reg_periksa.no_rkm_medis')
            ->first();

        return $data;
    }

    public function getRiwayatPemeriksaan($noRM)
    {
        $data = DB::table('reg_periksa')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->join('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('no_rkm_medis', $noRM)
            ->where('reg_periksa.stts', '<>', 'Batal')
            ->select(
                'reg_periksa.tgl_registrasi',
                'reg_periksa.no_rawat',
                'dokter.nm_dokter',
                'reg_periksa.status_lanjut',
                'poliklinik.nm_poli',
                'reg_periksa.no_reg'
            )
            ->orderBy('reg_periksa.tgl_registrasi', 'desc')
            ->limit(10)
            ->get();

        // dd($data);

        return $data;
    }

    public function getPemeriksaanRalan($noRawat, $status)
    {
        if ($status == 'Ralan') {
            $data = DB::table('pemeriksaan_ralan')
                ->where('no_rawat', $noRawat)
                ->get();
        } else {
            $data = DB::table('pemeriksaan_ranap')
                ->where('no_rawat', $noRawat)
                ->get();
        }
        return $data;
    }

    public function getDiagnosa($noRawat)
    {
        $data = DB::table('diagnosa_pasien')
            ->join('penyakit', 'diagnosa_pasien.kd_penyakit', '=', 'penyakit.kd_penyakit')
            ->where('diagnosa_pasien.no_rawat', $noRawat)
            ->select('penyakit.kd_penyakit', 'penyakit.nm_penyakit')
            ->get();
        return $data;
    }

    public function getTono($noRawat)
    {
        return DB::table('pemeriksaan_tono')->where('no_rawat', $noRawat)->first();
    }

    public function getPemeriksaanLab($noRawat)
    {
        $data = DB::table('detail_periksa_lab')
            ->join('template_laboratorium', 'detail_periksa_lab.id_template', '=', 'template_laboratorium.id_template')
            ->where('detail_periksa_lab.no_rawat', $noRawat)
            ->select('template_laboratorium.Pemeriksaan', 'detail_periksa_lab.tgl_periksa', 'detail_periksa_lab.jam', 'detail_periksa_lab.nilai', 'template_laboratorium.satuan', 'detail_periksa_lab.nilai_rujukan', 'detail_periksa_lab.keterangan')
            ->orderBy('detail_periksa_lab.tgl_periksa', 'desc')
            ->get();
        return $data;
    }

    public function getResume($noRM)
    {
        return DB::table('resume_pasien')
            ->where('no_rawat', $noRM)
            ->first();
    }

    public function getRadiologi($noRM)
    {
        return DB::table('hasil_radiologi')
            ->where('no_rawat', $noRM)
            ->get();
    }

    public function getFotoRadiologi($noRM)
    {
        return DB::table('gambar_radiologi')
            ->where('no_rawat', $noRM)
            ->get();
    }
}
