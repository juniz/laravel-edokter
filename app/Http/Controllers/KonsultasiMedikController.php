<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use Illuminate\Http\Request;
use App\Models\KonsultasiMedik;
use App\Models\JawabanKonsultasiMedik;
use App\Models\RegPeriksa;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;

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

            $konsul = KonsultasiMedik::where('no_permintaan', $no_permintaan)->first();
            $reg = RegPeriksa::where('no_rawat', $konsul->no_rawat)->first();
            $dokter_dikonsuli = Dokter::where('kd_dokter', $konsul->kd_dokter_dikonsuli)->first();
            $dokter = Dokter::where('kd_dokter', $konsul->kd_dokter)->first();
            $message =
                "*Jawaban Konsultasi Medik* 👨‍⚕️\n\n" .
                "*Pasien:* " . $reg->pasien->nm_pasien . "\n" .
                "*No. RM:* " . $reg->pasien->no_rkm_medis . "\n" .
                "*No. Permintaan:* " . $no_permintaan . "\n" .
                "*Jenis Permintaan:* " . $konsul->jenis_permintaan . "\n" .
                "*Tanggal:* " . $konsul->tanggal . "\n" .
                "*Dokter Dikonsuli:* " . $dokter_dikonsuli->nm_dokter . "\n\n" .
                "*Diagnosa Kerja:*\n" . $konsul->diagnosa_kerja . "\n\n" .
                "*Uraian Konsultasi:*\n" . $konsul->uraian_jawaban . "\n\n" .
                "*Jawaban Diagnosa Kerja:*\n" . $request->diagnosa_kerja_jawab . "\n\n" .
                "*Uraian Jawaban:*\n" . $request->uraian_jawaban . "\n\n" .
                "*Pesan ini dikirim melalui aplikasi E-Dokter* 🚀" . "\n" .
                "*Jangan balas pesan ini* ❌";
            @Http::withHeaders([
                'Authorization' => env('FONNTE_API_KEY'),
            ])->post('https://api.fonnte.com/send', [
                'target' => $dokter->no_telp,
                'message' => $message,
                'countryCode' => '62',
            ]);
            return redirect()->route('konsultasi')->with('success', 'Data jawaban berhasil disimpan');
        } catch (\Exception $e) {
            return redirect()->route('konsultasi.jawaban', $no_permintaan)->with('error', 'Data jawaban gagal disimpan : ' . $e->getMessage());
        }
    }
}
