<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\EnkripsiData;

class ResepController extends Controller
{
    use EnkripsiData;

    public function getObatRanap(Request $request, $bangsal)
    {
        $q = $request->get('q');
        $que = '%' . $q . '%';

        // $depo = DB::table('set_depo_ranap')
        //     ->where('kd_bangsal', $bangsal)
        //     ->first();

        $obat = DB::table('databarang')
            ->join('gudangbarang', 'databarang.kode_brng', '=', 'gudangbarang.kode_brng')
            ->where('status', '1')
            ->where('gudangbarang.stok', '>', '0')
            ->where('gudangbarang.kd_bangsal', $bangsal)
            ->where(function ($query) use ($que) {
                $query->where('databarang.kode_brng', 'like', $que)
                    ->orWhere('databarang.nama_brng', 'like', $que);
            })
            ->selectRaw('gudangbarang.kode_brng AS id, databarang.nama_brng AS text')
            ->get();
        return response()->json($obat, 200);
    }

    public function getObatRalan(Request $request, $poli)
    {
        $q = $request->get('q');
        $que = '%' . $q . '%';

        $depo = DB::table('set_depo_ralan')
            ->where('kd_poli', $poli)
            ->first();

        if (!$depo) {
            return response()->json([], 200);
        }

        $obat = DB::table('databarang')
            ->join('gudangbarang', 'databarang.kode_brng', '=', 'gudangbarang.kode_brng')
            ->where('databarang.status', '1')
            ->where('gudangbarang.kd_bangsal', $depo->kd_bangsal)
            ->where(function ($query) use ($que) {
                $query->where('databarang.kode_brng', 'like', $que)
                    ->orWhere('databarang.nama_brng', 'like', $que);
            })
            ->selectRaw('gudangbarang.kode_brng AS id, databarang.nama_brng AS text, gudangbarang.stok AS stok')
            ->get();
        return response()->json($obat, 200);
    }

    public function getObatLuar(Request $request)
    {
        $q = $request->get('q');
        $que = '%' . $q . '%';

        $obat = DB::table('databarang')
            ->where('status', '1')
            ->where(function ($query) use ($que) {
                $query->where('databarang.kode_brng', 'like', $que)
                    ->orWhere('databarang.nama_brng', 'like', $que);
            })
            ->selectRaw('databarang.kode_brng AS id, databarang.nama_brng AS text')
            ->get();
        return response()->json($obat, 200);
    }

    public function getDataObat(Request $request, $kdObat)
    {
        $input = $request->all();
        $status = $input['status'];
        $kode = $input['kode'];
        $bangsal = "";
        if ($status == 'ralan') {
            $db = DB::table('set_depo_ralan')->where('kd_poli', $kode)->first();
            $bangsal = $db?->kd_bangsal;
        } else {
            $db = DB::table('set_depo_ranap')->where('kd_bangsal', $kode)->first();
            $bangsal = $db?->kd_depo;
        }
        
        // Ambil stok terakhir untuk obat & depo terkait.
        // Untuk 1 obat saja, correlated subquery masih aman dan sederhana.
        $data = DB::table('databarang')
            ->leftJoin('riwayat_barang_medis as rbm', function ($join) use ($kdObat, $bangsal) {
                $join->on('databarang.kode_brng', '=', 'rbm.kode_brng')
                    ->where('rbm.kode_brng', $kdObat)
                    ->where('rbm.kd_bangsal', $bangsal)
                    ->whereRaw('(rbm.tanggal, rbm.jam) = (
                        SELECT tanggal, jam
                        FROM riwayat_barang_medis rbm2
                        WHERE rbm2.kode_brng = ?
                          AND rbm2.kd_bangsal = ?
                        ORDER BY rbm2.tanggal DESC, rbm2.jam DESC
                        LIMIT 1
                    )', [$kdObat, $bangsal]);
            })
            ->where('databarang.kode_brng', $kdObat)
            ->select('databarang.*', 'rbm.stok_akhir')
            ->first();

        return response()->json($data);
    }

    public function postResep(Request $request, $noRawat)
    {
        $startTime = microtime(true);
        $dokter = session()->get('username');
        $resObat = $request->get('obat');
        $resJml = $request->get('jumlah');
        $resAturan = $request->get('aturan_pakai');
        $status = $request->get('status');
        $kode = $request->get('kode');
        $noRawat = $this->decryptData($noRawat);
        $bangsal = "";
        $iter = $request->get('iter');

        // FIX: Pindahkan operasi resep_iter ke luar transaksi utama untuk menghindari lock timeout
        // Lakukan validasi dan prepare data terlebih dahulu
        try {
            if ($status == 'Ralan') {
                $db = DB::table('set_depo_ralan')->where('kd_poli', $kode)->first();
                if (!$db) {
                    Log::error('POST RESEP - Depo ralan tidak ditemukan', ['kd_poli' => $kode]);
                    return response()->json([
                        'status' => 'gagal',
                        'pesan' => 'Depo ralan tidak ditemukan untuk poli: ' . $kode
                    ], 400);
                }
                $bangsal = $db->kd_bangsal;
            } else {
                $db = DB::table('set_depo_ranap')->where('kd_bangsal', $kode)->first();
                if (!$db) {
                    Log::error('POST RESEP - Depo ranap tidak ditemukan', ['kd_bangsal' => $kode]);
                    return response()->json([
                        'status' => 'gagal',
                        'pesan' => 'Depo ranap tidak ditemukan untuk bangsal: ' . $kode
                    ], 400);
                }
                $bangsal = $db->kd_depo;
            }
        } catch (\Exception $e) {
            Log::error('POST RESEP - Error saat mencari depo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'status' => $status,
                'kode' => $kode,
            ]);
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Error saat mencari depo: ' . $e->getMessage()
            ], 500);
        }

        DB::beginTransaction();
        try {

            // Filter obat yang valid (jumlah > 0)
            $obatData = [];
            for ($i = 0; $i < count($resObat); $i++) {
                $jml = $resJml[$i];
                if (!empty($jml) && $jml >= 1) {
                    $obatData[] = [
                        'kode' => $resObat[$i],
                        'jumlah' => $jml,
                        'aturan' => $resAturan[$i] ?? '-',
                    ];
                }
            }

            if (empty($obatData)) {
                DB::rollback();
                Log::warning('POST RESEP - Tidak ada obat yang valid', [
                    'no_rawat' => $noRawat,
                    'res_obat' => $resObat,
                    'res_jml' => $resJml,
                ]);
                return response()->json([
                    'status' => 'gagal',
                    'pesan' => 'Tidak ada obat yang valid untuk disimpan'
                ]);
            }

            // FIX: Gunakan query yang lebih efisien untuk menghindari hang
            // Query dengan correlated subquery sangat lambat, gunakan derived table
            $obatKodes = array_column($obatData, 'kode');
            
            try {
                // FIX: Query harus mencari MAX(tanggal) dulu, lalu MAX(jam) untuk tanggal tersebut
                // Query sebelumnya salah karena mengambil MAX(tanggal) dan MAX(jam) secara terpisah
                // yang bisa menghasilkan tanggal dan jam dari record berbeda
                // Query ini menggunakan pendekatan yang lebih efisien dengan derived table
                // yang tetap memastikan tanggal dan jam berasal dari record yang sama
                $placeholders = implode(',', array_fill(0, count($obatKodes), '?'));
                
                $stokData = DB::select("
                    SELECT rbm.kode_brng, rbm.stok_akhir
                    FROM riwayat_barang_medis rbm
                    INNER JOIN (
                        SELECT 
                            kode_brng,
                            MAX(tanggal) as max_tanggal
                        FROM riwayat_barang_medis
                        WHERE kode_brng IN ($placeholders)
                        AND kd_bangsal = ?
                        GROUP BY kode_brng
                    ) AS max_tgl ON rbm.kode_brng = max_tgl.kode_brng
                        AND rbm.tanggal = max_tgl.max_tanggal
                    INNER JOIN (
                        SELECT 
                            kode_brng,
                            tanggal,
                            MAX(jam) as max_jam
                        FROM riwayat_barang_medis
                        WHERE kode_brng IN ($placeholders)
                        AND kd_bangsal = ?
                        GROUP BY kode_brng, tanggal
                    ) AS max_jam ON rbm.kode_brng = max_jam.kode_brng
                        AND rbm.tanggal = max_jam.tanggal
                        AND rbm.jam = max_jam.max_jam
                    WHERE rbm.kd_bangsal = ?
                ", array_merge($obatKodes, [$bangsal], $obatKodes, [$bangsal, $bangsal]));
                
                $stokData = collect($stokData)->keyBy('kode_brng');
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('POST RESEP - Error saat cek stok', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'obat_kodes' => $obatKodes,
                    'bangsal' => $bangsal,
                ]);
                return response()->json([
                    'status' => 'gagal',
                    'pesan' => 'Error saat mengecek stok obat: ' . $e->getMessage()
                ], 500);
            }

            // Filter obat yang stoknya mencukupi dan siapkan detail untuk yang tidak mencukupi
            $obatTersedia = [];
            $obatStokKurang = [];
            
            // Identifikasi obat yang stoknya kurang
            $obatKurangKodes = [];
            foreach ($obatData as $obat) {
                $stok = $stokData->get($obat['kode']);
                if ($stok && $stok->stok_akhir >= $obat['jumlah']) {
                    $obatTersedia[] = $obat;
                } else {
                    $obatKurangKodes[] = $obat['kode'];
                    $obatStokKurang[] = [
                        'kode' => $obat['kode'],
                        'jumlah_diminta' => $obat['jumlah'],
                        'stok_tersedia' => $stok ? $stok->stok_akhir : 0,
                    ];
                }
            }

            // Jika semua obat stoknya kurang
            if (empty($obatTersedia)) {
                DB::rollback();
                
                // FIX: Ambil nama obat untuk ditampilkan di error message
                $namaObat = [];
                if (!empty($obatKurangKodes)) {
                    $namaObat = DB::table('databarang')
                        ->whereIn('kode_brng', $obatKurangKodes)
                        ->pluck('nama_brng', 'kode_brng')
                        ->toArray();
                }
                
                // Gabungkan nama obat dengan detail stok
                $detailStok = [];
                foreach ($obatStokKurang as $obat) {
                    $detailStok[] = [
                        'kode' => $obat['kode'],
                        'nama_brng' => $namaObat[$obat['kode']] ?? 'Unknown',
                        'stok_tersedia' => $obat['stok_tersedia'],
                        'jumlah_diminta' => $obat['jumlah_diminta'],
                    ];
                }
                
                Log::warning('POST RESEP - Stok tidak mencukupi', [
                    'no_rawat' => $noRawat,
                    'bangsal' => $bangsal,
                    'detail_stok' => $detailStok,
                ]);
                
                return response()->json([
                    'status' => 'gagal',
                    'pesan' => 'Stok obat tidak mencukupi',
                    'detail_stok' => $detailStok
                ]);
            }
            
            // Jika ada yang kurang tapi ada yang cukup, tampilkan yang kurang saja
            if (!empty($obatStokKurang)) {
                // Ambil nama obat untuk yang kurang
                $namaObat = [];
                if (!empty($obatKurangKodes)) {
                    $namaObat = DB::table('databarang')
                        ->whereIn('kode_brng', $obatKurangKodes)
                        ->pluck('nama_brng', 'kode_brng')
                        ->toArray();
                }
                
                $detailStok = [];
                foreach ($obatStokKurang as $obat) {
                    $detailStok[] = [
                        'kode' => $obat['kode'],
                        'nama_brng' => $namaObat[$obat['kode']] ?? 'Unknown',
                        'stok_tersedia' => $obat['stok_tersedia'],
                        'jumlah_diminta' => $obat['jumlah_diminta'],
                    ];
                }
                
                DB::rollback();
                Log::warning('POST RESEP - Beberapa obat stok tidak mencukupi', [
                    'no_rawat' => $noRawat,
                    'detail_stok' => $detailStok,
                ]);
                
                return response()->json([
                    'status' => 'gagal',
                    'pesan' => 'Stok beberapa obat tidak mencukupi',
                    'detail_stok' => $detailStok
                ]);
            }

            // OPTIMASI: Single query untuk cek resep existing (O(log n))
            $tglPeresepan = date('Y-m-d');
            $resep = DB::table('resep_obat')
                ->where('no_rawat', $noRawat)
                ->where('tgl_peresepan', $tglPeresepan)
                ->where('kd_dokter', $dokter)
                ->first();

            if (!empty($resep) && $resep->tgl_perawatan != '0000-00-00') {
                DB::rollback();
                return response()->json([
                    'status' => 'gagal',
                    'pesan' => 'Resep obat sudah tervalidasi'
                ]);
            }

            // OPTIMASI: Single query untuk generate nomor resep (O(log n))
            $noResep = null;
            if (empty($resep)) {
                $no = DB::table('resep_obat')
                    ->where(function ($query) {
                        $query->where('tgl_perawatan', 'like', '%' . date('Y-m-d') . '%')
                            ->orWhere('tgl_peresepan', 'like', '%' . date('Y-m-d') . '%');
                    })
                    ->selectRaw("ifnull(MAX(CONVERT(RIGHT(no_resep,4),signed)),0) as resep")
                    ->first();
                $maxNo = substr($no->resep, 0, 4);
                $nextNo = sprintf('%04s', ($maxNo + 1));
                $tgl = date('Ymd');
                $noResep = $tgl . '' . $nextNo;

                // Insert resep_obat sekali saja
                DB::table('resep_obat')->insert([
                    'no_resep' => $noResep,
                    'tgl_perawatan' => '0000-00-00',
                    'jam' => '00:00:00',
                    'no_rawat' => $noRawat,
                    'kd_dokter' => $dokter,
                    'tgl_peresepan' => $tgl,
                    'jam_peresepan' => date('H:i:s'),
                    'status' => $status,
                ]);
            } else {
                $noResep = $resep->no_resep;
            }

            // OPTIMASI: Bulk insert untuk resep_dokter (O(1))
            $resepDokterData = [];
            foreach ($obatTersedia as $obat) {
                $resepDokterData[] = [
                    'no_resep' => $noResep,
                    'kode_brng' => $obat['kode'],
                    'jml' => $obat['jumlah'],
                    'aturan_pakai' => $obat['aturan'],
                ];
            }

            if (!empty($resepDokterData)) {
                try {
                    DB::table('resep_dokter')->insert($resepDokterData);
                } catch (\Exception $e) {
                    DB::rollback();
                    Log::error('POST RESEP - Error saat insert resep_dokter', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'no_resep' => $noResep,
                        'data' => $resepDokterData,
                    ]);
                    return response()->json([
                        'status' => 'gagal',
                        'pesan' => 'Error saat menyimpan resep dokter: ' . $e->getMessage()
                    ], 500);
                }
            }

            // Query untuk response
            try {
                $resep = DB::table('resep_dokter')
                    ->join('databarang', 'resep_dokter.kode_brng', '=', 'databarang.kode_brng')
                    ->join('resep_obat', 'resep_obat.no_resep', '=', 'resep_dokter.no_resep')
                    ->where('resep_obat.no_rawat', $noRawat)
                    ->where('resep_obat.kd_dokter', $dokter)
                    ->select('resep_dokter.no_resep', 'resep_dokter.kode_brng', 'resep_dokter.jml', 'databarang.nama_brng', 'resep_dokter.aturan_pakai', 'resep_obat.tgl_peresepan', 'resep_obat.jam_peresepan')
                    ->orderBy('resep_obat.jam_peresepan', 'desc')
                    ->get();
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('POST RESEP - Error saat query response', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'no_rawat' => $noRawat,
                    'dokter' => $dokter,
                ]);
                return response()->json([
                    'status' => 'gagal',
                    'pesan' => 'Error saat mengambil data resep: ' . $e->getMessage()
                ], 500);
            }
            
            DB::commit();

            // FIX: Lakukan operasi resep_iter di luar transaksi utama untuk menghindari lock timeout
            // FIX: Hanya lakukan operasi jika iter tidak kosong (tidak null, tidak empty, tidak hanya whitespace, dan bukan '-')
            $iterNotEmpty = !empty($iter) && trim($iter) !== '' && trim($iter) !== '-';
            
            if ($status == 'Ralan' && $iterNotEmpty) {
                try {
                    DB::table('resep_iter')->upsert(
                        [
                            'no_rawat' => $noRawat,
                            'catatan_iter' => trim($iter),
                        ],
                        ['no_rawat'],
                        ['catatan_iter']
                    );
                } catch (\Exception $e) {
                    // Log error tapi tidak gagalkan response karena resep sudah berhasil disimpan
                    Log::warning('POST RESEP - Gagal menyimpan resep_iter', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'no_rawat' => $noRawat,
                        'iter' => $iter,
                        'no_resep' => $noResep ?? 'N/A',
                    ]);
                }
            }

            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Input resep berhasil',
                'data' => $resep,
            ]);
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::error('POST RESEP - QueryException', [
                'error' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'sql' => $ex->getSql() ?? 'N/A',
                'bindings' => $ex->getBindings() ?? [],
                'trace' => $ex->getTraceAsString(),
                'no_rawat' => $noRawat,
                'dokter' => $dokter,
                'status' => $status,
                'execution_time_ms' => $executionTime,
            ]);
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Error database: ' . $ex->getMessage(),
                'error_code' => $ex->getCode(),
            ], 500);
        } catch (\Exception $ex) {
            DB::rollback();
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            Log::error('POST RESEP - Exception', [
                'error' => $ex->getMessage(),
                'code' => $ex->getCode(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
                'trace' => $ex->getTraceAsString(),
                'no_rawat' => $noRawat,
                'dokter' => $dokter,
                'status' => $status,
                'execution_time_ms' => $executionTime,
            ]);
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Terjadi kesalahan: ' . $ex->getMessage(),
                'error_code' => $ex->getCode(),
            ], 500);
        }
    }

    public function postResepRanap(Request $request, $noRawat)
    {
        $dokter = $request->get('dokter');
        $resObat = $request->get('obat');
        $resJml = $request->get('jumlah');
        $resAturan = $request->get('aturan_pakai');
        $status = $request->get('status');
        $kode = $request->get('kode');
        $noRawat = $this->decryptData($noRawat);
        $bangsal = "";

        if (empty($dokter)) {
            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Dokter tidak boleh kosong'
            ]);
        }

        try {
            DB::beginTransaction();
            $bangsal = $kode;

            // Filter dan prepare data obat
            $obatData = [];
            for ($i = 0; $i < count($resObat); $i++) {
                $jml = $resJml[$i] < 1 ? 1 : $resJml[$i];
                $obatData[] = [
                    'kode' => $resObat[$i],
                    'jumlah' => $jml,
                    'aturan' => $resAturan[$i] ?? '-',
                ];
            }

            if (empty($obatData)) {
                DB::rollback();
                return response()->json([
                    'status' => 'gagal',
                    'pesan' => 'Tidak ada obat yang valid untuk disimpan'
                ]);
            }

            // FIX: Gunakan query yang lebih efisien untuk menghindari hang
            // Query dengan correlated subquery sangat lambat, gunakan derived table
            $obatKodes = array_column($obatData, 'kode');
            try {
                // FIX: Query harus mencari MAX(tanggal) dulu, lalu MAX(jam) untuk tanggal tersebut
                // Query sebelumnya salah karena mengambil MAX(tanggal) dan MAX(jam) secara terpisah
                // yang bisa menghasilkan tanggal dan jam dari record berbeda
                // Query ini menggunakan pendekatan yang lebih efisien dengan derived table
                // yang tetap memastikan tanggal dan jam berasal dari record yang sama
                $placeholders = implode(',', array_fill(0, count($obatKodes), '?'));
                
                $stokData = DB::select("
                    SELECT rbm.kode_brng, rbm.stok_akhir
                    FROM riwayat_barang_medis rbm
                    INNER JOIN (
                        SELECT 
                            kode_brng,
                            MAX(tanggal) as max_tanggal
                        FROM riwayat_barang_medis
                        WHERE kode_brng IN ($placeholders)
                        AND kd_bangsal = ?
                        GROUP BY kode_brng
                    ) AS max_tgl ON rbm.kode_brng = max_tgl.kode_brng
                        AND rbm.tanggal = max_tgl.max_tanggal
                    INNER JOIN (
                        SELECT 
                            kode_brng,
                            tanggal,
                            MAX(jam) as max_jam
                        FROM riwayat_barang_medis
                        WHERE kode_brng IN ($placeholders)
                        AND kd_bangsal = ?
                        GROUP BY kode_brng, tanggal
                    ) AS max_jam ON rbm.kode_brng = max_jam.kode_brng
                        AND rbm.tanggal = max_jam.tanggal
                        AND rbm.jam = max_jam.max_jam
                    WHERE rbm.kd_bangsal = ?
                ", array_merge($obatKodes, [$bangsal], $obatKodes, [$bangsal, $bangsal]));
                
                $stokData = collect($stokData)->keyBy('kode_brng');
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('POST RESEP RANAP - Error saat cek stok', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'obat_kodes' => $obatKodes,
                    'bangsal' => $bangsal,
                ]);
                return response()->json([
                    'status' => 'gagal',
                    'pesan' => 'Error saat mengecek stok obat: ' . $e->getMessage()
                ], 500);
            }

            // Filter obat yang stoknya mencukupi dan siapkan detail untuk yang tidak mencukupi
            $obatTersedia = [];
            $obatStokKurang = [];
            
            // Identifikasi obat yang stoknya kurang
            $obatKurangKodes = [];
            foreach ($obatData as $obat) {
                $stok = $stokData->get($obat['kode']);
                if ($stok && $stok->stok_akhir >= $obat['jumlah']) {
                    $obatTersedia[] = $obat;
                } else {
                    $obatKurangKodes[] = $obat['kode'];
                    $obatStokKurang[] = [
                        'kode' => $obat['kode'],
                        'jumlah_diminta' => $obat['jumlah'],
                        'stok_tersedia' => $stok ? $stok->stok_akhir : 0,
                    ];
                }
            }

            // Jika semua obat stoknya kurang
            if (empty($obatTersedia)) {
                DB::rollback();
                
                // FIX: Ambil nama obat untuk ditampilkan di error message
                $namaObat = [];
                if (!empty($obatKurangKodes)) {
                    $namaObat = DB::table('databarang')
                        ->whereIn('kode_brng', $obatKurangKodes)
                        ->pluck('nama_brng', 'kode_brng')
                        ->toArray();
                }
                
                // Gabungkan nama obat dengan detail stok
                $detailStok = [];
                foreach ($obatStokKurang as $obat) {
                    $detailStok[] = [
                        'kode' => $obat['kode'],
                        'nama_brng' => $namaObat[$obat['kode']] ?? 'Unknown',
                        'stok_tersedia' => $obat['stok_tersedia'],
                        'jumlah_diminta' => $obat['jumlah_diminta'],
                    ];
                }
                
                Log::warning('POST RESEP RANAP - Stok tidak mencukupi', [
                    'no_rawat' => $noRawat,
                    'bangsal' => $bangsal,
                    'detail_stok' => $detailStok,
                ]);
                
                return response()->json([
                    'status' => 'gagal',
                    'pesan' => 'Stok obat tidak mencukupi',
                    'detail_stok' => $detailStok
                ]);
            }
            
            // Jika ada yang kurang tapi ada yang cukup, tampilkan yang kurang saja
            if (!empty($obatStokKurang)) {
                // Ambil nama obat untuk yang kurang
                $namaObat = [];
                if (!empty($obatKurangKodes)) {
                    $namaObat = DB::table('databarang')
                        ->whereIn('kode_brng', $obatKurangKodes)
                        ->pluck('nama_brng', 'kode_brng')
                        ->toArray();
                }
                
                $detailStok = [];
                foreach ($obatStokKurang as $obat) {
                    $detailStok[] = [
                        'kode' => $obat['kode'],
                        'nama_brng' => $namaObat[$obat['kode']] ?? 'Unknown',
                        'stok_tersedia' => $obat['stok_tersedia'],
                        'jumlah_diminta' => $obat['jumlah_diminta'],
                    ];
                }
                
                DB::rollback();
                Log::warning('POST RESEP RANAP - Beberapa obat stok tidak mencukupi', [
                    'no_rawat' => $noRawat,
                    'detail_stok' => $detailStok,
                ]);
                
                return response()->json([
                    'status' => 'gagal',
                    'pesan' => 'Stok beberapa obat tidak mencukupi',
                    'detail_stok' => $detailStok
                ]);
            }

            // OPTIMASI: Single query untuk cek resep existing (O(log n))
            $tglPeresepan = date('Y-m-d');
            $maxTglResep = DB::table('resep_obat')
                ->where('no_rawat', $noRawat)
                ->where('tgl_peresepan', $tglPeresepan)
                ->where('kd_dokter', $dokter)
                ->max('jam_peresepan');
            
            $resep = null;
            if ($maxTglResep) {
                $resep = DB::table('resep_obat')
                    ->where('no_rawat', $noRawat)
                    ->where('tgl_peresepan', $tglPeresepan)
                    ->where('kd_dokter', $dokter)
                    ->where('jam_peresepan', $maxTglResep)
                    ->first();
            }

            // OPTIMASI: Single query untuk generate nomor resep (O(log n))
            $tgl = date('Ymd');
            $noResep = null;
            
            if (!empty($resep) && $resep->tgl_perawatan != '0000-00-00') {
                // Resep sudah divalidasi, buat resep baru
                $no = DB::table('resep_obat')
                    ->where(function ($query) {
                        $query->where('tgl_perawatan', 'like', '%' . date('Y-m-d') . '%')
                            ->orWhere('tgl_peresepan', 'like', '%' . date('Y-m-d') . '%');
                    })
                    ->selectRaw("ifnull(MAX(CONVERT(RIGHT(no_resep,4),signed)),0) as resep")
                    ->first();
                $maxNo = substr($no->resep, 0, 4);
                $nextNo = sprintf('%04s', ($maxNo + 1));
                $noResep = $tgl . '' . $nextNo;

                DB::table('resep_obat')->insert([
                    'no_resep' => $noResep,
                    'tgl_perawatan' => '0000-00-00',
                    'jam' => '00:00:00',
                    'no_rawat' => $noRawat,
                    'kd_dokter' => $dokter,
                    'tgl_peresepan' => $tgl,
                    'jam_peresepan' => date('H:i:s'),
                    'status' => $status,
                ]);
            } else if (empty($resep)) {
                // Resep belum ada
                $no = DB::table('resep_obat')
                    ->where(function ($query) {
                        $query->where('tgl_perawatan', 'like', '%' . date('Y-m-d') . '%')
                            ->orWhere('tgl_peresepan', 'like', '%' . date('Y-m-d') . '%');
                    })
                    ->selectRaw("ifnull(MAX(CONVERT(RIGHT(no_resep,4),signed)),0) as resep")
                    ->first();
                $maxNo = substr($no->resep, 0, 4);
                $nextNo = sprintf('%04s', ($maxNo + 1));
                $noResep = $tgl . '' . $nextNo;

                DB::table('resep_obat')->insert([
                    'no_resep' => $noResep,
                    'tgl_perawatan' => '0000-00-00',
                    'jam' => '00:00:00',
                    'no_rawat' => $noRawat,
                    'kd_dokter' => $dokter,
                    'tgl_peresepan' => $tgl,
                    'jam_peresepan' => date('H:i:s'),
                    'status' => $status,
                ]);
            } else {
                // Resep sudah ada dan belum divalidasi
                $noResep = $resep->no_resep;
            }

            // OPTIMASI: Bulk insert untuk resep_dokter (O(1))
            $resepDokterData = [];
            foreach ($obatTersedia as $obat) {
                $resepDokterData[] = [
                    'no_resep' => $noResep,
                    'kode_brng' => $obat['kode'],
                    'jml' => $obat['jumlah'],
                    'aturan_pakai' => $obat['aturan'],
                ];
            }

            if (!empty($resepDokterData)) {
                DB::table('resep_dokter')->insert($resepDokterData);
            }

            DB::commit();
            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Input resep berhasil'
            ]);
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollback();
            return response()->json([
                'status' => 'gagal',
                'pesan' => $ex->getMessage()
            ]);
        }
    }

    public function postResepRacikan(Request $request, $noRawat)
    {
        $input = $request->all();
        $namaRacikan = $input['nama_racikan'];
        $aturanPakai = $input['aturan_racikan'];
        $jumlahRacikan = $input['jumlah_racikan'];
        $metodeRacikan = $input['metode_racikan'];
        $keteranganRacikan = $input['keterangan_racikan'];
        $iter = $input['iter'] ?? '-';
        $satu_resep = $input['satu_resep'] ?? 0;

        $kdObat = $input['kd_obat'];
        $p1 = $input['p1'];
        $p2 = $input['p2'];
        $kandungan = $input['kandungan'];
        $jml = $input['jml'];

        $no_rawat = $this->decryptData($noRawat);
        $dokter = session()->get('username');
        $status = $request->get('status');
        $kode = $request->get('kode');

        $request->validate([
            'nama_racikan' => 'required',
            'aturan_racikan' => 'required',
            'jumlah_racikan' => 'required',
            'metode_racikan' => 'required',
            'keterangan_racikan' => 'required',
            'kd_obat' => 'required',
            'kd_obat.*' => 'required',
            'p1' => 'required',
            'p1.*' => 'required',
            'p2' => 'required',
            'p2.*' => 'required',
            'kandungan' => 'required',
            'kandungan.*' => 'required',
            'jml' => 'required',
            'jml.*' => 'required',
        ], [
            'kd_obat.*.required' => 'Obat tidak boleh kosong',
            'p1.*.required' => 'P1 tidak boleh kosong',
            'p2.*.required' => 'P2 tidak boleh kosong',
            'kandungan.*.required' => 'Kandungan tidak boleh kosong',
            'jml.*.required' => 'Jumlah tidak boleh kosong',
        ]);

        // if($validate){
        //     return response()->json(['status'=>'gagal', 'message'=>'Data tidak boleh kosong', 'data' => $input]);
        // }

        try {
            DB::beginTransaction();

            $noResep = '';

            if ($satu_resep == 0) {
                $no = DB::table('resep_obat')->where('tgl_perawatan', 'like', '%' . date('Y-m-d') . '%')->orWhere('tgl_peresepan', 'like', '%' . date('Y-m-d') . '%')->selectRaw("ifnull(MAX(CONVERT(RIGHT(no_resep,4),signed)),0) as resep")->first();
                $maxNo = substr($no->resep, 0, 4);
                $nextNo = sprintf('%04s', ($maxNo + 1));
                $tgl = date('Ymd');
                $noResep = $tgl . '' . $nextNo;

                DB::table('resep_obat')
                    ->insert([
                        'no_resep' => $noResep,
                        'tgl_perawatan' => '0000-00-00',
                        'jam' => '00:00:00',
                        'no_rawat' => $no_rawat,
                        'kd_dokter' => $dokter,
                        'tgl_peresepan' => date('Y-m-d'),
                        'jam_peresepan' => date('H:i:s'),
                        'status' => 'ralan',
                        'tgl_penyerahan' => '0000-00-00',
                        'jam_penyerahan' => '00:00:00',
                    ]);
            } else {
                $resep = DB::table('resep_obat')->where('no_rawat', $no_rawat)->where('tgl_peresepan', date('Y-m-d'))->first();
                if (!empty($resep)) {
                    $noResep = $resep->no_resep;
                } else {
                    $no = DB::table('resep_obat')->where('tgl_perawatan', 'like', '%' . date('Y-m-d') . '%')->orWhere('tgl_peresepan', 'like', '%' . date('Y-m-d') . '%')->selectRaw("ifnull(MAX(CONVERT(RIGHT(no_resep,4),signed)),0) as resep")->first();
                    $maxNo = substr($no->resep, 0, 4);
                    $nextNo = sprintf('%04s', ($maxNo + 1));
                    $tgl = date('Ymd');
                    $noResep = $tgl . '' . $nextNo;

                    DB::table('resep_obat')
                        ->insert([
                            'no_resep' => $noResep,
                            'tgl_perawatan' => '0000-00-00',
                            'jam' => '00:00:00',
                            'no_rawat' => $no_rawat,
                            'kd_dokter' => $dokter,
                            'tgl_peresepan' => date('Y-m-d'),
                            'jam_peresepan' => date('H:i:s'),
                            'status' => 'ralan',
                            'tgl_penyerahan' => '0000-00-00',
                            'jam_penyerahan' => '00:00:00',
                        ]);
                }
            }

            DB::table('resep_dokter_racikan')
                ->insert([
                    'no_resep' => $noResep,
                    'no_racik' => '1',
                    'nama_racik' => $namaRacikan,
                    'kd_racik' => $metodeRacikan,
                    'jml_dr' => $jumlahRacikan,
                    'aturan_pakai' => $aturanPakai,
                    'keterangan' => $keteranganRacikan,
                ]);

            for ($i = 0; $i < count($kdObat); $i++) {
                DB::table('resep_dokter_racikan_detail')->insert([
                    'no_resep' => $noResep,
                    'no_racik' => '1',
                    'kode_brng' => $kdObat[$i],
                    'p1' => $p1[$i],
                    'p2' => $p2[$i],
                    'kandungan' => $kandungan[$i],
                    'jml' => $jml[$i],
                ]);
            }
            DB::commit();
            return response()->json(['status' => 'sukses', 'message' => 'Racikan berhasil ditambahkan']);

            // $cek = DB::table('resep_obat')
            //     ->join('resep_dokter_racikan', 'resep_obat.no_resep', '=', 'resep_dokter_racikan.no_resep')
            //     ->where('resep_obat.no_rawat', $no_rawat)->where('resep_obat.tgl_peresepan', date('Y-m-d'))
            //     ->select('resep_obat.no_resep', 'resep_obat.tgl_perawatan')
            //     ->first();

            // if (!empty($cek) && $cek->tgl_perawatan != '0000-00-00') {
            //     $noRacik = DB::table('resep_dokter_racikan')->where('no_resep', $cek->no_resep)->max('no_racik');
            //     $nextNoRacik = $noRacik + 1;
            //     $insert = DB::table('resep_dokter_racikan')
            //         ->insert([
            //             'no_resep' => $cek->no_resep,
            //             'no_racik' => $nextNoRacik,
            //             'nama_racik' => $namaRacikan,
            //             'kd_racik' => $metodeRacikan,
            //             'jml_dr' => $jumlahRacikan,
            //             'aturan_pakai' => $aturanPakai,
            //             'keterangan' => $keteranganRacikan,
            //         ]);
            //     if ($insert) {
            //         return response()->json(['status' => 'sukses', 'message' => 'Racikan berhasil ditambahkan']);
            //     }
            // } else {
            //     $insert = DB::table('resep_obat')
            //         ->insert([
            //             'no_resep' => $noResep,
            //             'tgl_perawatan' => '0000-00-00',
            //             'jam' => '00:00:00',
            //             'no_rawat' => $no_rawat,
            //             'kd_dokter' => $dokter,
            //             'tgl_peresepan' => date('Y-m-d'),
            //             'jam_peresepan' => date('H:i:s'),
            //             'status' => 'ralan',
            //             'tgl_penyerahan' => '0000-00-00',
            //             'jam_penyerahan' => '00:00:00',
            //         ]);
            //     if ($insert) {
            //         $insert = DB::table('resep_dokter_racikan')
            //             ->insert([
            //                 'no_resep' => $noResep,
            //                 'no_racik' => '1',
            //                 'nama_racik' => $namaRacikan,
            //                 'kd_racik' => $metodeRacikan,
            //                 'jml_dr' => $jumlahRacikan,
            //                 'aturan_pakai' => $aturanPakai,
            //                 'keterangan' => $keteranganRacikan,
            //             ]);
            //         if ($insert) {
            //             for ($i = 0; $i < count($kdObat); $i++) {
            //                 DB::table('resep_dokter_racikan_detail')->insert([
            //                     'no_resep' => $noResep,
            //                     'no_racik' => '1',
            //                     'kode_brng' => $kdObat[$i],
            //                     'p1' => $p1[$i],
            //                     'p2' => $p2[$i],
            //                     'kandungan' => $kandungan[$i],
            //                     'jml' => $jml[$i],
            //                 ]);
            //             }
            //             DB::commit();
            //             return response()->json(['status' => 'sukses', 'message' => 'Racikan berhasil ditambahkan']);
            //         }
            //     } else {
            //         DB::rollBack();
            //         return response()->json(['status' => 'gagal', 'message' => 'Racikan gagal ditambahkan']);
            //     }
            // }
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return response()->json(['status' => 'gagal', 'message' => 'Maaf ada obat masih kosong']);
        }
    }

    public function postResepRacikanRanap(Request $request, $noRawat)
    {
        $input = $request->all();
        $namaRacikan = $input['nama_racikan'];
        $aturanPakai = $input['aturan_racikan'];
        $jumlahRacikan = $input['jumlah_racikan'];
        $metodeRacikan = $input['metode_racikan'];
        $keteranganRacikan = $input['keterangan_racikan'];

        $kdObat = $input['kd_obat'];
        $p1 = $input['p1'];
        $p2 = $input['p2'];
        $kandungan = $input['kandungan'];
        $jml = $input['jml'];

        $no_rawat = $this->decryptData($noRawat);
        $dokter = session()->get('username');

        $request->validate([
            'nama_racikan' => 'required',
            'aturan_racikan' => 'required',
            'jumlah_racikan' => 'required',
            'metode_racikan' => 'required',
            'keterangan_racikan' => 'required',
            'kd_obat' => 'required',
            'kd_obat.*' => 'required',
            'p1' => 'required',
            'p1.*' => 'required',
            'p2' => 'required',
            'p2.*' => 'required',
            'kandungan' => 'required',
            'kandungan.*' => 'required',
            'jml' => 'required',
            'jml.*' => 'required',
        ]);

        // if($validate){
        //     return response()->json(['status'=>'gagal', 'message'=>'Data tidak boleh kosong', 'data' => $input]);
        // }

        try {
            DB::beginTransaction();
            $no = DB::table('resep_obat')->where('tgl_perawatan', 'like', '%' . date('Y-m-d') . '%')->orWhere('tgl_peresepan', 'like', '%' . date('Y-m-d') . '%')->selectRaw("ifnull(MAX(CONVERT(RIGHT(no_resep,4),signed)),0) as resep")->first();
            $maxNo = substr($no->resep, 0, 4);
            $nextNo = sprintf('%04s', ($maxNo + 1));
            $tgl = date('Ymd');
            $noResep = $tgl . '' . $nextNo;

            DB::table('resep_obat')
                ->insert([
                    'no_resep' => $noResep,
                    'tgl_perawatan' => '0000-00-00',
                    'jam' => '00:00:00',
                    'no_rawat' => $no_rawat,
                    'kd_dokter' => $dokter,
                    'tgl_peresepan' => date('Y-m-d'),
                    'jam_peresepan' => date('H:i:s'),
                    'status' => 'ranap',
                    'tgl_penyerahan' => '0000-00-00',
                    'jam_penyerahan' => '00:00:00',
                ]);

            DB::table('resep_dokter_racikan')
                ->insert([
                    'no_resep' => $noResep,
                    'no_racik' => '1',
                    'nama_racik' => $namaRacikan,
                    'kd_racik' => $metodeRacikan,
                    'jml_dr' => $jumlahRacikan,
                    'aturan_pakai' => $aturanPakai,
                    'keterangan' => $keteranganRacikan,
                ]);

            for ($i = 0; $i < count($kdObat); $i++) {
                DB::table('resep_dokter_racikan_detail')->insert([
                    'no_resep' => $noResep,
                    'no_racik' => '1',
                    'kode_brng' => $kdObat[$i],
                    'p1' => $p1[$i],
                    'p2' => $p2[$i],
                    'kandungan' => $kandungan[$i],
                    'jml' => $jml[$i],
                ]);
            }
            DB::commit();
            return response()->json(['status' => 'sukses', 'message' => 'Racikan berhasil ditambahkan']);

            // $cek = DB::table('resep_obat')
            //     ->join('resep_dokter_racikan', 'resep_obat.no_resep', '=', 'resep_dokter_racikan.no_resep')
            //     ->where('resep_obat.no_rawat', $no_rawat)->where('resep_obat.tgl_peresepan', date('Y-m-d'))
            //     ->select('resep_obat.no_resep')
            //     ->first();

            // if (!empty($cek)) {
            //     $noRacik = DB::table('resep_dokter_racikan')->where('no_resep', $cek->no_resep)->max('no_racik');
            //     $nextNoRacik = $noRacik + 1;
            //     $insert = DB::table('resep_dokter_racikan')
            //         ->insert([
            //             'no_resep' => $cek->no_resep,
            //             'no_racik' => $nextNoRacik,
            //             'nama_racik' => $namaRacikan,
            //             'kd_racik' => $metodeRacikan,
            //             'jml_dr' => $jumlahRacikan,
            //             'aturan_pakai' => $aturanPakai,
            //             'keterangan' => $keteranganRacikan,
            //         ]);
            //     if ($insert) {
            //         return response()->json(['status' => 'sukses', 'message' => 'Racikan berhasil ditambahkan']);
            //     }
            // } else {
            //     $insert = DB::table('resep_obat')
            //         ->insert([
            //             'no_resep' => $noResep,
            //             'tgl_perawatan' => '0000-00-00',
            //             'jam' => '00:00:00',
            //             'no_rawat' => $no_rawat,
            //             'kd_dokter' => $dokter,
            //             'tgl_peresepan' => date('Y-m-d'),
            //             'jam_peresepan' => date('H:i:s'),
            //             'status' => 'ranap',
            //             'tgl_penyerahan' => '0000-00-00',
            //             'jam_penyerahan' => '00:00:00',
            //         ]);
            //     if ($insert) {
            //         $insert = DB::table('resep_dokter_racikan')
            //             ->insert([
            //                 'no_resep' => $noResep,
            //                 'no_racik' => '1',
            //                 'nama_racik' => $namaRacikan,
            //                 'kd_racik' => $metodeRacikan,
            //                 'jml_dr' => $jumlahRacikan,
            //                 'aturan_pakai' => $aturanPakai,
            //                 'keterangan' => $keteranganRacikan,
            //             ]);
            //         if ($insert) {
            //             for ($i = 0; $i < count($kdObat); $i++) {
            //                 DB::table('resep_dokter_racikan_detail')->insert([
            //                     'no_resep' => $noResep,
            //                     'no_racik' => '1',
            //                     'kode_brng' => $kdObat[$i],
            //                     'p1' => $p1[$i],
            //                     'p2' => $p2[$i],
            //                     'kandungan' => $kandungan[$i],
            //                     'jml' => $jml[$i],
            //                 ]);
            //             }

            //             return response()->json(['status' => 'sukses', 'message' => 'Racikan berhasil ditambahkan']);
            //         }
            //     } else {
            //         return response()->json(['status' => 'gagal', 'message' => 'Racikan gagal ditambahkan']);
            //     }
            // }
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            return response()->json(['status' => 'gagal', 'message' => $ex->getMessage()]);
        }
    }

    public function hapusObat($noResep, $kdObat, $noRawat)
    {
        $dokter = session()->get('username');
        $noRawat = $this->decryptData($noRawat);
        try {
            $cek = DB::table('resep_obat')->where('no_resep', $noResep)->first();
            if ($cek->tgl_perawatan != '0000-00-00') {
                return response()->json(['status' => 'gagal', 'pesan' => 'Resep sudah tervalidasi, silahkan hubungi farmasi untuk menghapus obat']);
            }
            DB::table('resep_dokter')->where('no_resep', $noResep)->where('kode_brng', $kdObat)->delete();
            $resep = DB::table('resep_dokter')
                ->join('databarang', 'resep_dokter.kode_brng', '=', 'databarang.kode_brng')
                ->join('resep_obat', 'resep_obat.no_resep', '=', 'resep_dokter.no_resep')
                ->where('resep_obat.no_rawat', $noRawat)
                ->where('resep_obat.kd_dokter', $dokter)
                ->select('resep_dokter.no_resep', 'resep_dokter.kode_brng', 'resep_dokter.jml', 'databarang.nama_brng', 'resep_dokter.aturan_pakai', 'resep_dokter.no_resep', 'databarang.nama_brng', 'resep_obat.tgl_peresepan', 'resep_obat.jam_peresepan')
                ->get();
            return response()->json(['status' => 'sukses', 'pesan' => 'Obat berhasil dihapus', 'data' => $resep]);
        } catch (\Exception $ex) {
            return response()->json(['status' => 'gagal', 'pesan' => $ex->getMessage()]);
        }
    }

    public function hapusObatBatch(Request $request)
    {
        $dokter = session()->get('username');
        $noRawat = $this->decryptData($request->get('no_rawat'));
        $noResep = $request->get('no_resep');
        $kdObat = $request->get('obat');
        // return response()->json(['status' => 'sukses', 'pesan' => 'Obat berhasil dihapus', 'data' => $kdObat]);
        try {
            DB::beginTransaction();
            $cek = DB::table('resep_obat')->where('no_resep', $noResep)->first();
            if ($cek->tgl_perawatan != '0000-00-00') {
                return response()->json(['status' => 'gagal', 'pesan' => 'Resep sudah tervalidasi, silahkan hubungi farmasi untuk menghapus obat']);
            }
            foreach ($kdObat as $key => $value) {
                DB::table('resep_dokter')->where('no_resep', $noResep)->where('kode_brng', $kdObat[$key])->delete();
            }
            DB::commit();
            DB::table('resep_dokter')->where('no_resep', $noResep)->where('kode_brng', $kdObat)->delete();
            $resep = DB::table('resep_dokter')
                ->join('databarang', 'resep_dokter.kode_brng', '=', 'databarang.kode_brng')
                ->join('resep_obat', 'resep_obat.no_resep', '=', 'resep_dokter.no_resep')
                ->where('resep_obat.no_rawat', $noRawat)
                ->where('resep_obat.kd_dokter', $dokter)
                ->select('resep_dokter.no_resep', 'resep_dokter.kode_brng', 'resep_dokter.jml', 'databarang.nama_brng', 'resep_dokter.aturan_pakai', 'resep_dokter.no_resep', 'databarang.nama_brng', 'resep_obat.tgl_peresepan', 'resep_obat.jam_peresepan')
                ->get();
            return response()->json(['status' => 'sukses', 'pesan' => 'Obat berhasil dihapus', 'data' => $resep]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json(['status' => 'gagal', 'pesan' => $ex->getMessage()]);
        }
    }

    /**
     * Get riwayat peresepan untuk DataTable AJAX
     */
    public function getRiwayatPeresepan(Request $request)
    {
        try {
            $noRM = $request->get('no_rm');
            
            if (empty($noRM)) {
                return response()->json([
                    'draw' => intval($request->get('draw', 1)),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]);
            }

            // Query untuk mendapatkan riwayat peresepan
            $query = DB::table('reg_periksa')
                ->join('resep_obat', 'reg_periksa.no_rawat', '=', 'resep_obat.no_rawat')
                ->join('dokter', 'resep_obat.kd_dokter', '=', 'dokter.kd_dokter')
                ->where('reg_periksa.no_rkm_medis', $noRM)
                ->where('reg_periksa.status_lanjut', 'Ralan');

            // Get total records (before filtering)
            $recordsTotal = (clone $query)->count();

            // Apply ordering
            $orderData = $request->get('order', []);
            $orderColumn = isset($orderData[0]['column']) ? intval($orderData[0]['column']) : 2;
            $orderDir = isset($orderData[0]['dir']) ? $orderData[0]['dir'] : 'desc';
            
            $orderColumns = [
                0 => 'resep_obat.no_resep',
                1 => 'dokter.nm_dokter',
                2 => 'resep_obat.tgl_peresepan',
            ];
            
            $orderBy = $orderColumns[$orderColumn] ?? 'resep_obat.tgl_peresepan';
            
            $data = $query->select('resep_obat.no_resep', 'resep_obat.tgl_peresepan', 'dokter.nm_dokter')
                ->orderBy($orderBy, $orderDir)
                ->orderByDesc('resep_obat.jam_peresepan')
                ->get();

            // Format data untuk DataTable
            $formattedData = [];
            foreach ($data as $r) {
                // Get detail obat untuk setiap resep
                $resepObat = DB::table('resep_dokter')
                    ->join('databarang', 'resep_dokter.kode_brng', '=', 'databarang.kode_brng')
                    ->where('resep_dokter.no_resep', $r->no_resep)
                    ->select('databarang.nama_brng', 'resep_dokter.jml', 'resep_dokter.aturan_pakai')
                    ->get();

                // Format detail resep sebagai HTML
                $detailResep = '<ul class="p-4" style="margin: 0; padding-left: 20px;">';
                foreach ($resepObat as $ro) {
                    $detailResep .= '<li>' . htmlspecialchars($ro->nama_brng) . ' - ' . $ro->jml . ' - [' . htmlspecialchars($ro->aturan_pakai) . ']</li>';
                }
                $detailResep .= '</ul>';

                // Format tombol aksi (Copy dan Hapus)
                $aksi = '<button class="btn btn-primary btn-sm mr-1" onclick="getCopyResep(' . $r->no_resep . ', event)" title="Copy Resep"><i class="fa fa-pen"></i></button>';
                $aksi .= '<button class="btn btn-danger btn-sm" onclick="hapusResep(' . $r->no_resep . ', event)" title="Hapus Resep"><i class="fa fa-trash"></i></button>';

                $formattedData[] = [
                    $r->no_resep,
                    $r->nm_dokter,
                    $r->tgl_peresepan,
                    $detailResep,
                    $aksi
                ];
            }

            return response()->json([
                'draw' => intval($request->get('draw', 1)),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsTotal,
                'data' => $formattedData
            ]);

        } catch (\Exception $e) {
            Log::error('GET RIWAYAT PERESEPAN - Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'no_rm' => $request->get('no_rm'),
            ]);

            return response()->json([
                'draw' => intval($request->get('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error loading data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus resep dari tabel resep_obat
     */
    public function hapusResep(Request $request, $noResep)
    {
        try {
            DB::beginTransaction();

            // Cek apakah resep sudah tervalidasi
            $resep = DB::table('resep_obat')->where('no_resep', $noResep)->first();
            
            if (!$resep) {
                DB::rollBack();
                return response()->json([
                    'status' => 'gagal',
                    'pesan' => 'Resep tidak ditemukan'
                ], 404);
            }

            if ($resep->tgl_perawatan != '0000-00-00') {
                DB::rollBack();
                return response()->json([
                    'status' => 'gagal',
                    'pesan' => 'Resep sudah tervalidasi, silahkan hubungi farmasi untuk menghapus resep'
                ]);
            }

            // Hapus resep_dokter (detail obat) terlebih dahulu
            DB::table('resep_dokter')->where('no_resep', $noResep)->delete();
            
            // Hapus resep_dokter_racikan_detail jika ada
            DB::table('resep_dokter_racikan_detail')->where('no_resep', $noResep)->delete();
            
            // Hapus resep_dokter_racikan jika ada
            DB::table('resep_dokter_racikan')->where('no_resep', $noResep)->delete();
            
            // Hapus resep_obat
            DB::table('resep_obat')->where('no_resep', $noResep)->delete();

            DB::commit();

            return response()->json([
                'status' => 'sukses',
                'pesan' => 'Resep berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('HAPUS RESEP - Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'no_resep' => $noResep,
            ]);

            return response()->json([
                'status' => 'gagal',
                'pesan' => 'Error saat menghapus resep: ' . $e->getMessage()
            ], 500);
        }
    }
}
