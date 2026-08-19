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
     * Generate PDF binary content for penandaan area operasi.
     *
     * @param string $noRawat
     * @return string
     * @throws \Exception
     */
    public function generatePdf(string $noRawat): string
    {
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

        // Fallback for dash-formatted no_rawat if not found
        if (! $marking) {
            $altNoRawat = str_contains($noRawat, '/')
                ? str_replace('/', '-', $noRawat)
                : str_replace('-', '/', $noRawat);

            $marking = DB::table('penandaan_area_operasi')
                ->leftJoin('reg_periksa', 'penandaan_area_operasi.no_rawat', '=', 'reg_periksa.no_rawat')
                ->leftJoin('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                ->leftJoin('dokter', 'penandaan_area_operasi.operator_id', '=', 'dokter.kd_dokter')
                ->where('penandaan_area_operasi.no_rawat', $altNoRawat)
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
            throw new \Exception("Data penandaan area operasi tidak ditemukan untuk no_rawat: {$noRawat}");
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

        $verificationToken = PenandaanAreaOperasiController::generateToken($marking->no_rawat);
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
     * @return array
     */
    public function uploadPdf(string $noRawat, string $pdfBinary): array
    {
        $pdfBase64 = base64_encode($pdfBinary);
        $namaFile = 'PENANDAAN_OPERASI_' . str_replace('/', '-', $noRawat) . '_' . date('YmdHis');

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
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'message' => 'Gagal menghubungi server webapps upload (HTTP ' . $response->status() . ').',
                    'pdf_url' => null,
                ];
            }

            $resJson = $response->json();
            if (! $resJson || ! isset($resJson['success']) || ! $resJson['success']) {
                $errorMsg = $resJson['message'] ?? 'Gagal upload berkas di server webapps.';
                Log::error('PenandaanAreaOperasiService upload rejected by webapps server', [
                    'no_rawat' => $noRawat,
                    'response' => $resJson,
                ]);

                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'pdf_url' => null,
                ];
            }

            $filename = $namaFile . '.pdf';
            $pdfUrl = 'https://simrs.rsbhayangkaranganjuk.com/webapps/berkasrawat/pages/upload/' . $filename;

            return [
                'success' => true,
                'message' => 'Berkas penandaan area operasi berhasil disimpan dan diunggah.',
                'pdf_url' => $pdfUrl,
            ];
        } catch (\Throwable $e) {
            Log::error('PenandaanAreaOperasiService upload exception: ' . $e->getMessage(), [
                'no_rawat' => $noRawat,
                'exception' => $e,
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengunggah berkas: ' . $e->getMessage(),
                'pdf_url' => null,
            ];
        }
    }

    /**
     * Coordinate PDF generation and upload for penandaan area operasi.
     *
     * @param string $noRawat
     * @return array
     */
    public function generateAndUploadPdf(string $noRawat): array
    {
        try {
            $pdfBinary = $this->generatePdf($noRawat);
            return $this->uploadPdf($noRawat, $pdfBinary);
        } catch (\Throwable $e) {
            Log::error('PenandaanAreaOperasiService generateAndUploadPdf error: ' . $e->getMessage(), [
                'no_rawat' => $noRawat,
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
            $response = Http::timeout(3)->get('https://api.qrserver.com/v1/create-qr-code/?size=150x150&format=png&data=' . urlencode($url));
            if ($response->successful() && ! empty($response->body())) {
                return 'data:image/png;base64,' . base64_encode($response->body());
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to generate QR code via online service: ' . $e->getMessage());
        }

        return '';
    }
}
