<?php

namespace App\Http\Livewire\Rekap;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class TindakanDokter extends Component
{
    use WithPagination;

    public $tanggalMulai;
    public $tanggalAkhir;
    public $activeTab = 'ralan'; // 'ralan' atau 'ranap'
    public $perPage = 10;
    public $page = 1;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->tanggalMulai = date('Y-m-01');
        $this->tanggalAkhir = date('Y-m-d');
    }

    public function updatingTanggalMulai()
    {
        $this->page = 1;
    }

    public function updatingTanggalAkhir()
    {
        $this->page = 1;
    }

    public function updatingActiveTab()
    {
        $this->page = 1;
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->page = 1;
    }

    public function resetFilter()
    {
        $this->tanggalMulai = date('Y-m-01');
        $this->tanggalAkhir = date('Y-m-d');
        $this->page = 1;
    }

    public function gotoPage($page)
    {
        $this->page = (int)$page;
    }

    /**
     * Ambil data tindakan radiologi berdasarkan dokter dan periode.
     */
    private function getDataRadiologi()
    {
        return DB::table('periksa_radiologi')
            ->join('reg_periksa', 'periksa_radiologi.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('jns_perawatan_radiologi', 'periksa_radiologi.kd_jenis_prw', '=', 'jns_perawatan_radiologi.kd_jenis_prw')
            ->whereBetween('periksa_radiologi.tgl_periksa', [
                $this->tanggalMulai . ' 00:00:00',
                $this->tanggalAkhir . ' 23:59:59'
            ])
            ->where('periksa_radiologi.kd_dokter', session()->get('username'))
            ->select(
                'periksa_radiologi.no_rawat',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'periksa_radiologi.kd_jenis_prw',
                'jns_perawatan_radiologi.nm_perawatan',
                'periksa_radiologi.kd_dokter',
                'periksa_radiologi.tgl_periksa',
                'periksa_radiologi.jam',
                'periksa_radiologi.status',
                'periksa_radiologi.tarif_tindakan_dokter'
            )
            ->orderBy('periksa_radiologi.tgl_periksa', 'desc')
            ->orderBy('periksa_radiologi.jam', 'desc');
    }

    /**
     * Ambil data tindakan laboratorium berdasarkan dokter dan periode.
     */
    private function getDataLab()
    {
        return DB::table('periksa_lab')
            ->join('reg_periksa', 'periksa_lab.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('jns_perawatan_lab', 'periksa_lab.kd_jenis_prw', '=', 'jns_perawatan_lab.kd_jenis_prw')
            ->whereBetween('periksa_lab.tgl_periksa', [
                $this->tanggalMulai . ' 00:00:00',
                $this->tanggalAkhir . ' 23:59:59'
            ])
            ->where('periksa_lab.kd_dokter', session()->get('username'))
            ->select(
                'periksa_lab.no_rawat',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'periksa_lab.kd_jenis_prw',
                'jns_perawatan_lab.nm_perawatan',
                'periksa_lab.kd_dokter',
                'periksa_lab.tgl_periksa',
                'periksa_lab.jam',
                'periksa_lab.status',
                'periksa_lab.tarif_tindakan_dokter'
            )
            ->orderBy('periksa_lab.tgl_periksa', 'desc')
            ->orderBy('periksa_lab.jam', 'desc');
    }

    /**
     * Ambil data tindakan operasi berdasarkan dokter dan periode.
     * Dokter bisa terlibat sebagai operator1, operator2, operator3, dokter_anak, dokter_anestesi, dokter_pjanak, atau dokter_umum.
     */
    private function getDataOperasi()
    {
        $kdDokter = session()->get('username');
        // Escape kdDokter untuk mencegah SQL injection
        $kdDokterEscaped = DB::connection()->getPdo()->quote($kdDokter);

        return DB::table('operasi')
            ->join('reg_periksa', 'operasi.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('paket_operasi', 'operasi.kode_paket', '=', 'paket_operasi.kode_paket')
            ->whereBetween('operasi.tgl_operasi', [
                $this->tanggalMulai . ' 00:00:00',
                $this->tanggalAkhir . ' 23:59:59'
            ])
            ->where(function ($query) use ($kdDokter) {
                $query->where('operasi.operator1', $kdDokter)
                    ->orWhere('operasi.operator2', $kdDokter)
                    ->orWhere('operasi.operator3', $kdDokter)
                    ->orWhere('operasi.dokter_anak', $kdDokter)
                    ->orWhere('operasi.dokter_anestesi', $kdDokter)
                    ->orWhere('operasi.dokter_pjanak', $kdDokter)
                    ->orWhere('operasi.dokter_umum', $kdDokter);
            })
            ->select(
                'operasi.no_rawat',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'operasi.kode_paket',
                'paket_operasi.nm_perawatan',
                'operasi.tgl_operasi',
                'operasi.status',
                'operasi.kategori',
                'operasi.operator1',
                'operasi.operator2',
                'operasi.operator3',
                'operasi.dokter_anak',
                'operasi.dokter_anestesi',
                'operasi.dokter_pjanak',
                'operasi.dokter_umum',
                DB::raw("CASE 
                    WHEN operasi.operator1 = " . $kdDokterEscaped . " THEN operasi.biayaoperator1
                    WHEN operasi.operator2 = " . $kdDokterEscaped . " THEN operasi.biayaoperator2
                    WHEN operasi.operator3 = " . $kdDokterEscaped . " THEN operasi.biayaoperator3
                    WHEN operasi.dokter_anak = " . $kdDokterEscaped . " THEN operasi.biayadokter_anak
                    WHEN operasi.dokter_anestesi = " . $kdDokterEscaped . " THEN operasi.biayadokter_anestesi
                    WHEN operasi.dokter_pjanak = " . $kdDokterEscaped . " THEN operasi.biaya_dokter_pjanak
                    WHEN operasi.dokter_umum = " . $kdDokterEscaped . " THEN operasi.biaya_dokter_umum
                    ELSE 0
                END as biaya_dokter"),
                DB::raw("CASE 
                    WHEN operasi.operator1 = " . $kdDokterEscaped . " THEN 'Operator 1'
                    WHEN operasi.operator2 = " . $kdDokterEscaped . " THEN 'Operator 2'
                    WHEN operasi.operator3 = " . $kdDokterEscaped . " THEN 'Operator 3'
                    WHEN operasi.dokter_anak = " . $kdDokterEscaped . " THEN 'Dokter Anak'
                    WHEN operasi.dokter_anestesi = " . $kdDokterEscaped . " THEN 'Dokter Anestesi'
                    WHEN operasi.dokter_pjanak = " . $kdDokterEscaped . " THEN 'Dokter PJ Anak'
                    WHEN operasi.dokter_umum = " . $kdDokterEscaped . " THEN 'Dokter Umum'
                    ELSE '-'
                END as peran_dokter")
            )
            ->orderBy('operasi.tgl_operasi', 'desc');
    }

    private function getDataRalan()
    {
        return DB::table('pasien')
            ->join('reg_periksa', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('rawat_jl_dr', 'reg_periksa.no_rawat', '=', 'rawat_jl_dr.no_rawat')
            ->join('dokter', 'rawat_jl_dr.kd_dokter', '=', 'dokter.kd_dokter')
            ->join('jns_perawatan', 'rawat_jl_dr.kd_jenis_prw', '=', 'jns_perawatan.kd_jenis_prw')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->whereRaw("CONCAT(rawat_jl_dr.tgl_perawatan, ' ', rawat_jl_dr.jam_rawat) BETWEEN ? AND ?", [
                $this->tanggalMulai . ' 00:00:00',
                $this->tanggalAkhir . ' 23:59:59'
            ])
            ->where('rawat_jl_dr.kd_dokter', session()->get('username'))
            ->select(
                'rawat_jl_dr.no_rawat',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'rawat_jl_dr.kd_jenis_prw',
                'jns_perawatan.nm_perawatan',
                'rawat_jl_dr.kd_dokter',
                'dokter.nm_dokter',
                'rawat_jl_dr.tgl_perawatan',
                'rawat_jl_dr.jam_rawat',
                'penjab.png_jawab',
                'poliklinik.nm_poli',
                'rawat_jl_dr.material',
                'rawat_jl_dr.bhp',
                'rawat_jl_dr.tarif_tindakandr',
                'rawat_jl_dr.kso',
                'rawat_jl_dr.menejemen'
            )
            ->orderBy('rawat_jl_dr.no_rawat', 'desc')
            ->orderBy('rawat_jl_dr.tgl_perawatan', 'desc')
            ->orderBy('rawat_jl_dr.jam_rawat', 'desc');
    }

    private function getDataRanap()
    {
        return DB::table('pasien')
            ->join('reg_periksa', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->join('rawat_inap_dr', 'reg_periksa.no_rawat', '=', 'rawat_inap_dr.no_rawat')
            ->join('dokter', 'rawat_inap_dr.kd_dokter', '=', 'dokter.kd_dokter')
            ->join('jns_perawatan_inap', 'rawat_inap_dr.kd_jenis_prw', '=', 'jns_perawatan_inap.kd_jenis_prw')
            ->join('poliklinik', 'reg_periksa.kd_poli', '=', 'poliklinik.kd_poli')
            ->join('penjab', 'reg_periksa.kd_pj', '=', 'penjab.kd_pj')
            ->leftJoin('kamar_inap', function ($join) {
                $join->on('reg_periksa.no_rawat', '=', 'kamar_inap.no_rawat')
                    ->whereRaw('kamar_inap.tgl_masuk = (
                         SELECT MAX(tgl_masuk) 
                         FROM kamar_inap ki2 
                         WHERE ki2.no_rawat = reg_periksa.no_rawat
                     )');
            })
            ->leftJoin('kamar', 'kamar_inap.kd_kamar', '=', 'kamar.kd_kamar')
            ->leftJoin('bangsal', 'kamar.kd_bangsal', '=', 'bangsal.kd_bangsal')
            ->whereRaw("CONCAT(rawat_inap_dr.tgl_perawatan, ' ', rawat_inap_dr.jam_rawat) BETWEEN ? AND ?", [
                $this->tanggalMulai . ' 00:00:00',
                $this->tanggalAkhir . ' 23:59:59'
            ])
            ->where('rawat_inap_dr.kd_dokter', session()->get('username'))
            ->select(
                'rawat_inap_dr.no_rawat',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'rawat_inap_dr.kd_jenis_prw',
                'jns_perawatan_inap.nm_perawatan',
                'rawat_inap_dr.kd_dokter',
                'dokter.nm_dokter',
                'rawat_inap_dr.tgl_perawatan',
                'rawat_inap_dr.jam_rawat',
                'penjab.png_jawab',
                'poliklinik.nm_poli',
                'kamar_inap.kd_kamar',
                'bangsal.nm_bangsal',
                'rawat_inap_dr.material',
                'rawat_inap_dr.bhp',
                'rawat_inap_dr.tarif_tindakandr',
                'rawat_inap_dr.kso',
                'rawat_inap_dr.menejemen'
            )
            ->orderBy('rawat_inap_dr.no_rawat', 'desc')
            ->orderBy('rawat_inap_dr.tgl_perawatan', 'desc')
            ->orderBy('rawat_inap_dr.jam_rawat', 'desc');
    }

    private function groupByPasien($data, $isRanap = false)
    {
        return $data->groupBy('no_rawat')->map(function ($items) use ($isRanap) {
            $first = $items->first();
            $result = [
                'no_rawat' => $first->no_rawat,
                'no_rkm_medis' => $first->no_rkm_medis,
                'nm_pasien' => $first->nm_pasien,
                'nm_poli' => $first->nm_poli ?? null,
                'png_jawab' => $first->png_jawab,
                'tindakan' => $items,
                'total_biaya' => $items->sum('tarif_tindakandr'),
                'total_tindakan' => $items->count(),
            ];

            // Untuk ranap, tambahkan kd_kamar dan nm_bangsal
            if ($isRanap) {
                $result['kd_kamar'] = $first->kd_kamar ?? null;
                $result['nm_bangsal'] = $first->nm_bangsal ?? null;
            }

            return $result;
        })->values();
    }

    /**
     * Grouping khusus untuk radiologi berdasarkan no_rawat.
     */
    private function groupRadiologiByPasien($data)
    {
        return $data->groupBy('no_rawat')->map(function ($items) {
            $first = $items->first();

            return [
                'no_rawat'       => $first->no_rawat,
                'no_rkm_medis'   => $first->no_rkm_medis,
                'nm_pasien'      => $first->nm_pasien,
                'status'         => $first->status,
                'tindakan'       => $items,
                'total_biaya'    => $items->sum('tarif_tindakan_dokter'),
                'total_tindakan' => $items->count(),
            ];
        })->values();
    }

    /**
     * Grouping khusus untuk laboratorium berdasarkan no_rawat.
     */
    private function groupLabByPasien($data)
    {
        return $data->groupBy('no_rawat')->map(function ($items) {
            $first = $items->first();

            return [
                'no_rawat'       => $first->no_rawat,
                'no_rkm_medis'   => $first->no_rkm_medis,
                'nm_pasien'      => $first->nm_pasien,
                'status'         => $first->status,
                'tindakan'       => $items,
                'total_biaya'    => $items->sum('tarif_tindakan_dokter'),
                'total_tindakan' => $items->count(),
            ];
        })->values();
    }

    /**
     * Grouping khusus untuk operasi berdasarkan no_rawat.
     */
    private function groupOperasiByPasien($data)
    {
        return $data->groupBy('no_rawat')->map(function ($items) {
            $first = $items->first();

            return [
                'no_rawat'       => $first->no_rawat,
                'no_rkm_medis'   => $first->no_rkm_medis,
                'nm_pasien'      => $first->nm_pasien,
                'status'         => $first->status,
                'tindakan'       => $items,
                'total_biaya'    => $items->sum('biaya_dokter'),
                'total_tindakan' => $items->count(),
            ];
        })->values();
    }

    public function render()
    {
        // Rekap Ralan (selalu dihitung untuk semua tab)
        $ralanQuery = $this->getDataRalan();
        $ralanData = $ralanQuery->get();
        $totalRalan = $ralanData->sum('tarif_tindakandr');
        $totalRalanTindakan = $ralanData->count();

        // Rekap Ranap (selalu dihitung untuk semua tab)
        $ranapQuery = $this->getDataRanap();
        $ranapData = $ranapQuery->get();
        $totalRanap = $ranapData->sum('tarif_tindakandr');
        $totalRanapTindakan = $ranapData->count();

        // Rekap radiologi (berdasarkan kd_dokter dan tarif_tindakan_dokter)
        $radiologiQuery = $this->getDataRadiologi();
        $radiologiData = $radiologiQuery->get();
        $totalRadiologi = $radiologiData->sum('tarif_tindakan_dokter');
        $totalRadiologiTindakan = $radiologiData->count();

        // Rekap laboratorium (berdasarkan kd_dokter dan tarif_tindakan_dokter)
        $labQuery = $this->getDataLab();
        $labData = $labQuery->get();
        $totalLab = $labData->sum('tarif_tindakan_dokter');
        $totalLabTindakan = $labData->count();

        // Rekap operasi (berdasarkan operator1, operator2, operator3, dokter_anak, dokter_anestesi, dokter_pjanak, dokter_umum)
        $operasiQuery = $this->getDataOperasi();
        $operasiData = $operasiQuery->get();
        $totalOperasi = $operasiData->sum('biaya_dokter');
        $totalOperasiTindakan = $operasiData->count();

        if ($this->activeTab === 'ralan') {
            $allData = $ralanData;
            $grouped = $this->groupByPasien($allData, false);

            // Paginate grouped data using Livewire pagination
            $currentPage = $this->page ?? request()->get('page', 1);
            $perPage = $this->perPage;
            $items = $grouped->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $total = $grouped->count();

            // Create paginator manually
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $currentPage,
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );

            // Append query parameters except page
            $paginator->appends(request()->except('page'));

            $ralanGrouped = $paginator;
            $radiologiGrouped = collect([]);
            $labGrouped = collect([]);
            $operasiGrouped = collect([]);

            return view('livewire.rekap.tindakan-dokter', [
                'ralanGrouped'             => $ralanGrouped,
                'ranapGrouped'             => collect([]),
                'radiologiGrouped'         => $radiologiGrouped,
                'labGrouped'               => $labGrouped,
                'operasiGrouped'           => $operasiGrouped,
                // Ralan
                'totalRalan'               => $totalRalan,
                'totalRalanTindakan'       => $totalRalanTindakan,
                // Ranap
                'totalRanap'               => $totalRanap,
                'totalRanapTindakan'       => $totalRanapTindakan,
                // Radiologi
                'totalRadiologi'           => $totalRadiologi,
                'totalRadiologiTindakan'   => $totalRadiologiTindakan,
                // Lab
                'totalLab'                 => $totalLab,
                'totalLabTindakan'         => $totalLabTindakan,
                // Operasi
                'totalOperasi'             => $totalOperasi,
                'totalOperasiTindakan'     => $totalOperasiTindakan,
            ]);
        } elseif ($this->activeTab === 'ranap') {
            $allData = $ranapData;
            $grouped = $this->groupByPasien($allData, true);

            // Paginate grouped data using Livewire pagination
            $currentPage = $this->page ?? request()->get('page', 1);
            $perPage = $this->perPage;
            $items = $grouped->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $total = $grouped->count();

            // Create paginator manually
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $currentPage,
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );

            // Append query parameters except page
            $paginator->appends(request()->except('page'));

            $ranapGrouped = $paginator;
            $radiologiGrouped = collect([]);
            $labGrouped = collect([]);
            $operasiGrouped = collect([]);

            return view('livewire.rekap.tindakan-dokter', [
                'ralanGrouped'             => collect([]),
                'ranapGrouped'             => $ranapGrouped,
                'radiologiGrouped'         => $radiologiGrouped,
                'labGrouped'               => $labGrouped,
                'operasiGrouped'           => $operasiGrouped,
                // Ralan
                'totalRalan'               => $totalRalan,
                'totalRalanTindakan'       => $totalRalanTindakan,
                // Ranap
                'totalRanap'               => $totalRanap,
                'totalRanapTindakan'       => $totalRanapTindakan,
                // Radiologi
                'totalRadiologi'           => $totalRadiologi,
                'totalRadiologiTindakan'   => $totalRadiologiTindakan,
                // Lab
                'totalLab'                 => $totalLab,
                'totalLabTindakan'         => $totalLabTindakan,
                // Operasi
                'totalOperasi'             => $totalOperasi,
                'totalOperasiTindakan'     => $totalOperasiTindakan,
            ]);
        } elseif ($this->activeTab === 'radiologi') {
            // Tab Radiologi
            $allData = $radiologiData;
            $grouped = $this->groupRadiologiByPasien($allData);

            $currentPage = $this->page ?? request()->get('page', 1);
            $perPage = $this->perPage;
            $items = $grouped->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $total = $grouped->count();

            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $currentPage,
                [
                    'path'     => request()->url(),
                    'pageName' => 'page',
                ]
            );

            $paginator->appends(request()->except('page'));

            $radiologiGrouped = $paginator;
            $labGrouped = collect([]);
            $operasiGrouped = collect([]);

            return view('livewire.rekap.tindakan-dokter', [
                'ralanGrouped'             => collect([]),
                'ranapGrouped'             => collect([]),
                'radiologiGrouped'         => $radiologiGrouped,
                'labGrouped'               => $labGrouped,
                'operasiGrouped'           => $operasiGrouped,
                // Ralan & Ranap
                'totalRalan'               => $totalRalan,
                'totalRalanTindakan'       => $totalRalanTindakan,
                'totalRanap'               => $totalRanap,
                'totalRanapTindakan'       => $totalRanapTindakan,
                // Radiologi
                'totalRadiologi'           => $totalRadiologi,
                'totalRadiologiTindakan'   => $totalRadiologiTindakan,
                // Lab
                'totalLab'                 => $totalLab,
                'totalLabTindakan'         => $totalLabTindakan,
                // Operasi
                'totalOperasi'             => $totalOperasi,
                'totalOperasiTindakan'     => $totalOperasiTindakan,
            ]);
        } elseif ($this->activeTab === 'lab') {
            // Tab Lab
            $allData = $labData;
            $grouped = $this->groupLabByPasien($allData);

            $currentPage = $this->page ?? request()->get('page', 1);
            $perPage = $this->perPage;
            $items = $grouped->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $total = $grouped->count();

            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $currentPage,
                [
                    'path'     => request()->url(),
                    'pageName' => 'page',
                ]
            );

            $paginator->appends(request()->except('page'));

            $labGrouped = $paginator;
            $radiologiGrouped = collect([]);
            $operasiGrouped = collect([]);

            return view('livewire.rekap.tindakan-dokter', [
                'ralanGrouped'             => collect([]),
                'ranapGrouped'             => collect([]),
                'radiologiGrouped'         => $radiologiGrouped,
                'labGrouped'               => $labGrouped,
                'operasiGrouped'           => $operasiGrouped,
                // Ralan & Ranap
                'totalRalan'               => $totalRalan,
                'totalRalanTindakan'       => $totalRalanTindakan,
                'totalRanap'               => $totalRanap,
                'totalRanapTindakan'       => $totalRanapTindakan,
                // Radiologi
                'totalRadiologi'           => $totalRadiologi,
                'totalRadiologiTindakan'   => $totalRadiologiTindakan,
                // Lab
                'totalLab'                 => $totalLab,
                'totalLabTindakan'         => $totalLabTindakan,
                // Operasi
                'totalOperasi'             => $totalOperasi,
                'totalOperasiTindakan'     => $totalOperasiTindakan,
            ]);
        } else {
            // Tab Operasi
            $allData = $operasiData;
            $grouped = $this->groupOperasiByPasien($allData);

            $currentPage = $this->page ?? request()->get('page', 1);
            $perPage = $this->perPage;
            $items = $grouped->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $total = $grouped->count();

            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $currentPage,
                [
                    'path'     => request()->url(),
                    'pageName' => 'page',
                ]
            );

            $paginator->appends(request()->except('page'));

            $operasiGrouped = $paginator;
            $radiologiGrouped = collect([]);
            $labGrouped = collect([]);

            return view('livewire.rekap.tindakan-dokter', [
                'ralanGrouped'             => collect([]),
                'ranapGrouped'             => collect([]),
                'radiologiGrouped'         => $radiologiGrouped,
                'labGrouped'               => $labGrouped,
                'operasiGrouped'           => $operasiGrouped,
                // Ralan & Ranap
                'totalRalan'               => $totalRalan,
                'totalRalanTindakan'       => $totalRalanTindakan,
                'totalRanap'               => $totalRanap,
                'totalRanapTindakan'       => $totalRanapTindakan,
                // Radiologi
                'totalRadiologi'           => $totalRadiologi,
                'totalRadiologiTindakan'   => $totalRadiologiTindakan,
                // Lab
                'totalLab'                 => $totalLab,
                'totalLabTindakan'         => $totalLabTindakan,
                // Operasi
                'totalOperasi'             => $totalOperasi,
                'totalOperasiTindakan'     => $totalOperasiTindakan,
            ]);
        }
    }
}
