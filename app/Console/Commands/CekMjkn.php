<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CekMjkn extends Command
{
    public $tgl;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cek:mjkn {--tgl=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perintan untuk mengecek MJKN yang belum terkirim ke BPJS';

    public function __construct()
    {
        // $this->tgl = $this->option('tgl');
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            DB::beginTransaction();
            $datas = DB::table('referensi_mobilejkn_bpjs')
                ->where('tanggalperiksa', Carbon::now()->subDay()->format('Y-m-d'))
                ->where('statuskirim', 'Belum')
                ->get();
            foreach ($datas as $data) {
                DB::table('referensi_mobilejkn_bpjs')
                    ->where('nobooking', $data->nobooking)
                    ->update(['statuskirim' => 'Sudah']);
                @DB::table('referensi_mobilejkn_bpjs_batal')
                    ->where('nobooking', $data->nobooking)
                    ->update(['statuskirim' => 'Sudah']);
            }
            DB::commit();
            $this->info('Data MJKN berhasil dikirim sebanyak ' . count($datas));
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Data MJKN gagal dikirim karena ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
