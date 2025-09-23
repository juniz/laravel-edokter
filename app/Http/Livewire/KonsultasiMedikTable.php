<?php

namespace App\Http\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\KonsultasiMedik;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateFilter;
use App\Models\Dokter;
use Illuminate\Support\Str;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class KonsultasiMedikTable extends DataTableComponent
{
    protected $model = KonsultasiMedik::class;

    public function mount()
    {
        // $this->setFilter('tanggal', date('Y-m-d'));
    }

    public function configure(): void
    {
        $this->setPrimaryKey('no_permintaan')
            ->setTableRowUrl(function ($row) {
                return route('konsultasi.jawaban', $row->no_permintaan);
            })
            ->setTableRowUrlTarget(function ($row) {
                return '_self';
            });

        $this->setFiltersStatus(true);
    }

    public function builder(): Builder
    {
        return KonsultasiMedik::query()
            ->with('jawaban')
            ->with('dokter')
            ->with('regPeriksa')
            ->orderBy('konsultasi_medik.tanggal', 'desc')
            ->where(function ($query) {
                $query->where('konsultasi_medik.kd_dokter', session()->get('username'))
                    ->orWhere('konsultasi_medik.kd_dokter_dikonsuli', session()->get('username'));
            });
    }

    public function filters(): array
    {
        return [
            DateFilter::make('Tanggal Mulai', 'tanggal_mulai')
                ->filter(function (Builder $query, $value) {
                    $query->where('konsultasi_medik.tanggal', '>=', $value);
                }),
            DateFilter::make('Tanggal Akhir', 'tanggal_akhir')
                ->filter(function (Builder $query, $value) {
                    $query->where('konsultasi_medik.tanggal', '<=', $value);
                }),
        ];
    }

    public function columns(): array
    {
        return [
            Column::make("No permintaan", "no_permintaan")
                ->searchable()
                ->sortable(),
            Column::make("Pasien", "regPeriksa.pasien.nm_pasien")
                ->searchable()
                ->sortable(),
            Column::make("Tanggal", "tanggal")
                ->sortable(),
            Column::make("Jenis permintaan", "jenis_permintaan")
                ->sortable(),
            Column::make("Dokter Konsul", "dokter.nm_dokter")
                ->sortable(),
            Column::make("Dokter Dikonsuli", "kd_dokter_dikonsuli")
                ->format(function ($value, $column, $row) {
                    $dokter = Dokter::where('kd_dokter', $value)->first();
                    return $dokter->nm_dokter;
                })
                ->sortable(),
            Column::make("Diagnosa kerja Konsul", "diagnosa_kerja")
                ->sortable(),
            Column::make("Uraian Konsultasi", "uraian_konsultasi")
                ->sortable()
                ->format(function ($value, $column, $row) {
                    return Str::limit($value, 100);
                })
                ->html(),
            Column::make("Diagnosa Kerja", "jawaban.diagnosa_kerja")
                ->sortable(),
            Column::make("Uraian Jawaban", "jawaban.uraian_jawaban")
                ->sortable(),
            // Column::make("Action")
            //     ->format(function ($value, $column, $row) {
            //         return view('livewire.konsultasi-medik-table-action', ['row' => $row]);
            //     }),
        ];
    }
}
