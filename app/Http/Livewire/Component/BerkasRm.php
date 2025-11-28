<?php

namespace App\Http\Livewire\Component;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BerkasRm extends Component
{
    public $isLoading = true;
    public $rm;
    public $berkas = [];
    public $berkasGrouped;

    protected $listeners = [
        'setRm' => 'setRm',
        'loadBerkas' => 'loadBerkas'
    ];

    public function mount($rm = null)
    {
        $this->rm = $rm;
        if ($this->rm) {
            $this->loadBerkas($this->rm);
        } else {
            $this->isLoading = false;
        }
    }

    public function setRm($rm)
    {
        $this->loadBerkas($rm);
    }

    public function render()
    {
        return view('livewire.component.berkas-rm', [
            'berkasGrouped' => $this->berkasGrouped
        ]);
    }

    public function placeholder()
    {
        return <<<'HTML'
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            HTML;
    }

    public function getBerkas()
    {
        if (!$this->rm) {
            return collect([]);
        }

        try {
            return DB::table('berkas_digital_perawatan')
                ->join('master_berkas_digital', 'berkas_digital_perawatan.kode', '=', 'master_berkas_digital.kode')
                ->whereRaw(
                    "berkas_digital_perawatan.no_rawat IN (SELECT no_rawat FROM reg_periksa WHERE no_rkm_medis = :noRM) AND berkas_digital_perawatan.lokasi_file <> :file AND (berkas_digital_perawatan.kode = :kode OR berkas_digital_perawatan.kode = :lab OR berkas_digital_perawatan.kode = :rad OR berkas_digital_perawatan.kode = :op)",
                    ['noRM' => $this->rm, 'file' => 'pages/upload/', 'kode' => 'B00', 'lab' => 'B05', 'rad' => 'B06', 'op' => 'B08']
                )
                ->select(
                    'berkas_digital_perawatan.*',
                    'master_berkas_digital.kode',
                    'master_berkas_digital.nama as nama_kelompok'
                )
                ->orderBy('master_berkas_digital.nama')
                ->orderBy('berkas_digital_perawatan.no_rawat', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error loading berkas: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getBerkasGrouped()
    {
        if (!$this->berkas || $this->berkas->isEmpty()) {
            return collect([]);
        }

        /** @var \Illuminate\Support\Collection $grouped */
        $grouped = $this->berkas->groupBy('kode')->map(function ($group, $kode) {
            return [
                'kode' => $kode,
                'nama' => $group->first()->nama_kelompok ?? 'Lainnya',
                'berkas' => $group,
                'count' => $group->count()
            ];
        });

        return $grouped->sortBy('nama');
    }

    public function loadBerkas($rm = null)
    {
        $this->isLoading = true;

        if ($rm) {
            $this->rm = $rm;
        }

        if ($this->rm) {
            $this->berkas = $this->getBerkas();
            $this->berkasGrouped = $this->getBerkasGrouped();
        } else {
            $this->berkas = collect([]);
            $this->berkasGrouped = collect();
        }

        $this->isLoading = false;
    }

    public function updatedRm()
    {
        $this->loadBerkas();
    }
}
