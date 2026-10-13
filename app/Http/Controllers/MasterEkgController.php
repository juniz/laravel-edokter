<?php

namespace App\Http\Controllers;

use App\Models\BacaanEcho;
use Illuminate\Http\Request;
use App\Models\TemplateEKG;
use Illuminate\Support\Facades\DB;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;

class MasterEkgController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $heads = ['No', 'Nama Template', 'Isi', 'Aksi'];
        $data = TemplateEKG::all();
        return view('master-template-ekg', compact('heads', 'data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $template = new TemplateEKG();
            $template->nama_template = $request->nama;
            $template->template = clean($request->isi);
            $template->save();

            return redirect()->back()->with('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Data gagal disimpan');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $data = TemplateEKG::find($id);
            // dd($data);
            return view('master-template-ekg-edit', compact('data'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Data gagal diubah');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {

            $template = TemplateEKG::find($id);
            $template->nama_template = $request->nama;
            $template->template = clean($request->isi);
            $template->save();

            return redirect()->to('master-ekg')->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Data gagal diubah');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            TemplateEKG::find($id)->delete();
            return redirect()->back()->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Data gagal dihapus');
        }
    }

    public function cetakEkg(Request $request)
    {
        $pasien = DB::table('pasien')
            ->where('no_rkm_medis', $request->no_rm)
            ->first();
        $dokter = DB::table('dokter')
            ->where('kd_dokter', session()->get('username'))
            ->first();
        $dokter_pengirim = DB::table('dokter')
            ->where('kd_dokter', $request->dokter_pengirim)
            ->first();
        $isi = $request->isi;
        $data = [
            'isi' => $request->isi,
            'pengirim' => $dokter_pengirim->nm_dokter,
            'dokter' => $dokter,
            'pasien' => $pasien,
        ];

        BacaanEcho::upsert(
            [
                'no_rawat' => $request->no_rawat,
                'kd_dokter' => session()->get('username'),
                'dokter_pengirim' => $request->dokter_pengirim,
                'hasil_bacaan' => Str::markdown($isi),
            ],
            ['no_rawat'],
            ['kd_dokter', 'dokter_pengirim', 'hasil_bacaan']
        );

        try {
            // Embed logo as base64 so DomPDF can render it without HTTP requests
            $logoPath = public_path('img/logo/logo-kop.png');
            $logoBase64 = '';
            if (file_exists($logoPath)) {
                $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            }
            $data['logo_base64'] = $logoBase64;

            // Generate PDF using DomPDF
            $pdf = Pdf::loadView('prints.echo', $data)->setPaper('a4', 'portrait');
            $pdfBase64 = base64_encode($pdf->output());

            // Format file name
            $namaFile = 'ECHO_' . str_replace('/', '-', $request->no_rawat) . '_' . date('YmdHis');

            // Upload via HTTP client to webapps server
            $response = Http::post("https://simrs.rsbhayangkaranganjuk.com/webapps/upload-berkas-edokter.php", [
                "file" => $pdfBase64,
                "no_rawat" => $request->no_rawat,
                "kd_berkas" => 'A14',
                "nama_file" => $namaFile,
            ]);

            if (!$response->successful()) {
                throw new \Exception('Gagal menghubungi server webapps upload.');
            }

            $resJson = $response->json();
            if (!$resJson || !isset($resJson['success']) || !$resJson['success']) {
                $errorMsg = isset($resJson['message']) ? $resJson['message'] : 'Gagal upload berkas di server webapps';
                throw new \Exception($errorMsg);
            }

            $filename = $namaFile . '.pdf';
            $pdfUrl = 'https://simrs.rsbhayangkaranganjuk.com/webapps/berkasrawat/pages/upload/' . $filename;

            return response()->json([
                'success' => true,
                'message' => 'Berkas Echo berhasil disimpan',
                'pdf_url' => $pdfUrl,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses dan menyimpan PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTemplate($id)
    {
        $template = TemplateEKG::find($id);

        if (!$template) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $template
        ]);
    }

    public function viewPdf(Request $request)
    {
        $url = $request->query('url');
        if (!$url) {
            abort(400, 'Parameter url tidak ditemukan.');
        }

        // Validate the URL is from our trusted domain
        if (strpos($url, 'https://simrs.rsbhayangkaranganjuk.com') !== 0) {
            abort(403, 'Akses ditolak.');
        }

        try {
            $response = Http::get($url);
            if (!$response->successful()) {
                throw new \Exception('Gagal mengambil file PDF.');
            }

            return response($response->body(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="document.pdf"'
            ]);
        } catch (\Exception $e) {
            abort(500, 'Error: ' . $e->getMessage());
        }
    }
}
