<?php

namespace App\Http\Controllers;

use App\Services\PenandaanAreaOperasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PenandaanAreaOperasiController extends Controller
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

        if (! $noRawat || ! is_string($noRawat)) {
            abort(400, 'Parameter no_rawat is required.');
        }

        $marking = DB::table('penandaan_area_operasi')
            ->leftJoin('reg_periksa', 'penandaan_area_operasi.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('dokter', 'penandaan_area_operasi.operator_id', '=', 'dokter.kd_dokter')
            ->where('penandaan_area_operasi.no_rawat', $noRawat)
            ->select([
                'penandaan_area_operasi.*',
                'reg_periksa.no_rkm_medis',
                'reg_periksa.umurdaftar',
                'reg_periksa.sttsumur',
                'reg_periksa.tgl_registrasi',
                'pasien.nm_pasien',
                'pasien.tgl_lahir as pasien_tgl_lahir',
                'pasien.jk',
                'dokter.nm_dokter',
            ])
            ->first();

        if (! $marking) {
            abort(404, 'Data penandaan area operasi tidak ditemukan.');
        }

        if (empty($marking->nama_pasien) && ! empty($marking->nm_pasien)) {
            $marking->nama_pasien = $marking->nm_pasien;
        }

        if (empty($marking->nama_operator) && ! empty($marking->nm_dokter)) {
            $marking->nama_operator = $marking->nm_dokter;
        }

        $images = [];
        if (! empty($marking->gambar_penandaan_base64)) {
            try {
                $images = json_decode($marking->gambar_penandaan_base64, true, 512, JSON_THROW_ON_ERROR) ?? [];
            } catch (\Throwable $e) {
                $images = [];
            }
        }

        $setting = DB::table('setting')->first();
        if (! $setting) {
            $setting = (object) [
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

        $tglLahir = $marking->pasien_tgl_lahir ?? $marking->tgl_lahir ?? null;
        if ($tglLahir) {
            $umurPasien = Carbon::parse($tglLahir)->age . ' Th';
        } elseif (isset($marking->umurdaftar) && isset($marking->sttsumur)) {
            $umurPasien = $marking->umurdaftar . ' ' . $marking->sttsumur;
        } else {
            $umurPasien = '-';
        }

        $jenisKelaminTitle = (isset($marking->jk) && $marking->jk === 'L') ? 'LAKI-LAKI' : 'PEREMPUAN';

        $tanggalPenandaan = $marking->tanggal_penandaan
            ? Carbon::parse($marking->tanggal_penandaan)->isoFormat('D MMMM Y')
            : Carbon::now()->isoFormat('D MMMM Y');

        $verificationToken = self::generateToken($marking->no_rawat);

        return view('prints.penandaan-area-operasi', compact(
            'marking',
            'images',
            'setting',
            'umurPasien',
            'jenisKelaminTitle',
            'tanggalPenandaan',
            'verificationToken'
        ));
    }

    public function verifikasi(Request $request, ?string $token = null)
    {
        $param = $request->input('token') ?? $request->input('no_rawat') ?? $token ?? $request->route('token') ?? $request->route('no_rawat');

        if (! $param || ! is_string($param)) {
            abort(400, 'Parameter verifikasi tidak valid.');
        }

        $marking = null;
        $cleanParam = trim(str_replace('-', '/', $param));

        if (strlen($param) === 32 && ctype_xdigit($param)) {
            // Find record matching HMAC token
            $allMarkings = DB::table('penandaan_area_operasi')->select('no_rawat')->get();
            $targetNoRawat = null;

            foreach ($allMarkings as $item) {
                if (self::generateToken($item->no_rawat) === $param) {
                    $targetNoRawat = $item->no_rawat;
                    break;
                }
            }

            if (! $targetNoRawat) {
                abort(404, 'Token verifikasi dokumen tidak ditemukan atau tidak valid.');
            }

            $cleanParam = $targetNoRawat;
        } else {
            // Legacy / direct no_rawat check - require valid HMAC token or session authentication
            $tokenParam = $request->input('t');
            $expectedToken = self::generateToken($cleanParam);

            if ($tokenParam !== $expectedToken && ! session()->has('username')) {
                abort(403, 'Akses verifikasi memerlukan token HMAC yang sah.');
            }
        }

        $marking = DB::table('penandaan_area_operasi')
            ->leftJoin('reg_periksa', 'penandaan_area_operasi.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('dokter', 'penandaan_area_operasi.operator_id', '=', 'dokter.kd_dokter')
            ->where('penandaan_area_operasi.no_rawat', $cleanParam)
            ->select([
                'penandaan_area_operasi.*',
                'reg_periksa.no_rkm_medis',
                'reg_periksa.umurdaftar',
                'reg_periksa.sttsumur',
                'reg_periksa.tgl_registrasi',
                'pasien.nm_pasien',
                'pasien.tgl_lahir as pasien_tgl_lahir',
                'pasien.jk',
                'dokter.nm_dokter',
            ])
            ->first();

        if (! $marking) {
            abort(404, 'Data penandaan area operasi tidak ditemukan.');
        }

        if (empty($marking->nama_pasien) && ! empty($marking->nm_pasien)) {
            $marking->nama_pasien = $marking->nm_pasien;
        }

        if (empty($marking->nama_operator) && ! empty($marking->nm_dokter)) {
            $marking->nama_operator = $marking->nm_dokter;
        }

        $setting = DB::table('setting')->first();
        if (! $setting) {
            $setting = (object) [
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

        $tglLahir = $marking->pasien_tgl_lahir ?? $marking->tgl_lahir ?? null;
        if ($tglLahir) {
            $umurPasien = Carbon::parse($tglLahir)->age . ' Th';
        } elseif (isset($marking->umurdaftar) && isset($marking->sttsumur)) {
            $umurPasien = $marking->umurdaftar . ' ' . $marking->sttsumur;
        } else {
            $umurPasien = '-';
        }

        $jenisKelaminTitle = (isset($marking->jk) && $marking->jk === 'L') ? 'LAKI-LAKI' : 'PEREMPUAN';

        $tanggalPenandaan = $marking->tanggal_penandaan
            ? Carbon::parse($marking->tanggal_penandaan)->isoFormat('D MMMM Y HH:mm:ss')
            : Carbon::now()->isoFormat('D MMMM Y HH:mm:ss');

        return view('prints.verifikasi-penandaan', compact(
            'marking',
            'setting',
            'umurPasien',
            'jenisKelaminTitle',
            'tanggalPenandaan'
        ));
    }

    public function downloadPdf(Request $request, ?PenandaanAreaOperasiService $service = null)
    {
        $service = $service ?? app(PenandaanAreaOperasiService::class);
        $noRawat = $request->input('no_rawat') ?? $request->route('no_rawat');

        if (! $noRawat || ! is_string($noRawat)) {
            abort(400, 'Parameter no_rawat is required.');
        }

        $pdfBinary = $service->generatePdf($noRawat);
        $filename = 'PENANDAAN_OPERASI_' . str_replace('/', '-', $noRawat) . '_' . date('YmdHis') . '.pdf';

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function uploadPdf(Request $request, ?PenandaanAreaOperasiService $service = null)
    {
        $service = $service ?? app(PenandaanAreaOperasiService::class);
        $noRawat = $request->input('no_rawat') ?? $request->route('no_rawat');

        if (! $noRawat || ! is_string($noRawat)) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter no_rawat is required.',
                'pdf_url' => null,
            ], 400);
        }

        try {
            $result = $service->generateAndUploadPdf($noRawat);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'pdf_url' => $result['pdf_url'] ?? null,
                'is_replaced' => $result['is_replaced'] ?? false,
            ], $result['success'] ? 200 : 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses dan menyimpan PDF: ' . $e->getMessage(),
                'pdf_url' => null,
                'is_replaced' => false,
            ], 500);
        }
    }
}


