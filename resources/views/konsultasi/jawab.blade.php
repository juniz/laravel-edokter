@extends('adminlte::page')

@section('title', 'Jawab Konsultasi Medik')

@section('content_header')
    <h1>Jawab Konsultasi Medik</h1>
@stop

@section('content')
@if($error = Session::get('error'))
<x-adminlte-alert theme="danger" title="Gagal">
    {{ $error }}
</x-adminlte-alert>
@endif

<div class="row">
    <div class="col-md-6">
        <livewire:ralan.pasien-tabs :noRawat="$konsultasi->no_rawat" />
    </div>
    <div class="col-md-6">
        <x-adminlte-card theme="info" theme-mode="outline">
            <form method="POST" action="{{ route('konsultasi.jawaban.simpan', $konsultasi->no_permintaan) }}">
                @csrf
                <x-adminlte-input name="dokter" label="Dari Dokter" value="{{ $konsultasi->dokter->nm_dokter }}" disabled />
                <x-adminlte-input name="jenis_permintaan" label="Jenis Permintaan" value="{{ $konsultasi->jenis_permintaan }}" disabled />
                <x-adminlte-input name="diagnosa_kerja" label="Diagnosa Kerja" value="{{ $konsultasi->diagnosa_kerja }}" disabled />
                <x-adminlte-textarea name="uraian_konsultasi" label="Uraian Konsultasi" rows='10' disabled>
                    {{ $konsultasi->uraian_konsultasi }}
                </x-adminlte-textarea>
                <x-adminlte-input name="diagnosa_kerja_jawab" label="Diagnosa Kerja" value="{{ $jawaban->diagnosa_kerja ?? '' }}" />
                <x-adminlte-textarea name="uraian_jawaban" label="Uraian Jawaban" rows='10'>
                    {{ $jawaban->uraian_jawaban ??  '' }}
                </x-adminlte-textarea>
                @if($konsultasi->kd_dokter_dikonsuli != session()->get('username'))
                <x-adminlte-button label="Simpan" theme="success" icon="fas fa-save" type="submit" disabled/>
                @else
                <x-adminlte-button label="Simpan" theme="success" icon="fas fa-save" type="submit" />
                @endif
            </form>
        </x-adminlte-card>
    </div>
</div>
<x-adminlte-modal wire:ignore.self id="modalRiwayatPemeriksaanRalan" title="Riwayat Pemeriksaan" size="lg" theme="info" v-centered
    static-backdrop scrollable>
    <livewire:component.riwayat :noRawat="$konsultasi->no_rawat" />
    
    <x-slot name="footerSlot">
        <x-adminlte-button theme="danger" label="Tutup" data-dismiss="modal" />
    </x-slot>
</x-adminlte-modal>
</div>

@push('js')
<script>
    $(document).ready(function () {
            $('#example').DataTable();
        });

        $(document).on('click', '[data-toggle="lightbox"]', function(event) {
                event.preventDefault();
                $(this).ekkoLightbox();
        });
</script>
@endpush
@stop

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
    </script>
@stop
