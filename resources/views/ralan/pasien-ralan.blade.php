@extends('adminlte::page')

@section('title', 'Pasien Ralan')

@section('content_header')
    <h1>Pasien Ralan</h1>
@stop

@section('content')
    <x-adminlte-callout theme="info" title="{{$nm_poli}}">
        <ul class="nav nav-tabs" id="tabRalan" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pasien-tab" data-toggle="tab" data-target="#pasien" type="button" role="tab" aria-controls="pasien" aria-selected="true">Pasien Ralan</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="rujuk-tab" data-toggle="tab" data-target="#rujuk" type="button" role="tab" aria-controls="rujuk" aria-selected="false">Rujuk Internal</button>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="pasien" role="tabpanel" aria-labelledby="pasien-tab">
                <x-adminlte-card theme="info">
                    @php
                        $config["responsive"] = true;
                    @endphp
                    {{-- Minimal example / fill data using the component slot --}}
                    <x-adminlte-datatable id="tablePasienRalan" :heads="$heads" :config="$config" head-theme="dark" striped hoverable bordered compressed>
                        @foreach($data as $row)
                            <tr @if(!empty($row->diagnosa_utama)) class="bg-success" @endif >
                                <td>{{$row->no_reg}}</td>
                                <td> 
                                    @php
                                    $noRawat = App\Http\Controllers\Ralan\PasienRalanController::encryptData($row->no_rawat);
                                    $noRM = App\Http\Controllers\Ralan\PasienRalanController::encryptData($row->no_rkm_medis);
                                    @endphp
                                    <a @if(!empty($row->diagnosa_utama)) class="text-white" @else class="text-primary" @endif href="{{route('ralan.pemeriksaan', ['no_rawat' => $noRawat, 'no_rm' => $noRM])}} ">
                                        {{$row->nm_pasien}}
                                    </a>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button id="my-dropdown" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">{{$row->no_rawat}}</button>
                                        <div class="dropdown-menu" aria-labelledby="my-dropdown">
                                            <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-medis" wire:click="$emit('awalMedis')">Penilaian Awal Medis Umum</button>
                                            <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-igd">Penilaian Awal Medis IGD</button>
                                            <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-tht">Penilaian Awal Medis THT</button>
                                            <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-anak">Penilaian Awal Medis Bayi/Anak</button>
                                            <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-kandungan">Penilaian Awal Medis Kandungan</button>
                                            <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-dalam">Penilaian Awal Medis Penyakit Dalam</button>
                                            <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-psikiatri">Penilaian Awal Medis Psikiatri</button>
                                            <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-mata">Penilaian Awal Medis Mata</button>
                                            <button id="{{$row->no_rawat}}" class="dropdown-item btn-persetujuan-penolakan-tindakan">Persetujuan/Penolakan Tindakan</button>
                                        </div>
                                    </div>
                                </td>
                                <td>{{$row->no_tlp}}</td>
                                <td>{{$row->nm_dokter}}</td>
                                <td>{{$row->stts}}</td>
                            </tr>
                        @endforeach
                    </x-adminlte-datatable>
                </x-adminlte-card>
            </div>
            <div class="tab-pane fade" id="rujuk" role="tabpanel" aria-labelledby="rujuk-tab">
                <x-adminlte-card theme="info">
                    @php
                        $config["responsive"] = true;
                    @endphp
                    {{-- Minimal example / fill data using the component slot --}}
                    <x-adminlte-datatable id="tableRujuk" :heads="$headsInternal" :config="$config" head-theme="dark" striped hoverable bordered compressed>
                        @foreach($dataInternal as $row)
                            <tr @if($row->stts == 'Sudah') class="bg-success" @endif >
                                <td>{{$row->no_reg}}</td>
                                <td>
                                    @php
                                    $noRawat = App\Http\Controllers\Ralan\PasienRalanController::encryptData($row->no_rawat);
                                    $noRM = App\Http\Controllers\Ralan\PasienRalanController::encryptData($row->no_rkm_medis);
                                    @endphp
                                    <a @if($row->stts == 'Sudah') class="text-white" @else class="text-primary" @endif href="{{route('ralan.pemeriksaan', ['no_rawat' => $noRawat, 'no_rm' => $noRM])}} ">{{$row->nm_pasien}}</a>
                                </td>
                                <td>{{$row->no_rkm_medis}}</td>
                                <td>{{$row->nm_dokter}}</td>
                                <td>{{$row->stts}}</td>
                            </tr>
                        @endforeach
                    </x-adminlte-datatable>
                </x-adminlte-card>
            </div>
        </div>
        <div class="row justify-content-end pr-2">
            <div class="md:col-3 sm:col-auto">
                @php
                $config = ['format' => 'YYYY-MM-DD'];
                @endphp
                <form action="{{route('ralan.pasien')}}" method="GET">
                <x-adminlte-input-date name="tanggal" value="{{$tanggal}}" :config="$config" placeholder="Pilih Tanggal...">
                    <x-slot name="appendSlot">
                        <x-adminlte-button class="btn-flat" type="submit" theme="primary" icon="fas fa-lg fa-search"/>
                    </x-slot>
                </x-adminlte-input-date>
                </form>
            </div>
        </div>
    </x-adminlte-callout>
    
    <x-adminlte-modal wire:ignore.self id="modal-awal-keperawatan" title="Penilaian Awal Medis Umum" size="xl" v-centered static-backdrop scrollable>
        <livewire:component.awal-ralan.form />
    </x-adminlte-modal>

    <x-adminlte-modal wire:ignore.self id="modal-awal-medis-tht" title="Penilaian Awal Medis THT" size="xl" v-centered static-backdrop scrollable>
        <livewire:component.awal-tht.form  />
    </x-adminlte-modal>

    <x-adminlte-modal wire:ignore.self id="modal-awal-medis-anak" title="Penilaian Awal Medis Anak" size="xl" v-centered static-backdrop scrollable>
        <livewire:component.awal-anak.form-anak  />
    </x-adminlte-modal>

    <x-adminlte-modal wire:ignore.self id="modal-awal-medis-kandungan" title="Penilaian Awal Medis Kandungan" size="xl" v-centered static-backdrop scrollable>
        <livewire:component.awal-kandungan.form-kandungan  />
    </x-adminlte-modal>

    <x-adminlte-modal wire:ignore.self id="modal-awal-medis-dalam" title="Penilaian Awal Medis Penyakit Dalam" size="xl" v-centered static-backdrop scrollable>
        <livewire:component.awal-dalam.form-dalam  />
    </x-adminlte-modal>

    <x-adminlte-modal wire:ignore.self id="modal-awal-medis-psikiatri" title="Penilaian Awal Medis Psikiatri" size="xl" v-centered static-backdrop scrollable>
        <livewire:component.awal-psikiatri.form-psikiatri  />
    </x-adminlte-modal>

    <x-adminlte-modal wire:ignore.self id="modal-awal-medis-mata" title="Penilaian Awal Medis Mata" size="xl" v-centered static-backdrop scrollable>
        <livewire:component.awal-mata.form-mata  />
    </x-adminlte-modal>

    <x-adminlte-modal wire:ignore.self id="modal-persetujuan-penolakan-tindakan" title="Persetujuan/Penolakan Tindakan" size="xl" v-centered static-backdrop scrollable>
        <livewire:component.persetujuan-penolakan-tindakan.form />
    </x-adminlte-modal>

    <x-adminlte-modal wire:ignore.self id="modal-awal-medis-igd" title="Penilaian Awal Medis IGD" size="xl" v-centered static-backdrop scrollable>
        <livewire:component.awal-igd.form />
    </x-adminlte-modal>
@stop

@section('plugins.TempusDominusBs4', true)
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card,
        .card-body,
        .table-responsive {
            overflow: visible !important;
        }
        .dropdown-menu {
            z-index: 2000 !important;
            /* max-height: 220px; */
            /* overflow-y: auto; */
        }
        .dropdown {
            position: relative;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .nav-tabs .nav-link {
            border-radius: 5px 5px 0 0;
            margin-right: 5px;
        }
        .nav-tabs .nav-link.active {
            background-color: #17a2b8;
            color: white;
            border-color: #17a2b8;
        }
        .dropdown-item {
            padding: 0.5rem 1.5rem;
        }
        .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        .badge {
            padding: 0.5em 0.75em;
            font-size: 0.875em;
        }
        .table {
            border-radius: 8px;
            /* overflow: hidden; */
        }
        .table thead th {
            background-color: #343a40;
            color: white;
            border-bottom: 2px solid #dee2e6;
        }
        .input-group {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            border-radius: 0 4px 4px 0;
        }
    </style>
@stop

@push('js')
<script>
$(document).on('show.bs.dropdown', '.table', function (e) {
    var $dropdown = $(e.relatedTarget).parent();
    var $menu = $dropdown.find('.dropdown-menu');
    var dropdownOffset = $dropdown.offset();
    var dropdownHeight = $menu.outerHeight();
    var tableOffset = $(this).offset();
    var tableHeight = $(this).height();
    var spaceBelow = tableOffset.top + tableHeight - (dropdownOffset.top + $dropdown.outerHeight());
    var spaceAbove = dropdownOffset.top - tableOffset.top;
    if (spaceBelow < dropdownHeight && spaceAbove > dropdownHeight) {
        $dropdown.addClass('dropup');
    } else {
        $dropdown.removeClass('dropup');
    }
});
$(document).on('hide.bs.dropdown', '.table', function (e) {
    var $dropdown = $(e.relatedTarget).parent();
    $dropdown.removeClass('dropup');
});
</script>
@endpush
