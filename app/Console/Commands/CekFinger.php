<?php

namespace App\Console\Commands;

use App\Models\FingerBpjs;
use App\Models\ReferensiMobilejknBpjs;
use App\Traits\BpjsTraits;
use Illuminate\Console\Command;

class CekFinger extends Command
{
    use BpjsTraits;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bpjs:finger {--nopeserta=}';

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
        $tanggal = date('Y-m-d');
        $referensi = ReferensiMobilejknBpjs::where('tanggalperiksa', $tanggal)->get();
        $no = 1;
        foreach ($referensi as $r) {
            $nopeserta = $r->nomorkartu;
            $response = $this->requestFinger('SEP/FingerPrint/Peserta/' . $nopeserta . '/TglPelayanan' . '/' . $tanggal);
            $cek = FingerBpjs::where('no_rawat', $r->no_rawat)->first();
            if ($cek) {
                $cek->update([
                    'tanggal' => $tanggal,
                    'kode' => $response->getData()->kode,
                    'status' => $response->getData()->status
                ]);
            } else {
                FingerBpjs::create([
                    'no_rawat' => $r->no_rawat,
                    'no_kartu' => $nopeserta,
                    'tanggal' => $tanggal,
                    'kode' => $response->getData()->kode,
                    'status' => $response->getData()->status
                ]);
            }
            $this->info('#' . $no . '. No Kartu: ' . $nopeserta . ' Kode: ' . $response->getData()->kode . ' Status: ' . $response->getData()->status);
            $no++;
        }
    }
}
