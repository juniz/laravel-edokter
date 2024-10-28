@extends('adminlte::page')

@section('title', 'Permintaan Radiologi Ralan')

@section('content_header')
    <h1>Pemeriksaan Radiologi</h1>
@stop

@section('content')
    <x-adminlte-card theme="info" theme-mode="outline">
        <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-photo-tab" data-toggle="tab" data-target="#nav-photo" type="button" role="tab" aria-controls="nav-photo" aria-selected="true">Photo Radiologi</button>
                <button class="nav-link" id="nav-profile-tab" data-toggle="tab" data-target="#nav-bacaan" type="button" role="tab" aria-controls="nav-bacaan" aria-selected="false">Bacaan Radiologi</button>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-photo" role="tabpanel" aria-labelledby="nav-photo-tab">
                <x-adminlte-card>
                    <livewire:radiologi.photo-radiologi :no_rawat='$no_rawat'>
                </x-adminlte-card>
            </div>
            <div class="tab-pane fade" id="nav-bacaan" role="tabpanel" aria-labelledby="nav-bacaan-tab">
                <livewire:radiologi.bacaan-radiologi :no_rawat='$no_rawat' />
            </div>
        </div>
    </x-adminlte-card>
@stop

@section('css')
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
    </script>
@stop
