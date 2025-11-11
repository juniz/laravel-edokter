@extends('adminlte::page')

@section('title', 'Pasien Ranap')

@section('content_header')
    <h1>Pasien Ranap</h1>
@stop

@section('content')
<x-adminlte-callout theme="info" >
    <div class="d-flex justify-content-end mb-3">
        <form action="{{route('ranap.pasien')}}" method="GET">
            <div class="d-flex" style="gap: 10px">
                @php
                    $config = ['format' => 'YYYY-MM-DD'];
                @endphp
                <x-adminlte-select name="status" id="status">
                    <option value="belum_pulang" {{ request('status') == 'belum_pulang' ? 'selected' : '' }}>Belum Pulang</option>
                    <option value="sudah_pulang" {{ request('status') == 'sudah_pulang' ? 'selected' : '' }}>Sudah Pulang</option>
                </x-adminlte-select>
                <x-adminlte-input-date name="tanggal" value="{{ request('tanggal') ?? date('Y-m-d') }}" :config="$config" placeholder="Pilih Tanggal...">
                    <x-slot name="appendSlot">
                        <x-adminlte-button class="btn-sm" type="submit" theme="primary" icon="fas fa-lg fa-search"/>
                    </x-slot>
                </x-adminlte-input-date>
            </div>
        </form>
    </div>
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
                        <button id="my-dropdown-{{$row->no_rawat}}" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">{{$row->no_rawat}}</button>
                        <div class="dropdown-menu" aria-labelledby="my-dropdown-{{$row->no_rawat}}">
                            <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-medis-ranap"> Penilaian Awal Medis Ranap</button>
                            <a class="dropdown-item" href="{{route('ralan.pemeriksaan', ['no_rawat' => $noRawat, 'no_rm' => $noRM])}}">Pemeriksaan Ralan</a>
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
@section('css')
<style>
    .dropdown-menu .dropdown-item {
        color: #212529;
    }
    
    .dropdown-menu .dropdown-item:hover,
    .dropdown-menu .dropdown-item:focus {
        color: #212529 !important;
        background-color: #f8f9fa;
    }
</style>
@stop
@section('js')
<script>
    // $(function() {
    //     // Filter handler - redirect to same page with status parameter
    //     $('#filter_status').on('change', function() {
    //         window.location.href = "{{ route('ranap.pasien') }}?status=" + $(this).val();
    //     });
    //     $('#tanggal').on('change.datetimepicker', function() {
    //         window.location.href = "{{ route('ranap.pasien') }}?tanggal=" + $(this).val();
    //     });
    // });
</script>
@stop
