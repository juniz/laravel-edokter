<?php

namespace App\Http\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\PermintaanRadiologi;
use App\Traits\EnkripsiData;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateFilter;

class PermintaanRadiologiRalanTable extends DataTableComponent
{
    use EnkripsiData;
    protected $model = PermintaanRadiologi::class;

    public function mount()
    {
        $this->setFilter('tgl_permintaan', date('Y-m-d'));
    }

    public function configure(): void
    {
        $this->setPrimaryKey('noorder')
            ->setTableRowUrl(function ($row) {
                return route('radiologi.pemeriksaan', $this->encryptData($row->no_rawat));
            });
        $this->setFiltersStatus(true);
    }

    public function filters(): array
    {
        return [
            DateFilter::make('Tgl Permintaan', 'tgl_permintaan')
                ->filter(function (Builder $query, $value) {
                    $query->whereDate('permintaan_radiologi.tgl_permintaan', $value);
                }),
        ];
    }

    public function builder(): Builder
    {
        return PermintaanRadiologi::query()
            ->join('reg_periksa', 'permintaan_radiologi.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('dokter', 'permintaan_radiologi.dokter_perujuk', '=', 'dokter.kd_dokter')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->where('permintaan_radiologi.status', 'Ralan')
            ->select('permintaan_radiologi.*', 'reg_periksa.no_rkm_medis', 'dokter.nm_dokter', 'pasien.nm_pasien', 'pasien.no_rkm_medis');
    }

    public function columns(): array
    {
        return [
            Column::make("No. Permintaan", "noorder")
                ->sortable(),
            Column::make("No. Rawat", "no_rawat")
                ->collapseOnMobile()
                ->sortable(),
            Column::make("No. RM", "pasien.no_rkm_medis")
                ->collapseOnMobile()
                ->sortable(),
            Column::make("Pasien", "pasien.nm_pasien")
                ->sortable(),
            Column::make("Pemeriksaan", "noorder")
                ->collapseOnMobile()
                ->format(function ($value, $row, Column $column) {
                    $pemeriksaan = "";
                    $permintaan = \App\Models\PermintaanPemeriksaanRadiologi::where('noorder', $value)->get();
                    foreach ($permintaan as $p) {
                        $pemeriksaan .= $p->jnsPerawatanRadiologi->nm_perawatan . "<br>";
                    }
                    return $pemeriksaan;
                })
                ->html(),
            Column::make("Permintaan", "tgl_permintaan")
                ->collapseOnMobile()
                ->sortable(),
            Column::make("Jam", "jam_permintaan")
                ->collapseOnMobile()
                ->sortable(),
            Column::make("Sample", "tgl_sampel")
                ->collapseOnMobile()
                ->sortable(),
            Column::make("Jam", "jam_sampel")
                ->collapseOnMobile()
                ->sortable(),
            Column::make("Hasil", "tgl_hasil")
                ->collapseOnMobile()
                ->sortable(),
            Column::make("Jam", "jam_hasil")
                ->collapseOnMobile()
                ->sortable(),
            Column::make("Dokter Perujuk", "dokter.nm_dokter")
                ->collapseOnMobile()
                ->sortable(),
            Column::make("Informasi Tambahan", "informasi_tambahan")
                ->collapseOnMobile()
                ->sortable(),
            Column::make("Diagnosis ", "diagnosa_klinis")
                ->collapseOnMobile()
                ->sortable(),
        ];
    }
}
