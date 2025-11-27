<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class Absensi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:absensi {--id=} {--bulan=} {--tahun=}';

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
        // $nameToId = [
        //     'MUSTIKA' => 338,
        //     'BRITA'   => 551,
        //     'YOVI'    => 248,
        //     'DEVI'    => 242,
        //     'FAUZI'   => 261,
        //     'OLIN'    => 247,
        //     'WIWIK'   => 221,
        //     'FIFI'    => 390,
        //     'DIAN'  => 243,
        // ];

        $id = $this->option('id');
        // $shift = $this->option('shift');
        // $tanggal = $this->option('tanggal');
        $bulan = $this->option('bulan');
        $tahun = $this->option('tahun');
        $jadwal = $this->getShift($id, $tahun, $bulan);

        if (!$jadwal) {
            $this->error('Data jadwal tidak ditemukan');
            return 1;
        }

        // Looping data jadwal dari h1 sampai h31
        $this->info("=== JADWAL PEGAWAI ID: {$id} ===");
        $this->info("Tahun: {$tahun}, Bulan: {$bulan}");
        $this->info("=====================================");

        for ($i = 1; $i <= 31; $i++) {
            $columnName = 'h' . $i;
            $shiftValue = $jadwal->$columnName ?? null; // Set null jika tidak ada data

            if (!empty($shiftValue)) {
                // Ada jadwal shift - tampilkan shift yang dijadwalkan
                // $this->info("Hari {$i}: {$shiftValue}");
                $tanggal = $tahun . '-' . $bulan . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                $cek = DB::table('rekap_presensi')->where('id', $id)->where('jam_datang', 'like', $tanggal . '%')->first();

                if ($cek) {
                    $this->info("Hari {$i}: Absensi sudah ada");
                } else {
                    // $this->error("Hari {$i}: Absensi belum ada");
                    $jam = DB::table('jam_masuk')->where('shift', $shiftValue)->first();

                    if (!$jam) {
                        $this->error('Data jam masuk untuk shift ' . $shiftValue . ' tidak ditemukan');
                        return 1;
                    }

                    // Hitung durasi menggunakan Carbon untuk menghindari error non-numeric
                    $jamMasuk = Carbon::parse($jam->jam_masuk);
                    $jamPulang = Carbon::parse($jam->jam_pulang);

                    // Generate jam datang dan pulang secara random yang masih tepat waktu
                    $jamDatangRandom = $this->generateRandomJamDatang($jamMasuk);
                    $jamPulangRandom = $this->generateRandomJamPulang($jamPulang);

                    $durasi = $jamPulangRandom->diff($jamDatangRandom)->format('%H:%I:%S');
                    try {
                        DB::table('rekap_presensi')->insert([
                            'id' => $id,
                            'shift' => $shiftValue,
                            'jam_datang' => $tanggal . ' ' . $jamDatangRandom->format('H:i:s'),
                            'jam_pulang' => $tanggal . ' ' . $jamPulangRandom->format('H:i:s'),
                            'status' => 'Tepat Waktu',
                            'keterlambatan' => '00:00:00',
                            'durasi' => $durasi,
                            'keterangan' => '',
                            'photo' => '',
                        ]);
                        $this->info("Hari {$i}: Absensi berhasil disimpan");
                    } catch (\Exception $e) {
                        $this->error($e->getMessage());
                    }
                }
            } else {
                $this->info("Hari {$i}: Libur");
            }
        }

        // Panggil method helper untuk melihat berbagai cara looping
        // $this->loopJadwalData($jadwal);

        // $jam = DB::table('jam_masuk')->where('shift', $shift)->first();

        // if (!$jam) {
        //     $this->error('Data jam masuk untuk shift ' . $shift . ' tidak ditemukan');
        //     return 1;
        // }

        // // Hitung durasi menggunakan Carbon untuk menghindari error non-numeric
        // $jamMasuk = Carbon::parse($jam->jam_masuk);
        // $jamPulang = Carbon::parse($jam->jam_pulang);

        // // Generate jam datang dan pulang secara random yang masih tepat waktu
        // $jamDatangRandom = $this->generateRandomJamDatang($jamMasuk);
        // $jamPulangRandom = $this->generateRandomJamPulang($jamPulang);

        // $durasi = $jamPulangRandom->diff($jamDatangRandom)->format('%H:%I:%S');
        // try {
        //     DB::beginTransaction();
        //     DB::table('rekap_presensi')->insert([
        //         'id' => $id,
        //         'shift' => $shift,
        //         'jam_datang' => $tanggal . ' ' . $jamDatangRandom->format('H:i:s'),
        //         'jam_pulang' => $tanggal . ' ' . $jamPulangRandom->format('H:i:s'),
        //         'status' => 'Tepat Waktu',
        //         'keterlambatan' => '',
        //         'durasi' => $durasi,
        //         'keterangan' => '',
        //         'photo' => '',
        //     ]);
        //     DB::commit();
        //     $this->info('Absensi berhasil disimpan');
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     $this->error($e->getMessage());
        //     return 1;
        // }

        return 0;
    }

    /**
     * Generate jam datang random yang masih tepat waktu
     * Jam datang bisa 15 menit sebelum jam masuk atau tepat waktu
     */
    private function generateRandomJamDatang($jamMasuk)
    {
        // Jam datang bisa 15 menit sebelum jam masuk atau tepat waktu
        $jamDatangMin = $jamMasuk->copy()->subMinutes(15);
        $jamDatangMax = $jamMasuk->copy();

        // Generate random menit antara 0-15 menit sebelum jam masuk
        $randomMinutes = rand(0, 15);

        return $jamMasuk->copy()->subMinutes($randomMinutes);
    }

    /**
     * Generate jam pulang random yang masih dalam rentang normal
     * Jam pulang bisa 15 menit sebelum atau sesudah jam pulang seharusnya
     */
    private function generateRandomJamPulang($jamPulang)
    {
        // Jam pulang bisa 15 menit sebelum atau sesudah jam pulang seharusnya
        $randomMinutes = rand(-15, 15);

        return $jamPulang->copy()->addMinutes($randomMinutes);
    }

    private function getShift($id, $tahun, $bulan)
    {
        $jadwal = DB::table('jadwal_pegawai')->where('id', $id)->where('tahun', $tahun)->where('bulan', $bulan)->first();
        return $jadwal;
    }

    /**
     * Method untuk looping data jadwal dengan berbagai cara
     */
    private function loopJadwalData($jadwal)
    {
        $this->info("\n=== CONTOH BERBAGAI CARA LOOPING JADWAL ===");

        // Cara 1: Dynamic Property Access (Paling sederhana)
        $this->info("\n1. Dynamic Property Access:");
        for ($i = 1; $i <= 31; $i++) {
            $columnName = 'h' . $i;
            $shiftValue = $jadwal->$columnName ?? 'Tidak ada data';
            $this->info("   Hari {$i}: {$shiftValue}");
            // $cek = DB::table('rekap_presensi')->where('id', $id)->where('jam_datang', 'like', $tanggal . '%')->first();
            // if ($cek) {
            //     $this->error('Absensi sudah ada');
            //     return 1;
            // }
        }
    }
}
