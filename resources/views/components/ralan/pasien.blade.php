<div>
    <x-adminlte-profile-widget name="{{$data->nm_pasien ?? '-'}}" desc="{{$data->no_rkm_medis ?? '-'}}" theme="lightblue" layout-type="classic" img="https://simrs.rsbhayangkaranganjuk.com/webapps/photopasien/{{$data->gambar ?? 'avatar.png'}}">
        <x-adminlte-profile-row-item icon="fas fa-fw fa-book-medical" title="No Rawat" text="{{$data->no_rawat ?? '-'}}" />
        <x-adminlte-profile-row-item icon="fas fa-fw fa-calendar" title="Tgl Lahir" text="{{$data->tgl_lahir  ?? '-'}}"/>
        <x-adminlte-profile-row-item icon="fas fa-fw fa-clock" title="Umur" text="{{$data->umur ?? '-'}}"/>
        <x-adminlte-profile-row-item icon="fas fa-fw fa-wallet" title="Cara Bayar" text="{{$data->png_jawab ?? '-'}}"/>
        <x-adminlte-profile-row-item icon="fas fa-fw fa-phone" title="No Telp" text="{{$data->no_tlp ?? '-'}}"/>
        <x-adminlte-profile-row-item icon="fas fa-fw fa-building" title="Pekerjaan" text="{{$data->pekerjaan ?? '-'}}"/>
        <x-adminlte-profile-row-item icon="fas fa-fw fa-id-card" title="No Peserta" text="{{$data->no_peserta ?? '-'}}"/>
        <x-adminlte-profile-row-item icon="fas fa-fw fa-map" title="Alamat" text="{{$data->alamat ?? '-'}}"/>
        <x-adminlte-profile-row-item icon="fas fa-fw fa-sticky-note" title="Catatan" text="{{$data->catatan ?? '-'}}"/>
        <div class="p-0 col-12">
            <span class="nav-link">
                <x-adminlte-button label="Riwayat Pemeriksaan" data-toggle="modal" data-target="#modalRiwayatPemeriksaanRalan" class="bg-info justify-content-end"/>
            </span>
            <span class="nav-link">
                <x-adminlte-button label="Berkas RM Lama" onclick="getBerkasRM()" class="bg-success justify-content-end"/>
            </span>
        </div>
        <span class="nav-link">
            <x-adminlte-input-file id="fileupload" name="fileupload" igroup-size="sm" accept="image/*" placeholder="Berkas Digital" legend="Pilih">
                <x-slot name="appendSlot">
                    <x-adminlte-button theme="primary" onclick="uploadFile()" label="Upload"/>
                </x-slot>
                <x-slot name="prependSlot">
                    <div class="input-group-text text-primary">
                        <i class="fas fa-file-upload"></i>
                    </div>
                </x-slot>
            </x-adminlte-input-file>
        </span>
    </x-adminlte-profile-widget>
</div>

<x-adminlte-modal id="modalBerkasRM" title="Berkas RM" size="lg" theme="info"
    icon="fas fa-bell" v-centered static-backdrop scrollable>
    <div class="container" style="color:#0d2741">
        <div class="row row-cols-auto">
            <div class="col mb-3 body-modal-berkasrm">
            </div>
        </div>
    </div>
</x-adminlte-modal>

@push('js')
    {{-- <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
    <script>
        function uploadFile() {
            var file_data = $('#fileupload').prop('files')[0];
            var form_data = new FormData();
            form_data.append('file', file_data);
            form_data.append('no_rawat', '{{$data->no_rawat}}');
            form_data.append('url', '{{url()->current()}}');
            $.ajax({
                url: "https://simrs.rsbhayangkaranganjuk.com/webapps/edokterfile.php",
                type: "POST",
                data: form_data,
                contentType: false,
                cache: false,
                processData: false,
                success: function (data) {
                    Swal.fire({
                        title: 'Sukses',
                        text: 'Berkas berhasil diupload',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    })
                },
                error: function (data) {
                    Swal.fire({
                        title: 'Sukses',
                        text: 'Berkas berhasil diupload',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    })
                }
            });
        }

        function getBerkasRM() {
            $.ajax({
                url: "/berkas/{{$data->no_rawat}}",
                type: "GET",
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
                success: function (data) {
                    Swal.close();
                    if(data.status == 'success'){
                        var html = '';
                        data.data.forEach(function(item){
                            let decoded = decodeURIComponent(item.lokasi_file);
                            html += '<a href="https://simrs.rsbhayangkaranganjuk.com/webapps/berkasrawat/'+decoded+'" data-toggle="lightbox" data-gallery="example-gallery" class="col-sm-4"><img src="https://simrs.rsbhayangkaranganjuk.com/webapps/berkasrawat/'+decoded+'" class="img-fluid" style="width:200px;height:400px;"></a>';
                            
                        });
                        $('.body-modal-berkasrm').html(html);
                        $('#modalBerkasRM').modal('show');
                    }else{
                        Swal.fire({
                            title: 'Kosong',
                            text: data.message,
                            icon: 'info',
                            confirmButtonText: 'OK'
                        })
                    }
                },
                error: function (data) {
                    console.log('Error:', data);
                }
            });
        }
    </script>
    <script>
        $(document).on('click', '[data-toggle="lightbox"]', function(event) {
                event.preventDefault();
                $(this).ekkoLightbox();
                $('#modalBerkasRM').modal('hide');
            });
    </script>
@endpush