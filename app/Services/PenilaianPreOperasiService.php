<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PenilaianPreOperasiService
{
    /**
     * Find a penilaian pre operasi record robustly.
     *
     * @param string $noRawat
     * @param string|null $tanggal
     * @return object|null
     */
    public static function findPenilaian(string $noRawat, ?string $tanggal = null): ?object
    {
        $altNoRawat = str_contains($noRawat, '/')
            ? str_replace('/', '-', $noRawat)
            : str_replace('-', '/', $noRawat);

        $query = DB::table('penilaian_pre_operasi')
            ->where(function ($q) use ($noRawat, $altNoRawat) {
                $q->where('no_rawat', $noRawat)->orWhere('no_rawat', $altNoRawat);
            });

        if (! empty($tanggal)) {
            $query->where('tanggal', $tanggal);
        } else {
            $query->orderByDesc('tanggal');
        }

        $penilaian = $query->first();
        if (! $penilaian) {
            return null;
        }

        $rawatNo = $penilaian->no_rawat ?? $noRawat;
        $altRawatNo = str_contains($rawatNo, '/')
            ? str_replace('/', '-', $rawatNo)
            : str_replace('-', '/', $rawatNo);

        $regPeriksa = DB::table('reg_periksa')
            ->where(function ($q) use ($rawatNo, $altRawatNo) {
                $q->where('no_rawat', $rawatNo)->orWhere('no_rawat', $altRawatNo);
            })
            ->first();

        $noRm = $regPeriksa->no_rkm_medis ?? null;
        $pasien = $noRm ? DB::table('pasien')->where('no_rkm_medis', $noRm)->first() : null;
        $dokter = ! empty($penilaian->kd_dokter) ? DB::table('dokter')->where('kd_dokter', $penilaian->kd_dokter)->first() : null;

        $penilaian->no_rkm_medis = $noRm;
        $penilaian->umurdaftar = $regPeriksa->umurdaftar ?? null;
        $penilaian->sttsumur = $regPeriksa->sttsumur ?? null;
        $penilaian->tgl_registrasi = $regPeriksa->tgl_registrasi ?? null;
        $penilaian->nm_pasien = $pasien->nm_pasien ?? '';
        $penilaian->nama_pasien = $pasien->nm_pasien ?? '';
        $penilaian->pasien_tgl_lahir = $pasien->tgl_lahir ?? null;
        $penilaian->tgl_lahir = $pasien->tgl_lahir ?? null;
        $penilaian->jk = $pasien->jk ?? null;
        $penilaian->nm_dokter = $dokter->nm_dokter ?? '';
        $penilaian->nama_dokter = $dokter->nm_dokter ?? '';

        return $penilaian;
    }

    /**
     * Generate PDF binary content for penilaian pre operasi.
     *
     * @param string $noRawat
     * @param string|null $tanggal
     * @return string
     * @throws \InvalidArgumentException
     */
    public function generatePdf(string $noRawat, ?string $tanggal = null): string
    {
        ini_set('memory_limit', '512M');

        $penilaian = self::findPenilaian($noRawat, $tanggal);

        if (! $penilaian) {
            throw new \InvalidArgumentException("Data penilaian pre operasi tidak ditemukan untuk no_rawat: {$noRawat}" . ($tanggal ? " (Tanggal: {$tanggal})" : ''));
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

        $tglLahir = $penilaian->pasien_tgl_lahir ?? $penilaian->tgl_lahir ?? null;
        if ($tglLahir) {
            $umurPasien = Carbon::parse($tglLahir)->age . ' Th';
        } elseif (isset($penilaian->umurdaftar) && isset($penilaian->sttsumur)) {
            $umurPasien = $penilaian->umurdaftar . ' ' . $penilaian->sttsumur;
        } else {
            $umurPasien = '-';
        }

        $jenisKelaminTitle = (isset($penilaian->jk) && $penilaian->jk === 'L') ? 'LAKI-LAKI' : 'PEREMPUAN';

        $tanggalPenilaian = $penilaian->tanggal
            ? Carbon::parse($penilaian->tanggal)->isoFormat('D MMMM Y HH:mm')
            : Carbon::now()->isoFormat('D MMMM Y HH:mm');

        $tteData = 'DITANDATANGANI SECARA ELEKTRONIK OLEH: ' . ($penilaian->nama_dokter ?? '') .
            ' (Kode: ' . ($penilaian->kd_dokter ?? '') . ') PADA ' . $tanggalPenilaian .
            ' [NO RAWAT: ' . ($penilaian->no_rawat ?? $noRawat) . ']';
        $qrBase64 = $this->generateQrBase64($tteData);

        $pdf = Pdf::loadView('prints.penilaian-pre-operasi-pdf', [
            'penilaian' => $penilaian,
            'setting' => $setting,
            'umurPasien' => $umurPasien,
            'jenisKelaminTitle' => $jenisKelaminTitle,
            'jenisKelamin' => $penilaian->jk ?? '-',
            'tanggalPenilaian' => $tanggalPenilaian,
            'qrBase64' => $qrBase64,
        ])->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    /**
     * Upload generated PDF to SIMRS webapps server.
     *
     * @param string $noRawat
     * @param string $pdfBinary
     * @param string|null $tanggal
     * @return array
     */
    public function uploadPdf(string $noRawat, string $pdfBinary, ?string $tanggal = null): array
    {
        $pdfBase64 = base64_encode($pdfBinary);
        $sanitizedNoRawat = str_replace('/', '-', $noRawat);
        $filePrefix = 'PENILAIAN_PRE_OPERASI_' . $sanitizedNoRawat . '_';
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
                Log::error('PenilaianPreOperasiService upload failed with HTTP status ' . $response->status(), [
                    'no_rawat' => $noRawat,
                    'tanggal' => $tanggal,
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
                Log::error('PenilaianPreOperasiService upload rejected by webapps server', [
                    'no_rawat' => $noRawat,
                    'tanggal' => $tanggal,
                    'response' => $resJson,
                ]);

                return [
                    'success' => false,
                    'message' => $errorMsg,
                    'pdf_url' => null,
                    'is_replaced' => false,
                ];
            }

            // Clean up old penilaian pre operasi digital records to prevent duplicates,
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
                    ? 'Berkas penilaian pre operasi berhasil disimpan dan berkas digital diperbarui.'
                    : 'Berkas penilaian pre operasi berhasil disimpan dan diunggah.',
                'pdf_url' => $pdfUrl,
                'is_replaced' => $hasExistingOldFiles,
            ];
        } catch (\Throwable $e) {
            Log::error('PenilaianPreOperasiService upload exception: ' . $e->getMessage(), [
                'no_rawat' => $noRawat,
                'tanggal' => $tanggal,
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
     * Coordinate PDF generation and upload for penilaian pre operasi.
     *
     * @param string $noRawat
     * @param string|null $tanggal
     * @return array
     */
    public function generateAndUploadPdf(string $noRawat, ?string $tanggal = null): array
    {
        try {
            $pdfBinary = $this->generatePdf($noRawat, $tanggal);
            return $this->uploadPdf($noRawat, $pdfBinary, $tanggal);
        } catch (\Throwable $e) {
            Log::error('PenilaianPreOperasiService generateAndUploadPdf error: ' . $e->getMessage(), [
                'no_rawat' => $noRawat,
                'tanggal' => $tanggal,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memproses PDF penilaian pre operasi: ' . $e->getMessage(),
                'pdf_url' => null,
                'is_replaced' => false,
            ];
        }
    }

    /**
     * Delete digital file records for penilaian pre operasi under kode A05.
     *
     * @param string $noRawat
     * @return void
     */
    public function deleteDigitalFile(string $noRawat): void
    {
        $sanitizedNoRawat = str_replace('/', '-', $noRawat);
        $filePrefix = 'PENILAIAN_PRE_OPERASI_' . $sanitizedNoRawat . '_';

        try {
            DB::table('berkas_digital_perawatan')
                ->where('no_rawat', $noRawat)
                ->where('kode', 'A05')
                ->where('lokasi_file', 'like', '%' . $filePrefix . '%')
                ->delete();
        } catch (\Throwable $e) {
            Log::error('PenilaianPreOperasiService deleteDigitalFile exception: ' . $e->getMessage(), [
                'no_rawat' => $noRawat,
                'exception' => $e,
            ]);
        }
    }

    /**
     * Helper to generate QR code as base64 data URI.
     *
     * @param string $data
     * @return string
     */
    protected function generateQrBase64(string $data): string
    {
        try {
            return QrCodeGenerator::pngDataUri($data, 150, 2);
        } catch (\Throwable $e) {
            Log::warning('Local QR code generator exception: ' . $e->getMessage());
        }

        try {
            $response = Http::timeout(3)->get('https://api.qrserver.com/v1/create-qr-code/?size=150x150&format=png&data=' . urlencode($data));
            if ($response->successful() && ! empty($response->body())) {
                return 'data:image/png;base64,' . base64_encode($response->body());
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to generate QR code via online service fallback: ' . $e->getMessage());
        }

        return '';
    }
}
