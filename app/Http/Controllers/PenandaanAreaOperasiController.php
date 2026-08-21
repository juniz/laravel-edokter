<?php

namespace App\Http\Controllers;

use App\Services\PenandaanAreaOperasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PenandaanAreaOperasiController extends Controller
{
    public static function generateToken(string $noRawat, ?int $id = null): string
    {
        $normalized = str_replace('-', '/', $noRawat) . ($id ? '#' . $id : '');
        $key = config('app.key', 'laravel-edokter-secret-key-2026');
        return substr(hash_hmac('sha256', $normalized, $key), 0, 32);
    }

    public function cetak(Request $request)
    {
        $noRawat = $request->input('no_rawat') ?? $request->route('no_rawat');
        $id = $request->input('id') ? (int) $request->input('id') : null;

        if (! $noRawat || ! is_string($noRawat)) {
            abort(400, 'Parameter no_rawat is required.');
        }

        $query = DB::table('penandaan_area_operasi')
            ->leftJoin('reg_periksa', 'penandaan_area_operasi.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('dokter', 'penandaan_area_operasi.operator_id', '=', 'dokter.kd_dokter')
            ->where('penandaan_area_operasi.no_rawat', $noRawat);

        if ($id) {
            $query->where('penandaan_area_operasi.id', $id);
        } else {
            $query->orderByDesc('penandaan_area_operasi.tanggal_penandaan')
                ->orderByDesc('penandaan_area_operasi.id');
        }

        $marking = $query->select([
            'penandaan_area_operasi.*',
            'reg_periksa.no_rkm_medis',
            'reg_periksa.umurdaftar',
            'reg_periksa.sttsumur',
            'reg_periksa.tgl_registrasi',
            'pasien.nm_pasien',
            'pasien.tgl_lahir as pasien_tgl_lahir',
            'pasien.jk',
            'dokter.nm_dokter',
        ])->first();

        if (! $marking) {
            $altNoRawat = str_contains($noRawat, '/')
                ? str_replace('/', '-', $noRawat)
                : str_replace('-', '/', $noRawat);

            $altQuery = DB::table('penandaan_area_operasi')
                ->leftJoin('reg_periksa', 'penandaan_area_operasi.no_rawat', '=', 'reg_periksa.no_rawat')
                ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                ->leftJoin('dokter', 'penandaan_area_operasi.operator_id', '=', 'dokter.kd_dokter')
                ->where('penandaan_area_operasi.no_rawat', $altNoRawat);

            if ($id) {
                $altQuery->where('penandaan_area_operasi.id', $id);
            } else {
                $altQuery->orderByDesc('penandaan_area_operasi.tanggal_penandaan')
                    ->orderByDesc('penandaan_area_operasi.id');
            }

            $marking = $altQuery->select([
                'penandaan_area_operasi.*',
                'reg_periksa.no_rkm_medis',
                'reg_periksa.umurdaftar',
                'reg_periksa.sttsumur',
                'reg_periksa.tgl_registrasi',
                'pasien.nm_pasien',
                'pasien.tgl_lahir as pasien_tgl_lahir',
                'pasien.jk',
                'dokter.nm_dokter',
            ])->first();
        }

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

        $verificationToken = self::generateToken($marking->no_rawat, $marking->id ?? null);

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
        $targetId = null;

        if (strlen($param) === 32 && ctype_xdigit($param)) {
            // Find record matching HMAC token (with id or legacy without id)
            $allMarkings = DB::table('penandaan_area_operasi')->select(['id', 'no_rawat'])->get();
            $targetNoRawat = null;

            foreach ($allMarkings as $item) {
                if (self::generateToken($item->no_rawat, $item->id) === $param || self::generateToken($item->no_rawat) === $param) {
                    $targetId = $item->id;
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
            $idParam = $request->input('id') ? (int) $request->input('id') : null;
            $expectedTokenWithId = $idParam ? self::generateToken($cleanParam, $idParam) : null;
            $expectedTokenNoId = self::generateToken($cleanParam);

            $hasValidToken = ($tokenParam && ($tokenParam === $expectedTokenWithId || $tokenParam === $expectedTokenNoId));

            if (! $hasValidToken && ! session()->has('username')) {
                abort(403, 'Akses verifikasi memerlukan token HMAC yang sah.');
            }

            if ($idParam) {
                $targetId = $idParam;
            }
        }

        $query = DB::table('penandaan_area_operasi')
            ->leftJoin('reg_periksa', 'penandaan_area_operasi.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->leftJoin('dokter', 'penandaan_area_operasi.operator_id', '=', 'dokter.kd_dokter');

        if ($targetId) {
            $query->where('penandaan_area_operasi.id', $targetId);
        } else {
            $query->where('penandaan_area_operasi.no_rawat', $cleanParam)
                ->orderByDesc('penandaan_area_operasi.tanggal_penandaan')
                ->orderByDesc('penandaan_area_operasi.id');
        }

        $marking = $query->select([
            'penandaan_area_operasi.*',
            'reg_periksa.no_rkm_medis',
            'reg_periksa.umurdaftar',
            'reg_periksa.sttsumur',
            'reg_periksa.tgl_registrasi',
            'pasien.nm_pasien',
            'pasien.tgl_lahir as pasien_tgl_lahir',
            'pasien.jk',
            'dokter.nm_dokter',
        ])->first();

        if (! $marking && ! $targetId) {
            $altParam = str_contains($cleanParam, '/') ? str_replace('/', '-', $cleanParam) : str_replace('-', '/', $cleanParam);
            $marking = DB::table('penandaan_area_operasi')
                ->leftJoin('reg_periksa', 'penandaan_area_operasi.no_rawat', '=', 'reg_periksa.no_rawat')
                ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                ->leftJoin('dokter', 'penandaan_area_operasi.operator_id', '=', 'dokter.kd_dokter')
                ->where('penandaan_area_operasi.no_rawat', $altParam)
                ->orderByDesc('penandaan_area_operasi.tanggal_penandaan')
                ->orderByDesc('penandaan_area_operasi.id')
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
        }

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
        $id = $request->input('id') ? (int) $request->input('id') : null;

        if (! $noRawat || ! is_string($noRawat)) {
            abort(400, 'Parameter no_rawat is required.');
        }

        $pdfBinary = $service->generatePdf($noRawat, $id);
        $filename = 'PENANDAAN_OPERASI_' . str_replace('/', '-', $noRawat) . ($id ? '_' . $id : '') . '_' . date('YmdHis') . '.pdf';

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function uploadPdf(Request $request, ?PenandaanAreaOperasiService $service = null)
    {
        $service = $service ?? app(PenandaanAreaOperasiService::class);
        $noRawat = $request->input('no_rawat') ?? $request->route('no_rawat');
        $id = $request->input('id') ? (int) $request->input('id') : null;

        if (! $noRawat || ! is_string($noRawat)) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter no_rawat is required.',
                'pdf_url' => null,
            ], 400);
        }

        try {
            $result = $service->generateAndUploadPdf($noRawat, $id);

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


