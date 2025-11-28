@props(['noRawat', 'collapsible' => false])

<div class="d-flex flex-column align-items-center pasien-header-container">
    <div class="pasien-photo-box text-center mb-3">
        <img src="https://simrs.rsbhayangkaranganjuk.com/webapps/photopasien/{{$data->gambar ?? 'avatar.png'}}" 
             class="img-circle elevation-2"
             style="width: 120px; height: 120px; object-fit: cover;"
             alt="Foto Pasien">
        <h6 class="mt-2 mb-0 font-weight-bold">{{$data->nm_pasien ?? '-'}}</h6>
        <h6 class="text-muted">{{$data->no_rkm_medis ?? '-'}}</h6>
        <h6 class="mt-2 mb-0 font-weight-bold">{{$data->no_rawat ?? '-'}}</h6>
    </div>
    <div class="pasien-card-box w-100 d-flex justify-content-center">
        <x-adminlte-card title="Detail Data Pasien" theme="lightblue" icon="fas fa-user-circle" collapsible="collapsed" class="pasien-card-center">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="info-box bg-light h-100">
                        <div class="info-box-content">
                            <h6 class="border-bottom pb-2 mb-3">Informasi Utama</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <i class="fas fa-fw fa-exclamation-triangle text-danger"></i>
                                        <div class="info-item-content">
                                            <strong>Alergi</strong>
                                            <span class="separator">:</span>
                                            <span class="value">{{$alergi->alergi ?? 'Tidak ada'}}</span>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-fw fa-book-medical text-primary"></i>
                                        <div class="info-item-content">
                                            <strong>No Rawat</strong>
                                            <span class="separator">:</span>
                                            <span class="value">{{$data->no_rawat ?? '-'}}</span>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-fw fa-id-card text-info"></i>
                                        <div class="info-item-content">
                                            <strong>No KTP</strong>
                                            <span class="separator">:</span>
                                            <span class="value">{{$data->no_ktp ?? '-'}}</span>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-fw fa-user text-success"></i>
                                        <div class="info-item-content">
                                            <strong>Gender</strong>
                                            <span class="separator">:</span>
                                            <span class="value">{{$data->jk == 'L' ? 'Laki - Laki' : 'Perempuan' }}</span>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-fw fa-calendar text-warning"></i>
                                        <div class="info-item-content">
                                            <strong>TTL</strong>
                                            <span class="separator">:</span>
                                            <span class="value">{{$data->tmp_lahir ?? '-'}}, {{\Carbon\Carbon::parse($data->tgl_lahir)->isoFormat('LL')  ?? '-'}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <i class="fas fa-fw fa-droplet text-danger"></i>
                                        <div class="info-item-content">
                                            <strong>Gol Darah</strong>
                                            <span class="separator">:</span>
                                            <span class="value">{{$data->gol_darah ?? '-'}}</span>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-fw fa-clock text-info"></i>
                                        <div class="info-item-content">
                                            <strong>Umur</strong>
                                            <span class="separator">:</span>
                                            <span class="value">{{$data->umur ?? '-'}}</span>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-fw fa-wallet text-success"></i>
                                        <div class="info-item-content">
                                            <strong>Cara Bayar</strong>
                                            <span class="separator">:</span>
                                            <span class="value">{{$data->png_jawab ?? '-'}}</span>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-fw fa-phone text-primary"></i>
                                        <div class="info-item-content">
                                            <strong>Telp</strong>
                                            <span class="separator">:</span>
                                            <span class="value">{{$data->no_tlp ?? '-'}}</span>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-fw fa-building text-warning"></i>
                                        <div class="info-item-content">
                                            <strong>Pekerjaan</strong>
                                            <span class="separator">:</span>
                                            <span class="value">{{$data->pekerjaan ?? '-'}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="info-box bg-light h-100">
                        <div class="info-box-content">
                            <h6 class="border-bottom pb-2 mb-3">Informasi Tambahan</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <i class="fas fa-fw fa-user text-info"></i>
                                        <div class="info-item-content">
                                            <strong>Nama Ibu</strong>
                                            <span class="separator">:</span>
                                            <span class="value">{{$data->nm_ibu ?? '-'}}</span>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-fw fa-map text-success"></i>
                                        <div class="info-item-content">
                                            <strong>Alamat</strong>
                                            <span class="separator">:</span>
                                            <span class="value">{{$data->alamat ?? '-'}}</span>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-fw fa-user text-primary"></i>
                                        <div class="info-item-content">
                                            <strong>Keluarga</strong>
                                            <span class="separator">:</span>
                                            <span class="value">{{$data->namakeluarga ?? '-'}}</span>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-fw fa-ring text-warning"></i>
                                        <div class="info-item-content">
                                            <strong>Status</strong>
                                            <span class="separator">:</span>
                                            <span class="value">{{$data->stts_nikah ?? '-'}}</span>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-fw fa-book text-danger"></i>
                                        <div class="info-item-content">
                                            <strong>Agama</strong>
                                            <span class="separator">:</span>
                                            <span class="value">{{$data->agama ?? '-'}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <i class="fas fa-fw fa-briefcase text-info"></i>
                                        <div class="info-item-content">
                                            <strong>Pekerjaan PJ</strong>
                                            <span class="separator">:</span>
                                            <span class="value">{{$data->pekerjaanpj ?? '-'}}</span>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-fw fa-map text-success"></i>
                                        <div class="info-item-content">
                                            <strong>Alamat PJ</strong>
                                            <span class="separator">:</span>
                                            <span class="value">{{$data->alamatpj ?? '-'}}</span>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-fw fa-id-card text-primary"></i>
                                        <div class="info-item-content">
                                            <strong>No Peserta</strong>
                                            <span class="separator">:</span>
                                            <span class="value">{{$data->no_peserta ?? '-'}}</span>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-fw fa-sticky-note text-warning"></i>
                                        <div class="info-item-content">
                                            <strong>Catatan</strong>
                                            <span class="separator">:</span>
                                            <span class="value">{{$data->catatan ?? '-'}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer p-2 bg-light">
                <div class="d-flex flex-wrap justify-content-end" style="gap: 5px;">
                    <button onclick="openRiwayatTab()" class="btn btn-xs btn-info"><i class="fas fa-history"></i> Riwayat</button>
                    <button id="icare-button" class="btn btn-xs btn-success" onclick="openIcareModal()"><i class="fas fa-heart"></i> I-Care</button>
                    <button id="btn-rm" data-rm="{{$data->no_rkm_medis}}" class="btn btn-xs btn-success"><i class="fas fa-folder-open"></i> RM Digital</button>
                    <button onclick="getBerkasRetensi()" class="btn btn-xs btn-secondary"><i class="fas fa-archive"></i> Retensi</button>
                    <button id="echo-button" data-toggle="modal" data-target="#modalEcho" class="btn btn-xs btn-info"><i class="fas fa-file-medical"></i> ECHO</button>
                    <button data-toggle="modal" data-target="#modal-upload-berkas" class="btn btn-xs btn-success"><i class="fas fa-file-upload"></i> Upload</button>
                </div>
            </div>
        </x-adminlte-card>
    </div>
</div>


<!-- Modals and other components that were in the original file can be included here or kept in the main page if they are shared -->
    <!-- For now, I'll assume the modals are already present in the main page or I should include them if they are specific to this component. -->
    <!-- The original component included modals. I should probably include them here too to ensure functionality works. -->
    
    <x-adminlte-modal wire:ignore.self id="modalEcho" title="ECHO" size="lg" theme="info" icon="fas fa-file" v-centered static-backdrop scrollable>
        <form id="form-echo" method="POST" action="{{ url('print-ekg') }}">
            @csrf
            <input type="hidden" name="no_rm" value="{{ $data->no_rkm_medis }}" >
            <input type="hidden" name="no_rawat" value="{{ $data->no_rawat }}" >
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Template Echo</label>
                        <select name="echo-select" id="echo-select" class="form-control select2" style="width: 100%;">
                            <option value=""></option>
                            @foreach($echoTemplates as $item)
                                <option value="{{$item->id}}">{{$item->nama_template}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Dokter Pengirim</label>
                        <select name="dokter_pengirim" id="dokter_pengirim" class="form-control select2" style="width: 100%;">
                            <option value=""></option>
                            @foreach($dokterList as $item)
                                <option value="{{$item->kd_dokter}}">{{$item->nm_dokter}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <textarea name="isi" id="isi-echo" class="form-control summernote" style="height: 300px;"></textarea>
            <button type="submit" class="btn btn-success mt-3">Cetak</button>
        </form>
        <x-slot name="footerSlot">
            <x-adminlte-button theme="danger" label="Tutup" data-dismiss="modal"/>
        </x-slot>
    </x-adminlte-modal>

    <!-- Other modals -->
    <x-adminlte-modal id="modalBerkasRM" class="modal-fullscreen-custom" title="Berkas RM" size="xl" theme="info" icon="fas fa-folder-open" v-centered static-backdrop scrollable>
        <div class="body-modal-berkasrm" style="gap:20px"></div>
    </x-adminlte-modal>

    <x-adminlte-modal id="modal-rm" class="modal-fullscreen-custom" title="Berkas RM" size="xl" theme="info" icon="fas fa-folder-open" v-centered scrollable>
        <div id="berkas-rm-container">
            <livewire:component.berkas-rm :rm="$data->no_rkm_medis ?? null" :key="'berkas-rm-'.($data->no_rkm_medis ?? 'default')" />
        </div>
    </x-adminlte-modal>

    <x-adminlte-modal id="icare-modal" title="I-Care BPJS" size="lg" theme="info" icon="fas fa-heart" v-centered static-backdrop scrollable>
        <div class="container-fluid container-icare"></div>
        <x-slot name="footerSlot">
            <x-adminlte-button theme="danger" label="Tutup" data-dismiss="modal"/>
        </x-slot>
    </x-adminlte-modal>

    <x-adminlte-modal id="modalBerkasRetensi" title="Berkas Retensi" size="lg" theme="info" icon="fas fa-archive" v-centered static-backdrop scrollable>
        <div class="container-retensi" style="color:#0d2741"></div>
        <x-slot name="footerSlot">
            <x-adminlte-button theme="danger" label="Tutup" data-dismiss="modal"/>
        </x-slot>
    </x-adminlte-modal>

    <!-- Modal Riwayat Pemeriksaan -->
    <x-adminlte-modal wire:ignore.self id="modalRiwayatPemeriksaan" title="Riwayat Pemeriksaan" size="xl" theme="info" icon="fas fa-history" v-centered static-backdrop scrollable>
        <div id="riwayat-container">
            <livewire:component.riwayat :noRawat="$data->no_rawat" :key="'riwayat-'.$data->no_rawat" />
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button theme="danger" label="Tutup" data-dismiss="modal"/>
        </x-slot>
    </x-adminlte-modal>

    <livewire:component.change-phone />
    <livewire:component.change-umur />
    <livewire:component.upload-berkas-digital :noRawat="$data->no_rawat" />

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css">
<style>
    /* Modal Fullscreen Custom */
    #modalBerkasRM .modal-dialog,
    #modal-rm .modal-dialog {
        max-width: 98vw !important;
        width: 98vw !important;
        height: 95vh !important;
        margin: 1rem auto !important;
    }
    #modalBerkasRM .modal-content,
    #modal-rm .modal-content {
        height: 100% !important;
        border-radius: 8px;
    }
    #modalBerkasRM .modal-body,
    #modal-rm .modal-body {
        height: calc(100% - 60px) !important;
        overflow-y: auto;
        padding: 1rem;
    }
    #modalBerkasRM .body-modal-berkasrm {
        min-height: 100%;
    }
    @media (max-width: 767.98px) {
        #modalBerkasRM .modal-dialog,
        #modal-rm .modal-dialog {
            max-width: 100vw !important;
            width: 100vw !important;
            height: 100vh !important;
            margin: 0 !important;
        }
        #modalBerkasRM .modal-content,
        #modal-rm .modal-content {
            border-radius: 0;
        }
    }
    
    .info-box {
        min-height: auto;
        padding: 0;
        box-shadow: 0 1px 3px rgba(0,0,0,.12), 0 1px 2px rgba(0,0,0,.24);
        transition: all 0.3s cubic-bezier(.25,.8,.25,1);
    }
    .info-box:hover {
        box-shadow: 0 3px 6px rgba(0,0,0,.16), 0 3px 6px rgba(0,0,0,.23);
    }
    .info-box-content {
        padding: 1.25rem;
    }
    .img-circle {
        border: 3px solid #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,.15);
        transition: transform 0.2s ease;
    }
    .img-circle:hover {
        transform: scale(1.05);
    }
    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        font-size: 0.9rem;
        line-height: 1.4;
        margin-bottom: 0.5rem;
        text-align: left;
    }
    .info-item i {
        margin-top: 0.25rem;
        width: 20px;
        text-align: left;
    }
    .info-item-content {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        text-align: left;
        flex: 1;
    }
    .info-item strong {
        min-width: 90px;
        color: #666;
        display: inline-block;
        text-align: left;
    }
    .info-item .separator {
        color: #666;
        margin: 0 0.25rem;
        display: inline-block;
        width: 10px;
        text-align: center;
    }
    .info-item .value {
        color: #333;
        text-align: left;
        flex: 1;
    }
    h6 {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 1rem;
        text-align: left;
    }
    .border-bottom {
        border-color: rgba(0,0,0,.1) !important;
    }
    .text-center {
        text-align: left !important;
    }
    .btn-group {
        box-shadow: 0 1px 3px rgba(0,0,0,.12);
        border-radius: 0.25rem;
        overflow: hidden;
    }
    .btn-group .btn {
        border: none;
        margin: 0;
    }
    @media (max-width: 767.98px) {
        .d-flex.justify-content-between {
            flex-direction: column;
            align-items: stretch !important;
        }
        .btn-group {
            display: flex;
            width: 100%;
        }
        .btn-group .btn {
            flex: 1;
        }
        .info-box {
            margin-bottom: 1rem;
        }
        .col-md-2 {
            margin-bottom: 1.5rem;
        }
    }
    .pasien-photo:hover {
        opacity: 0.9;
        transition: opacity 0.2s ease;
    }
    /* Tambahan untuk layout baru */
    .col-md-10 .card {
        margin-bottom: 0;
    }
    @media (max-width: 767.98px) {
        .col-md-10 {
            margin-top: 1rem;
        }
    }
    .pasien-header-container {
        margin-bottom: 1.5rem;
    }
    .pasien-photo-box {
        min-width: 140px;
        max-width: 180px;
    }
    .pasien-card-box {
        min-width: 0;
    }
    .pasien-card-center {
        max-width: 100%;
        width: 100%;
        margin-left: auto;
        margin-right: auto;
        margin-top: 1.2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        border-radius: 12px;
        transition: box-shadow 0.2s, max-width 0.2s, margin 0.2s;
    }
    .pasien-card-center.collapsed-card {
        max-width: 100%;
        margin-left: 0;
        margin-right: 0;
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        opacity: 0.95;
    }
    .pasien-card-center .card-header {
        display: flex;
        justify-content: stretch;
        align-items: center;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
    .pasien-card-center .card-header .card-title {
        width: 100%;
        text-align: center;
    }
    .pasien-card-center .card-body {
        padding: 1.5rem 1.2rem;
    }
    @media (max-width: 767.98px) {
        .pasien-card-center {
            max-width: 100%;
            margin-top: 1rem;
            border-radius: 8px;
        }
        .pasien-card-center .card-body {
            padding: 1rem 0.7rem;
        }
    }
    @media (max-width: 900px) {
        .pasien-card-center .row.g-3 {
            flex-direction: column !important;
        }
        .pasien-card-center .col-12,
        .pasien-card-center .col-md-6 {
            width: 100% !important;
            max-width: 100% !important;
            margin-bottom: 1rem;
        }
        .pasien-card-center .info-box {
            margin-bottom: 0.75rem;
        }
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>
<script>
    $(document).ready(function() {
        // Inisialisasi WYSIWYG Summernote untuk textarea ECHO
        if (typeof $.fn.summernote !== 'undefined') {
            $('#isi-echo').summernote({
                height: 300,
                placeholder: 'Tulis hasil ECHO di sini...',
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'picture', 'table']],
                    ['view', ['fullscreen', 'codeview']]
                ]
            });
        }

        const templateEndpoint = "{{ url('template-ekg') }}";

        // Fungsi untuk membuka accordion saat foto diklik
        $('.pasien-photo').click(function() {
            const firstAccordion = $('#accordionRiwayatPemeriksaan .pemeriksaan-item:first');
            const firstCollapse = firstAccordion.find('.collapse');
            
            // Buka accordion pertama
            firstCollapse.collapse('show');
            
            // Scroll ke accordion yang dibuka
            $('html, body').animate({
                scrollTop: firstAccordion.offset().top - 100
            }, 500);
        });

        $('#echo-select').on('change', function() {
            const templateId = $(this).val();

            if (typeof $.fn.summernote === 'undefined') {
                return;
            }

            if (!templateId) {
                $('#isi-echo').summernote('code', '');
                return;
            }

            $.ajax({
                url: `${templateEndpoint}/${templateId}`,
                type: 'GET',
                success: function(response) {
                    if (response.status === 'success' && response.data) {
                        $('#isi-echo').summernote('code', response.data.template || '');
                    } else {
                        $('#isi-echo').summernote('code', '');
                        Swal.fire({
                            title: 'Gagal',
                            text: response.message || 'Template tidak ditemukan.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    $('#isi-echo').summernote('code', '');
                    let message = 'Gagal memuat template.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'Gagal',
                        text: message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    });

</script>
@endpush

<script>
    window.openIcareModal = function() {
        console.log('openIcareModal called');
        
        // Pastikan jQuery dan Swal sudah ter-load
        if (typeof $ === 'undefined') {
            console.error('jQuery is not loaded');
            alert('jQuery belum ter-load. Silakan refresh halaman.');
            return;
        }
        
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 is not loaded');
            alert('SweetAlert2 belum ter-load. Silakan refresh halaman.');
            return;
        }
        
        let kdDokter = "{{ session()->get('username') }}";
        let param = "{{ $data->no_peserta ?? '' }}";
        
        console.log('kdDokter:', kdDokter);
        console.log('param:', param);
        
        // Validasi no_peserta
        if (!param || param === '') {
            Swal.fire({
                title: 'Gagal',
                text: 'No. Peserta BPJS tidak ditemukan untuk pasien ini.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        $.ajax({
            url: '/api/icare',
            type: 'POST',
            data: {
                kodedokter: kdDokter,
                param: param
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                Swal.fire({
                    title: 'Loading....',
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(data) {
                console.log('I-Care API Response:', data);
                Swal.close();
                if(data.code == 200){
                    let url = data.data.url;
                    let html = '';
                    $('#icare-modal').modal('show');
                    html += '<iframe src="'+url+'" frameborder="0" height="700px" width="100%"></iframe>';
                    $('.container-icare').html(html);
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message || 'Terjadi kesalahan saat memuat data I-Care.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                console.log('I-Care API Error:', xhr);
                Swal.close();
                let errorMessage = 'Terjadi kesalahan saat memuat data I-Care.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire({
                    title: 'Gagal',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    };

    // Function untuk membuka modal Retensi - didefinisikan di global scope
    window.getBerkasRetensi = function() {
        console.log('getBerkasRetensi called');
        
        // Pastikan jQuery dan Swal sudah ter-load
        if (typeof $ === 'undefined') {
            console.error('jQuery is not loaded');
            alert('jQuery belum ter-load. Silakan refresh halaman.');
            return;
        }
        
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 is not loaded');
            alert('SweetAlert2 belum ter-load. Silakan refresh halaman.');
            return;
        }
        
        let noRM = "{{ $data->no_rkm_medis ?? '' }}";
        
        console.log('noRM:', noRM);
        
        // Validasi noRM
        if (!noRM || noRM === '') {
            Swal.fire({
                title: 'Gagal',
                text: 'No. Rekam Medis tidak ditemukan.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        $.ajax({
            url: "/berkas-retensi/" + noRM,
            type: "GET",
            beforeSend: function() {
                Swal.fire({
                    title: 'Loading....',
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(data) {
                console.log('Retensi API Response:', data);
                Swal.close();
                if(data.status == 'success'){
                    let decode = decodeURIComponent(data.data.lokasi_pdf);
                    var html = '';
                    html += '<iframe src="http://simrs.rsbhayangkaranganjuk.com/webapps/medrec/'+decode+'" frameborder="0" height="700px" width="100%"></iframe>';
                    $('.container-retensi').html(html);
                    $('#modalBerkasRetensi').modal('show');
                } else {
                    Swal.fire({
                        title: 'Kosong',
                        text: data.message || 'Data retensi tidak ditemukan.',
                        icon: 'info',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                console.log('Retensi API Error:', xhr);
                Swal.close();
                let errorMessage = 'Terjadi kesalahan saat memuat data retensi.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire({
                    title: 'Gagal',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    };

    function openModalUmur(noRm, umur, tglLahir) {
        @if(config('livewire.inject_assets', true))
        Livewire.emit('setRmUmur', noRm, umur, tglLahir);
        @endif
        setTimeout(function() {
            $('#change-umur').modal('show');
        }, 100);
    }

    function openRiwayatTab() {
        // Buka modal riwayat
        $('#modalRiwayatPemeriksaan').modal('show');
    }

    // Handle modal shown event untuk memuat data riwayat saat modal dibuka
    $(document).ready(function() {
        // Event listener untuk modal shown
        $('#modalRiwayatPemeriksaan').on('shown.bs.modal', function () {
            // Tunggu sebentar untuk memastikan komponen Livewire sudah ter-render
            setTimeout(function() {
                @if(config('livewire.inject_assets', true))
                if (typeof Livewire !== 'undefined') {
                    // Emit global event - ini akan diterima oleh semua komponen yang mendengarkan
                    Livewire.emit('loadRiwayatPasien');
                    
                    // Juga coba dengan cara lain untuk memastikan event terpanggil
                    setTimeout(function() {
                        Livewire.emit('loadRiwayatPasien');
                    }, 200);
                }
                @endif
            }, 300);
        });
    });

    // Event handler dengan event delegation sebagai backup
    $(document).ready(function() {
        console.log('Document ready, setting up button handlers');
        
        // Event delegation untuk tombol I-Care
        $(document).on('click', '#icare-button', function(event) {
            event.preventDefault();
            console.log('I-Care button clicked via delegation');
            if (typeof window.openIcareModal === 'function') {
                window.openIcareModal();
            } else {
                console.error('openIcareModal function not found');
            }
        });
        
        // Event delegation untuk tombol Retensi
        $(document).on('click', 'button[onclick="getBerkasRetensi()"]', function(event) {
            event.preventDefault();
            console.log('Retensi button clicked via delegation');
            if (typeof window.getBerkasRetensi === 'function') {
                window.getBerkasRetensi();
            } else {
                console.error('getBerkasRetensi function not found');
            }
        });
    });

    // Juga attach setelah Livewire update
    document.addEventListener('livewire:load', function () {
        console.log('Livewire loaded, attaching button handlers');
        
        // Handler untuk I-Care
        $(document).on('click', '#icare-button', function(event) {
            event.preventDefault();
            console.log('I-Care button clicked after Livewire load');
            if (typeof window.openIcareModal === 'function') {
                window.openIcareModal();
            } else {
                console.error('openIcareModal function not found');
            }
        });
        
        // Handler untuk Retensi
        $(document).on('click', 'button[onclick="getBerkasRetensi()"]', function(event) {
            event.preventDefault();
            console.log('Retensi button clicked after Livewire load');
            if (typeof window.getBerkasRetensi === 'function') {
                window.getBerkasRetensi();
            } else {
                console.error('getBerkasRetensi function not found');
            }
        });
    });

    document.addEventListener('livewire:load', function () {
        window.addEventListener('refreshUmur', function (event) {
            var umurDisplay = document.getElementById('umur-display');
            if (umurDisplay && event.detail && event.detail.umur) {
                umurDisplay.textContent = event.detail.umur;
            }
            $('#change-umur').modal('hide');
            // Reload halaman untuk refresh data
            setTimeout(function() {
                location.reload();
            }, 500);
        });
    });
</script>