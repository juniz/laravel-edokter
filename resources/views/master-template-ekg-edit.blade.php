@extends('adminlte::page')

@section('title', 'Master Template EKG')

@section('content_header')
    <h1>Master Template EKG</h1>
@stop

@section('content')
    @if(Session::has('success'))
        <x-adminlte-alert theme="success" title="Sukses">
            {{ Session::get('success') }}
        </x-adminlte-alert>
    @endif
    @if(Session::has('error'))
        <x-adminlte-alert theme="danger" title="Gagal">
            {{ Session::get('error') }}
        </x-adminlte-alert>
    @endif
    <x-adminlte-card>
        <form method="POST" action="{{ url('/master-ekg', ['master_ekg' => $data->id]) }}">
            @method('PUT')
            @csrf
            {{-- <x-ui.input id="id_template" name="id_template" type="hidden" value="{{ $data->id }}" /> --}}
            <x-ui.input id="nama" label="Nama Template" value="{{ $data->nama_template }}" placeholder="Nama Template" />
            <x-adminlte-text-editor name="isi" id="isi" :config='["height" => "300"]' label="Isi Template">
            {{ $data->template }}
            </x-adminlte-text-editor>
            <button type="submit" class="btn btn-primary">Ubah</button>
        </form>
    </x-adminlte-card>
@stop

@section('plugins.Summernote', true)

