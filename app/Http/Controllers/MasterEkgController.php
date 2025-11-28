<?php

namespace App\Http\Controllers;

use App\Models\BacaanEcho;
use Illuminate\Http\Request;
use App\Models\TemplateEKG;
use Illuminate\Support\Facades\DB;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Str;

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
            $template->template = $request->isi;
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
            $template->template = $request->isi;
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
        // $pdf = SnappyPdf::loadView('prints.echo', $data);
        // return $pdf->inline('ekg.pdf');
        return view('prints.echo', $data);
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
}
