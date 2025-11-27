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

    public function render()
    {
        if ($this->activeTab === 'ralan') {
            $query = $this->getDataRalan();
            $allData = $query->get();
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
            $totalRalan = $allData->sum('tarif_tindakandr');
            $totalRalanTindakan = $allData->count();

            return view('livewire.rekap.tindakan-dokter', [
                'ralanGrouped' => $ralanGrouped,
                'ranapGrouped' => collect([]),
                'totalRalan' => $totalRalan,
                'totalRanap' => 0,
                'totalRalanTindakan' => $totalRalanTindakan,
                'totalRanapTindakan' => 0,
            ]);
        } else {
            $query = $this->getDataRanap();
            $allData = $query->get();
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
            $totalRanap = $allData->sum('tarif_tindakandr');
            $totalRanapTindakan = $allData->count();

            return view('livewire.rekap.tindakan-dokter', [
                'ralanGrouped' => collect([]),
                'ranapGrouped' => $ranapGrouped,
                'totalRalan' => 0,
                'totalRanap' => $totalRanap,
                'totalRalanTindakan' => 0,
                'totalRanapTindakan' => $totalRanapTindakan,
            ]);
        }
    }
}
