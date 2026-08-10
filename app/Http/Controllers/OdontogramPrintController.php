<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OdontogramPrintController extends Controller
{
    public static function generateToken(string $noRawat): string
    {
        $normalized = str_replace('-', '/', $noRawat);
        $key = config('app.key', 'laravel-edokter-secret-key-2026');
        return substr(hash_hmac('sha256', $normalized, $key), 0, 32);
    }

    public function cetak(Request $request)
    {
        $noRawat = $request->input('no_rawat') ?? $request->route('no_rawat');

        if (!$noRawat || !is_string($noRawat)) {
            abort(400, 'Parameter no_rawat is required.');
        }

        $regData = DB::table('reg_periksa')
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('dokter', 'reg_periksa.kd_dokter', '=', 'dokter.kd_dokter')
            ->where('reg_periksa.no_rawat', $noRawat)
            ->select([
                'reg_periksa.*',
                'pasien.nm_pasien',
                'pasien.tgl_lahir as pasien_tgl_lahir',
                'pasien.jk',
                'dokter.nm_dokter',
            ])
            ->first();

        if (!$regData) {
            abort(404, 'Data registrasi pasien tidak ditemukan.');
        }

        $listPemeriksaan = DB::table('pemeriksaan_odontogram')
            ->where('no_rawat', $noRawat)
            ->get();

        $namaDokterPetugas = $regData->nm_dokter ?? '-';
        $kodeDokterPetugas = $regData->kd_dokter ?? '-';

        $setting = DB::table('setting')->first();
        if (!$setting) {
            $setting = (object)[
                'nama_instansi' => 'RUMAH SAKIT BHAYANGKARA TK. III NGANJUK',
                'alamat_instansi' => '',
                'kabupaten' => 'NGANJUK',
                'propinsi' => 'JAWA TIMUR',
                'kontak' => '',
                'email' => '',
                'logo' => null,
            ];
        }

        $setting->header_title_1 = 'POLRI DAERAH JAWA TIMUR';
        $setting->header_title_2 = 'BIDANG KEDOKTERAN DAN KESEHATAN';
        $setting->hospital_name = $setting->nama_instansi ?? 'RUMAH SAKIT BHAYANGKARA TK. III NGANJUK';
        $setting->location = $setting->kabupaten ?? 'NGANJUK';

        $tglLahir = $regData->pasien_tgl_lahir ?? null;
        if ($tglLahir) {
            $umurPasien = Carbon::parse($tglLahir)->age . ' Th';
        } elseif (isset($regData->umurdaftar) && isset($regData->sttsumur)) {
            $umurPasien = $regData->umurdaftar . ' ' . $regData->sttsumur;
        } else {
            $umurPasien = '-';
        }

        $jenisKelaminTitle = (isset($regData->jk) && $regData->jk === 'L') ? 'LAKI-LAKI' : 'PEREMPUAN';
        $tanggalPemeriksaan = Carbon::now()->isoFormat('D MMMM Y');
        $verificationToken = self::generateToken($noRawat);

        return view('prints.odontogram', compact(
            'regData',
            'listPemeriksaan',
            'setting',
            'umurPasien',
            'jenisKelaminTitle',
            'tanggalPemeriksaan',
            'verificationToken',
            'namaDokterPetugas',
            'kodeDokterPetugas'
        ));
    }
}
