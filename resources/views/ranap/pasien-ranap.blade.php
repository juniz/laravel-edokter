@extends('adminlte::page')

@section('title', 'Pasien Ranap')

@section('content_header')
    <h1>Pasien Ranap</h1>
@stop

@section('content')
<x-adminlte-callout theme="info" >
    @php
        $config["responsive"] = true;
    @endphp
    {{-- Minimal example / fill data using the component slot --}}
    <x-adminlte-datatable id="tablePasienRanap" :heads="$heads" :config="$config" head-theme="dark" striped hoverable bordered compressed>
        @foreach($data as $row)
            @php
                $noRawat = App\Http\Controllers\Ranap\PasienRanapController::encryptData($row->no_rawat);
                $noRM = App\Http\Controllers\Ranap\PasienRanapController::encryptData($row->no_rkm_medis);
            @endphp
            <tr>
                <td> 
                    <a class="text-primary" href="{{route('ranap.pemeriksaan', ['no_rawat' => $noRawat, 'no_rm' => $noRM, 'bangsal' => $row->kd_bangsal])}}">
                        {{$row->nm_pasien}}
                    </a>
                </td>
                <td>
                    <div class="dropdown">
                        <button id="my-dropdown" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">{{$row->no_rawat}}</button>
                        <div class="dropdown-menu" aria-labelledby="my-dropdown">
                            <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-medis-ranap"> Penilaian Awal Medis Ranap</button>
                        </div>
                    </div>
                </td>
                <td>{{$row->no_rkm_medis}}</td>
                <td>{{$row->nm_bangsal}}</td>
                <td>{{$row->kd_kamar}}</td>
                <td>{{$row->tgl_masuk}}</td>
                <td>{{$row->png_jawab}}</td>
            </tr>
        @endforeach
    </x-adminlte-datatable>
    
</x-adminlte-callout>
<x-adminlte-modal wire:ignore.self id="modal-awal-medis-ranap" title=" Medis Ranap" size="xl" v-centered static-backdrop scrollable>
    <livewire:component.awal-medis-ranap.form />
</x-adminlte-modal>
@stop

@section('plugins.TempusDominusBs4', true)
@section('js')
@stop
