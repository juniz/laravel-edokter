@extends('adminlte::page')

@section('title', 'Rujukan Internal')

@section('content_header')
    <h1>Rujukan Internal</h1>
@stop

@section('content')
{{-- <x-ralan.riwayat :no-rawat="$noRawat" /> --}}
{{-- <x-ralan.riwayat :no-rawat="request()->get('no_rawat')" /> --}}
<div class="row">
    <div class="col-md-4">
        <x-ralan.pasien :no-rawat="request()->get('no_rawat')" />
    </div>
    <div class="col-md-8 d-flex flex-column">
        <x-adminlte-card title="Konsul Rujukan Internal" theme="dark" theme-mode="outline" >
            <div class="row">
                <div class="col-md-3 mb-auto label-side">
                    Poli Perujuk
                </div>
                <div class="col-md-9 pb-2">
                    {{$regPeriksa->nm_poli ?? ''}}
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-auto label-side">
                    Dokter Perujuk
                </div>
                <div class="col-md-9 pb-2">
                    {{$regPeriksa->nm_dokter ?? ''}}
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-auto label-side">
                    Catatan Konsul
                </div>
                <div class="col-md-9 pb-2">
                    {{$rujukan->konsul ?? ''}}
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-auto label-side">
                    Pemeriksaan
                </div>
                <x-adminlte-textarea name="pemeriksaan" fgroup-class="col-md-9" rows="4" placeholder="Tulis pemeriksaan konsul di sini">
                    {{$rujukan->pemeriksaan ?? ''}}
                </x-adminlte-textarea>
            </div>
            <div class="row">
                <div class="col-md-3 mb-auto label-side">
                    Diagnosa
                </div>
                <x-adminlte-textarea name="diagnosa" fgroup-class="col-md-9" rows="4" placeholder="Tulis diagnosa di sini">
                    {{$rujukan->diagnosa ?? ''}}
                </x-adminlte-textarea>
            </div>
            <div class="row">
                <div class="col-md-3 mb-auto label-side">
                    Saran
                </div>
                <x-adminlte-textarea name="saran" fgroup-class="col-md-9" rows="4" placeholder="Tulis saran di sini">
                    {{$rujukan->saran ?? ''}}
                </x-adminlte-textarea>
            </div>
            <div class="d-flex flex-row-reverse">
                <x-adminlte-button label="Kirim" onclick="updateRujukan(event)" theme="primary" />
            </div>
        </x-adminlte-card>
        <x-adminlte-card title="Pemeriksaan" theme="info" icon="fas fa-lg fa-bell" collapsible="collapsed" maximizable >
            <livewire:ralan.pemeriksaan :noRawat="request()->get('no_rawat')" :noRm="request()->get('no_rm')" />
            <livewire:ralan.modal.edit-pemeriksaan />
        </x-adminlte-card>
        <x-adminlte-card title="Resep" id="resepCard" theme="info" icon="fas fa-lg fa-pills" collapsible="collapsed"
            maximizable>
            <x-ralan.resep />
        </x-adminlte-card>
        <x-adminlte-card title="Konsultasi Medik" icon='fas fa-user' theme="info" maximizable collapsible="collapsed">
            <livewire:component.konsultasi-medik :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" />
        </x-adminlte-card>
    </div>
</div>
@stop

@section('plugins.TempusDominusBs4', true)
@push('css')
<style>
    .label-side{
        font-weight: bold;
        font-size: 15px;
    }
</style>
@endpush

@push('js')
<script>
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
                event.preventDefault();
                $(this).ekkoLightbox();
        });
    function updateRujukan(e){
        e.preventDefault();
        let data = {
            _token: '{{ csrf_token() }}',
            pemeriksaan: $("textarea[name='pemeriksaan']").val(),
            diagnosa: $("textarea[name='diagnosa']").val(),
            saran: $("textarea[name='saran']").val(),
        }

        $.ajax({
            url: '/ralan/rujuk-internal/update/'+"{{$encryptNoRawat}}",
            type: "PUT",
            data: data,
            format: 'json',
            beforeSend:function() {
                Swal.fire({
                    title: 'Loading....',
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                        }
                    });
                },
            success: function(response){
                if(response.status == "success"){
                    Swal.fire({
                        title: "Berhasil",
                        text: "Rujukan internal berhasil dikirim",
                        icon: "success",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "OK",
                    });
                }else{
                    Swal.fire({
                        title: "Gagal",
                        text: "Rujukan internal gagal dikirim",
                        icon: "error",
                        showCancelButton: false,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "OK",
                    });
                }
            },
            error: function(response){
                console.log(response);
                Swal.fire({
                    title: "Gagal",
                    text: response.message,
                    icon: "error",
                    showCancelButton: false,
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: "OK",
                });
            }
        });

    }
</script>
@endpush