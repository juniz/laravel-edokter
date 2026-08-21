<?php

namespace App\Services;

use App\Http\Controllers\PenandaanAreaOperasiController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PenandaanAreaOperasiService
{
    /**
     * Find a marking record robustly, either by ID or no_rawat fallback.
     *
     * @param string $noRawat
     * @param int|null $id
     * @return object|null
     */
    public static function findMarking(string $noRawat, ?int $id = null): ?object
    {
        $marking = null;
        if ($id) {
            $marking = DB::table('penandaan_area_operasi')->where('id', $id)->first();
        } else {
            $altNoRawat = str_contains($noRawat, '/')
                ? str_replace('/', '-', $noRawat)
                : str_replace('-', '/', $noRawat);

            $marking = DB::table('penandaan_area_operasi')
                ->where(function ($q) use ($noRawat, $altNoRawat) {
                    $q->where('no_rawat', $noRawat)->orWhere('no_rawat', $altNoRawat);
                })
                ->orderByDesc('tanggal_penandaan')
                ->orderByDesc('id')
                ->first();
        }

        if (! $marking) {
            return null;
        }

        $rawatNo = $marking->no_rawat ?? $noRawat;
        $altRawatNo = str_contains($rawatNo, '/')
            ? str_replace('/', '-', $rawatNo)
            : str_replace('-', '/', $rawatNo);

        $regPeriksa = DB::table('reg_periksa')
            ->where(function ($q) use ($rawatNo, $altRawatNo) {
                $q->where('no_rawat', $rawatNo)->orWhere('no_rawat', $altRawatNo);
            })
            ->first();

        $noRm = $regPeriksa->no_rkm_medis ?? $marking->no_rm ?? null;
        $pasien = $noRm ? DB::table('pasien')->where('no_rkm_medis', $noRm)->first() : null;
        $dokter = ! empty($marking->operator_id) ? DB::table('dokter')->where('kd_dokter', $marking->operator_id)->first() : null;

        $marking->no_rkm_medis = $noRm;
        $marking->umurdaftar = $regPeriksa->umurdaftar ?? null;
        $marking->sttsumur = $regPeriksa->sttsumur ?? null;
        $marking->tgl_registrasi = $regPeriksa->tgl_registrasi ?? null;
        $marking->nm_pasien = $pasien->nm_pasien ?? $marking->nama_pasien ?? '';
        $marking->pasien_tgl_lahir = $pasien->tgl_lahir ?? $marking->tgl_lahir ?? null;
        $marking->jk = $pasien->jk ?? null;
        $marking->nm_dokter = $dokter->nm_dokter ?? $marking->nama_operator ?? '';

        if (empty($marking->nama_pasien)) {
            $marking->nama_pasien = $marking->nm_pasien;
        }
        if (empty($marking->nama_operator)) {
            $marking->nama_operator = $marking->nm_dokter;
        }

        return $marking;
    }

    /**
     * Generate PDF binary content for penandaan area operasi.
     *
     * @param string $noRawat
     * @param int|null $id
     * @return string
     * @throws \Exception
     */
    public function generatePdf(string $noRawat, ?int $id = null): string
    {
        $marking = self::findMarking($noRawat, $id);

        if (! $marking) {
            throw new \Exception("Data penandaan area operasi tidak ditemukan untuk no_rawat: {$noRawat}" . ($id ? " (ID: {$id})" : ''));
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
                $images = is_array($marking->gambar_penandaan_base64)
                    ? $marking->gambar_penandaan_base64
                    : json_decode($marking->gambar_penandaan_base64, true, 512, JSON_THROW_ON_ERROR);
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

        $logoPath = public_path('img/logo/logo-kop.png');
        if (! file_exists($logoPath)) {
            $logoPath = public_path('img/logo/logo.png');
        }
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }
        $setting->logo_base64 = $logoBase64;

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

        $verificationToken = PenandaanAreaOperasiController::generateToken($marking->no_rawat, $marking->id ?? null);
        $qrUrl = url('/penandaan-area-operasi/verifikasi/' . $verificationToken);
        $qrBase64 = $this->generateQrBase64($qrUrl);

        $pdf = Pdf::loadView('prints.penandaan-area-operasi-pdf', [
            'marking' => $marking,
            'images' => $images,
            'diagrams' => $images,
            'setting' => $setting,
            'umurPasien' => $umurPasien,
            'jenisKelaminTitle' => $jenisKelaminTitle,
            'tanggalPenandaan' => $tanggalPenandaan,
            'verificationToken' => $verificationToken,
            'qrBase64' => $qrBase64,
        ])->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    /**
     * Upload generated PDF to SIMRS webapps server.
     *
     * @param string $noRawat
     * @param string $pdfBinary
     * @param int|null $id
     * @return array
     */
    public function uploadPdf(string $noRawat, string $pdfBinary, ?int $id = null): array
    {
        $pdfBase64 = base64_encode($pdfBinary);
        $sanitizedNoRawat = str_replace('/', '-', $noRawat);
        $filePrefix = 'PENANDAAN_OPERASI_' . $sanitizedNoRawat . '_' . ($id ? $id . '_' : '');
        $namaFile = $filePrefix . date('YmdHis');
        $filename = $namaFile . '.pdf';
        $newLocation = 'pages/upload/' . $filename;

        $hasExistingOldFiles = DB::table('berkas_digital_perawatan')
            ->where('no_rawat', $noRawat)
            ->where('kode', 'A05')
            ->where('lokasi_file', 'like', '%' . $filePrefix . '%')
            ->exists();

        try {
            $response = Http::timeout(30)->post('https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php', [
                'file' => $pdfBase64,
                'no_rawat' => $noRawat,
                'kd_berkas' => 'A05',
                'nama_file' => $namaFile,
            ]);

            if (! $response->successful()) {
                Log::error('PenandaanAreaOperasiService upload failed with HTTP status ' . $response->status(), [
                    'no_rawat' => $noRawat,
                    'id' => $id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'message' => 'Gagal menghubungi server webapps upload (HTTP ' . $response->status() . ').',
                    'pdf_url' => null,
                    'is_replaced' => false,
                ];
            }

            $resJson = $response->json();
            if (! $resJson || ! isset($resJson['success']) || ! $resJson['success']) {
                $errorMsg = $resJson['message'] ?? 'Gagal upload berkas di server webapps.';
                Log::error('PenandaanAreaOperasiService upload rejected by webapps server', [
                    'no_rawat' => $noRawat,
                    'id' => $id,
                    'response' => $resJson,
                ]);

                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'pdf_url' => null,
                    'is_replaced' => false,
                ];
            }

            // Clean up old penandaan operasi digital records to prevent duplicates,
            // while preserving other A05 surgical documents.
            DB::table('berkas_digital_perawatan')
                ->where('no_rawat', $noRawat)
                ->where('kode', 'A05')
                ->where('lokasi_file', 'like', '%' . $filePrefix . '%')
                ->where('lokasi_file', '<>', $newLocation)
                ->delete();

            $pdfUrl = 'https://simrs.rsbhayangkaranganjuk.com/webapps/berkasrawat/pages/upload/' . $filename;

            return [
                'success' => true,
                'message' => $hasExistingOldFiles
                    ? 'Berkas penandaan area operasi berhasil disimpan dan berkas digital diperbarui.'
                    : 'Berkas penandaan area operasi berhasil disimpan dan diunggah.',
                'pdf_url' => $pdfUrl,
                'is_replaced' => $hasExistingOldFiles,
            ];
        } catch (\Throwable $e) {
            Log::error('PenandaanAreaOperasiService upload exception: ' . $e->getMessage(), [
                'no_rawat' => $noRawat,
                'id' => $id,
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengunggah berkas: ' . $e->getMessage(),
                'pdf_url' => null,
                'is_replaced' => false,
            ];
        }
    }

    /**
     * Coordinate PDF generation and upload for penandaan area operasi.
     *
     * @param string $noRawat
     * @param int|null $id
     * @return array
     */
    public function generateAndUploadPdf(string $noRawat, ?int $id = null): array
    {
        try {
            $pdfBinary = $this->generatePdf($noRawat, $id);
            return $this->uploadPdf($noRawat, $pdfBinary, $id);
        } catch (\Throwable $e) {
            Log::error('PenandaanAreaOperasiService generateAndUploadPdf error: ' . $e->getMessage(), [
                'no_rawat' => $noRawat,
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memproses PDF penandaan area operasi: ' . $e->getMessage(),
                'pdf_url' => null,
            ];
        }
    }

    /**
     * Helper to generate QR code as base64 data URI.
     *
     * @param string $url
     * @return string
     */
    protected function generateQrBase64(string $url): string
    {
        try {
            return QrCodeGenerator::pngDataUri($url, 150, 2);
        } catch (\Throwable $e) {
            Log::warning('Local QR code generator exception: ' . $e->getMessage());
        }

        try {
            $response = Http::timeout(3)->get('https://api.qrserver.com/v1/create-qr-code/?size=150x150&format=png&data=' . urlencode($url));
            if ($response->successful() && ! empty($response->body())) {
                return 'data:image/png;base64,' . base64_encode($response->body());
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to generate QR code via online service fallback: ' . $e->getMessage());
        }

        return '';
    }
}
