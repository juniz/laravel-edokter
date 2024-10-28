@extends('adminlte::page')

@section('title', 'Permintaan Radiologi Ralan')

@section('content_header')
    <h1>Permintaan Radiologi Ralan</h1>
@stop

@section('content')
    <x-adminlte-card title="Data Pasien" theme="info" theme-mode="outline">
        <livewire:permintaan-radiologi-ralan-table />
    </x-adminlte-card>
@stop

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
    </script>
@stop
