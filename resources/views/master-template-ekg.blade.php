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
        <form method="POST" action="{{ url('/master-ekg') }}">
            @csrf
            <x-ui.input id="nama" label="Nama Template" placeholder="Nama Template" />
            <x-adminlte-text-editor name="isi" id="isi" :config='["height" => "300"]' label="Isi Template"/>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </x-adminlte-card>
    <x-adminlte-card>
        <x-adminlte-datatable id="table2" :heads="$heads" head-theme="dark" striped hoverable bordered compressed>
            @foreach($data as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->nama_template}}</td>
                    <td>{!! $item->template !!}</td>
                    <td>
                        <a type="button" class="btn btn-info" href="{{ route('master-ekg.edit', ['master_ekg' => $item->id]) }}">
                            Edit
                        </a>
                        <form action="{{ route('master-ekg.destroy', ['master_ekg' => $item->id]) }}" method="POST" class="d-inline">
                            @method('DELETE')
                            @csrf
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
        </x-adminlte-datatable>
    </x-adminlte-card>
@stop

@section('plugins.Summernote', true)

