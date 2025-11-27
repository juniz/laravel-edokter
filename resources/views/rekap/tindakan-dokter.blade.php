@extends('adminlte::page')

@section('title', 'Rekap Tindakan Dokter')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-chart-line mr-2"></i> Rekap Tindakan Dokter</h1>
</div>
@stop

@section('content')
<div class="container-fluid">
    <livewire:rekap.tindakan-dokter />
</div>
@stop

