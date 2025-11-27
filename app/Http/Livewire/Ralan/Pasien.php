<?php

namespace App\Http\Livewire\Ralan;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\TemplateEKG;

class Pasien extends Component
{
    public $noRawat;

    protected $listeners = ['setNoRawat' => 'loadPasien'];

    public function mount($noRawat = null)
    {
        $this->noRawat = $noRawat;
    }

    public function loadPasien($noRawat)
    {
        $this->noRawat = $noRawat;
    }

    public function render()
    {
        $data = null;
        if ($this->noRawat) {
            $data = DB::table('reg_periksa')
                ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
                ->join('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
                ->leftJoin('catatan_pasien', 'reg_periksa.no_rkm_medis', '=', 'catatan_pasien.no_rkm_medis')
                ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
                ->leftJoin('personal_pasien', 'pasien.no_rkm_medis', '=', 'personal_pasien.no_rkm_medis')
                ->leftJoin('propinsi', 'pasien.kd_prop', '=', 'propinsi.kd_prop')
                ->leftJoin('kabupaten', 'pasien.kd_kab', '=', 'kabupaten.kd_kab')
                ->leftJoin('kecamatan', 'pasien.kd_kec', '=', 'kecamatan.kd_kec')
                ->leftJoin('kelurahan', 'pasien.kd_kel', '=', 'kelurahan.kd_kel')
                ->where('reg_periksa.no_rawat', $this->noRawat)
                ->select(
                    'pasien.*',
                    'penjab.png_jawab',
                    'reg_periksa.no_rkm_medis',
                    'reg_periksa.no_rawat',
                    'reg_periksa.status_lanjut',
                    'reg_periksa.kd_pj',
                    'dokter.nm_dokter',
                    'poliklinik.nm_poli',
                    'reg_periksa.kd_poli',
                    'catatan_pasien.catatan',
                    'personal_pasien.gambar',
                    'propinsi.nm_prop',
                    'kabupaten.nm_kab',
                    'kecamatan.nm_kec',
                    'kelurahan.nm_kel',
                )
                ->first();
        }

        $dokterList = DB::table('dokter')->where('status', '1')->get();
        $echoTemplates = TemplateEKG::all();

        return view('livewire.ralan.pasien', [
            'data' => $data,
            'dokterList' => $dokterList,
            'echoTemplates' => $echoTemplates
        ]);
    }
}
