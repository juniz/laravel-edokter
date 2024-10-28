@extends('adminlte::page')

@section('title', 'Permintaan Radiologi Ranap')

@section('content_header')
    <h1>Permintaan Radiologi Ranap</h1>
@stop

@section('content')
    <x-adminlte-card title="Data Pasien" theme="info" theme-mode="outline">
        <livewire:permintaan-radiologi-ranap-table />
    </x-adminlte-card>
@stop

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
    </script>
@stop
