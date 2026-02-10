<?php

namespace App\Http\Controllers\Ranap;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PasienRanapController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('loginauth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $statusPasien = $request->status ?? 'belum_pulang';
        $tanggalMulai = $request->tanggal_mulai ?? date('Y-m-01');
        $tanggalAkhir = $request->tanggal_akhir ?? date('Y-m-d');
        $kd_dokter = session()->get('username');
        $kd_sps = session()->get('kd_sps');
        // Kolom header tabel pasien ranap
        $heads = ['Nama', 'No Rawat Ibu', 'No. RM', 'Kamar', 'Bed', 'Tanggal Masuk', 'Cara Bayar'];

        // Proses data dengan struktur hierarkis (ibu-anak)
        $rows = $this->buildPasienRanapQuery($statusPasien, $tanggalMulai, $tanggalAkhir, $kd_dokter, $kd_sps)->get();
        $grouped = $rows->groupBy('no_rawat_ibu');
        
        $processedData = [];
        
        foreach ($grouped as $noRawatIbu => $group) {
            $row = $group->first();
            
            // Data ibu
            $noRawatIbuEnc = self::encryptData($row->no_rawat_ibu);
            $noRMEnc = self::encryptData($row->no_rkm_medis);
            
            $processedData[] = [
                'type' => 'ibu',
                'nama' => $row->nm_pasien,
                'nama_link' => route('ranap.pemeriksaan', [
                    'no_rawat' => $noRawatIbuEnc,
                    'no_rm' => $noRMEnc,
                    'bangsal' => $row->kd_bangsal,
                ]),
                'no_rawat' => $row->no_rawat_ibu,
                'no_rawat_enc' => $noRawatIbuEnc,
                'no_rkm_medis' => $row->no_rkm_medis,
                'no_rm_enc' => $noRMEnc,
                'nm_bangsal' => $row->nm_bangsal,
                'kd_kamar' => $row->kd_kamar,
                'tgl_masuk' => $row->tgl_masuk,
                'png_jawab' => $row->png_jawab,
                'kd_bangsal' => $row->kd_bangsal,
            ];
            
            // Data anak
            $anakList = DB::table('ranap_gabung')
                ->where('no_rawat', $noRawatIbu)
                ->whereNotNull('no_rawat2')
                ->where('no_rawat2', '!=', '')
                ->pluck('no_rawat2')
                ->filter(function($value) {
                    return !empty($value);
                })
                ->unique()
                ->values();
            
            foreach ($anakList as $noRawatAnak) {
                $anakData = $this->getAnakData($noRawatAnak, $statusPasien, $tanggalMulai, $tanggalAkhir, $kd_dokter, $kd_sps);
                
                if (!$anakData) {
                    $anakData = DB::table('reg_periksa')
                        ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
                        ->leftJoin('penjab', 'penjab.kd_pj', '=', 'reg_periksa.kd_pj')
                        ->where('reg_periksa.no_rawat', $noRawatAnak)
                        ->select(
                            'pasien.nm_pasien',
                            'reg_periksa.no_rkm_medis',
                            DB::raw("'-' as nm_bangsal"),
                            DB::raw("'-' as kd_kamar"),
                            DB::raw("'-' as tgl_masuk"),
                            DB::raw('COALESCE(penjab.png_jawab, "-") as png_jawab'),
                            DB::raw("'-' as kd_bangsal")
                        )
                        ->first();
                }
                
                if ($anakData && !empty($anakData->nm_pasien)) {
                    $noRawatAnakEnc = self::encryptData($noRawatAnak);
                    $noRMAnakEnc = self::encryptData($anakData->no_rkm_medis);
                    $kdBangsalAnak = ($anakData->kd_bangsal && $anakData->kd_bangsal != '-') ? $anakData->kd_bangsal : $row->kd_bangsal;
                    
                    $processedData[] = [
                        'type' => 'anak',
                        'nama' => $anakData->nm_pasien,
                        'nama_link' => route('ranap.pemeriksaan', [
                            'no_rawat' => $noRawatAnakEnc,
                            'no_rm' => $noRMAnakEnc,
                            'bangsal' => $kdBangsalAnak,
                        ]),
                        'no_rawat' => $noRawatAnak,
                        'no_rawat_enc' => $noRawatAnakEnc,
                        'no_rkm_medis' => $anakData->no_rkm_medis ?? '-',
                        'no_rm_enc' => $noRMAnakEnc,
                        'nm_bangsal' => $anakData->nm_bangsal ?? '-',
                        'kd_kamar' => $anakData->kd_kamar ?? '-',
                        'tgl_masuk' => $anakData->tgl_masuk ?? '-',
                        'png_jawab' => $anakData->png_jawab ?? '-',
                        'kd_bangsal' => $kdBangsalAnak,
                    ];
                }
            }
        }

        return view('ranap.pasien-ranap', [
            'heads' => $heads,
            'data' => $processedData,
        ]);
    }

    /**
     * Endpoint data untuk DataTables AJAX.
     */
    public function data(Request $request)
    {
        $statusPasien = $request->status ?? 'belum_pulang';
        $tanggalMulai = $request->tanggal_mulai ?? date('Y-m-01');
        $tanggalAkhir = $request->tanggal_akhir ?? date('Y-m-d');
        $kd_dokter    = session()->get('username');
        $kd_sps       = session()->get('kd_sps');

        $rows = $this->buildPasienRanapQuery($statusPasien, $tanggalMulai, $tanggalAkhir, $kd_dokter, $kd_sps)->get();

        // Grouping per no_rawat_ibu, anak ditampilkan sebagai baris terpisah di bawah ibu
        $grouped = $rows->groupBy('no_rawat_ibu');

        $result = collect();

        foreach ($grouped as $noRawatIbu => $group) {
            $row = $group->first();

            $noRawatIbuEnc  = self::encryptData($row->no_rawat_ibu);
            $noRMEnc        = self::encryptData($row->no_rkm_medis);

            $namaLink = '<a class="text-primary" href="' . route('ranap.pemeriksaan', [
                'no_rawat' => $noRawatIbuEnc,
                'no_rm'    => $noRMEnc,
                'bangsal'  => $row->kd_bangsal,
            ]) . '">' . e($row->nm_pasien) . '</a>';

            // Dropdown aksi tetap pakai no_rawat ibu
            $dropdown = '
                <div class="dropdown">
                    <button id="my-dropdown-' . e($row->no_rawat_ibu) . '" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">'
                        . e($row->no_rawat_ibu) .
                    '</button>
                    <div class="dropdown-menu" aria-labelledby="my-dropdown-' . e($row->no_rawat_ibu) . '">
                        <button id="' . e($row->no_rawat_ibu) . '" class="dropdown-item btn-awal-medis-ranap"> Penilaian Awal Medis Ranap</button>
                        <a class="dropdown-item" href="' . route('ralan.pemeriksaan', [
                            'no_rawat' => $noRawatIbuEnc,
                            'no_rm'    => $noRMEnc,
                        ]) . '">Pemeriksaan Ralan</a>
                    </div>
                </div>';

            // Baris untuk ibu
            $result->push([
                'nama'          => $namaLink,
                'no_rawat_ibu'  => $dropdown,
                'no_rkm_medis'  => $row->no_rkm_medis,
                'nm_bangsal'    => $row->nm_bangsal,
                'kd_kamar'      => $row->kd_kamar,
                'tgl_masuk'     => $row->tgl_masuk,
                'png_jawab'     => $row->png_jawab,
            ]);

            // Ambil semua no_rawat anak dari ranap_gabung untuk ibu ini
            $anakList = DB::table('ranap_gabung')
                ->where('no_rawat', $noRawatIbu)
                ->whereNotNull('no_rawat2')
                ->where('no_rawat2', '!=', '')
                ->pluck('no_rawat2')
                ->filter(function($value) {
                    return !empty($value);
                })
                ->unique()
                ->values();

            // Buat baris terpisah untuk setiap anak
            foreach ($anakList as $noRawatAnak) {
                // Ambil data anak - coba dari getAnakData dulu, jika tidak ada ambil dari reg_periksa
                $anakData = $this->getAnakData($noRawatAnak, $statusPasien, $tanggalMulai, $tanggalAkhir, $kd_dokter, $kd_sps);
                
                // Jika data anak tidak ditemukan, ambil data minimal dari reg_periksa dan pasien
                if (!$anakData) {
                    $anakData = DB::table('reg_periksa')
                        ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
                        ->leftJoin('penjab', 'penjab.kd_pj', '=', 'reg_periksa.kd_pj')
                        ->where('reg_periksa.no_rawat', $noRawatAnak)
                        ->select(
                            'pasien.nm_pasien',
                            'reg_periksa.no_rkm_medis',
                            DB::raw("'-' as nm_bangsal"),
                            DB::raw("'-' as kd_kamar"),
                            DB::raw("'-' as tgl_masuk"),
                            DB::raw('COALESCE(penjab.png_jawab, "-") as png_jawab'),
                            DB::raw("'-' as kd_bangsal")
                        )
                        ->first();
                }
                
                // Pastikan data anak ada sebelum menambahkan ke result
                if ($anakData && !empty($anakData->nm_pasien)) {
                    $noRawatAnakEnc = self::encryptData($noRawatAnak);
                    $noRMAnakEnc = self::encryptData($anakData->no_rkm_medis);
                    $kdBangsalAnak = ($anakData->kd_bangsal && $anakData->kd_bangsal != '-') ? $anakData->kd_bangsal : $row->kd_bangsal;

                    $namaAnakLink = '<a class="text-primary" href="' . route('ranap.pemeriksaan', [
                        'no_rawat' => $noRawatAnakEnc,
                        'no_rm'    => $noRMAnakEnc,
                        'bangsal'  => $kdBangsalAnak,
                    ]) . '">' . e($anakData->nm_pasien) . '</a>';

                    $dropdownAnak = '
                        <div class="dropdown">
                            <button id="my-dropdown-' . e($noRawatAnak) . '" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">'
                                . e($noRawatAnak) .
                            '</button>
                            <div class="dropdown-menu" aria-labelledby="my-dropdown-' . e($noRawatAnak) . '">
                                <button id="' . e($noRawatAnak) . '" class="dropdown-item btn-awal-medis-ranap"> Penilaian Awal Medis Ranap</button>
                                <a class="dropdown-item" href="' . route('ralan.pemeriksaan', [
                                    'no_rawat' => $noRawatAnakEnc,
                                    'no_rm'    => $noRMAnakEnc,
                                ]) . '">Pemeriksaan Ralan</a>
                            </div>
                        </div>';

                    // Baris untuk anak (indentasi visual dengan menampilkan no_rawat anak di kolom No Rawat Ibu)
                    $result->push([
                        'nama'          => '<span class="ml-3 text-muted">└─ </span>' . $namaAnakLink,
                        'no_rawat_ibu'  => $dropdownAnak,
                        'no_rkm_medis'  => $anakData->no_rkm_medis ?? '-',
                        'nm_bangsal'    => $anakData->nm_bangsal ?? '-',
                        'kd_kamar'      => $anakData->kd_kamar ?? '-',
                        'tgl_masuk'     => $anakData->tgl_masuk ?? '-',
                        'png_jawab'     => $anakData->png_jawab ?? '-',
                    ]);
                }
            }
        }

        return response()->json(['data' => $result->values()]);
    }

    /**
     * Build query pasien ranap termasuk gabungan ibu-anak (ranap_gabung).
     */
    private function buildPasienRanapQuery(string $statusPasien, string $tanggalMulai, string $tanggalAkhir, string $kd_dokter, ?string $kd_sps)
    {
        $isSpesial = in_array($kd_dokter, ['86062112', 'SP0000005', 'SP0000002', 'SP0000006']) || $kd_sps === 'S004';

        if ($isSpesial) {
            if ($statusPasien == 'belum_pulang') {
                // Belum pulang: tidak gunakan filter tanggal
                $query = DB::table('kamar_inap')
                    ->join('reg_periksa', 'reg_periksa.no_rawat', '=', 'kamar_inap.no_rawat')
                    ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
                    ->join('kamar', 'kamar.kd_kamar', '=', 'kamar_inap.kd_kamar')
                    ->join('bangsal', 'bangsal.kd_bangsal', '=', 'kamar.kd_bangsal')
                    ->join('penjab', 'penjab.kd_pj', '=', 'reg_periksa.kd_pj')
                    ->where('kamar_inap.stts_pulang', '-');
            } else {
                // Sudah pulang: gunakan filter tanggal
                $query = DB::table('kamar_inap')
                    ->join('reg_periksa', 'reg_periksa.no_rawat', '=', 'kamar_inap.no_rawat')
                    ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
                    ->join('kamar', 'kamar.kd_kamar', '=', 'kamar_inap.kd_kamar')
                    ->join('bangsal', 'bangsal.kd_bangsal', '=', 'kamar.kd_bangsal')
                    ->join('penjab', 'penjab.kd_pj', '=', 'reg_periksa.kd_pj')
                    ->whereBetween('kamar_inap.tgl_masuk', [$tanggalMulai, $tanggalAkhir])
                    ->whereNotIn('kamar_inap.stts_pulang', ['-', 'Pindah Kamar']);
            }
        } else {
            if ($statusPasien == 'belum_pulang') {
                // Belum pulang: tidak gunakan filter tanggal
                $query = DB::table('kamar_inap')
                    ->join('reg_periksa', 'reg_periksa.no_rawat', '=', 'kamar_inap.no_rawat')
                    ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
                    ->join('kamar', 'kamar.kd_kamar', '=', 'kamar_inap.kd_kamar')
                    ->join('bangsal', 'bangsal.kd_bangsal', '=', 'kamar.kd_bangsal')
                    ->join('penjab', 'penjab.kd_pj', '=', 'reg_periksa.kd_pj')
                    ->join('dpjp_ranap', 'dpjp_ranap.no_rawat', '=', 'reg_periksa.no_rawat')
                    ->where('kamar_inap.stts_pulang', '-')
                    ->where('dpjp_ranap.kd_dokter', $kd_dokter);
            } else {
                // Sudah pulang: gunakan filter tanggal
                $query = DB::table('kamar_inap')
                    ->join('reg_periksa', 'reg_periksa.no_rawat', '=', 'kamar_inap.no_rawat')
                    ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
                    ->join('kamar', 'kamar.kd_kamar', '=', 'kamar_inap.kd_kamar')
                    ->join('bangsal', 'bangsal.kd_bangsal', '=', 'kamar.kd_bangsal')
                    ->join('penjab', 'penjab.kd_pj', '=', 'reg_periksa.kd_pj')
                    ->join('dpjp_ranap', 'dpjp_ranap.no_rawat', '=', 'reg_periksa.no_rawat')
                    ->where('dpjp_ranap.kd_dokter', $kd_dokter)
                    ->whereBetween('kamar_inap.tgl_masuk', [$tanggalMulai, $tanggalAkhir])
                    ->where('kamar_inap.stts_pulang', '!=', '-');
            }
        }

        // JOIN ranap_gabung: no_rawat (ibu), no_rawat2 (anak)
        $query->leftJoin('ranap_gabung', 'ranap_gabung.no_rawat', '=', 'reg_periksa.no_rawat');

        return $query->select(
            'pasien.nm_pasien',
            'reg_periksa.no_rkm_medis',
            'bangsal.nm_bangsal',
            'kamar_inap.kd_kamar',
            'kamar_inap.tgl_masuk',
            'penjab.png_jawab',
            'reg_periksa.no_rawat as no_rawat_ibu',
            'ranap_gabung.no_rawat2 as no_rawat_anak',
            'bangsal.kd_bangsal'
        );
    }

    /**
     * Ambil data lengkap untuk pasien anak berdasarkan no_rawat.
     * Data anak diambil tanpa filter dokter dan status agar tetap muncul jika ada di ranap_gabung.
     */
    private function getAnakData(string $noRawatAnak, string $statusPasien, string $tanggalMulai, string $tanggalAkhir, string $kd_dokter, ?string $kd_sps)
    {
        // Ambil data anak dari reg_periksa dan tabel terkait
        // Gunakan LEFT JOIN untuk kamar_inap karena anak mungkin sudah pulang atau tidak ada di kamar_inap
        $query = DB::table('reg_periksa')
            ->join('pasien', 'pasien.no_rkm_medis', '=', 'reg_periksa.no_rkm_medis')
            ->leftJoin('kamar_inap', 'kamar_inap.no_rawat', '=', 'reg_periksa.no_rawat')
            ->leftJoin('kamar', 'kamar.kd_kamar', '=', 'kamar_inap.kd_kamar')
            ->leftJoin('bangsal', 'bangsal.kd_bangsal', '=', 'kamar.kd_bangsal')
            ->leftJoin('penjab', 'penjab.kd_pj', '=', 'reg_periksa.kd_pj')
            ->where('reg_periksa.no_rawat', $noRawatAnak)
            ->orderBy('kamar_inap.tgl_masuk', 'desc'); // Ambil yang terbaru jika ada beberapa record

        $result = $query->select(
            'pasien.nm_pasien',
            'reg_periksa.no_rkm_medis',
            DB::raw('COALESCE(bangsal.nm_bangsal, "-") as nm_bangsal'),
            DB::raw('COALESCE(kamar_inap.kd_kamar, "-") as kd_kamar'),
            DB::raw('COALESCE(kamar_inap.tgl_masuk, "-") as tgl_masuk'),
            DB::raw('COALESCE(penjab.png_jawab, "-") as png_jawab'),
            DB::raw('COALESCE(bangsal.kd_bangsal, "-") as kd_bangsal')
        )->first();

        return $result;
    }

    private function getPoliklinik($kd_poli)
    {
        $poli = DB::table('poliklinik')->where('kd_poli', $kd_poli)->first();
        return $poli->nm_poli;
    }

    public static function encryptData($data)
    {
        $data = Crypt::encrypt($data);
        return $data;
    }
}
