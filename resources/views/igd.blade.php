@extends('adminlte::page')

@section('title', 'Pasien IGD')

@section('content_header')
    <h1>Pasien IGD</h1>
@stop

@section('content')
    <x-adminlte-callout theme="info" title="{{$nm_poli}}">
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
                                <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-igd">Penilaian Awal Medis IGD</button>
                                <button id="{{$row->no_rawat}}" class="dropdown-item btn-awal-medis" wire:click="$emit('awalMedis')">Penilaian Awal Medis Umum</button>
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
@push('js')
<script>
    
</script>
@endpush
