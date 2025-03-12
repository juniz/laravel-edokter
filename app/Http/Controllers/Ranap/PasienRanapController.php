<?php

namespace App\Http\Controllers\Ranap;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PasienRanapController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('loginauth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $statusPasien = $request->status ?? 'belum_pulang';
        $tanggal = $request->tanggal ?? date('Y-m-d');
        $kd_dokter = session()->get('username');
        $kd_sps = session()->get('kd_sps');
        $heads = ['Nama', 'No Rawat', 'No. RM', 'Kamar', 'Bed', 'Tanggal Masuk', 'Cara Bayar'];

        if ($kd_dokter == '86062112' || $kd_dokter == 'SP0000005' || $kd_dokter == 'SP0000002' || $kd_dokter == 'SP0000006' || $kd_sps == 'S004') {

            $data = DB::table('kamar_inap')
                ->join('reg_periksa', 'reg_periksa.no_rawat', '=', 'kamar_inap.no_rawat')
                ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
                ->join('kamar', 'kamar.kd_kamar', '=', 'kamar_inap.kd_kamar')
                ->join('bangsal', 'bangsal.kd_bangsal', '=', 'kamar.kd_bangsal')
                ->join('penjab', 'penjab.kd_pj', '=', 'reg_periksa.kd_pj')
                ->where('kamar_inap.stts_pulang', '-')
                ->select('pasien.nm_pasien', 'reg_periksa.no_rkm_medis', 'bangsal.nm_bangsal', 'kamar_inap.kd_kamar', 'kamar_inap.tgl_masuk', 'penjab.png_jawab', 'reg_periksa.no_rawat', 'bangsal.kd_bangsal')
                ->get();
        } else {

            if ($statusPasien == 'belum_pulang') {
                $data = DB::table('kamar_inap')
                    ->join('reg_periksa', 'reg_periksa.no_rawat', '=', 'kamar_inap.no_rawat')
                    ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
                    ->join('kamar', 'kamar.kd_kamar', '=', 'kamar_inap.kd_kamar')
                    ->join('bangsal', 'bangsal.kd_bangsal', '=', 'kamar.kd_bangsal')
                    ->join('penjab', 'penjab.kd_pj', '=', 'reg_periksa.kd_pj')
                    ->join('dpjp_ranap', 'dpjp_ranap.no_rawat', '=', 'reg_periksa.no_rawat')
                    ->where('kamar_inap.stts_pulang', '-')
                    ->where('dpjp_ranap.kd_dokter', $kd_dokter)
                    ->select('pasien.nm_pasien', 'reg_periksa.no_rkm_medis', 'bangsal.nm_bangsal', 'kamar_inap.kd_kamar', 'kamar_inap.tgl_masuk', 'penjab.png_jawab', 'reg_periksa.no_rawat', 'bangsal.kd_bangsal')
                    ->get();
            } else {
                $data = DB::table('kamar_inap')
                    ->join('reg_periksa', 'reg_periksa.no_rawat', '=', 'kamar_inap.no_rawat')
                    ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
                    ->join('kamar', 'kamar.kd_kamar', '=', 'kamar_inap.kd_kamar')
                    ->join('bangsal', 'bangsal.kd_bangsal', '=', 'kamar.kd_bangsal')
                    ->join('penjab', 'penjab.kd_pj', '=', 'reg_periksa.kd_pj')
                    ->join('dpjp_ranap', 'dpjp_ranap.no_rawat', '=', 'reg_periksa.no_rawat')
                    ->where('dpjp_ranap.kd_dokter', $kd_dokter)
                    ->where('kamar_inap.tgl_masuk', $tanggal)
                    ->where(function ($query) {
                        $query->where('kamar_inap.stts_pulang', 'Pulang')
                            ->orWhere('kamar_inap.stts_pulang', 'Atas Persetujuan Dokter')
                            ->orWhere('kamar_inap.stts_pulang', 'Sembuh');
                    })
                    ->select('pasien.nm_pasien', 'reg_periksa.no_rkm_medis', 'bangsal.nm_bangsal', 'kamar_inap.kd_kamar', 'kamar_inap.tgl_masuk', 'penjab.png_jawab', 'reg_periksa.no_rawat', 'bangsal.kd_bangsal')
                    ->get();
            }
        }

        return view('ranap.pasien-ranap', [
            'heads' => $heads,
            'data' => $data,
        ]);
    }

    private function getPoliklinik($kd_poli)
    {
        $poli = DB::table('poliklinik')->where('kd_poli', $kd_poli)->first();
        return $poli->nm_poli;
    }

    public static function encryptData($data)
    {
        $data = Crypt::encrypt($data);
        return $data;
    }
}
