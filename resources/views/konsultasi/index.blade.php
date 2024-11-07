@extends('adminlte::page')

@section('title', 'Permintaan Konsultasi Medik')

@section('content_header')
    <h1>Permintaan Konsultasi Medik</h1>
@stop

@section('content')
@if($success = Session::get('success'))
<x-adminlte-alert theme="success" title="Berhasil">
    {{ $success }}
</x-adminlte-alert>
@endif
    <x-adminlte-card theme="info" theme-mode="outline">
        <livewire:konsultasi-medik-table />
    </x-adminlte-card>
@stop

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
    </script>
@stop
