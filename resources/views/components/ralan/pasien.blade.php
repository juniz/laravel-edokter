<div class="patient-detail-container">
    <!-- Header Section dengan Foto dan Info Utama -->
    <div class="patient-header-card">
        <div class="patient-header-content">
            <div class="patient-photo-wrapper">
                <img src="https://simrs.rsbhayangkaranganjuk.com/webapps/photopasien/{{$data->gambar ?? 'avatar.png'}}" 
                     alt="{{$data->nm_pasien ?? 'Pasien'}}" 
                     class="patient-photo">
                <div class="patient-status-badge">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="patient-header-info">
                <h2 class="patient-name">{{$data->nm_pasien ?? '-'}}</h2>
                <div class="patient-id-badge">
                    <i class="fas fa-id-card mr-2"></i>
                    <span>No. RM: <strong>{{$data->no_rkm_medis ?? '-'}}</strong></span>
                </div>
                <div class="patient-meta-info">
                    <span class="meta-item">
                        <i class="fas fa-{{$data->jk == 'L' ? 'mars' : 'venus'}} mr-2"></i>
                        {{$data->jk == 'L' ? 'Laki-Laki' : 'Perempuan'}}
                    </span>
                    <span class="meta-divider">•</span>
                    <span class="meta-item">
                        <i class="fas fa-birthday-cake mr-2"></i>
                        {{$data->umur ?? '-'}}
                    </span>
                    <span class="meta-divider">•</span>
                    <span class="meta-item">
                        <i class="fas fa-droplet mr-1"></i>
                        {{$data->gol_darah ?? '-'}}
                    </span>
                </div>
            </div>
        </div>
        <div class="patient-header-actions">
            <div class="quick-info-badges">
                <span class="info-badge badge-primary">
                    <i class="fas fa-book-medical mr-1"></i>
                    {{$data->no_rawat ?? '-'}}
                </span>
                <span class="info-badge badge-success">
                    <i class="fas fa-wallet mr-1"></i>
                    {{$data->png_jawab ?? '-'}}
                </span>
            </div>
        </div>
    </div>

    <!-- Data Sections dalam Grid -->
    <div class="patient-data-grid">
        <!-- Identitas Pasien -->
        <div class="data-card accordion-item">
            <div class="data-card-header accordion-header" data-accordion-target="accordion-identitas">
                <div class="accordion-title">
                    <i class="fas fa-user-circle me-2"></i>
                    <h5>Identitas Pasien</h5>
                </div>
                <i class="fas fa-chevron-down accordion-icon"></i>
            </div>
            <div class="data-card-body accordion-body" id="accordion-identitas">
                <div class="info-row">
                    <div class="info-label">
                        <i class="fas fa-id-card"></i>
                        <span>No. KTP</span>
                    </div>
                    <div class="info-value">{{$data->no_ktp ? \Illuminate\Support\Str::mask($data->no_ktp, '*', 0) : '-'}}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Tempat, Tgl Lahir</span>
                    </div>
                    <div class="info-value">
                        {{$data->tmp_lahir ?? '-'}}, 
                        {{$data->tgl_lahir ? \Illuminate\Support\Str::mask(\Carbon\Carbon::parse($data->tgl_lahir)->isoFormat('LL'), '*', 0) : '-'}}
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">
                        <i class="fas fa-school"></i>
                        <span>Pendidikan</span>
                    </div>
                    <div class="info-value">{{$data->pnd ?? '-'}}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">
                        <i class="fas fa-building"></i>
                        <span>Pekerjaan</span>
                    </div>
                    <div class="info-value">{{$data->pekerjaan ?? '-'}}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">
                        <i class="fas fa-ring"></i>
                        <span>Status Nikah</span>
                    </div>
                    <div class="info-value">{{$data->stts_nikah ?? '-'}}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">
                        <i class="fas fa-book"></i>
                        <span>Agama</span>
                    </div>
                    <div class="info-value">{{$data->agama ?? '-'}}</div>
                </div>
            </div>
        </div>

        <!-- Kontak & Alamat -->
        <div class="data-card accordion-item">
            <div class="data-card-header accordion-header" data-accordion-target="accordion-kontak">
                <div class="accordion-title">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    <h5>Kontak & Alamat</h5>
                </div>
                <i class="fas fa-chevron-down accordion-icon"></i>
            </div>
            <div class="data-card-body accordion-body" id="accordion-kontak">
                <div class="info-row">
                    <div class="info-label">
                        <i class="fas fa-phone"></i>
                        <span>No. Telepon</span>
                    </div>
                    <div class="info-value">
                        <span id="data-phone">{{$data->no_tlp ?? '-'}}</span>
                        <button id="btn-phone" class="btn-edit-phone" title="Edit No. Telepon">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">
                        <i class="fas fa-map"></i>
                        <span>Alamat</span>
                    </div>
                    <div class="info-value">{{$data->alamat ?? '-'}}</div>
                </div>
                @if($data->kd_prop || $data->kd_kab || $data->kd_kec || $data->kd_kel)
                <div class="info-row">
                    <div class="info-label">
                        <i class="fas fa-map-marked-alt"></i>
                        <span>Wilayah Administratif</span>
                    </div>
                    <div class="info-value">
                        <div class="address-hierarchy">
                            @if($data->nm_kel)
                            <div class="address-item">
                                <i class="fas fa-building text-primary"></i>
                                <span><strong>Kelurahan:</strong> {{$data->nm_kel}}</span>
                            </div>
                            @endif
                            @if($data->nm_kec)
                            <div class="address-item">
                                <i class="fas fa-city text-info"></i>
                                <span><strong>Kecamatan:</strong> {{$data->nm_kec}}</span>
                            </div>
                            @endif
                            @if($data->nm_kab)
                            <div class="address-item">
                                <i class="fas fa-landmark text-success"></i>
                                <span><strong>Kabupaten:</strong> {{$data->nm_kab}}</span>
                            </div>
                            @endif
                            @if($data->nm_prop)
                            <div class="address-item">
                                <i class="fas fa-map text-warning"></i>
                                <span><strong>Provinsi:</strong> {{$data->nm_prop}}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Informasi Medis -->
        <div class="data-card accordion-item">
            <div class="data-card-header accordion-header" data-accordion-target="accordion-medis">
                <div class="accordion-title">
                    <i class="fas fa-heartbeat me-2"></i>
                    <h5>Informasi Medis</h5>
                </div>
                <i class="fas fa-chevron-down accordion-icon"></i>
            </div>
            <div class="data-card-body accordion-body" id="accordion-medis">
                <div class="info-row">
                    <div class="info-label">
                        <i class="fas fa-droplet"></i>
                        <span>Golongan Darah</span>
                    </div>
                    <div class="info-value">
                        <span class="blood-type-badge">{{$data->gol_darah ?? '-'}}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">
                        <i class="fas fa-user"></i>
                        <span>Nama Ibu</span>
                    </div>
                    <div class="info-value">{{$data->nm_ibu ?? '-'}}</div>
                </div>
                @if($data->catatan)
                <div class="info-row">
                    <div class="info-label">
                        <i class="fas fa-sticky-note"></i>
                        <span>Catatan</span>
                    </div>
                    <div class="info-value">
                        <div class="note-box">{{$data->catatan}}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Informasi Penanggung Jawab -->
        <div class="data-card accordion-item">
            <div class="data-card-header accordion-header" data-accordion-target="accordion-pj">
                <div class="accordion-title">
                    <i class="fas fa-user-friends me-2"></i>
                    <h5>Penanggung Jawab</h5>
                </div>
                <i class="fas fa-chevron-down accordion-icon"></i>
            </div>
            <div class="data-card-body accordion-body" id="accordion-pj">
                <div class="info-row">
                    <div class="info-label">
                        <i class="fas fa-user"></i>
                        <span>Nama Keluarga</span>
                    </div>
                    <div class="info-value">{{$data->namakeluarga ?? '-'}}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">
                        <i class="fas fa-briefcase"></i>
                        <span>Pekerjaan PJ</span>
                    </div>
                    <div class="info-value">{{$data->pekerjaanpj ?? '-'}}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">
                        <i class="fas fa-map"></i>
                        <span>Alamat PJ</span>
                    </div>
                    <div class="info-value">{{$data->alamatpj ?? '-'}}</div>
                </div>
            </div>
        </div>

        <!-- Informasi Asuransi -->
        <div class="data-card accordion-item">
            <div class="data-card-header accordion-header" data-accordion-target="accordion-asuransi">
                <div class="accordion-title">
                    <i class="fas fa-shield-alt me-2"></i>
                    <h5>Informasi Asuransi</h5>
                </div>
                <i class="fas fa-chevron-down accordion-icon"></i>
            </div>
            <div class="data-card-body accordion-body" id="accordion-asuransi">
                <div class="info-row">
                    <div class="info-label">
                        <i class="fas fa-wallet"></i>
                        <span>Cara Bayar</span>
                    </div>
                    <div class="info-value">
                        <span class="payment-badge">{{$data->png_jawab ?? '-'}}</span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">
                        <i class="fas fa-id-card"></i>
                        <span>No. Peserta</span>
                    </div>
                    <div class="info-value">{{$data->no_peserta ?? '-'}}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons Section -->
    <div class="patient-actions-section">
        <h5 class="actions-title">
            <i class="fas fa-cog me-2"></i>
            Aksi Cepat
        </h5>
        <div class="actions-grid">
            <button onclick="openRiwayatTab()" class="action-btn btn-info">
                <i class="fas fa-history"></i>
                <span>Riwayat Pemeriksaan</span>
            </button>
            <button id="icare-button" class="action-btn btn-success">
                <i class="fas fa-heart"></i>
                <span>I-Care BPJS</span>
            </button>
            <button id="btn-rm" data-rm="{{$data->no_rkm_medis}}" class="action-btn btn-success">
                <i class="fas fa-folder-open"></i>
                <span>Berkas RM Digital</span>
            </button>
            <button onclick="getBerkasRetensi()" class="action-btn btn-secondary">
                <i class="fas fa-archive"></i>
                <span>Berkas RM Retensi</span>
            </button>
            <button id="echo-button" data-toggle="modal" data-target="#modalEcho" class="action-btn btn-info">
                <i class="fas fa-file-medical"></i>
                <span>ECHO</span>
            </button>
            <button data-toggle="modal" data-target="#modal-upload-berkas" class="action-btn btn-success">
                <i class="fas fa-file-upload"></i>
                <span>Upload Berkas Digital</span>
            </button>
        </div>
    </div>
</div>

@section('plugins.Summernote', true)

<x-adminlte-modal id="modalEcho" title="ECHO" size="lg" theme="info"
    icon="fas fa-file" v-centered static-backdrop scrollable>
    <form id="form-echo" method="POST" action="{{ url('print-ekg') }}">
        @csrf
        <input type="hidden" name="no_rm" value="{{ $data->no_rkm_medis }}" >
        <input type="hidden" name="no_rawat" value="{{ $data->no_rawat }}" >
        <div class="row">
            <div class="col-md-6">
                <x-adminlte-select2 name="echo-select" id="echo-select" label="Template Echo" data-placeholder="Pilih template...">
                    <option value=""></option>
                    @foreach($echo as $item)
                        <option value="{{$item->id}}">{{$item->nama_template}}</option>
                    @endforeach
                </x-adminlte-select2>
            </div>
            <div class="col-md-6">
                <x-adminlte-select2 name="dokter_pengirim" id="dokter_pengirim" label="Dokter pengirim" data-placeholder="Pilih dokter...">
                    <option value=""></option>
                    @foreach($dokterlist as $item)
                        <option value="{{$item->kd_dokter}}">{{$item->nm_dokter}}</option>
                    @endforeach
                </x-adminlte-select2>
            </div>
        </div>
        <x-adminlte-text-editor name="isi" id="isi-echo" :config='["height" => "300"]' label="Isi Template"/>
        <x-adminlte-button label="Cetak" type="submit" theme="success" />
    </form>
    <x-slot name="footerSlot">
        <x-adminlte-button theme="danger" label="Tutup" data-dismiss="modal"/>
    </x-slot>
</x-adminlte-modal>

<x-adminlte-modal id="modalBerkasRM" class="modal-lg" title="Berkas RM" size="lg" theme="info" icon="fas fa-bell"
    v-centered static-backdrop scrollable>
    <div class="body-modal-berkasrm" style="gap:20px">
        {{-- <div class="row row-cols-auto body-modal-berkasrm" style="gap:20px">
            <div class="body-modal-berkasrm">
            </div>
        </div> --}}
    </div>
</x-adminlte-modal>

<x-adminlte-modal id="modal-rm" class="modal-lg" title="Berkas RM" size="lg" theme="info" icon="fas fa-bell" v-centered
    scrollable>
    <livewire:component.berkas-rm />
</x-adminlte-modal>

<x-adminlte-modal id="icare-modal" title="I-Care BPJS" size="lg" theme="info" icon="fas fa-bell" v-centered
    static-backdrop scrollable>
    <div class="container-fluid container-icare">

    </div>
</x-adminlte-modal>

<x-adminlte-modal id="modalBerkasRetensi" title="Berkas Retensi" size="lg" theme="info" icon="fas fa-bell" v-centered
    static-backdrop scrollable>
    <div class="container-retensi" style="color:#0d2741">
    </div>
</x-adminlte-modal>

<livewire:component.change-phone />
<livewire:component.upload-berkas-digital :noRawat="$data->no_rawat" />

@push('css')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    @media (min-width: 992px) {
        .modal-lg {
            max-width: 100%;
        }
    }

    /* Patient Detail Container */
    .patient-detail-container {
        font-family: 'Inter', sans-serif;
        padding: 0;
    }

    /* Header Card */
    .patient-header-card {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0, 123, 255, 0.3);
        color: white;
        position: relative;
        overflow: hidden;
    }

    .patient-header-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
        transform: rotate(45deg);
        animation: shine 3s infinite;
    }

    /* @keyframes shine {
        0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
    } */

    .patient-header-content {
        display: flex;
        align-items: center;
        gap: 2rem;
        position: relative;
        z-index: 1;
    }

    .patient-photo-wrapper {
        position: relative;
        flex-shrink: 0;
    }

    .patient-photo {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 5px solid rgba(255,255,255,0.3);
        object-fit: cover;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }

    .patient-status-badge {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 35px;
        height: 35px;
        background: #28a745;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }

    .patient-status-badge i {
        color: white;
        font-size: 0.9rem;
    }

    .patient-header-info {
        flex: 1;
    }

    .patient-name {
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 0.5rem 0;
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .patient-id-badge {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-size: 0.95rem;
        margin-bottom: 1rem;
        backdrop-filter: blur(10px);
    }

    .patient-meta-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .meta-item {
        display: flex;
        align-items: center;
        font-size: 0.95rem;
        opacity: 0.95;
    }

    .meta-divider {
        opacity: 0.5;
    }

    .patient-header-actions {
        margin-top: 1.5rem;
        position: relative;
        z-index: 1;
    }

    .quick-info-badges {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .info-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        backdrop-filter: blur(10px);
    }

    .badge-primary {
        background: rgba(255,255,255,0.25);
        color: white;
    }

    .badge-success {
        background: rgba(40, 167, 69, 0.3);
        color: white;
    }

    /* Data Grid */
    .patient-data-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    /* Data Card */
    .data-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 123, 255, 0.1);
    }

    .data-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 123, 255, 0.15);
    }

    .data-card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1.25rem 1.5rem;
        border-bottom: 2px solid rgba(0, 123, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #007bff;
    }

    .accordion-title {
        display: flex;
        align-items: center;
        flex: 1;
    }

    .data-card-header h5 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: #007bff;
    }

    .data-card-header i {
        font-size: 1.2rem;
    }

    .accordion-icon {
        font-size: 1rem;
        transition: transform 0.3s ease;
        color: #007bff;
        margin-left: 1rem;
        display: none; /* Hidden di desktop */
    }

    .data-card-body {
        padding: 1.5rem;
    }

    /* Di desktop, semua accordion body selalu terbuka */
    @media (min-width: 769px) {
        .accordion-body {
            max-height: none !important;
            padding: 1.5rem !important;
            display: block !important;
        }
        
        .accordion-header {
            cursor: default;
        }
    }

    /* Accordion Styles untuk Mobile */
    @media (max-width: 768px) {
        .patient-data-grid {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .accordion-item {
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 0;
        }

        .accordion-icon {
            display: block; /* Tampilkan icon di mobile */
        }

        .accordion-header {
            cursor: pointer;
            user-select: none;
            transition: all 0.3s ease;
            padding: 1rem 1.25rem;
        }

        .accordion-header:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        }

        .accordion-header:active {
            transform: scale(0.98);
        }

        .accordion-header.active {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }

        .accordion-header.active .accordion-title h5,
        .accordion-header.active .accordion-title i,
        .accordion-header.active .accordion-icon {
            color: white;
        }

        .accordion-header.active .accordion-icon {
            transform: rotate(180deg);
        }

        .accordion-body {
            max-height: 0;
            overflow: hidden;
            padding: 0 1.5rem;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), padding 0.3s ease;
        }

        .accordion-body.active {
            max-height: 2000px;
            padding: 1.5rem;
        }

        /* Nonaktifkan hover effect di mobile untuk accordion */
        .accordion-item:hover {
            transform: none;
        }

        .accordion-item .data-card:hover {
            transform: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        /* Smooth animation untuk accordion */
        .accordion-body {
            display: block;
        }
    }

    .info-row {
        display: flex;
        flex-direction: column;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 500;
        margin-bottom: 0.25rem;
    }

    .info-label i {
        color: #007bff;
        width: 18px;
        text-align: center;
    }

    .info-value {
        font-size: 0.95rem;
        color: #2d3748;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .address-hierarchy {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        width: 100%;
    }

    .address-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0;
        font-size: 0.9rem;
    }

    .address-item i {
        font-size: 1rem;
        width: 20px;
        text-align: center;
    }

    .address-item strong {
        color: #495057;
        margin-right: 0.25rem;
    }

    .address-item span {
        color: #2d3748;
    }

    .address-item small {
        font-size: 0.8rem;
    }

    .btn-edit-phone {
        background: #28a745;
        color: white;
        border: none;
        padding: 0.25rem 0.5rem;
        border-radius: 5px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-edit-phone:hover {
        background: #218838;
        transform: scale(1.1);
    }

    .blood-type-badge {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .payment-badge {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .note-box {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 0.75rem;
        border-radius: 5px;
        font-size: 0.9rem;
        color: #856404;
        margin-top: 0.5rem;
    }

    /* Actions Section */
    .patient-actions-section {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        border: 1px solid rgba(0, 123, 255, 0.1);
    }

    .actions-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #007bff;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 1.5rem 1rem;
        border: none;
        border-radius: 12px;
        font-weight: 500;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s ease;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .action-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .action-btn:hover::before {
        left: 100%;
    }

    .action-btn i {
        font-size: 1.8rem;
    }

    .action-btn.btn-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
    }

    .action-btn.btn-info:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(23, 162, 184, 0.4);
    }

    .action-btn.btn-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    }

    .action-btn.btn-success:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
    }

    .action-btn.btn-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    }

    .action-btn.btn-secondary:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .patient-header-content {
            flex-direction: column;
            text-align: center;
        }

        .patient-photo {
            width: 100px;
            height: 100px;
        }

        .patient-name {
            font-size: 1.5rem;
        }

        .patient-data-grid {
            grid-template-columns: 1fr;
        }

        .actions-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .action-btn {
            padding: 1.25rem 0.75rem;
            font-size: 0.85rem;
        }

        .action-btn i {
            font-size: 1.5rem;
        }
    }

    @media (max-width: 480px) {
        .patient-header-card {
            padding: 1.5rem;
        }

        .patient-name {
            font-size: 1.3rem;
        }

        .actions-grid {
            grid-template-columns: 1fr;
        }

        .patient-meta-info {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .meta-divider {
            display: none;
        }
    }
</style>
@endpush

@push('js')
{{-- <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
<script>
    $('#echo-select').on('change', function(){
        let id = $(this).val();
        $.ajax({
            url: '/api/master-ekg/'+id,
            type: 'GET',
            success: function(response){
                console.log(response);
                $('#isi-echo').summernote('code', response.data.template);
            },
            error: function(data){
                console.log(data);
            }
        })
    });

    $('#btn-phone').on('click', function(event){
        event.preventDefault();
        var phone = $('#data-phone').text();
        Livewire.emit('setRmPhone', "{{$data->no_rkm_medis}}", phone.trim());
        $('#change-phone').modal('show');
    });

    Livewire.on('refreshPhone', function(event){
        $('#change-phone').modal('hide');
        console.log(event);
        $('#data-phone').text(event);
    });

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
                    console.log(data);
                    Swal.fire({
                        title: data.status ? 'Sukses' : 'Gagal',
                        text: data.message ?? 'Berkas berhasil diupload',
                        icon: data.status ? 'success' : 'error',
                        confirmButtonText: 'OK'
                    })
                },
                error: function (data) {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message ?? 'Berkas berhasil diupload',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    })
                }
            });
        }

        $('#icare-button').on('click', function(event){
            event.preventDefault();
            let kdDokter = "{{$dokter}}"
            let param = "{{$data->no_peserta}}"
            $.ajax({
                url: '/api/icare',
                type: 'POST',
                data: {
                    kodedokter: kdDokter,
                    param: param
                },
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
                success: function(data){
                    console.log(data);
                    Swal.close();
                    if(data.code == 200){
                        let url = data.data.url;
                        let html = '';
                        $('#icare-modal').modal('show');
                        html += '<iframe src="'+url+'" frameborder="0" height="700px" width="100%"></iframe>';
                        $('.container-icare').html(html);
                    }else{
                        Swal.fire({
                            title: 'Gagal',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        })
                    }
                },
                error: function(data){
                    console.log(data);
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    })
                }
            })
        })

        function getBerkasRetensi(){
            $.ajax({
                url: "/berkas-retensi/{{$data->no_rkm_medis}}",
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
                        let decode = decodeURIComponent(data.data.lokasi_pdf);
                        var html = '';
                        html += '<iframe src="http://simrs.rsbhayangkaranganjuk.com/webapps/medrec/'+decode+'" frameborder="0" height="700px" width="100%"></iframe>';
                        $('.container-retensi').html(html);
                        $('#modalBerkasRetensi').modal('show');
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

        function openRiwayatTab() {
            // Trigger event untuk membuka tab riwayat di pasien-tabs component
            if (typeof Livewire !== 'undefined') {
                // Dispatch Livewire event
                Livewire.emit('openRiwayatTab');
            }
            // Atau langsung trigger click pada tab riwayat
            const riwayatTab = document.getElementById('riwayat-pasien-tab');
            if (riwayatTab) {
                riwayatTab.click();
            } else {
                // Jika tab belum ada, dispatch browser event
                window.dispatchEvent(new CustomEvent('openRiwayatTab'));
            }
        }

        function getBerkasRM() {
            $.ajax({
                url: "/berkas/{{$data->no_rawat}}/{{$data->no_rkm_medis}}",
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
                    console.log(data);  
                    if(data.status == 'success'){
                        var html = '';
                        data.data.forEach(function(item){
                            let decoded = decodeURIComponent(item.lokasi_file);
                            html += '<iframe src="https://simrs.rsbhayangkaranganjuk.com/webapps/berkasrawat/'+decoded+'" frameborder="0" height="700px" width="100%"></iframe>';
                            
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

    // Accordion Functionality untuk Mobile
    $(document).ready(function() {
        function initAccordion() {
            const isMobile = $(window).width() <= 768;
            
            // Hanya aktifkan accordion di mobile (max-width: 768px)
            if (isMobile) {
                // Enable click handlers
                $('.accordion-header').off('click').on('click', function() {
                    const $header = $(this);
                    const $item = $header.closest('.accordion-item');
                    const $body = $item.find('.accordion-body');
                    const isActive = $header.hasClass('active');

                    // Toggle active state
                    if (isActive) {
                        $header.removeClass('active');
                        $body.removeClass('active');
                    } else {
                        // Open clicked accordion (allow multiple open)
                        $header.addClass('active');
                        $body.addClass('active');
                    }
                });

                // Set first accordion open by default di mobile
                if ($('.accordion-header.active').length === 0) {
                    $('.accordion-header').first().addClass('active');
                    $('.accordion-body').first().addClass('active');
                }
            } else {
                // Di desktop, nonaktifkan accordion dan tampilkan semua
                $('.accordion-header').off('click');
                $('.accordion-header').removeClass('active');
                $('.accordion-body').addClass('active');
            }
        }

        // Initialize on load
        initAccordion();

        // Reinitialize on resize dengan debounce
        let resizeTimer;
        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                initAccordion();
            }, 250);
        });
    });
</script>
{{-- <script>

</script> --}}
@endpush