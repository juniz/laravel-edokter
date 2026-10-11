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
