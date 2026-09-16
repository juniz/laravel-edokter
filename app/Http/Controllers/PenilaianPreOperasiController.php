<?php

namespace App\Http\Controllers;

use App\Services\PenilaianPreOperasiService;
use Illuminate\Http\Request;

class PenilaianPreOperasiController extends Controller
{
    /**
     * Download generated PDF for penilaian pre operasi.
     *
     * @param Request $request
     * @param PenilaianPreOperasiService|null $service
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf(Request $request, ?PenilaianPreOperasiService $service = null)
    {
        $service = $service ?? app(PenilaianPreOperasiService::class);
        $noRawat = $request->input('no_rawat') ?? $request->route('no_rawat');
        $tanggal = $request->input('tanggal') ?? $request->route('tanggal');

        if (! $noRawat || ! is_string($noRawat)) {
            abort(400, 'Parameter no_rawat is required.');
        }

        $pdfBinary = $service->generatePdf($noRawat, $tanggal);
        $filename = 'PENILAIAN_PRE_OPERASI_' . str_replace('/', '-', $noRawat) . '_' . date('YmdHis') . '.pdf';

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Generate and upload PDF for penilaian pre operasi.
     *
     * @param Request $request
     * @param PenilaianPreOperasiService|null $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadPdf(Request $request, ?PenilaianPreOperasiService $service = null)
    {
        $service = $service ?? app(PenilaianPreOperasiService::class);
        $noRawat = $request->input('no_rawat') ?? $request->route('no_rawat');
        $tanggal = $request->input('tanggal') ?? $request->route('tanggal');

        if (! $noRawat || ! is_string($noRawat)) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter no_rawat is required.',
                'pdf_url' => null,
                'is_replaced' => false,
            ], 400);
        }

        try {
            $result = $service->generateAndUploadPdf($noRawat, $tanggal);

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
