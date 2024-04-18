<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PersetujuanPenolakanTindakan extends Controller
{
    public function index(Request $request)
    {
        $nopernyataan = $request->get('nopernyataan');
        $no_rawat = $request->get('no_rawat');
        $pasien = DB::table('reg_periksa')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('kelurahan', 'pasien.kd_kel', '=', 'kelurahan.kd_kel')
            ->join('kecamatan', 'pasien.kd_kec', '=', 'kecamatan.kd_kec')
            ->join('kabupaten', 'pasien.kd_kab', '=', 'kabupaten.kd_kab')
            ->where('reg_periksa.no_rawat', $no_rawat)
            ->selectRaw("reg_periksa.no_rawat,pasien.no_rkm_medis,pasien.nm_pasien,if(pasien.jk='L','LAKI-LAKI','PEREMPUAN') as jk,
                    pasien.umur,DATE_FORMAT(pasien.tgl_lahir,'%d-%m-%Y') as tgl_lahir,concat(pasien.alamat,', ',kelurahan.nm_kel,', ',kecamatan.nm_kec,', ',kabupaten.nm_kab) as alamat, 
                    pasien.no_tlp")
            ->first();
        $tindakan = DB::table('persetujuan_penolakan_tindakan')
            ->where('no_pernyataan', $nopernyataan)
            ->selectRaw("persetujuan_penolakan_tindakan.tanggal,persetujuan_penolakan_tindakan.diagnosa,persetujuan_penolakan_tindakan.tindakan,persetujuan_penolakan_tindakan.indikasi_tindakan,
        persetujuan_penolakan_tindakan.tata_cara,persetujuan_penolakan_tindakan.tujuan,persetujuan_penolakan_tindakan.risiko,persetujuan_penolakan_tindakan.komplikasi,
        persetujuan_penolakan_tindakan.prognosis,persetujuan_penolakan_tindakan.alternatif_dan_risikonya,persetujuan_penolakan_tindakan.biaya,
        persetujuan_penolakan_tindakan.lain_lain,persetujuan_penolakan_tindakan.kd_dokter,persetujuan_penolakan_tindakan.nip,persetujuan_penolakan_tindakan.penerima_informasi,
        persetujuan_penolakan_tindakan.alasan_diwakilkan_penerima_informasi,if(persetujuan_penolakan_tindakan.jk_penerima_informasi='L','LAKI-LAKI','PEREMPUAN') as jk_penerima_informasi,
        DATE_FORMAT(persetujuan_penolakan_tindakan.tanggal_lahir_penerima_informasi,'%d-%m-%Y') as tanggal_lahir_penerima_informasi,
        persetujuan_penolakan_tindakan.umur_penerima_informasi,persetujuan_penolakan_tindakan.alamat_penerima_informasi,persetujuan_penolakan_tindakan.no_hp,
        persetujuan_penolakan_tindakan.hubungan_penerima_informasi,persetujuan_penolakan_tindakan.saksi_keluarga")
            ->first();
        return view('persetujuan-penolakan-tindakan', [
            'pasien' => $pasien,
            'tindakan' => $tindakan,
            'nopernyataan' => $nopernyataan,
            'no_rawat' => $no_rawat,
        ]);
    }

    public function simpan(Request $request)
    {
        $request = Http::post('https://simrs.rsbhayangkaranganjuk.com/webapps/persetujuantindakan/pages/storeImage.php', $request->all());
        dd($request->body());
        // return redirect()->to('/ralan/pasien');
    }
}
