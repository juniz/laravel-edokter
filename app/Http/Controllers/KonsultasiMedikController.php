<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KonsultasiMedik;
use App\Models\JawabanKonsultasiMedik;
use Illuminate\Support\Facades\URL;

class KonsultasiMedikController extends Controller
{
    public function jawaban($no_permintaan)
    {
        $konsultasi = KonsultasiMedik::with('dokter')->find($no_permintaan);
        $jawaban = JawabanKonsultasiMedik::find($no_permintaan);
        return view('konsultasi.jawab', compact('konsultasi', 'jawaban'));
    }

    public function jawabanWa(Request $request)
    {
        if (!URL::hasValidSignature($request)) {
            abort(403, 'Unauthorized action.');
        }
        $konsultasi = KonsultasiMedik::with('dokter')->find($request->no_permintaan);
        $jawaban = JawabanKonsultasiMedik::find($request->no_permintaan);
        return view('konsultasi.jawab', compact('konsultasi', 'jawaban'));
    }

    public function simpan($no_permintaan, Request $request)
    {
        $this->validate($request, [
            'diagnosa_kerja_jawab' => 'required',
            'uraian_jawaban' => 'required',
        ], [
            'diagnosa_kerja_jawab.required' => 'Diagnosa kerja wajib diisi',
            'uraian_jawaban.required' => 'Uraian jawaban wajib diisi',
        ]);

        try {
            JawabanKonsultasiMedik::upsert([
                'no_permintaan' => $no_permintaan,
                'tanggal' => date('Y-m-d H:i:s'),
                'diagnosa_kerja' => $request->diagnosa_kerja_jawab,
                'uraian_jawaban' => $request->uraian_jawaban,
            ], ['no_permintaan'], ['tanggal', 'diagnosa_kerja', 'uraian_jawaban']);

            return redirect()->route('konsultasi')->with('success', 'Data jawaban berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->route('konsultasi.jawaban', $no_permintaan)->with('error', 'Data jawaban gagal disimpan : ' . $e->getMessage());
        }
    }
}
