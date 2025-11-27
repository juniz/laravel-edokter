<?php

namespace App\Console\Commands;

use App\Models\KamarInap;
use App\Models\KonsultasiMedik;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class SendWa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:message {--message=} {--phone=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $message = $this->option('message');
        $phone = $this->option('phone');
        // $tempUrl = URL::temporarySignedRoute('temp-konsultasi', now()->addMinutes(5), ['no_permintaan' => 'KM202503110043']);
        // $this->info($tempUrl);
        $no_permintaan = 'KM202503240021';
        $konsultasi = KonsultasiMedik::query()
            ->with(['dokter', 'regPeriksa.pasien', 'regPeriksa'])
            ->where('no_permintaan', $no_permintaan)
            ->first();
        $asalPasien = '';
        if ($konsultasi->regPeriksa->status_lanjut == 'Ralan') {
            $asalPasien = "*Poliklinik:* " . $konsultasi->regPeriksa->poliklinik->nm_poli . "\n";
        } else {
            $bangsal = KamarInap::with('kamar.bangsal')->where('no_rawat', $konsultasi->no_rawat)->first();
            // $this->info($bangsal->kamar->bangsal->nm_bangsal);
            $asalPasien = "*Kamar:* " . $bangsal->kamar->bangsal->nm_bangsal . ' ' . $bangsal->kd_kamar . "\n";
        }
        $pasien = $konsultasi->regPeriksa->pasien;
        $message =
            "*Konsultasi Medik* 👨‍⚕️\n\n" .
            "*Pasien:* " . $pasien->nm_pasien . "\n" .
            "*No. RM:* " . $pasien->no_rkm_medis . "\n" .
            $asalPasien .
            "*No. Permintaan:* " . $konsultasi->no_permintaan . "\n" .
            "*Jenis Permintaan:* " . $konsultasi->jenis_permintaan . "\n" .
            "*Tanggal:* " . $konsultasi->tanggal . "\n" .
            "*Dokter:* " . $konsultasi->dokter->nm_dokter . "\n\n" .
            "*Diagnosa Kerja:*\n" . $konsultasi->diagnosa_kerja . "\n\n" .
            "*Uraian Konsultasi:*\n" . $konsultasi->uraian_konsultasi . "\n\n" .
            "Silahkan klik link berikut untuk menjawab konsultasi: " . URL::temporarySignedRoute('temp-konsultasi', now()->addDay(), ['no_permintaan' => $no_permintaan]) . "\n\n" .
            "*Link akan kadaluarsa dalam 24 jam*\n\n" .
            "*Pesan ini dikirim melalui aplikasi E-Dokter* 🚀" . "\n" .
            "*Jangan balas pesan ini* ❌";
        $response = Http::withHeaders([
            'Authorization' => env('FONNTE_API_KEY'),
        ])->post('https://api.fonnte.com/send', [
            'target' => '08994750136',
            'message' => $message,
            'countryCode' => '62',
        ]);
        $this->info($response->body());
    }
}
