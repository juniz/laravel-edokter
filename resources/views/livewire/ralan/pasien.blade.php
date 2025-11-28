<div>
    <style>
        .accordion .card-header button:not(.collapsed) .fa-chevron-down {
            transform: rotate(180deg);
        }
        .accordion .card-header button .fa-chevron-down {
            transition: transform 0.3s ease;
            display: inline-block;
        }
    </style>
    @if($data)
    <div class="card card-widget widget-user-2 shadow-sm mb-3">
        <!-- Add the bg color to the header using any of the bg-* classes -->
        <div class="widget-user-header bg-primary p-3">
            <div class="d-flex align-items-start">
                <div class="widget-user-image mr-3">
                    <img class="img-circle elevation-2" src="https://simrs.rsbhayangkaranganjuk.com/webapps/photopasien/{{$data->gambar ?? 'avatar.png'}}" alt="User Avatar" style="width: 65px; height: 65px; object-fit: cover;">
                </div>
                <div class="flex-grow-1 d-flex justify-content-between">
                    <div class="d-flex flex-column" style="align-items: flex-start;">
                        <span class="text-white mb-1" style="font-size: 0.95rem; opacity: 0.95; text-align: left;">No. RM: {{$data->no_rkm_medis}}</span>
                        <div class="d-flex flex-wrap align-items-center" style="gap: 12px; font-size: 0.9rem; text-align: left;">
                            <span class="text-white"><i class="fas fa-{{$data->jk == 'L' ? 'mars' : 'venus'}}"></i> {{$data->jk == 'L' ? 'Laki-Laki' : 'Perempuan'}}</span>
                            <span class="text-white"><i class="fas fa-droplet"></i> {{$data->gol_darah}}</span>
                        </div>
                    </div>
                    <div class="d-flex flex-column align-items-end" style="gap: 5px;">
                        <span class="badge badge-light badge-lg" style="font-size: 0.85rem; padding: 0.35rem 0.65rem;">{{$data->no_rawat}}</span>
                        <span class="badge badge-warning badge-lg" style="font-size: 0.85rem; padding: 0.35rem 0.65rem;">{{$data->png_jawab}}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer p-0">
            <div class="card-body p-2">
                <!-- Desktop View: 4 Columns -->
                <div class="row d-none d-md-flex" style="font-size: 0.9rem;">
                    <!-- Identitas -->
                    <div class="col-md-3 border-right">
                        <h6 class="text-primary mb-2" style="font-weight: 600; border-bottom: 1px solid #dee2e6; padding-bottom: 5px;">Identitas</h6>
                        <dl class="mb-0">
                            <dt style="font-size: 0.85rem; color: #6c757d;">No. KTP</dt>
                            <dd class="mb-2" style="font-size: 0.9rem;">{{$data->no_ktp ?? '-'}}</dd>
                            <dt style="font-size: 0.85rem; color: #6c757d;">Nama</dt>
                            <dd class="mb-2" style="font-size: 0.9rem;">{{$data->nm_pasien}}</dd>
                            <dt style="font-size: 0.85rem; color: #6c757d;">TTL</dt>
                            <dd class="mb-2" style="font-size: 0.9rem;">{{$data->tmp_lahir}}, {{$data->tgl_lahir ? \Carbon\Carbon::parse($data->tgl_lahir)->isoFormat('D MMM Y') : '-'}}</dd>
                            <dt style="font-size: 0.85rem; color: #6c757d;">Umur</dt>
                            <dd class="mb-2" style="font-size: 0.9rem;">
                                <span style="display: flex; align-items: center;">
                                    <span id="umur-display">{{$data->umur}}</span>
                                    <button class="btn btn-xs btn-success ml-2" 
                                            title="Edit Umur" 
                                            onclick="openModalUmur('{{$data->no_rkm_medis}}', '{{$data->umur}}', '{{$data->tgl_lahir}}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </span>
                            </dd>
                            <dt style="font-size: 0.85rem; color: #6c757d;">Pendidikan</dt>
                            <dd class="mb-2" style="font-size: 0.9rem;">{{$data->pnd}}</dd>
                            <dt style="font-size: 0.85rem; color: #6c757d;">Pekerjaan</dt>
                            <dd class="mb-2" style="font-size: 0.9rem;">{{$data->pekerjaan}}</dd>
                            <dt style="font-size: 0.85rem; color: #6c757d;">Agama</dt>
                            <dd class="mb-0" style="font-size: 0.9rem;">{{$data->agama}}</dd>
                        </dl>
                    </div>

                    <!-- Kontak -->
                    <div class="col-md-3 border-right">
                        <h6 class="text-primary mb-2" style="font-weight: 600; border-bottom: 1px solid #dee2e6; padding-bottom: 5px;">Kontak</h6>
                        <dl class="mb-0">
                            <dt style="font-size: 0.85rem; color: #6c757d;">No. Tlp</dt>
                            <dd class="mb-2" style="font-size: 0.9rem;">
                                {{$data->no_tlp}}
                                <button class="btn btn-xs btn-success ml-2" title="Edit"><i class="fas fa-edit"></i></button>
                            </dd>
                            <dt style="font-size: 0.85rem; color: #6c757d;">Alamat</dt>
                            <dd class="mb-2" style="font-size: 0.9rem;">{{$data->alamat}}</dd>
                            <dt style="font-size: 0.85rem; color: #6c757d;">Wilayah</dt>
                            <dd class="mb-0" style="font-size: 0.9rem;">
                                {{$data->nm_kel}}, {{$data->nm_kec}}, {{$data->nm_kab}}, {{$data->nm_prop}}
                            </dd>
                        </dl>
                    </div>

                    <!-- Medis -->
                    <div class="col-md-3 border-right">
                        <h6 class="text-primary mb-2" style="font-weight: 600; border-bottom: 1px solid #dee2e6; padding-bottom: 5px;">Medis</h6>
                        <dl class="mb-0">
                            <dt style="font-size: 0.85rem; color: #6c757d;">Gol. Darah</dt>
                            <dd class="mb-2" style="font-size: 0.9rem;">{{$data->gol_darah}}</dd>
                            <dt style="font-size: 0.85rem; color: #6c757d;">Nama Ibu</dt>
                            <dd class="mb-2" style="font-size: 0.9rem;">{{$data->nm_ibu}}</dd>
                            <dt style="font-size: 0.85rem; color: #6c757d;">Catatan</dt>
                            <dd class="mb-0" style="font-size: 0.9rem;">
                                @if($data->catatan)
                                <div class="alert alert-warning p-1 mb-0" style="font-size: 0.85rem;">
                                    {{$data->catatan}}
                                </div>
                                @else
                                -
                                @endif
                            </dd>
                        </dl>
                    </div>

                    <!-- Penanggung Jawab -->
                    <div class="col-md-3">
                        <h6 class="text-primary mb-2" style="font-weight: 600; border-bottom: 1px solid #dee2e6; padding-bottom: 5px;">Penanggung Jawab</h6>
                        <dl class="mb-0">
                            <dt style="font-size: 0.85rem; color: #6c757d;">Nama PJ</dt>
                            <dd class="mb-2" style="font-size: 0.9rem;">{{$data->namakeluarga}}</dd>
                            <dt style="font-size: 0.85rem; color: #6c757d;">Pekerjaan</dt>
                            <dd class="mb-2" style="font-size: 0.9rem;">{{$data->pekerjaanpj}}</dd>
                            <dt style="font-size: 0.85rem; color: #6c757d;">Alamat</dt>
                            <dd class="mb-0" style="font-size: 0.9rem;">{{$data->alamatpj}}</dd>
                        </dl>
                    </div>
                </div>

                <!-- Mobile View: Accordion -->
                <div class="accordion d-md-none" id="pasienAccordion" style="font-size: 0.9rem;">
                    <!-- Identitas -->
                    <div class="card">
                        <div class="card-header p-2" id="headingIdentitas">
                            <h6 class="mb-0">
                                <button class="btn btn-link btn-block text-left text-primary" type="button" data-toggle="collapse" data-target="#collapseIdentitas" aria-expanded="true" aria-controls="collapseIdentitas" style="font-weight: 600; text-decoration: none;">
                                    <i class="fas fa-chevron-down mr-2"></i> Identitas
                                </button>
                            </h6>
                        </div>
                        <div id="collapseIdentitas" class="collapse show" aria-labelledby="headingIdentitas" data-parent="#pasienAccordion">
                            <div class="card-body p-2">
                                <dl class="mb-0">
                                    <dt style="font-size: 0.85rem; color: #6c757d;">No. KTP</dt>
                                    <dd class="mb-2" style="font-size: 0.9rem;">{{$data->no_ktp ? \Illuminate\Support\Str::mask($data->no_ktp, '*', 0) : '-'}}</dd>
                                    <dt style="font-size: 0.85rem; color: #6c757d;">TTL</dt>
                                    <dd class="mb-2" style="font-size: 0.9rem;">{{$data->tmp_lahir}}, {{$data->tgl_lahir ? \Carbon\Carbon::parse($data->tgl_lahir)->isoFormat('D MMM Y') : '-'}}</dd>
                                    <dt style="font-size: 0.85rem; color: #6c757d;">Pendidikan</dt>
                                    <dd class="mb-2" style="font-size: 0.9rem;">{{$data->pnd}}</dd>
                                    <dt style="font-size: 0.85rem; color: #6c757d;">Pekerjaan</dt>
                                    <dd class="mb-2" style="font-size: 0.9rem;">{{$data->pekerjaan}}</dd>
                                    <dt style="font-size: 0.85rem; color: #6c757d;">Agama</dt>
                                    <dd class="mb-0" style="font-size: 0.9rem;">{{$data->agama}}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <!-- Kontak -->
                    <div class="card">
                        <div class="card-header p-2" id="headingKontak">
                            <h6 class="mb-0">
                                <button class="btn btn-link btn-block text-left text-primary collapsed" type="button" data-toggle="collapse" data-target="#collapseKontak" aria-expanded="false" aria-controls="collapseKontak" style="font-weight: 600; text-decoration: none;">
                                    <i class="fas fa-chevron-down mr-2"></i> Kontak
                                </button>
                            </h6>
                        </div>
                        <div id="collapseKontak" class="collapse" aria-labelledby="headingKontak" data-parent="#pasienAccordion">
                            <div class="card-body p-2">
                                <dl class="mb-0">
                                    <dt style="font-size: 0.85rem; color: #6c757d;">No. Tlp</dt>
                                    <dd class="mb-2" style="font-size: 0.9rem;">
                                        {{$data->no_tlp}}
                                        <button class="btn btn-xs btn-success ml-2" title="Edit"><i class="fas fa-edit"></i></button>
                                    </dd>
                                    <dt style="font-size: 0.85rem; color: #6c757d;">Alamat</dt>
                                    <dd class="mb-2" style="font-size: 0.9rem;">{{$data->alamat}}</dd>
                                    <dt style="font-size: 0.85rem; color: #6c757d;">Wilayah</dt>
                                    <dd class="mb-0" style="font-size: 0.9rem;">
                                        {{$data->nm_kel}}, {{$data->nm_kec}}, {{$data->nm_kab}}, {{$data->nm_prop}}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <!-- Medis -->
                    <div class="card">
                        <div class="card-header p-2" id="headingMedis">
                            <h6 class="mb-0">
                                <button class="btn btn-link btn-block text-left text-primary collapsed" type="button" data-toggle="collapse" data-target="#collapseMedis" aria-expanded="false" aria-controls="collapseMedis" style="font-weight: 600; text-decoration: none;">
                                    <i class="fas fa-chevron-down mr-2"></i> Medis
                                </button>
                            </h6>
                        </div>
                        <div id="collapseMedis" class="collapse" aria-labelledby="headingMedis" data-parent="#pasienAccordion">
                            <div class="card-body p-2">
                                <dl class="mb-0">
                                    <dt style="font-size: 0.85rem; color: #6c757d;">Gol. Darah</dt>
                                    <dd class="mb-2" style="font-size: 0.9rem;">{{$data->gol_darah}}</dd>
                                    <dt style="font-size: 0.85rem; color: #6c757d;">Nama Ibu</dt>
                                    <dd class="mb-2" style="font-size: 0.9rem;">{{$data->nm_ibu}}</dd>
                                    <dt style="font-size: 0.85rem; color: #6c757d;">Catatan</dt>
                                    <dd class="mb-0" style="font-size: 0.9rem;">
                                        @if($data->catatan)
                                        <div class="alert alert-warning p-1 mb-0" style="font-size: 0.85rem;">
                                            {{$data->catatan}}
                                        </div>
                                        @else
                                        -
                                        @endif
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <!-- Penanggung Jawab -->
                    <div class="card">
                        <div class="card-header p-2" id="headingPJ">
                            <h6 class="mb-0">
                                <button class="btn btn-link btn-block text-left text-primary collapsed" type="button" data-toggle="collapse" data-target="#collapsePJ" aria-expanded="false" aria-controls="collapsePJ" style="font-weight: 600; text-decoration: none;">
                                    <i class="fas fa-chevron-down mr-2"></i> Penanggung Jawab
                                </button>
                            </h6>
                        </div>
                        <div id="collapsePJ" class="collapse" aria-labelledby="headingPJ" data-parent="#pasienAccordion">
                            <div class="card-body p-2">
                                <dl class="mb-0">
                                    <dt style="font-size: 0.85rem; color: #6c757d;">Nama PJ</dt>
                                    <dd class="mb-2" style="font-size: 0.9rem;">{{$data->namakeluarga}}</dd>
                                    <dt style="font-size: 0.85rem; color: #6c757d;">Pekerjaan</dt>
                                    <dd class="mb-2" style="font-size: 0.9rem;">{{$data->pekerjaanpj}}</dd>
                                    <dt style="font-size: 0.85rem; color: #6c757d;">Alamat</dt>
                                    <dd class="mb-0" style="font-size: 0.9rem;">{{$data->alamatpj}}</dd>
                                </dl>
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
    <x-adminlte-modal id="modalBerkasRM" class="modal-lg" title="Berkas RM" size="lg" theme="info" icon="fas fa-bell" v-centered static-backdrop scrollable>
        <div class="body-modal-berkasrm" style="gap:20px"></div>
    </x-adminlte-modal>

    <x-adminlte-modal id="modal-rm" class="modal-lg" title="Berkas RM" size="lg" theme="info" icon="fas fa-bell" v-centered scrollable>
        <livewire:component.berkas-rm />
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

    @else
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Pilih pasien untuk melihat detail.
    </div>
    @endif
</div>

<script>
    // Function untuk membuka modal I-Care - didefinisikan di global scope
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
