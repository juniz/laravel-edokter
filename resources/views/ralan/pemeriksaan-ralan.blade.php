@extends('adminlte::page')

@section('title', 'Pemeriksaan Pasien Ralan')

@section('content_header')
<div class="d-flex flex-row justify-content-between">
    <h1>Pemeriksaan Ralan</h1>
    <div class="d-flex flex-row" style="gap: 10px">
        <button type="button" data-toggle="modal" data-target="#modalPurple" class="btn btn-sm btn-secondary">Tanya AI</button>
        <a name="" id="" class="btn btn-sm btn-primary" href="{{ url('ralan/pasien') }}" role="button">Daftar Pasien</a>
    </div>
</div>

@stop

@section('content')
<livewire:ralan.pasien :noRawat="request()->get('no_rawat')" />
<div class="row">
    <div class="col-md-6">
        <livewire:ralan.pasien-tabs :noRawat="request()->get('no_rawat')" />
    </div>
    <div class="col-md-6">
        @if(session()->get('kd_poli') == 'U017')
        <x-adminlte-card title="Uji Fungsi KFR" theme="info" collapsible="collapsed" maximizable>
            <livewire:ralan.uji-fungsi-kfr :noRawat="request()->get('no_rawat')" />
        </x-adminlte-card>
        @endif
        <x-adminlte-card title="Pemeriksaan" theme="info" icon="fas fa-lg fa-bell" collapsible maximizable>
            <livewire:ralan.pemeriksaan :noRawat="request()->get('no_rawat')" :noRm="request()->get('no_rm')" />
            <livewire:ralan.modal.edit-pemeriksaan />
        </x-adminlte-card>
        @if(session()->get('kd_poli') == 'U002' || session()->get('kd_poli') == 'U003')
        <livewire:ralan.odontogram :noRawat=" request()->get('no_rawat')" :noRm="request()->get('no_rm')">
            @endif
            <x-adminlte-card title="Resep" id="resepCard" theme="info" icon="fas fa-lg fa-pills" collapsible="collapsed"
                maximizable>
                <x-ralan.resep />
            </x-adminlte-card>
            {{-- <x-adminlte-card title="Resep Luar" id="resepLuarCard" theme="info" icon="fas fa-lg fa-pills" collapsible="collapsed"
                maximizable>
                <livewire:ralan.resep :noRawat="request()->get('no_rawat')" :noRm="request()->get('no_rm')">
                <livewire:component.resep-luar.table-resep :noRawat="request()->get('no_rawat')" />
            </x-adminlte-card> --}}
                {{--
                <x-ralan.resume /> --}}
                <livewire:ralan.resume :no-rawat="request()->get('no_rawat')" :noRm="request()->get('no_rm')" />
                <x-adminlte-card title="Diagnosa" theme="info" icon="fas fa-lg fa-file-medical" collapsible="collapsed"
                    maximizable>
                    <livewire:ralan.diagnosa :noRawat="request()->get('no_rawat')" :noRm="request()->get('no_rm')" />
                </x-adminlte-card>
                {{--
                <x-ralan.diagnosa :no-rawat="request()->get('no_rawat')" :noRm="request()->get('no_rm')" /> --}}
                {{--
                <x-ralan.catatan :no-rawat="request()->get('no_rawat')" /> --}}
                <livewire:ralan.catatan :noRawat="request()->get('no_rawat')" :noRm="request()->get('no_rm')" />
                <x-adminlte-card title="Konsultasi Medik" icon='fas fa-user' theme="info" maximizable collapsible="collapsed">
                    <livewire:component.konsultasi-medik :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" />
                </x-adminlte-card>
                {{-- <x-ralan.rujuk-internal :no-rawat="request()->get('no_rawat')" /> --}}
                {{--
                <x-ralan.permintaan-lab :no-rawat="request()->get('no_rawat')" /> --}}
                <livewire:ralan.permintaan-lab :no-rawat="request()->get('no_rawat')" />
                {{--
                <x-ralan.permintaan-radiologi :no-rawat="request()->get('no_rawat')" /> --}}
                <livewire:ralan.permintaan-radiologi :no-rawat="request()->get('no_rawat')" />

                <x-adminlte-card title="Laporan Operasi" icon='fas fa-stethoscope' theme="info" maximizable collapsible="collapsed">
                    <livewire:ranap.lap-operasi :no-rawat="request()->get('no_rawat')" />
                    <livewire:ranap.template-lap-operasi />
                </x-adminlte-card>
    </div>
</div>
<x-adminlte-modal id="modalPurple" title="Tanya AI" theme="secondary"
    icon="fas fa-bolt" size='xl' v-centered static-backdrop scrollable>
    <livewire:component.modal-ai />
</x-adminlte-modal>
@stop

@section('plugins.TempusDominusBs4', true)

@push('js')
<script>
    $(function () {
        $('#pemeriksaan-tab').on('click', function () {
            alert('pemeriksaan');
        })
    })
</script>
@endpush