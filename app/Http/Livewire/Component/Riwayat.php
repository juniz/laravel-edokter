<?php

namespace App\Http\Livewire\Component;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Riwayat extends Component
{
    public $noRawat;
    public $pasien;

    public function mount($noRawat)
    {
        $this->noRawat = $noRawat;
        $this->pasien = $this->getPasien($noRawat);
    }

    public function render()
    {
        return view('livewire.component.riwayat', [
            'data' => $this->getRiwayatPemeriksaan($this->pasien->no_rkm_medis)
        ]);
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
            ->get();

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

    public static function getTono($noRawat)
    {
        return DB::table('pemeriksaan_tono')->where('no_rawat', $noRawat)->first();
    }
}
