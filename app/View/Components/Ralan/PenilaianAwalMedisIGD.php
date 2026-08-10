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
