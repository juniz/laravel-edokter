<div class="pasien-tabs-container" style="height: 100vh; display: flex; flex-direction: column;">
    <div class="card card-primary card-tabs" style="display: flex; flex-direction: column; height: 100%;">
        <div class="card-header p-0 pt-1" style="flex-shrink: 0;">
            <ul class="nav nav-tabs" id="custom-tabs-pasien-tab" role="tablist">
                {{-- <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'detail' ? 'active' : '' }}" 
                       id="detail-pasien-tab" 
                       href="#detail-pasien-content" 
                       role="tab" 
                       aria-controls="detail-pasien-content"
                       aria-selected="{{ $activeTab === 'detail' ? 'true' : 'false' }}"
                       wire:click.prevent="setActiveTab('detail')"
                       style="cursor: pointer;">
                        <i class="fas fa-user"></i> Detail Pasien
                    </a>
                </li> --}}
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'riwayat' ? 'active' : '' }}" 
                       id="riwayat-pasien-tab" 
                       href="#riwayat-pasien-content" 
                       role="tab" 
                       aria-controls="riwayat-pasien-content"
                       aria-selected="{{ $activeTab === 'riwayat' ? 'true' : 'false' }}"
                       wire:click.prevent="setActiveTab('riwayat')"
                       style="cursor: pointer;">
                        <i class="fas fa-history"></i> 10 Riwayat Terakhir
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'tindakan' ? 'active' : '' }}" 
                       id="tindakan-dokter-tab" 
                       href="#tindakan-dokter-content" 
                       role="tab" 
                       aria-controls="tindakan-dokter-content"
                       aria-selected="{{ $activeTab === 'tindakan' ? 'true' : 'false' }}"
                       wire:click.prevent="setActiveTab('tindakan')"
                       style="cursor: pointer;">
                        <i class="fas fa-stethoscope"></i> Tindakan Dokter
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body" style="flex: 1; overflow-y: auto; padding: 1rem;">
            <div class="tab-content" id="custom-tabs-pasien-tabContent" style="height: 100%;">
                <!-- Tab Detail Pasien -->
                {{-- <div class="tab-pane fade {{ $activeTab === 'detail' ? 'show active' : '' }}" 
                     id="detail-pasien-content" 
                     role="tabpanel" 
                     aria-labelledby="detail-pasien-tab">
                    <x-ralan.pasien :no-rawat="$noRawat" />
                </div> --}}
                
                <!-- Tab Riwayat Pasien -->
                <div class="tab-pane fade {{ $activeTab === 'riwayat' ? 'show active' : '' }}" 
                     id="riwayat-pasien-content" 
                     role="tabpanel" 
                     aria-labelledby="riwayat-pasien-tab">
                    <div>
                        <div class="card mb-3">
                            <div class="card-header bg-info p-0" id="filterRiwayatHeader">
                                <button class="btn btn-link text-white w-100 text-left p-3 d-flex justify-content-between align-items-center" 
                                        type="button" 
                                        data-toggle="collapse" 
                                        data-target="#filterRiwayatCollapse" 
                                        aria-expanded="false" 
                                        aria-controls="filterRiwayatCollapse"
                                        style="text-decoration: none;">
                                    <h6 class="mb-0">
                                        <i class="fas fa-filter"></i> Filter Pencarian
                                    </h6>
                                    <i class="fas fa-chevron-down" id="filterRiwayatIcon"></i>
                                </button>
                            </div>
                            <div id="filterRiwayatCollapse" 
                                 class="collapse" 
                                 aria-labelledby="filterRiwayatHeader" 
                                 data-parent="#riwayat-pasien-content">
                                <div class="card-body p-2 p-md-3">
                                    <div class="row">
                                        <div class="col-12 col-md-3 mb-2 mb-md-2">
                                            <label for="tanggal-mulai-tabs" class="mb-1"><strong>Tanggal Mulai:</strong></label>
                                            <input type="date" 
                                                   id="tanggal-mulai-tabs" 
                                                   class="form-control form-control-sm" 
                                                   wire:model="tanggalMulai">
                                        </div>
                                        <div class="col-12 col-md-3 mb-2 mb-md-2">
                                            <label for="tanggal-akhir-tabs" class="mb-1"><strong>Tanggal Akhir:</strong></label>
                                            <input type="date" 
                                                   id="tanggal-akhir-tabs" 
                                                   class="form-control form-control-sm" 
                                                   wire:model="tanggalAkhir">
                                        </div>
                                        <div class="col-12 col-md-3 mb-2 mb-md-2">
                                            <label for="riwayat-dokter-tabs" class="mb-1"><strong>Dokter:</strong></label>
                                            <div wire:ignore>
                                                <select name="dokter" id="riwayat-dokter-tabs" class="form-control form-control-sm">
                                                    <option value="">Pilih Dokter</option>
                                                    @foreach($dokter as $dok)
                                                    <option value="{{$dok->kd_dokter}}">{{$dok->nm_dokter}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3 mb-2 mb-md-2">
                                            <label for="jenis-perawatan-tabs" class="mb-1">
                                                <strong><i class="fas fa-filter"></i> Jenis Perawatan:</strong>
                                            </label>
                                            <select id="jenis-perawatan-tabs" 
                                                    class="form-control form-control-sm" 
                                                    wire:model="jenisPerawatan">
                                                <option value="">Semua (Ralan & Ranap)</option>
                                                <option value="Ralan">Ralan</option>
                                                <option value="Ranap">Ranap</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <button type="button" 
                                                    class="btn btn-sm btn-secondary btn-block btn-md-inline-block" 
                                                    wire:click="resetFilter">
                                                <i class="fas fa-redo"></i> Reset Filter
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div wire:loading wire:target='init, updatedSelectDokter, updatedTanggalMulai, updatedTanggalAkhir, resetFilter'>
                            <div class="d-flex flex-row">
                                <div class="mx-auto">
                                    Loading ...
                                </div>
                            </div>
                        </div>

                        @if(count($data)>0)
                        @php
                        // Ambil data Penilaian Awal Keperawatan Kebidanan Ranap dari data pertama yang memiliki status Ranap
                        $penilaianKebidananRanap = null;
                        $noRawatRanap = null;
                        foreach($data as $row) {
                            $no_rawat_temp = is_array($row) ? ($row['no_rawat'] ?? null) : ($row->no_rawat ?? null);
                            $status_lanjut_temp = is_array($row) ? ($row['status_lanjut'] ?? null) : ($row->status_lanjut ?? null);
                            if ($no_rawat_temp && $status_lanjut_temp == 'Ranap') {
                                $penilaianKebidananRanap = $this->getPenilaianAwalKeperawatanKebidananRanap($no_rawat_temp);
                                $noRawatRanap = $no_rawat_temp;
                                break;
                            }
                        }
                        @endphp
                        
                        @if($penilaianKebidananRanap)
                        <x-adminlte-card theme="warning" title="Penilaian Awal Keperawatan Kebidanan Ranap" icon="fas fa-clipboard-check" theme-mode="outline" collapsible class="mb-4">
                            @php
                            $tglPenilaian = date_create($penilaianKebidananRanap->tanggal ?? '0000-00-00');
                            $datePenilaian = date_format($tglPenilaian,"d M Y H:i");
                            @endphp
                            
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Tanggal Penilaian</small>
                                        <strong><i class="fas fa-calendar"></i> {{ $datePenilaian }}</strong>
                                    </div>
                                    @if($penilaianKebidananRanap->nm_dokter)
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Dokter</small>
                                        <strong><i class="fas fa-user-md"></i> {{ $penilaianKebidananRanap->nm_dokter }}</strong>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Informasi Umum -->
                            <div class="card mb-3 border-left-info" style="border-left-width: 4px;">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Umum</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <small class="text-muted d-block">Informasi</small>
                                            <strong>{{ $penilaianKebidananRanap->informasi ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <small class="text-muted d-block">Tiba di Ruang Rawat</small>
                                            <strong>{{ $penilaianKebidananRanap->tiba_diruang_rawat ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <small class="text-muted d-block">Cara Masuk</small>
                                            <strong>{{ $penilaianKebidananRanap->cara_masuk ?? '-' }}</strong>
                                        </div>
                                    </div>
                                    @if($penilaianKebidananRanap->keluhan)
                                    <div class="mt-2">
                                        <small class="text-muted d-block">Keluhan</small>
                                        <div class="border rounded p-2 bg-light">{!! nl2br(e($penilaianKebidananRanap->keluhan)) !!}</div>
                                    </div>
                                    @endif
                                    <div class="row mt-2">
                                        <div class="col-md-4 mb-2">
                                            <small class="text-muted d-block">RPK</small>
                                            <strong>{{ $penilaianKebidananRanap->rpk ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <small class="text-muted d-block">PSK</small>
                                            <strong>{{ $penilaianKebidananRanap->psk ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <small class="text-muted d-block">RP</small>
                                            <strong>{{ $penilaianKebidananRanap->rp ?? '-' }}</strong>
                                        </div>
                                    </div>
                                    @if($penilaianKebidananRanap->alergi && $penilaianKebidananRanap->alergi != '-' && $penilaianKebidananRanap->alergi != 'Tidak Ada')
                                    <div class="mt-2">
                                        <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Alergi: {{ $penilaianKebidananRanap->alergi }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Riwayat Menstruasi -->
                            <div class="card mb-3 border-left-primary" style="border-left-width: 4px;">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-calendar-alt"></i> Riwayat Menstruasi</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">Umur</small>
                                            <strong>{{ $penilaianKebidananRanap->riwayat_mens_umur ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">Lamanya</small>
                                            <strong>{{ $penilaianKebidananRanap->riwayat_mens_lamanya ?? '-' }} hari</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">Banyaknya</small>
                                            <strong>{{ $penilaianKebidananRanap->riwayat_mens_banyaknya ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">Siklus</small>
                                            <strong>{{ $penilaianKebidananRanap->riwayat_mens_siklus ?? '-' }} hari</strong>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Keterangan Siklus</small>
                                            <strong>{{ $penilaianKebidananRanap->riwayat_mens_ket_siklus ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Dirasakan</small>
                                            <strong>{{ $penilaianKebidananRanap->riwayat_mens_dirasakan ?? '-' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Riwayat Perkawinan -->
                            <div class="card mb-3 border-left-success" style="border-left-width: 4px;">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-heart"></i> Riwayat Perkawinan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Status</small>
                                        <strong>{{ $penilaianKebidananRanap->riwayat_perkawinan_status ?? '-' }}</strong>
                                        @if($penilaianKebidananRanap->riwayat_perkawinan_ket_status && $penilaianKebidananRanap->riwayat_perkawinan_ket_status != '-')
                                        <span class="ml-2">({{ $penilaianKebidananRanap->riwayat_perkawinan_ket_status }} tahun)</span>
                                        @endif
                                    </div>
                                    @if($penilaianKebidananRanap->riwayat_perkawinan_usia1 && $penilaianKebidananRanap->riwayat_perkawinan_usia1 != '-')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Usia Perkawinan 1</small>
                                        <strong>{{ $penilaianKebidananRanap->riwayat_perkawinan_usia1 }} tahun - {{ $penilaianKebidananRanap->riwayat_perkawinan_ket_usia1 ?? '-' }}</strong>
                                    </div>
                                    @endif
                                    @if($penilaianKebidananRanap->riwayat_perkawinan_usia2 && $penilaianKebidananRanap->riwayat_perkawinan_usia2 != '-')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Usia Perkawinan 2</small>
                                        <strong>{{ $penilaianKebidananRanap->riwayat_perkawinan_usia2 }} tahun - {{ $penilaianKebidananRanap->riwayat_perkawinan_ket_usia2 ?? '-' }}</strong>
                                    </div>
                                    @endif
                                    @if($penilaianKebidananRanap->riwayat_perkawinan_usia3 && $penilaianKebidananRanap->riwayat_perkawinan_usia3 != '-')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Usia Perkawinan 3</small>
                                        <strong>{{ $penilaianKebidananRanap->riwayat_perkawinan_usia3 }} tahun - {{ $penilaianKebidananRanap->riwayat_perkawinan_ket_usia3 ?? '-' }}</strong>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Riwayat Persalinan & Kehamilan -->
                            <div class="card mb-3 border-left-danger" style="border-left-width: 4px;">
                                <div class="card-header bg-danger text-white">
                                    <h6 class="mb-0"><i class="fas fa-baby"></i> Riwayat Persalinan & Kehamilan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">G</small>
                                            <strong>{{ $penilaianKebidananRanap->riwayat_persalinan_g ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">P</small>
                                            <strong>{{ $penilaianKebidananRanap->riwayat_persalinan_p ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">A</small>
                                            <strong>{{ $penilaianKebidananRanap->riwayat_persalinan_a ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">Hidup</small>
                                            <strong>{{ $penilaianKebidananRanap->riwayat_persalinan_hidup ?? '-' }}</strong>
                                        </div>
                                    </div>
                                    @if($penilaianKebidananRanap->riwayat_hamil_hpht && $penilaianKebidananRanap->riwayat_hamil_hpht != '0000-00-00')
                                    <div class="row mb-2">
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">HPHT</small>
                                            <strong>{{ date('d M Y', strtotime($penilaianKebidananRanap->riwayat_hamil_hpht)) }}</strong>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">Usia Kehamilan</small>
                                            <strong>{{ $penilaianKebidananRanap->riwayat_hamil_usiahamil ?? '-' }} minggu</strong>
                                        </div>
                                        @if($penilaianKebidananRanap->riwayat_hamil_tp && $penilaianKebidananRanap->riwayat_hamil_tp != '0000-00-00')
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">TP</small>
                                            <strong>{{ date('d M Y', strtotime($penilaianKebidananRanap->riwayat_hamil_tp)) }}</strong>
                                        </div>
                                        @endif
                                    </div>
                                    @endif
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Imunisasi TT</small>
                                            <strong>{{ $penilaianKebidananRanap->riwayat_hamil_imunisasi ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">ANC</small>
                                            <strong>{{ $penilaianKebidananRanap->riwayat_hamil_anc ?? '-' }}x</strong>
                                            @if($penilaianKebidananRanap->riwayat_hamil_ancke && $penilaianKebidananRanap->riwayat_hamil_ancke != '-')
                                            <span class="ml-2">({{ $penilaianKebidananRanap->riwayat_hamil_ancke }}x - {{ $penilaianKebidananRanap->riwayat_hamil_ket_ancke ?? '-' }})</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($penilaianKebidananRanap->riwayat_hamil_keluhan_hamil_muda && $penilaianKebidananRanap->riwayat_hamil_keluhan_hamil_muda != 'Tidak Ada')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Keluhan Hamil Muda</small>
                                        <strong>{{ $penilaianKebidananRanap->riwayat_hamil_keluhan_hamil_muda }}</strong>
                                    </div>
                                    @endif
                                    @if($penilaianKebidananRanap->riwayat_hamil_keluhan_hamil_tua && $penilaianKebidananRanap->riwayat_hamil_keluhan_hamil_tua != 'Tidak Ada')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Keluhan Hamil Tua</small>
                                        <strong>{{ $penilaianKebidananRanap->riwayat_hamil_keluhan_hamil_tua }}</strong>
                                    </div>
                                    @endif
                                    @if($penilaianKebidananRanap->komplikasi_sebelumnya && $penilaianKebidananRanap->komplikasi_sebelumnya != 'Tidak')
                                    <div class="mt-2">
                                        <span class="badge badge-warning">
                                            <i class="fas fa-exclamation-triangle"></i> Komplikasi Sebelumnya: {{ $penilaianKebidananRanap->komplikasi_sebelumnya }}
                                            @if($penilaianKebidananRanap->keterangan_komplikasi_sebelumnya && $penilaianKebidananRanap->keterangan_komplikasi_sebelumnya != '-')
                                            - {{ $penilaianKebidananRanap->keterangan_komplikasi_sebelumnya }}
                                            @endif
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Riwayat KB -->
                            @if($penilaianKebidananRanap->riwayat_kb && $penilaianKebidananRanap->riwayat_kb != 'Belum Pernah')
                            <div class="card mb-3 border-left-secondary" style="border-left-width: 4px;">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0"><i class="fas fa-pills"></i> Riwayat KB</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Jenis KB</small>
                                        <strong>{{ $penilaianKebidananRanap->riwayat_kb }}</strong>
                                        @if($penilaianKebidananRanap->riwayat_kb_lamanya && $penilaianKebidananRanap->riwayat_kb_lamanya != '-')
                                        <span class="ml-2">({{ $penilaianKebidananRanap->riwayat_kb_lamanya }})</span>
                                        @endif
                                    </div>
                                    @if($penilaianKebidananRanap->riwayat_kb_komplikasi && $penilaianKebidananRanap->riwayat_kb_komplikasi == 'Ada')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Komplikasi</small>
                                        <strong class="text-danger">{{ $penilaianKebidananRanap->riwayat_kb_ket_komplikasi ?? '-' }}</strong>
                                    </div>
                                    @endif
                                    @if($penilaianKebidananRanap->riwayat_kb_kapaberhenti && $penilaianKebidananRanap->riwayat_kb_kapaberhenti != '-')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Kapan Berhenti</small>
                                        <strong>{{ $penilaianKebidananRanap->riwayat_kb_kapaberhenti }}</strong>
                                    </div>
                                    @endif
                                    @if($penilaianKebidananRanap->riwayat_kb_alasanberhenti && $penilaianKebidananRanap->riwayat_kb_alasanberhenti != '-')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Alasan Berhenti</small>
                                        <strong>{{ $penilaianKebidananRanap->riwayat_kb_alasanberhenti }}</strong>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Pemeriksaan Kebidanan -->
                            <div class="card mb-3 border-left-warning" style="border-left-width: 4px;">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-stethoscope"></i> Pemeriksaan Kebidanan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">Keadaan Umum</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_keadaan_umum ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">GCS</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_gcs ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">TD</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_td ?? '-' }} mmHg</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">Nadi</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_nadi ?? '-' }} /min</strong>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">RR</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_rr ?? '-' }} /min</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">Suhu</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_suhu ?? '-' }} °C</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">SpO2</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_spo2 ?? '-' }}%</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">BB</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_bb ?? '-' }} kg</strong>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">TB</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_tb ?? '-' }} cm</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">LILA</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_lila ?? '-' }} cm</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">TFU</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_tfu ?? '-' }} cm</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">TBJ</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_tbj ?? '-' }} cm</strong>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">Letak</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_letak ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">Presentasi</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_presentasi ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">Penurunan</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_penurunan ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">HIS</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_his ?? '-' }}</strong>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">Kekuatan</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_kekuatan ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">Lamanya</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_lamanya ?? '-' }} detik</strong>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">DJJ</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_djj ?? '-' }} /min</strong>
                                            @if($penilaianKebidananRanap->pemeriksaan_kebidanan_ket_djj)
                                            <span class="ml-1">({{ $penilaianKebidananRanap->pemeriksaan_kebidanan_ket_djj }})</span>
                                            @endif
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <small class="text-muted d-block">Pembukaan</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_pembukaan ?? '-' }} cm</strong>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 mb-2">
                                            <small class="text-muted d-block">Portio</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_portio ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <small class="text-muted d-block">Ketuban</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_ketuban ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <small class="text-muted d-block">Hodge</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_hodge ?? '-' }}</strong>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Panggul</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_panggul ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Mental</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_mental ?? '-' }}</strong>
                                        </div>
                                    </div>
                                    @if($penilaianKebidananRanap->pemeriksaan_kebidanan_inspekulo && $penilaianKebidananRanap->pemeriksaan_kebidanan_inspekulo == 'Dilakukan')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Inspekulo</small>
                                        <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_ket_inspekulo ?? '-' }}</strong>
                                    </div>
                                    @endif
                                    @if($penilaianKebidananRanap->pemeriksaan_kebidanan_lakmus && $penilaianKebidananRanap->pemeriksaan_kebidanan_lakmus == 'Dilakukan')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Lakmus</small>
                                        <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_ket_lakmus ?? '-' }}</strong>
                                    </div>
                                    @endif
                                    @if($penilaianKebidananRanap->pemeriksaan_kebidanan_ctg && $penilaianKebidananRanap->pemeriksaan_kebidanan_ctg == 'Dilakukan')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">CTG</small>
                                        <strong>{{ $penilaianKebidananRanap->pemeriksaan_kebidanan_ket_ctg ?? '-' }}</strong>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Pemeriksaan Umum -->
                            <div class="card mb-3 border-left-dark" style="border-left-width: 4px;">
                                <div class="card-header bg-dark text-white">
                                    <h6 class="mb-0"><i class="fas fa-user-check"></i> Pemeriksaan Umum</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Kepala</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_umum_kepala ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Muka</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_umum_muka ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Mata</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_umum_mata ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Hidung</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_umum_hidung ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Telinga</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_umum_telinga ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Mulut</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_umum_mulut ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Leher</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_umum_leher ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Dada</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_umum_dada ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Perut</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_umum_perut ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Genitalia</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_umum_genitalia ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Ekstrimitas</small>
                                            <strong>{{ $penilaianKebidananRanap->pemeriksaan_umum_ekstrimitas ?? '-' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pengkajian Fungsi -->
                            <div class="card mb-3 border-left-info" style="border-left-width: 4px;">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-walking"></i> Pengkajian Fungsi</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Kemampuan Aktivitas</small>
                                        <strong>{{ $penilaianKebidananRanap->pengkajian_fungsi_kemampuan_aktifitas ?? '-' }}</strong>
                                    </div>
                                    @if($penilaianKebidananRanap->pengkajian_fungsi_berjalan && $penilaianKebidananRanap->pengkajian_fungsi_berjalan != 'TAK')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Berjalan</small>
                                        <strong>{{ $penilaianKebidananRanap->pengkajian_fungsi_berjalan }}</strong>
                                        @if($penilaianKebidananRanap->pengkajian_fungsi_ket_berjalan && $penilaianKebidananRanap->pengkajian_fungsi_ket_berjalan != '-')
                                        <span class="ml-2">({{ $penilaianKebidananRanap->pengkajian_fungsi_ket_berjalan }})</span>
                                        @endif
                                    </div>
                                    @endif
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Aktivitas</small>
                                            <strong>{{ $penilaianKebidananRanap->pengkajian_fungsi_aktivitas ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Ambulasi</small>
                                            <strong>{{ $penilaianKebidananRanap->pengkajian_fungsi_ambulasi ?? '-' }}</strong>
                                        </div>
                                    </div>
                                    @if($penilaianKebidananRanap->pengkajian_fungsi_ekstrimitas_atas && $penilaianKebidananRanap->pengkajian_fungsi_ekstrimitas_atas != 'TAK')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Ekstrimitas Atas</small>
                                        <strong>{{ $penilaianKebidananRanap->pengkajian_fungsi_ekstrimitas_atas }}</strong>
                                        @if($penilaianKebidananRanap->pengkajian_fungsi_ket_ekstrimitas_atas && $penilaianKebidananRanap->pengkajian_fungsi_ket_ekstrimitas_atas != '-')
                                        <span class="ml-2">({{ $penilaianKebidananRanap->pengkajian_fungsi_ket_ekstrimitas_atas }})</span>
                                        @endif
                                    </div>
                                    @endif
                                    @if($penilaianKebidananRanap->pengkajian_fungsi_ekstrimitas_bawah && $penilaianKebidananRanap->pengkajian_fungsi_ekstrimitas_bawah != 'TAK')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Ekstrimitas Bawah</small>
                                        <strong>{{ $penilaianKebidananRanap->pengkajian_fungsi_ekstrimitas_bawah }}</strong>
                                        @if($penilaianKebidananRanap->pengkajian_fungsi_ket_ekstrimitas_bawah && $penilaianKebidananRanap->pengkajian_fungsi_ket_ekstrimitas_bawah != '-')
                                        <span class="ml-2">({{ $penilaianKebidananRanap->pengkajian_fungsi_ket_ekstrimitas_bawah }})</span>
                                        @endif
                                    </div>
                                    @endif
                                    @if($penilaianKebidananRanap->pengkajian_fungsi_gangguan_fungsi && $penilaianKebidananRanap->pengkajian_fungsi_gangguan_fungsi == 'Ya (Co DPJP)')
                                    <div class="mt-2">
                                        <span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Ada Gangguan Fungsi (Perlu Co DPJP)</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Riwayat Psikososial -->
                            <div class="card mb-3 border-left-purple" style="border-left-width: 4px; border-left-color: #6f42c1;">
                                <div class="card-header text-white" style="background-color: #6f42c1;">
                                    <h6 class="mb-0"><i class="fas fa-brain"></i> Riwayat Psikososial</h6>
                                </div>
                                <div class="card-body">
                                    @if($penilaianKebidananRanap->riwayat_psiko_kondisipsiko && $penilaianKebidananRanap->riwayat_psiko_kondisipsiko != 'Tidak Ada Masalah')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Kondisi Psikologis</small>
                                        <strong>{{ $penilaianKebidananRanap->riwayat_psiko_kondisipsiko }}</strong>
                                    </div>
                                    @endif
                                    @if($penilaianKebidananRanap->riwayat_psiko_adakah_prilaku && $penilaianKebidananRanap->riwayat_psiko_adakah_prilaku != 'Tidak Ada Masalah')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Perilaku</small>
                                        <strong>{{ $penilaianKebidananRanap->riwayat_psiko_adakah_prilaku }}</strong>
                                        @if($penilaianKebidananRanap->riwayat_psiko_ket_adakah_prilaku && $penilaianKebidananRanap->riwayat_psiko_ket_adakah_prilaku != '-')
                                        <span class="ml-2">({{ $penilaianKebidananRanap->riwayat_psiko_ket_adakah_prilaku }})</span>
                                        @endif
                                    </div>
                                    @endif
                                    @if($penilaianKebidananRanap->riwayat_psiko_gangguan_jiwa && $penilaianKebidananRanap->riwayat_psiko_gangguan_jiwa == 'Ya')
                                    <div class="mb-2">
                                        <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Ada Gangguan Jiwa</span>
                                    </div>
                                    @endif
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Hubungan Pasien</small>
                                            <strong>{{ $penilaianKebidananRanap->riwayat_psiko_hubungan_pasien ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Tinggal Dengan</small>
                                            <strong>{{ $penilaianKebidananRanap->riwayat_psiko_tinggal_dengan ?? '-' }}</strong>
                                            @if($penilaianKebidananRanap->riwayat_psiko_ket_tinggal_dengan && $penilaianKebidananRanap->riwayat_psiko_ket_tinggal_dengan != '-')
                                            <span class="ml-2">({{ $penilaianKebidananRanap->riwayat_psiko_ket_tinggal_dengan }})</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($penilaianKebidananRanap->riwayat_psiko_budaya && $penilaianKebidananRanap->riwayat_psiko_budaya == 'Ada')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Budaya</small>
                                        <strong>{{ $penilaianKebidananRanap->riwayat_psiko_ket_budaya ?? '-' }}</strong>
                                    </div>
                                    @endif
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Pendidikan PJ</small>
                                            <strong>{{ $penilaianKebidananRanap->riwayat_psiko_pend_pj ?? '-' }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Edukasi Pada</small>
                                            <strong>{{ $penilaianKebidananRanap->riwayat_psiko_edukasi_pada ?? '-' }}</strong>
                                            @if($penilaianKebidananRanap->riwayat_psiko_ket_edukasi_pada && $penilaianKebidananRanap->riwayat_psiko_ket_edukasi_pada != '-')
                                            <span class="ml-2">({{ $penilaianKebidananRanap->riwayat_psiko_ket_edukasi_pada }})</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Penilaian Nyeri -->
                            @if($penilaianKebidananRanap->penilaian_nyeri && $penilaianKebidananRanap->penilaian_nyeri != 'Tidak Ada Nyeri')
                            <div class="card mb-3 border-left-danger" style="border-left-width: 4px;">
                                <div class="card-header bg-danger text-white">
                                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Penilaian Nyeri</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Jenis Nyeri</small>
                                            <strong>{{ $penilaianKebidananRanap->penilaian_nyeri }}</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block mb-2">Skala Nyeri</small>
                                            @php
                                            $skalaNyeri = (int)($penilaianKebidananRanap->penilaian_nyeri_skala ?? 0);
                                            $persentase = ($skalaNyeri / 10) * 100;
                                            
                                            // Tentukan warna berdasarkan skala
                                            $colorClass = 'success';
                                            $badgeClass = 'success';
                                            $icon = 'fa-smile';
                                            $label = 'Ringan';
                                            
                                            if ($skalaNyeri >= 7) {
                                                $colorClass = 'danger';
                                                $badgeClass = 'danger';
                                                $icon = 'fa-exclamation-triangle';
                                                $label = 'Berat';
                                            } elseif ($skalaNyeri >= 4) {
                                                $colorClass = 'warning';
                                                $badgeClass = 'warning';
                                                $icon = 'fa-meh';
                                                $label = 'Sedang';
                                            } elseif ($skalaNyeri >= 1) {
                                                $colorClass = 'info';
                                                $badgeClass = 'info';
                                                $icon = 'fa-smile';
                                                $label = 'Ringan';
                                            } else {
                                                $colorClass = 'success';
                                                $badgeClass = 'success';
                                                $icon = 'fa-smile';
                                                $label = 'Tidak Ada';
                                            }
                                            @endphp
                                            
                                            <div class="pain-scale-visual">
                                                <div class="d-flex align-items-center mb-2">
                                                    <strong class="text-{{ $colorClass }} mr-2" style="font-size: 1.5rem;">
                                                        {{ $skalaNyeri }}/10
                                                    </strong>
                                                    <span class="badge badge-{{ $badgeClass }}">
                                                        <i class="fas {{ $icon }}"></i> {{ $label }}
                                                    </span>
                                                </div>
                                                
                                                <!-- Visual Pain Scale 0-10 -->
                                                <div class="pain-scale-container mb-2">
                                                    <!-- Progress Bar dengan gradient warna sebagai background -->
                                                    <div class="pain-scale-bar-wrapper" style="position: relative; height: 30px; margin-bottom: 8px;">
                                                        <!-- Background gradient untuk seluruh skala -->
                                                        <div class="pain-scale-background" 
                                                             style="position: absolute; top: 0; left: 0; right: 0; height: 100%; border-radius: 15px; background: linear-gradient(to right, #28a745 0%, #ffc107 40%, #ff9800 60%, #dc3545 100%); box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);">
                                                        </div>
                                                        
                                                        <!-- Filled portion berdasarkan skala -->
                                                        @if($skalaNyeri > 0)
                                                        <div class="pain-scale-filled" 
                                                             style="position: absolute; top: 0; left: 0; height: 100%; width: {{ $persentase }}%; border-radius: 15px; background: rgba(255,255,255,0.3); backdrop-filter: blur(2px);">
                                                        </div>
                                                        @endif
                                                        
                                                        <!-- Indicator untuk skala saat ini -->
                                                        @if($skalaNyeri > 0)
                                                        <div class="pain-indicator" 
                                                             style="position: absolute; left: {{ $persentase }}%; top: 50%; transform: translate(-50%, -50%); width: 32px; height: 32px; background: #fff; border: 3px solid #dc3545; border-radius: 50%; box-shadow: 0 3px 8px rgba(220,53,69,0.4); z-index: 10;">
                                                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.75rem; font-weight: bold; color: #dc3545; line-height: 1;">
                                                                {{ $skalaNyeri }}
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    
                                                    <!-- Numbers 0-10 di bawah progress bar -->
                                                    <div class="pain-scale-numbers d-flex justify-content-between mb-1" style="padding: 0 2px;">
                                                        @for($i = 0; $i <= 10; $i++)
                                                        <span class="pain-scale-number {{ $i == $skalaNyeri ? 'active' : '' }}" 
                                                              style="font-size: 0.7rem; font-weight: {{ $i == $skalaNyeri ? 'bold' : 'normal' }}; color: {{ $i == $skalaNyeri ? '#dc3545' : '#6c757d' }}; transition: all 0.3s ease;">
                                                            {{ $i }}
                                                        </span>
                                                        @endfor
                                                    </div>
                                                    
                                                    <!-- Label di bawah numbers -->
                                                    <div class="d-flex justify-content-between mt-1">
                                                        <small class="text-muted" style="font-size: 0.65rem;">
                                                            <i class="fas fa-smile text-success"></i> Tidak Ada
                                                        </small>
                                                        <small class="text-muted" style="font-size: 0.65rem;">
                                                            <i class="fas fa-exclamation-triangle text-danger"></i> Sangat Berat
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Penyebab</small>
                                            <strong>{{ $penilaianKebidananRanap->penilaian_nyeri_penyebab ?? '-' }}</strong>
                                            @if($penilaianKebidananRanap->penilaian_nyeri_ket_penyebab && $penilaianKebidananRanap->penilaian_nyeri_ket_penyebab != '-')
                                            <span class="ml-2">({{ $penilaianKebidananRanap->penilaian_nyeri_ket_penyebab }})</span>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Kualitas</small>
                                            <strong>{{ $penilaianKebidananRanap->penilaian_nyeri_kualitas ?? '-' }}</strong>
                                            @if($penilaianKebidananRanap->penilaian_nyeri_ket_kualitas && $penilaianKebidananRanap->penilaian_nyeri_ket_kualitas != '-')
                                            <span class="ml-2">({{ $penilaianKebidananRanap->penilaian_nyeri_ket_kualitas }})</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($penilaianKebidananRanap->penilaian_nyeri_lokasi && $penilaianKebidananRanap->penilaian_nyeri_lokasi != '-')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Lokasi</small>
                                        <strong>{{ $penilaianKebidananRanap->penilaian_nyeri_lokasi }}</strong>
                                        @if($penilaianKebidananRanap->penilaian_nyeri_menyebar && $penilaianKebidananRanap->penilaian_nyeri_menyebar == 'Ya')
                                        <span class="badge badge-warning ml-2">Menyebar</span>
                                        @endif
                                    </div>
                                    @endif
                                    @if($penilaianKebidananRanap->penilaian_nyeri_waktu && $penilaianKebidananRanap->penilaian_nyeri_waktu != '-')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Waktu</small>
                                        <strong>{{ $penilaianKebidananRanap->penilaian_nyeri_waktu }}</strong>
                                    </div>
                                    @endif
                                    @if($penilaianKebidananRanap->penilaian_nyeri_hilang && $penilaianKebidananRanap->penilaian_nyeri_hilang != '-')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Hilang Dengan</small>
                                        <strong>{{ $penilaianKebidananRanap->penilaian_nyeri_hilang }}</strong>
                                        @if($penilaianKebidananRanap->penilaian_nyeri_ket_hilang && $penilaianKebidananRanap->penilaian_nyeri_ket_hilang != '-')
                                        <span class="ml-2">({{ $penilaianKebidananRanap->penilaian_nyeri_ket_hilang }})</span>
                                        @endif
                                    </div>
                                    @endif
                                    @if($penilaianKebidananRanap->penilaian_nyeri_diberitahukan_dokter && $penilaianKebidananRanap->penilaian_nyeri_diberitahukan_dokter == 'Ya')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Diberitahukan ke Dokter</small>
                                        <strong>Ya</strong>
                                        @if($penilaianKebidananRanap->penilaian_nyeri_jam_diberitahukan_dokter && $penilaianKebidananRanap->penilaian_nyeri_jam_diberitahukan_dokter != '-')
                                        <span class="ml-2">({{ $penilaianKebidananRanap->penilaian_nyeri_jam_diberitahukan_dokter }})</span>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Penilaian Jatuh -->
                            @if($penilaianKebidananRanap->penilaian_jatuh_totalnilai && $penilaianKebidananRanap->penilaian_jatuh_totalnilai > 0)
                            <div class="card mb-3 border-left-warning" style="border-left-width: 4px;">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Penilaian Risiko Jatuh</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Total Nilai</small>
                                        <strong class="text-danger" style="font-size: 1.5rem;">{{ number_format($penilaianKebidananRanap->penilaian_jatuh_totalnilai, 1) }}</strong>
                                        @if($penilaianKebidananRanap->penilaian_jatuh_totalnilai >= 25)
                                        <span class="badge badge-danger ml-2">Risiko Tinggi</span>
                                        @elseif($penilaianKebidananRanap->penilaian_jatuh_totalnilai >= 15)
                                        <span class="badge badge-warning ml-2">Risiko Sedang</span>
                                        @else
                                        <span class="badge badge-success ml-2">Risiko Rendah</span>
                                        @endif
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Skala 1</small>
                                            <strong>{{ $penilaianKebidananRanap->penilaian_jatuh_skala1 ?? '-' }} ({{ $penilaianKebidananRanap->penilaian_jatuh_nilai1 ?? 0 }})</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Skala 2</small>
                                            <strong>{{ $penilaianKebidananRanap->penilaian_jatuh_skala2 ?? '-' }} ({{ $penilaianKebidananRanap->penilaian_jatuh_nilai2 ?? 0 }})</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Skala 3</small>
                                            <strong>{{ $penilaianKebidananRanap->penilaian_jatuh_skala3 ?? '-' }} ({{ $penilaianKebidananRanap->penilaian_jatuh_nilai3 ?? 0 }})</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Skala 4</small>
                                            <strong>{{ $penilaianKebidananRanap->penilaian_jatuh_skala4 ?? '-' }} ({{ $penilaianKebidananRanap->penilaian_jatuh_nilai4 ?? 0 }})</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Skala 5</small>
                                            <strong>{{ $penilaianKebidananRanap->penilaian_jatuh_skala5 ?? '-' }} ({{ $penilaianKebidananRanap->penilaian_jatuh_nilai5 ?? 0 }})</strong>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <small class="text-muted d-block">Skala 6</small>
                                            <strong>{{ $penilaianKebidananRanap->penilaian_jatuh_skala6 ?? '-' }} ({{ $penilaianKebidananRanap->penilaian_jatuh_nilai6 ?? 0 }})</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Skrining Gizi -->
                            @if($penilaianKebidananRanap->nilai_total_gizi && $penilaianKebidananRanap->nilai_total_gizi > 0)
                            <div class="card mb-3 border-left-success" style="border-left-width: 4px;">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-utensils"></i> Skrining Gizi</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Total Nilai</small>
                                        <strong class="text-primary" style="font-size: 1.5rem;">{{ number_format($penilaianKebidananRanap->nilai_total_gizi, 1) }}</strong>
                                        @if($penilaianKebidananRanap->nilai_total_gizi >= 3)
                                        <span class="badge badge-danger ml-2">Risiko Malnutrisi</span>
                                        @elseif($penilaianKebidananRanap->nilai_total_gizi >= 1)
                                        <span class="badge badge-warning ml-2">Risiko Sedang</span>
                                        @else
                                        <span class="badge badge-success ml-2">Normal</span>
                                        @endif
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Skala 1</small>
                                            <strong>{{ $penilaianKebidananRanap->skrining_gizi1 ?? '-' }} ({{ $penilaianKebidananRanap->nilai_gizi1 ?? 0 }})</strong>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted d-block">Skala 2</small>
                                            <strong>{{ $penilaianKebidananRanap->skrining_gizi2 ?? '-' }} ({{ $penilaianKebidananRanap->nilai_gizi2 ?? 0 }})</strong>
                                        </div>
                                    </div>
                                    @if($penilaianKebidananRanap->skrining_gizi_diagnosa_khusus && $penilaianKebidananRanap->skrining_gizi_diagnosa_khusus == 'Ya')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Diagnosa Khusus</small>
                                        <strong>{{ $penilaianKebidananRanap->skrining_gizi_ket_diagnosa_khusus ?? '-' }}</strong>
                                    </div>
                                    @endif
                                    @if($penilaianKebidananRanap->skrining_gizi_diketahui_dietisen && $penilaianKebidananRanap->skrining_gizi_diketahui_dietisen == 'Ya')
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Diketahui Dietisen</small>
                                        <strong>Ya</strong>
                                        @if($penilaianKebidananRanap->skrining_gizi_jam_diketahui_dietisen && $penilaianKebidananRanap->skrining_gizi_jam_diketahui_dietisen != '-')
                                        <span class="ml-2">({{ $penilaianKebidananRanap->skrining_gizi_jam_diketahui_dietisen }})</span>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Masalah & Rencana -->
                            @if($penilaianKebidananRanap->masalah || $penilaianKebidananRanap->rencana)
                            <div class="card mb-3 border-left-primary" style="border-left-width: 4px;">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-clipboard-list"></i> Masalah & Rencana</h6>
                                </div>
                                <div class="card-body">
                                    @if($penilaianKebidananRanap->masalah && $penilaianKebidananRanap->masalah != '-')
                                    <div class="mb-3">
                                        <small class="text-muted d-block mb-1"><strong>Masalah:</strong></small>
                                        <div class="border rounded p-2 bg-light">{!! nl2br(e($penilaianKebidananRanap->masalah)) !!}</div>
                                    </div>
                                    @endif
                                    @if($penilaianKebidananRanap->rencana && $penilaianKebidananRanap->rencana != '-')
                                    <div class="mb-2">
                                        <small class="text-muted d-block mb-1"><strong>Rencana:</strong></small>
                                        <div class="border rounded p-2 bg-light">{!! nl2br(e($penilaianKebidananRanap->rencana)) !!}</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Petugas -->
                            @if($penilaianKebidananRanap->nama_petugas1 || $penilaianKebidananRanap->nama_petugas2)
                            <div class="mt-3 pt-3 border-top">
                                <div class="row">
                                    @if($penilaianKebidananRanap->nama_petugas1)
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Petugas 1</small>
                                        <strong>{{ $penilaianKebidananRanap->nama_petugas1 }}</strong>
                                    </div>
                                    @endif
                                    @if($penilaianKebidananRanap->nama_petugas2)
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Petugas 2</small>
                                        <strong>{{ $penilaianKebidananRanap->nama_petugas2 }}</strong>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </x-adminlte-card>
                        @endif
                        
                        <div>
                            @foreach($data as $row)
                            @php
                            // Pastikan akses data konsisten, baik object maupun array
                            $no_rawat = is_array($row) ? ($row['no_rawat'] ?? null) : ($row->no_rawat ?? null);
                            $status_lanjut = is_array($row) ? ($row['status_lanjut'] ?? null) : ($row->status_lanjut ?? null);
                            $tgl_registrasi = is_array($row) ? ($row['tgl_registrasi'] ?? null) : ($row->tgl_registrasi ?? null);
                            $nm_dokter = is_array($row) ? ($row['nm_dokter'] ?? null) : ($row->nm_dokter ?? null);
                            
                            if (!$no_rawat) continue; // Skip jika tidak ada no_rawat
                            
                            $pemeriksaan = $this->getPemeriksaanRalan($no_rawat, $status_lanjut);
                            $diagnosa = $this->getDiagnosa($no_rawat);
                            $tono = $this->getTono($no_rawat);
                            $laboratorium = $this->getPemeriksaanLab($no_rawat);
                            $resume = $this->getResume($no_rawat);
                            $radiologi = $this->getRadiologi($no_rawat);
                            $gambarRadiologi = $this->getFotoRadiologi($no_rawat);
                            // $riwayatObat = $this->getRiwayatObat($no_rawat);
                            $berkasDigital = $this->getBerkasDigital($no_rawat);
                            $obatRanap = $this->getobatRanap($no_rawat);
                            $obatRalan = $this->getobatRalan($no_rawat);
                            $tgl = date_create($tgl_registrasi ?? '0000-00-00');
                            $date = date_format($tgl,"d M Y");
                            @endphp

                            <!-- Card Riwayat Pemeriksaan -->
                            <div class="card mb-4">
                                <div class="card-header bg-light p-2 p-md-3">
                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                                        <div class="mb-2 mb-md-0">
                                            <h5 class="mb-0 mb-md-0" style="font-size: 1rem;">
                                                <i class="fas fa-calendar-alt text-primary"></i> {{ $date ?? '' }}
                                                @if($status_lanjut == 'Ranap')
                                                <span class="badge badge-success ml-2">
                                                    <i class="fas fa-bed"></i> Ranap
                                                </span>
                                                @else
                                                <span class="badge badge-info ml-2">
                                                    <i class="fas fa-walking"></i> Ralan
                                                </span>
                                                @endif
                                            </h5>
                                            <small class="text-muted d-block d-md-inline-block mt-1">{{$no_rawat}}</small>
                                        </div>
                                        <div class="text-left text-md-right mt-2 mt-md-0">
                                            <span class="badge badge-primary">{{$nm_dokter}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-2 p-md-3">
                                        @php
                                        $allPemeriksaan = $this->getPemeriksaanRalan($no_rawat, $status_lanjut);
                                        // Filter hanya pemeriksaan milik dokter
                                        $pemeriksaanDokter = collect($allPemeriksaan)->filter(function($item) {
                                            return isset($item->jenis_petugas) && $item->jenis_petugas == 'Dokter';
                                        });
                                        @endphp

                                        @if(count($pemeriksaanDokter) > 0)
                                        <x-adminlte-card theme="primary" title="SOAP/CPPT" icon="fas fa-clipboard-list" theme-mode="outline" collapsible>
                                            @foreach($pemeriksaanDokter as $pemeriksaan)
                                            @php
                                            $tglPemeriksaan = date_create($pemeriksaan->tgl_perawatan ?? '0000-00-00');
                                            $datePemeriksaan = date_format($tglPemeriksaan,"d M Y");
                                            $isDokter = isset($pemeriksaan->jenis_petugas) && $pemeriksaan->jenis_petugas == 'Dokter';
                                            $badgeColor = $isDokter ? 'warning' : 'secondary';
                                            $headerColor = $isDokter ? 'primary' : 'info';
                                            $borderColor = $isDokter ? 'primary' : 'info';
                                            $icon = $isDokter ? 'fa-user-md' : 'fa-user-nurse';
                                            @endphp
                                            <!-- Card SOAP/CPPT -->
                                            <div class="card mb-3 border-left-{{$borderColor}}" style="border-left-width: 4px;">
                                                <div class="card-header bg-{{$headerColor}} text-white">
                                                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                                                        <div class="flex-grow-1">
                                                            <h5 class="mb-1">
                                                                <i class="fas {{$icon}}"></i> 
                                                                <span class="badge badge-{{$badgeColor}} ml-2">{{$pemeriksaan->jenis_petugas ?? 'Perawat'}}</span>
                                                                @if($status_lanjut == 'Ranap')
                                                                <span class="badge badge-success ml-2">
                                                                    <i class="fas fa-bed"></i> Ranap
                                                                </span>
                                                                @else
                                                                <span class="badge badge-info ml-2">
                                                                    <i class="fas fa-walking"></i> Ralan
                                                                </span>
                                                                @endif
                                                            </h5>
                                                            <div class="mt-1">
                                                                <small class="text-white-50">
                                                                    <i class="fas fa-calendar"></i> {{$datePemeriksaan}} | 
                                                                    <i class="fas fa-clock"></i> {{$pemeriksaan->jam_rawat}}
                                                                    @if(isset($pemeriksaan->nama_petugas))
                                                                    | <i class="fas fa-user"></i> {{$pemeriksaan->nama_petugas}}
                                                                    @endif
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <div class="text-right">
                                                            <span class="badge badge-light">{{$status_lanjut}}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="bg-white p-3 rounded-bottom">
                                                        <!-- Anamnesis/Subjektif -->
                                                        <div class="mb-3">
                                                            <div class="font-weight-bold text-primary mb-1"><i class="fas fa-comment-medical mr-1"></i>Anamnesis/Subjektif</div>
                                                            <div class="border rounded p-2 bg-light">{!! nl2br(e($pemeriksaan->keluhan ?? '-')) !!}</div>
                                                        </div>
                                                        <!-- Objektif -->
                                                        <div class="mb-3">
                                                            <div class="font-weight-bold text-primary mb-1"><i class="fas fa-notes-medical mr-1"></i>Objektif</div>
                                                            <div class="row mb-2">
                                                                <div class="col-md-3 col-6 mb-2">
                                                                    <div class="small text-muted">Suhu</div>
                                                                    <div class="font-weight-bold">{{ $pemeriksaan->suhu_tubuh ?? '-' }} <span class="small">°C</span></div>
                                                                </div>
                                                                <div class="col-md-3 col-6 mb-2">
                                                                    <div class="small text-muted">Tensi</div>
                                                                    <div class="font-weight-bold">{{ $pemeriksaan->tensi ?? '-' }} <span class="small">mmHg</span></div>
                                                                </div>
                                                                <div class="col-md-3 col-6 mb-2">
                                                                    <div class="small text-muted">Respirasi</div>
                                                                    <div class="font-weight-bold">{{ $pemeriksaan->respirasi ?? '-' }} <span class="small">/min</span></div>
                                                                </div>
                                                                <div class="col-md-3 col-6 mb-2">
                                                                    <div class="small text-muted">GCS</div>
                                                                    <div class="font-weight-bold">{{ $pemeriksaan->gcs ?? '-' }}</div>
                                                                </div>
                                                            </div>
                                                            <div class="row mb-2">
                                                                <div class="col-md-3 col-6 mb-2">
                                                                    <div class="small text-muted">Berat Badan</div>
                                                                    <div class="font-weight-bold">{{ $pemeriksaan->berat ?? '-' }} <span class="small">kg</span></div>
                                                                </div>
                                                                <div class="col-md-3 col-6 mb-2">
                                                                    <div class="small text-muted">Tinggi Badan</div>
                                                                    <div class="font-weight-bold">{{ $pemeriksaan->tinggi ?? '-' }} <span class="small">cm</span></div>
                                                                </div>
                                                                <div class="col-md-3 col-6 mb-2">
                                                                    <div class="small text-muted">Lingkar Perut</div>
                                                                    <div class="font-weight-bold">{{ $pemeriksaan->lingkar_perut ?? '-' }} <span class="small">cm</span></div>
                                                                </div>
                                                            </div>
                                                            <div class="mb-2">
                                                                <div class="small text-muted">Status Lokalis:</div>
                                                                <div class="border rounded p-2 bg-light">{!! nl2br(e($pemeriksaan->pemeriksaan ?? '-')) !!}</div>
                                                            </div>
                                                        </div>
                                                        <!-- Asesmen -->
                                                        <div class="mb-3">
                                                            <div class="font-weight-bold text-primary mb-1"><i class="fas fa-clipboard-check mr-1"></i>Asesmen</div>
                                                            <div class="border rounded p-2 bg-light">{!! nl2br(e($pemeriksaan->penilaian ?? '-')) !!}</div>
                                                        </div>
                                                        <!-- Planning -->
                                                        <div class="mb-3">
                                                            <div class="font-weight-bold text-primary mb-1"><i class="fas fa-tasks mr-1"></i>Planning</div>
                                                            <div class="border rounded p-2 bg-light">{!! nl2br(e($pemeriksaan->rtl ?? '-')) !!}</div>
                                                        </div>
                                                        <!-- Instruksi -->
                                                        <div class="mb-3">
                                                            <div class="font-weight-bold text-primary mb-1"><i class="fas fa-list-alt mr-1"></i>Instruksi</div>
                                                            <div class="border rounded p-2 bg-light">{!! nl2br(e($pemeriksaan->instruksi ?? '-')) !!}</div>
                                                        </div>
                                                        <!-- Evaluasi -->
                                                        <div class="mb-3">
                                                            <div class="font-weight-bold text-primary mb-1"><i class="fas fa-search mr-1"></i>Evaluasi</div>
                                                            <div class="border rounded p-2 bg-light">{!! nl2br(e($pemeriksaan->evaluasi ?? '-')) !!}</div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            @if(!$loop->last)
                                            <hr class="my-3" style="border-top: 2px dashed #dee2e6;">
                                            @endif
                                            @endforeach

                                            <!-- Resume Medis -->
                                            @if(isset($resume))
                                            <div class="card mb-3">
                                                <div class="card-header bg-primary">
                                                    <h6 class="mb-0"><i class="fas fa-file-medical"></i> Resume Medis</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-2"><strong>Keluhan Utama:</strong> {{$resume->keluhan_utama}}</div>
                                                    @if($resume->jalannya_penyakit)
                                                    <div class="mb-2"><strong>Jalannya Penyakit:</strong> {{$resume->jalannya_penyakit}}</div>
                                                    @endif
                                                    @if($resume->pemeriksaan_penunjang)
                                                    <div class="mb-2"><strong>Pemeriksaan Penunjang:</strong> {{$resume->pemeriksaan_penunjang}}</div>
                                                    @endif
                                                    @if($resume->hasil_laborat)
                                                    <div class="mb-2"><strong>Hasil Laborat:</strong> {{$resume->hasil_laborat}}</div>
                                                    @endif
                                                    @if($resume->diagnosa_utama)
                                                    <div class="mb-2"><strong>Diagnosa Utama:</strong> {{$resume->diagnosa_utama}} - {{$resume->kd_diagnosa_utama ?? ''}}</div>
                                                    @endif
                                                    @if($resume->diagnosa_sekunder)
                                                    <div class="mb-2"><strong>Diagnosa Sekunder 1:</strong> {{$resume->diagnosa_sekunder}} - {{$resume->kd_diagnosa_sekunder ?? ''}}</div>
                                                    @endif
                                                    @if($resume->diagnosa_sekunder2)
                                                    <div class="mb-2"><strong>Diagnosa Sekunder 2:</strong> {{$resume->diagnosa_sekunder2}} - {{$resume->kd_diagnosa_sekunder2 ?? ''}}</div>
                                                    @endif
                                                    @if($resume->diagnosa_sekunder3)
                                                    <div class="mb-2"><strong>Diagnosa Sekunder 3:</strong> {{$resume->diagnosa_sekunder3}} - {{$resume->kd_diagnosa_sekunder3 ?? ''}}</div>
                                                    @endif
                                                    @if($resume->diagnosa_sekunder4)
                                                    <div class="mb-2"><strong>Diagnosa Sekunder 4:</strong> {{$resume->diagnosa_sekunder4}} - {{$resume->kd_diagnosa_sekunder4 ?? ''}}</div>
                                                    @endif
                                                    @if($resume->prosedur_utama)
                                                    <div class="mb-2"><strong>Prosedur Utama:</strong> {{$resume->prosedur_utama}} - {{$resume->kd_prosedur_utama ?? ''}}</div>
                                                    @endif
                                                    @if($resume->prosedur_sekunder)
                                                    <div class="mb-2"><strong>Prosedur Sekunder 1:</strong> {{$resume->prosedur_sekunder}} - {{$resume->kd_prosedur_sekunder ?? ''}}</div>
                                                    @endif
                                                    @if($resume->prosedur_sekunder2)
                                                    <div class="mb-2"><strong>Prosedur Sekunder 2:</strong> {{$resume->prosedur_sekunder2}} - {{$resume->kd_prosedur_sekunder2 ?? ''}}</div>
                                                    @endif
                                                    @if($resume->prosedur_sekunder3)
                                                    <div class="mb-2"><strong>Prosedur Sekunder 3:</strong> {{$resume->prosedur_sekunder3}} - {{$resume->kd_prosedur_sekunder3 ?? ''}}</div>
                                                    @endif
                                                    @if(isset($resume->kondisi_pulang))
                                                    <div class="mb-2"><strong>Kondisi Pulang:</strong> {{$resume->kondisi_pulang}}</div>
                                                    @endif
                                                    @if($resume->obat_pulang)
                                                    <div class="mb-2"><strong>Obat Pulang:</strong> <pre class="mb-0" style="white-space: pre-wrap;">{{$resume->obat_pulang}}</pre></div>
                                                    @endif
                                                </div>
                                            </div>
                                            @endif
                                        </x-adminlte-card>
                                        @endif

                                        @if(count($this->getDiagnosa($no_rawat)) > 0)
                                        <div class="card card-outline card-primary mb-3">
                                            <div class="card-header">
                                                <h3 class="card-title"><i class="fas fa-diagnoses mr-2"></i>Diagnosa</h3>
                                                <div class="card-tools">
                                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Diagnosa</th>
                                                                <th>Keterangan</th>
                                                                <th>Status Diagnosa</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($this->getDiagnosa($no_rawat) as $diagnosa)
                                                            <tr>
                                                                <td>{{ $diagnosa->kd_penyakit }}</td>
                                                                <td>{{ $diagnosa->nm_penyakit }}</td>
                                                                <td>{{ $diagnosa->status }}</td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        @if(count($obatRalan)>0)
                                        <div class="card card-outline card-success mb-3">
                                            <div class="card-header">
                                                <h3 class="card-title"><i class="fas fa-pills mr-2"></i>Obat Ralan</h3>
                                                <div class="card-tools">
                                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>Nama Obat</th>
                                                                <th>Jumlah</th>
                                                                <th>Aturan Pakai</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($obatRalan as $obat)
                                                            <tr>
                                                                <td>{{$obat->nama_brng}}</td>
                                                                <td>{{$obat->jml}}</td>
                                                                <td>{{$obat->aturan}}</td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        @if(count($obatRanap)>0)
                                        <div class="card card-outline card-success mb-3">
                                            <div class="card-header">
                                                <h3 class="card-title"><i class="fas fa-pills mr-2"></i>Obat Ranap</h3>
                                                <div class="card-tools">
                                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body p-0">
                                                @php
                                                    $obatByDate = $obatRanap->groupBy('tgl_perawatan');
                                                @endphp
                                                
                                                <div class="card card-primary card-outline card-tabs">
                                                    <div class="card-header p-0 pt-1 border-bottom-0">
                                                        <ul class="nav nav-tabs" role="tablist">
                                                            @foreach($obatByDate as $tanggal => $obatList)
                                                                <li class="nav-item">
                                                                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" 
                                                                    data-toggle="pill" 
                                                                    href="#tab_{{ \Carbon\Carbon::parse($tanggal)->format('Y-m-d') }}" 
                                                                    role="tab">
                                                                        {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="tab-content">
                                                            @foreach($obatByDate as $tanggal => $obatList)
                                                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                                                                    id="tab_{{ \Carbon\Carbon::parse($tanggal)->format('Y-m-d') }}" 
                                                                    role="tabpanel">
                                                                    <div class="table-responsive">
                                                                        <table class="table table-hover">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Jam</th>
                                                                                    <th>Nama Obat</th>
                                                                                    <th>Jumlah</th>
                                                                                    <th>Aturan Pakai</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach($obatList as $obat)
                                                                                    <tr>
                                                                                        <td>{{ $obat->jam }}</td>
                                                                                        <td>{{ $obat->nama_brng }}</td>
                                                                                        <td>{{ $obat->jml }}</td>
                                                                                        <td>{{ $obat->aturan }}</td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        @if(count($laboratorium)>0)
                                        <x-adminlte-card theme="dark" title="Laboratorium" icon="fas fa-flask" theme-mode="outline" collapsible>
                                            <!-- Desktop Table View -->
                                            <div class="table-responsive d-none d-md-block">
                                                <table class="table table-sm table-hover mb-0">
                                                    <thead class="thead-dark">
                                                        <tr>
                                                            <th style="width: 5%;">No</th>
                                                            <th style="width: 25%;">Nama Pemeriksaan</th>
                                                            <th style="width: 10%;">Tgl Periksa</th>
                                                            <th style="width: 8%;">Jam</th>
                                                            <th style="width: 12%;" class="text-right">Hasil</th>
                                                            <th style="width: 10%;">Satuan</th>
                                                            <th style="width: 15%;">Nilai Rujukan</th>
                                                            <th style="width: 15%;" class="text-center">Keterangan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($laboratorium as $lab)
                                                        @php
                                                        $isAbnormal = ($lab->keterangan == 'L' || $lab->keterangan == 'H');
                                                        $isHigh = ($lab->keterangan == 'T');
                                                        @endphp
                                                        <tr class="@if($isAbnormal) bg-warning text-black @elseif($isHigh) bg-danger text-white @endif">
                                                            <td>{{$loop->iteration}}</td>
                                                            <td>
                                                                <strong>{{$lab->Pemeriksaan}}</strong>
                                                            </td>
                                                            <td>{{$lab->tgl_periksa}}</td>
                                                            <td>{{$lab->jam}}</td>
                                                            <td class="text-right">
                                                                <strong class="@if($isHigh) text-white @else text-primary @endif">
                                                                    {{$lab->nilai}}
                                                                </strong>
                                                            </td>
                                                            <td>{{$lab->satuan ?? '-'}}</td>
                                                            <td>
                                                                <span class="text-center">{{$lab->nilai_rujukan ?? '-'}}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                @if($isAbnormal || $isHigh)
                                                                    <i class="fas fa-exclamation-triangle"></i> {{$lab->keterangan}}
                                                                </span>
                                                                @elseif($lab->keterangan)
                                                                <span class="badge badge-success">{{$lab->keterangan}}</span>
                                                                @else
                                                                <span class="badge badge-secondary">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            
                                            <!-- Mobile Card View -->
                                            <div class="d-block d-md-none">
                                                @foreach($laboratorium as $lab)
                                                @php
                                                $isAbnormal = ($lab->keterangan == 'T' || $lab->keterangan == 'H');
                                                @endphp
                                                <div class="card mb-3 @if($isAbnormal) border-left-danger @else border-left-primary @endif" style="border-left-width: 4px;">
                                                    <div class="card-header @if($isAbnormal) bg-danger text-white @else bg-primary text-white @endif">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <h6 class="mb-0">
                                                                    <i class="fas fa-vial"></i> {{$lab->Pemeriksaan}}
                                                                </h6>
                                                                <small class="text-white-50">
                                                                    <i class="fas fa-calendar"></i> {{$lab->tgl_periksa}} | 
                                                                    <i class="fas fa-clock"></i> {{$lab->jam}}
                                                                </small>
                                                            </div>
                                                            <span class="badge badge-light">#{{$loop->iteration}}</span>
                                                        </div>
                                                    </div>
                                                    <div class="card-body p-3">
                                                        <div class="row">
                                                            <div class="col-6 mb-2">
                                                                <small class="text-muted d-block">Hasil</small>
                                                                <strong class="text-primary" style="font-size: 1.1rem;">
                                                                    {{$lab->nilai}}
                                                                </strong>
                                                                @if($lab->satuan)
                                                                <small class="text-muted"> {{$lab->satuan}}</small>
                                                                @endif
                                                            </div>
                                                            <div class="col-6 mb-2">
                                                                <small class="text-muted d-block">Nilai Rujukan</small>
                                                                <span class="text-dark">{{$lab->nilai_rujukan ?? '-'}}</span>
                                                            </div>
                                                        </div>
                                                        @if($lab->keterangan)
                                                        <div class="mt-2 pt-2 border-top">
                                                            <small class="text-muted d-block mb-1">Keterangan</small>
                                                            @if($isAbnormal)
                                                            <span class="badge badge-danger">
                                                                <i class="fas fa-exclamation-triangle"></i> {{$lab->keterangan}}
                                                            </span>
                                                            @else
                                                            <span class="badge badge-success">{{$lab->keterangan}}</span>
                                                            @endif
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </x-adminlte-card>
                                        @endif

                                        @if(count($gambarRadiologi)>0 || count($radiologi)>0)
                                        <x-adminlte-card theme="dark" title="Radiologi" icon="fas fa-x-ray" collapsible theme-mode="outline">
                                            <x-adminlte-card theme="dark" title="Gambar Radiologi" collapsible="collapsed">
                                                <div class="container">
                                                    <div class="row row-cols-auto">
                                                        @foreach($gambarRadiologi as $gambar)
                                                        <a href="{{ env('URL_RADIOLOGI').$gambar->lokasi_gambar }}"
                                                            data-toggle="lightbox" data-gallery="example-gallery" class="col-sm-4">
                                                            <img src="{{ env('URL_RADIOLOGI').$gambar->lokasi_gambar }}"
                                                                class="img-fluid" style="width: 200px;height:250px">
                                                        </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </x-adminlte-card>
                                            @if(count($radiologi)>0)
                                            @foreach($radiologi as $radiologi)
                                            <x-adminlte-card title="{{$radiologi->jam}}" theme="dark"
                                                footer-class="bg-dark border-top rounded border-light">
                                                <pre>{{$radiologi->hasil}}</pre>
                                            </x-adminlte-card>
                                            @endforeach
                                            @endif
                                        </x-adminlte-card>
                                        @endif

                                        {{-- @if(count($riwayatObat) > 0)
                                        <x-adminlte-card theme="success" title="Riwayat Obat" icon="fas fa-pills" theme-mode="outline" collapsible="collapsed" maximizable>
                                            @foreach($riwayatObat as $resep)
                                            @php
                                            $tglResep = date_create($resep->tgl_peresepan ?? '0000-00-00');
                                            $dateResep = date_format($tglResep,"d M Y");
                                            $tglSerah = $resep->tgl_penyerahan ? date_create($resep->tgl_penyerahan) : null;
                                            $dateSerah = $tglSerah ? date_format($tglSerah,"d M Y") : null;
                                            @endphp
                                            <div class="card mb-3 border-left-success" style="border-left-width: 4px;">
                                                <div class="card-header bg-success text-white">
                                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                                                        <div class="flex-grow-1 mb-2 mb-md-0">
                                                            <h6 class="mb-1 mb-md-1" style="font-size: 0.95rem;">
                                                                <i class="fas fa-prescription"></i> Resep #{{$resep->no_resep}}
                                                            </h6>
                                                            <div class="mt-1">
                                                                <small class="text-white-50 d-block d-md-inline-block mb-1 mb-md-0">
                                                                    <i class="fas fa-calendar"></i> {{$dateResep}}
                                                                </small>
                                                                <small class="text-white-50 d-block d-md-inline-block mb-1 mb-md-0">
                                                                    <i class="fas fa-clock"></i> {{$resep->jam_peresepan ?? '-'}}
                                                                </small>
                                                                @if($resep->nm_dokter)
                                                                <small class="text-white-50 d-block d-md-inline-block">
                                                                    <i class="fas fa-user-md"></i> {{$resep->nm_dokter}}
                                                                </small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="text-left text-md-right mt-2 mt-md-0">
                                                            <span class="badge badge-light">
                                                                @if($resep->status == 'ralan')
                                                                    <i class="fas fa-walking"></i> Ralan
                                                                @elseif($resep->status == 'ranap')
                                                                    <i class="fas fa-bed"></i> Ranap
                                                                @else
                                                                    {{$resep->status ?? '-'}}
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body p-2 p-md-3">
                                                    @if(count($resep->detail_obat) > 0)
                                                    <!-- Desktop Table View -->
                                                    <div class="table-responsive d-none d-md-block">
                                                        <table class="table table-sm table-hover mb-0">
                                                            <thead class="thead-light">
                                                                <tr>
                                                                    <th style="width: 5%;">#</th>
                                                                    <th style="width: 40%;">Nama Obat</th>
                                                                    <th style="width: 15%;" class="text-center">Jumlah</th>
                                                                    <th style="width: 40%;">Aturan Pakai</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($resep->detail_obat as $obat)
                                                                <tr>
                                                                    <td>{{$loop->iteration}}</td>
                                                                    <td>
                                                                        <strong class="text-primary">
                                                                            <i class="fas fa-capsules"></i> {{$obat->nama_brng}}
                                                                        </strong>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <span class="badge badge-info">{{$obat->jml}}</span>
                                                                    </td>
                                                                    <td>
                                                                        <small class="text-muted">
                                                                            <i class="fas fa-info-circle"></i> {{$obat->aturan_pakai ?? '-'}}
                                                                        </small>
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    
                                                    <!-- Mobile Card View -->
                                                    <div class="d-block d-md-none">
                                                        @foreach($resep->detail_obat as $obat)
                                                        <div class="card mb-2 border-left-info" style="border-left-width: 3px;">
                                                            <div class="card-body p-2">
                                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                                    <div class="flex-grow-1">
                                                                        <strong class="text-primary d-block mb-1" style="font-size: 0.9rem;">
                                                                            <i class="fas fa-capsules"></i> {{$obat->nama_brng}}
                                                                        </strong>
                                                                    </div>
                                                                    <div class="ml-2">
                                                                        <span class="badge badge-info">{{$obat->jml}}</span>
                                                                    </div>
                                                                </div>
                                                                @if($obat->aturan_pakai)
                                                                <div class="mt-2 pt-2 border-top">
                                                                    <small class="text-muted d-block">
                                                                        <i class="fas fa-info-circle"></i> <strong>Aturan Pakai:</strong>
                                                                    </small>
                                                                    <small class="text-dark d-block mt-1">
                                                                        {{$obat->aturan_pakai}}
                                                                    </small>
                                                                </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                    @else
                                                    <p class="text-muted mb-0 text-center">
                                                        <i class="fas fa-info-circle"></i> Tidak ada detail obat pada resep ini.
                                                    </p>
                                                    @endif
                                                    
                                                    @if($resep->tgl_penyerahan)
                                                    <div class="mt-3 pt-3 border-top">
                                                        <div class="row">
                                                            <div class="col-12 col-md-6 mb-2 mb-md-0">
                                                                <small class="text-muted d-block">
                                                                    <i class="fas fa-check-circle text-success"></i> 
                                                                    <strong>Tanggal Penyerahan:</strong> {{$dateSerah}}
                                                                </small>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <small class="text-muted d-block">
                                                                    <i class="fas fa-clock"></i> 
                                                                    <strong>Jam:</strong> {{$resep->jam_penyerahan ?? '-'}}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @else
                                                    <div class="mt-3 pt-3 border-top">
                                                        <small class="text-warning d-block text-center text-md-left">
                                                            <i class="fas fa-exclamation-triangle"></i> 
                                                            Belum diserahkan
                                                        </small>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                            @endforeach
                                        </x-adminlte-card>
                                        @endif --}}

                                        @if(count($berkasDigital) > 0)
                                        @php
                                        // Kelompokkan berkas berdasarkan kode master_berkas_digital
                                        $berkasGrouped = $berkasDigital->groupBy('kode');
                                        @endphp
                                        <x-adminlte-card theme="info" title="Berkas Digital" icon="fas fa-file-alt" theme-mode="outline" collapsible>
                                            @foreach($berkasGrouped as $kode => $berkasGroup)
                                            @php
                                            $namaKelompok = $berkasGroup->first()->nama ?? $kode;
                                            @endphp
                                            <div class="mb-4">
                                                <!-- Header Kelompok -->
                                                <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                                    <div class="mr-2">
                                                        <i class="fas fa-folder-open fa-2x text-warning"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h5 class="mb-0 text-dark">
                                                            <strong>{{ $namaKelompok }}</strong>
                                                        </h5>
                                                        <small class="text-muted">
                                                            <i class="fas fa-code"></i> Kode: {{ $kode }} | 
                                                            <i class="fas fa-file"></i> {{ count($berkasGroup) }} berkas
                                                        </small>
                                                    </div>
                                                </div>
                                                
                                                <!-- List Berkas dalam Kelompok -->
                                                <div class="list-group list-group-flush border">
                                                    @foreach($berkasGroup as $berkas)
                                                    @php
                                                    $urlBerkas = env('URL_BERKAS', '');
                                                    $fullPath = $urlBerkas . $berkas->lokasi_file;
                                                    $fileExtension = strtolower(pathinfo($berkas->lokasi_file, PATHINFO_EXTENSION));
                                                    
                                                    // Ekstrak nama file dari lokasi_file (setelah pages/upload/)
                                                    $fileName = $berkas->lokasi_file;
                                                    if (strpos($fileName, 'pages/upload/') !== false) {
                                                        $fileName = str_replace('pages/upload/', '', $fileName);
                                                    } else {
                                                        // Jika tidak ada prefix, gunakan basename
                                                        $fileName = basename($fileName);
                                                    }
                                                    
                                                    // Tentukan icon berdasarkan ekstensi file
                                                    $fileIcon = 'fa-file';
                                                    $fileColor = 'secondary';
                                                    if (in_array($fileExtension, ['pdf'])) {
                                                        $fileIcon = 'fa-file-pdf';
                                                        $fileColor = 'danger';
                                                    } elseif (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
                                                        $fileIcon = 'fa-file-image';
                                                        $fileColor = 'info';
                                                    } elseif (in_array($fileExtension, ['doc', 'docx'])) {
                                                        $fileIcon = 'fa-file-word';
                                                        $fileColor = 'primary';
                                                    } elseif (in_array($fileExtension, ['xls', 'xlsx'])) {
                                                        $fileIcon = 'fa-file-excel';
                                                        $fileColor = 'success';
                                                    }
                                                    @endphp
                                                    <div class="list-group-item d-flex flex-column flex-md-row align-items-start align-items-md-center berkas-item"
                                                        data-file-path="{{$fullPath}}"
                                                        data-file-name="{{$fileName}}"
                                                        data-file-extension="{{$fileExtension}}">
                                                        <div class="d-flex align-items-center flex-grow-1">
                                                            <div class="mr-3">
                                                                <i class="fas {{$fileIcon}} fa-lg text-{{$fileColor}}"></i>
                                                            </div>
                                                            <div>
                                                                <div class="font-weight-bold text-dark" style="word-break: break-all;">{{$fileName}}</div>
                                                                {{-- <small class="text-muted">
                                                                    <i class="fas fa-clock"></i> {{$berkas->tanggal ?? '-'}} &nbsp;|&nbsp;
                                                                    <i class="fas fa-user"></i> {{$berkas->petugas ?? '-'}} &nbsp;|&nbsp;
                                                                    <i class="fas fa-hashtag"></i> {{$berkas->no_rawat ?? '-'}}
                                                                </small> --}}
                                                            </div>
                                                        </div>
                                                        <div class="mt-3 mt-md-0 ml-md-3 d-flex flex-wrap berkas-item-actions" style="gap:0.5rem;">
                                                            <button type="button" class="btn btn-sm btn-outline-{{$fileColor}}" onclick="openBerkasModal('{{$fullPath}}', '{{$fileName}}', '{{$fileExtension}}')">
                                                                <i class="fas fa-eye"></i> Lihat
                                                            </button>
                                                            {{-- <a href="{{$fullPath}}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                                <i class="fas fa-external-link-alt"></i>
                                                            </a>
                                                            <a href="{{$fullPath}}" download class="btn btn-sm btn-outline-secondary">
                                                                <i class="fas fa-download"></i>
                                                            </a> --}}
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @if(!$loop->last)
                                            <hr class="my-4" style="border-top: 2px dashed #dee2e6;">
                                            @endif
                                            @endforeach
                                        </x-adminlte-card>
                                        @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-search fa-4x text-muted mb-3" style="opacity: 0.5;"></i>
                            </div>
                            <h4 class="text-muted mb-2">
                                <i class="fas fa-info-circle"></i> Data Tidak Ditemukan
                            </h4>
                            <p class="text-muted mb-4">
                                Tidak ada riwayat pemeriksaan yang ditemukan untuk periode yang dipilih.
                            </p>
                            <div class="mt-4">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary" 
                                        wire:click="resetFilter">
                                    <i class="fas fa-redo"></i> Reset Filter
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- Tab Tindakan Dokter -->
                <div class="tab-pane fade {{ $activeTab === 'tindakan' ? 'show active' : '' }}" 
                     id="tindakan-dokter-content" 
                     role="tabpanel" 
                     aria-labelledby="tindakan-dokter-tab">
                    @php
                    $statusLanjut = $this->getStatusLanjut($noRawat);
                    $tindakanRalan = $this->getTindakanRalan($noRawat);
                    $tindakanRanap = $this->getTindakanRanap($noRawat);
                    $tindakanRadiologi = $this->getTindakanRadiologi($noRawat);
                    $tindakanLab = $this->getTindakanLab($noRawat);
                    $tindakanOperasi = $this->getTindakanOperasi($noRawat);
                    $totalSemua = $this->getTotalSemuaTindakanDokter($noRawat);
                    @endphp
                    
                    <div class="card mb-3 border-left-primary" style="border-left-width: 4px;">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div>
                                    <h5 class="mb-1">
                                        <i class="fas fa-info-circle"></i> Informasi Perawatan
                                    </h5>
                                    <small class="text-white-50">
                                        <i class="fas fa-hospital"></i> No. Rawat: {{ $noRawat }}
                                    </small>
                                </div>
                                <div class="mt-2 mt-md-0">
                                    @if($statusLanjut == 'Ranap')
                                    <span class="badge badge-success" style="font-size: 0.9rem; padding: 0.5rem 0.75rem;">
                                        <i class="fas fa-bed"></i> Rawat Inap (Ranap)
                                    </span>
                                    @else
                                    <span class="badge badge-info" style="font-size: 0.9rem; padding: 0.5rem 0.75rem;">
                                        <i class="fas fa-walking"></i> Rawat Jalan (Ralan)
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs untuk Kategori Tindakan -->
                    <div class="card card-primary card-tabs">
                        <div class="card-header p-0 pt-1">
                            <ul class="nav nav-tabs" id="custom-tabs-tindakan-kategori-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link {{ $activeTabTindakan === 'ralan' ? 'active' : '' }}" 
                                       wire:click="setActiveTabTindakan('ralan')" 
                                       href="#" 
                                       role="tab">
                                        <i class="fas fa-walking"></i> Rawat Jalan 
                                        @if(count($tindakanRalan) > 0)
                                        <span class="badge badge-light ml-2">{{ count($tindakanRalan) }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $activeTabTindakan === 'ranap' ? 'active' : '' }}" 
                                       wire:click="setActiveTabTindakan('ranap')" 
                                       href="#" 
                                       role="tab">
                                        <i class="fas fa-bed"></i> Rawat Inap 
                                        @if(count($tindakanRanap) > 0)
                                        <span class="badge badge-light ml-2">{{ count($tindakanRanap) }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $activeTabTindakan === 'radiologi' ? 'active' : '' }}"
                                       wire:click="setActiveTabTindakan('radiologi')"
                                       href="#"
                                       role="tab">
                                        <i class="fas fa-x-ray"></i> Radiologi
                                        @if(count($tindakanRadiologi) > 0)
                                        <span class="badge badge-light ml-2">{{ count($tindakanRadiologi) }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $activeTabTindakan === 'lab' ? 'active' : '' }}"
                                       wire:click="setActiveTabTindakan('lab')"
                                       href="#"
                                       role="tab">
                                        <i class="fas fa-flask"></i> Laboratorium
                                        @if(count($tindakanLab) > 0)
                                        <span class="badge badge-light ml-2">{{ count($tindakanLab) }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $activeTabTindakan === 'operasi' ? 'active' : '' }}"
                                       wire:click="setActiveTabTindakan('operasi')"
                                       href="#"
                                       role="tab">
                                        <i class="fas fa-procedures"></i> Operasi
                                        @if(count($tindakanOperasi) > 0)
                                        <span class="badge badge-light ml-2">{{ count($tindakanOperasi) }}</span>
                                        @endif
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <!-- Tab Ralan -->
                                @if($activeTabTindakan === 'ralan')
                                <div class="tab-pane fade show active">
                                    @if(count($tindakanRalan) > 0)
                                    <!-- Desktop Table View -->
                                    <div class="table-responsive d-none d-md-block">
                                        <table class="table table-sm table-hover table-striped mb-0">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th style="width: 5%;">No</th>
                                                    <th style="width: 30%;">Nama Tindakan</th>
                                                    {{-- <th style="width: 15%;">Kode</th>
                                                    <th style="width: 15%;">Dokter</th> --}}
                                                    <th style="width: 12%;">Tanggal</th>
                                                    <th style="width: 8%;">Jam</th>
                                                    <th style="width: 15%;" class="text-right">Tarif Tindakan</th>
                                                    <th style="width: 10%;" class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($tindakanRalan as $tindakan)
                                                @php
                                                $tglTindakan = date_create($tindakan->tgl_perawatan ?? '0000-00-00');
                                                $dateTindakan = date_format($tglTindakan,"d M Y");
                                                $statusBayar = $tindakan->stts_bayar ?? 'Belum';
                                                $badgeStatus = $statusBayar == 'Sudah' ? 'success' : ($statusBayar == 'Suspen' ? 'warning' : 'secondary');
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <strong class="text-primary">
                                                            <i class="fas fa-procedures"></i> {{ $tindakan->nm_perawatan ?? '-' }}
                                                        </strong>
                                                    </td>
                                                    {{-- <td>
                                                        <small class="text-muted">
                                                            <i class="fas fa-code"></i> {{ $tindakan->kd_jenis_prw ?? '-' }}
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-user-md text-info"></i> {{ $tindakan->nm_dokter ?? '-' }}
                                                    </td> --}}
                                                    <td>
                                                        <i class="fas fa-calendar text-muted"></i> {{ $dateTindakan }}
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-clock text-muted"></i> {{ $tindakan->jam_rawat ?? '-' }}
                                                    </td>
                                                    <td class="text-right">
                                                        <strong class="text-success">
                                                            Rp {{ number_format($tindakan->tarif_tindakandr ?? 0, 0, ',', '.') }}
                                                        </strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-{{ $badgeStatus }}">
                                                            {{ $statusBayar }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        </div>
                                    
                                    <!-- Mobile Card View -->
                                    <div class="d-block d-md-none">
                                        @foreach($tindakanRalan as $tindakan)
                                        @php
                                        $tglTindakan = date_create($tindakan->tgl_perawatan ?? '0000-00-00');
                                        $dateTindakan = date_format($tglTindakan,"d M Y");
                                        $statusBayar = $tindakan->stts_bayar ?? 'Belum';
                                        $badgeStatus = $statusBayar == 'Sudah' ? 'success' : ($statusBayar == 'Suspen' ? 'warning' : 'secondary');
                                        @endphp
                                        <div class="card mb-3 shadow-sm border-left-info" style="border-left-width: 4px;">
                                            <div class="card-header bg-info text-white">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1" style="font-size: 0.95rem; line-height: 1.4;">
                                                            <i class="fas fa-procedures"></i> {{ $tindakan->nm_perawatan ?? '-' }}
                                                        </h6>
                                                        <small class="text-white-50 d-block mt-1">
                                                            <i class="fas fa-code"></i> {{ $tindakan->kd_jenis_prw ?? '-' }}
                                                        </small>
                                                    </div>
                                                    <span class="badge badge-light ml-2" style="font-size: 0.85rem;">#{{ $loop->iteration }}</span>
                                                </div>
                                            </div>
                                            <div class="card-body p-3">
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="fas fa-calendar text-muted mr-2"></i>
                                        <div>
                                                                <small class="text-muted d-block" style="font-size: 0.75rem;">Tanggal</small>
                                                                <strong style="font-size: 0.9rem;">{{ $dateTindakan }}</strong>
                                        </div>
                                    </div>
                                </div>
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="fas fa-clock text-muted mr-2"></i>
                                                            <div>
                                                                <small class="text-muted d-block" style="font-size: 0.75rem;">Jam</small>
                                                                <strong style="font-size: 0.9rem;">{{ $tindakan->jam_rawat ?? '-' }}</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @if($tindakan->nm_dokter)
                                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-user-md text-info mr-2"></i>
                                                        <div class="flex-grow-1">
                                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Dokter</small>
                                                            <strong class="text-info" style="font-size: 0.9rem;">{{ $tindakan->nm_dokter }}</strong>
                                        </div>
                                        </div>
                                    </div>
                                                @endif
                                                <div class="row align-items-end">
                                                    <div class="col-7">
                                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Tarif Tindakan</small>
                                                        <strong class="text-success" style="font-size: 1.1rem; font-weight: 600;">
                                                            Rp {{ number_format($tindakan->tarif_tindakandr ?? 0, 0, ',', '.') }}
                                                        </strong>
                                </div>
                                                    <div class="col-5 text-right">
                                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Status</small>
                                                        <span class="badge badge-{{ $badgeStatus }}" style="font-size: 0.85rem; padding: 0.4rem 0.6rem;">
                                                            {{ $statusBayar }}
                                                        </span>
                            </div>
                            </div>
                        </div>
                    </div>
                                        @endforeach
                                    </div>
                                    @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> Tidak ada data tindakan rawat jalan untuk pasien ini.
                                    </div>
                                    @endif
                                </div>
                                @endif

                                <!-- Tab Ranap -->
                                @if($activeTabTindakan === 'ranap')
                                <div class="tab-pane fade show active">
                                    @if(count($tindakanRanap) > 0)
                                    <!-- Desktop Table View -->
                                    <div class="table-responsive d-none d-md-block">
                                        <table class="table table-sm table-hover table-striped mb-0">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th style="width: 5%;">No</th>
                                                    <th style="width: 30%;">Nama Tindakan</th>
                                                    {{-- <th style="width: 15%;">Kode</th>
                                                    <th style="width: 15%;">Dokter</th> --}}
                                                    <th style="width: 12%;">Tanggal</th>
                                                    <th style="width: 8%;">Jam</th>
                                                    <th style="width: 15%;" class="text-right">Tarif Tindakan</th>
                                                    <th style="width: 10%;" class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($tindakanRanap as $tindakan)
                            @php
                            $tglTindakan = date_create($tindakan->tgl_perawatan ?? '0000-00-00');
                            $dateTindakan = date_format($tglTindakan,"d M Y");
                            $statusBayar = $tindakan->stts_bayar ?? 'Belum';
                            $badgeStatus = $statusBayar == 'Sudah' ? 'success' : ($statusBayar == 'Suspen' ? 'warning' : 'secondary');
                            @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <strong class="text-primary">
                                                            <i class="fas fa-procedures"></i> {{ $tindakan->nm_perawatan ?? '-' }}
                                                        </strong>
                                                    </td>
                                                    {{-- <td>
                                                        <small class="text-muted">
                                                            <i class="fas fa-code"></i> {{ $tindakan->kd_jenis_prw ?? '-' }}
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-user-md text-info"></i> {{ $tindakan->nm_dokter ?? '-' }}
                                                    </td> --}}
                                                    <td>
                                                        <i class="fas fa-calendar text-muted"></i> {{ $dateTindakan }}
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-clock text-muted"></i> {{ $tindakan->jam_rawat ?? '-' }}
                                                    </td>
                                                    <td class="text-right">
                                                        <strong class="text-success">
                                                            Rp {{ number_format($tindakan->tarif_tindakandr ?? 0, 0, ',', '.') }}
                                                        </strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-{{ $badgeStatus }}">
                                                            {{ $statusBayar }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Mobile Card View -->
                                    <div class="d-block d-md-none">
                                        @foreach($tindakanRanap as $tindakan)
                                        @php
                                        $tglTindakan = date_create($tindakan->tgl_perawatan ?? '0000-00-00');
                                        $dateTindakan = date_format($tglTindakan,"d M Y");
                                        $statusBayar = $tindakan->stts_bayar ?? 'Belum';
                                        $badgeStatus = $statusBayar == 'Sudah' ? 'success' : ($statusBayar == 'Suspen' ? 'warning' : 'secondary');
                                        @endphp
                                        <div class="card mb-3 shadow-sm border-left-success" style="border-left-width: 4px;">
                                            <div class="card-header bg-success text-white">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1" style="font-size: 0.95rem; line-height: 1.4;">
                                                    <i class="fas fa-procedures"></i> {{ $tindakan->nm_perawatan ?? '-' }}
                                                </h6>
                                                <small class="text-white-50 d-block mt-1">
                                                    <i class="fas fa-code"></i> {{ $tindakan->kd_jenis_prw ?? '-' }}
                                                </small>
                                            </div>
                                            <span class="badge badge-light ml-2" style="font-size: 0.85rem;">#{{ $loop->iteration }}</span>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row mb-3">
                                            <div class="col-6">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-calendar text-muted mr-2"></i>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Tanggal</small>
                                                        <strong style="font-size: 0.9rem;">{{ $dateTindakan }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-clock text-muted mr-2"></i>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Jam</small>
                                                        <strong style="font-size: 0.9rem;">{{ $tindakan->jam_rawat ?? '-' }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if($tindakan->nm_dokter)
                                        <div class="mb-3 pb-3 border-bottom">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-md text-info mr-2"></i>
                                                <div class="flex-grow-1">
                                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Dokter</small>
                                                    <strong class="text-info" style="font-size: 0.9rem;">{{ $tindakan->nm_dokter }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                        <div class="row align-items-end">
                                            <div class="col-7">
                                                <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Tarif Tindakan</small>
                                                <strong class="text-success" style="font-size: 1.1rem; font-weight: 600;">
                                                    Rp {{ number_format($tindakan->tarif_tindakandr ?? 0, 0, ',', '.') }}
                                                </strong>
                                            </div>
                                            <div class="col-5 text-right">
                                                <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Status</small>
                                                <span class="badge badge-{{ $badgeStatus }}" style="font-size: 0.85rem; padding: 0.4rem 0.6rem;">
                                                    {{ $statusBayar }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                        @endforeach
                            </div>
                                    @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> Tidak ada data tindakan rawat inap untuk pasien ini.
                                    </div>
                                    @endif
                                </div>
                                @endif

                                <!-- Tab Radiologi -->
                                @if($activeTabTindakan === 'radiologi')
                                <div class="tab-pane fade show active">
                                    @if(count($tindakanRadiologi) > 0)
                                    <!-- Desktop Table View -->
                                    <div class="table-responsive d-none d-md-block">
                                        <table class="table table-sm table-hover table-striped mb-0">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th style="width: 5%;">No</th>
                                                    <th style="width: 35%;">Nama Pemeriksaan</th>
                                                    {{-- <th style="width: 15%;">Kode</th> --}}
                                                    <th style="width: 12%;">Tanggal</th>
                                                    <th style="width: 8%;">Jam</th>
                                                    <th style="width: 10%;" class="text-center">Status</th>
                                                    <th style="width: 15%;" class="text-right">Tarif Dokter</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($tindakanRadiologi as $tindakan)
                                                @php
                                                $tglPeriksa = date_create($tindakan->tgl_periksa ?? '0000-00-00');
                                                $datePeriksa = date_format($tglPeriksa,"d M Y");
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <strong class="text-dark">
                                                            <i class="fas fa-x-ray"></i> {{ $tindakan->nm_perawatan ?? '-' }}
                                                        </strong>
                                                    </td>
                                                    {{-- <td>
                                                        <small class="text-muted">
                                                            <i class="fas fa-code"></i> {{ $tindakan->kd_jenis_prw ?? '-' }}
                                                        </small>
                                                    </td> --}}
                                                    <td>
                                                        <i class="fas fa-calendar text-muted"></i> {{ $datePeriksa }}
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-clock text-muted"></i> {{ $tindakan->jam ?? '-' }}
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-pill {{ $tindakan->status === 'Ranap' ? 'badge-success' : 'badge-info' }}">
                                                            {{ $tindakan->status }}
                                                        </span>
                                                    </td>
                                                    <td class="text-right">
                                                        <strong class="text-success">
                                                            Rp {{ number_format($tindakan->tarif_tindakan_dokter ?? 0, 0, ',', '.') }}
                                                        </strong>
                                                    </td>
                                                </tr>
                            @endforeach
                                            </tbody>
                                        </table>
                        </div>
                        
                                    <!-- Mobile Card View -->
                                    <div class="d-block d-md-none">
                                        @foreach($tindakanRadiologi as $tindakan)
                                        @php
                                        $tglPeriksa = date_create($tindakan->tgl_periksa ?? '0000-00-00');
                                        $datePeriksa = date_format($tglPeriksa,"d M Y");
                                        @endphp
                                        <div class="card mb-3 shadow-sm border-left-warning" style="border-left-width: 4px;">
                                            <div class="card-header bg-warning text-dark">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1" style="font-size: 0.95rem; line-height: 1.4;">
                                                            <i class="fas fa-x-ray"></i> {{ $tindakan->nm_perawatan ?? '-' }}
                                                        </h6>
                                                        <small class="text-dark-50 d-block mt-1">
                                                            <i class="fas fa-code"></i> {{ $tindakan->kd_jenis_prw ?? '-' }}
                                                        </small>
                                                    </div>
                                                    <span class="badge badge-light ml-2" style="font-size: 0.85rem;">#{{ $loop->iteration }}</span>
                                                </div>
                                            </div>
                                            <div class="card-body p-3">
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="fas fa-calendar text-muted mr-2"></i>
                                                            <div>
                                                                <small class="text-muted d-block" style="font-size: 0.75rem;">Tanggal</small>
                                                                <strong style="font-size: 0.9rem;">{{ $datePeriksa }}</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="fas fa-clock text-muted mr-2"></i>
                                                            <div>
                                                                <small class="text-muted d-block" style="font-size: 0.75rem;">Jam</small>
                                                                <strong style="font-size: 0.9rem;">{{ $tindakan->jam ?? '-' }}</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3 pb-3 border-bottom">
                                                    <span class="badge badge-pill {{ $tindakan->status === 'Ranap' ? 'badge-success' : 'badge-info' }}">
                                                        {{ $tindakan->status }}
                                                    </span>
                                                </div>
                                                <div class="row align-items-end">
                            <div class="col-12">
                                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Tarif Dokter</small>
                                                        <strong class="text-success" style="font-size: 1.1rem; font-weight: 600;">
                                                            Rp {{ number_format($tindakan->tarif_tindakan_dokter ?? 0, 0, ',', '.') }}
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> Tidak ada data tindakan radiologi untuk pasien ini.
                                    </div>
                                    @endif
                                </div>
                                @endif

                                <!-- Tab Lab -->
                                @if($activeTabTindakan === 'lab')
                                <div class="tab-pane fade show active">
                                    @if(count($tindakanLab) > 0)
                                    <!-- Desktop Table View -->
                                    <div class="table-responsive d-none d-md-block">
                                        <table class="table table-sm table-hover table-striped mb-0">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th style="width: 5%;">No</th>
                                                    <th style="width: 35%;">Nama Pemeriksaan</th>
                                                    {{-- <th style="width: 15%;">Kode</th> --}}
                                                    <th style="width: 12%;">Tanggal</th>
                                                    <th style="width: 8%;">Jam</th>
                                                    <th style="width: 10%;" class="text-center">Status</th>
                                                    <th style="width: 15%;" class="text-right">Tarif Dokter</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($tindakanLab as $tindakan)
                                                @php
                                                $tglPeriksa = date_create($tindakan->tgl_periksa ?? '0000-00-00');
                                                $datePeriksa = date_format($tglPeriksa,"d M Y");
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <strong class="text-dark">
                                                            <i class="fas fa-flask"></i> {{ $tindakan->nm_perawatan ?? '-' }}
                                                        </strong>
                                                    </td>
                                                    {{-- <td>
                                                        <small class="text-muted">
                                                            <i class="fas fa-code"></i> {{ $tindakan->kd_jenis_prw ?? '-' }}
                                                        </small>
                                                    </td> --}}
                                                    <td>
                                                        <i class="fas fa-calendar text-muted"></i> {{ $datePeriksa }}
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-clock text-muted"></i> {{ $tindakan->jam ?? '-' }}
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-pill {{ $tindakan->status === 'Ranap' ? 'badge-success' : 'badge-info' }}">
                                                            {{ $tindakan->status }}
                                                        </span>
                                                    </td>
                                                    <td class="text-right">
                                                        <strong class="text-success">
                                                            Rp {{ number_format($tindakan->tarif_tindakan_dokter ?? 0, 0, ',', '.') }}
                                                        </strong>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Mobile Card View -->
                                    <div class="d-block d-md-none">
                                        @foreach($tindakanLab as $tindakan)
                                        @php
                                        $tglPeriksa = date_create($tindakan->tgl_periksa ?? '0000-00-00');
                                        $datePeriksa = date_format($tglPeriksa,"d M Y");
                                        @endphp
                                        <div class="card mb-3 shadow-sm border-left-danger" style="border-left-width: 4px;">
                                            <div class="card-header bg-danger text-white">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1" style="font-size: 0.95rem; line-height: 1.4;">
                                                            <i class="fas fa-flask"></i> {{ $tindakan->nm_perawatan ?? '-' }}
                                                        </h6>
                                                        <small class="text-white-50 d-block mt-1">
                                                            <i class="fas fa-code"></i> {{ $tindakan->kd_jenis_prw ?? '-' }}
                                                        </small>
                                                    </div>
                                                    <span class="badge badge-light ml-2" style="font-size: 0.85rem;">#{{ $loop->iteration }}</span>
                                                </div>
                                            </div>
                                            <div class="card-body p-3">
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="fas fa-calendar text-muted mr-2"></i>
                                                            <div>
                                                                <small class="text-muted d-block" style="font-size: 0.75rem;">Tanggal</small>
                                                                <strong style="font-size: 0.9rem;">{{ $datePeriksa }}</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="fas fa-clock text-muted mr-2"></i>
                                                            <div>
                                                                <small class="text-muted d-block" style="font-size: 0.75rem;">Jam</small>
                                                                <strong style="font-size: 0.9rem;">{{ $tindakan->jam ?? '-' }}</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3 pb-3 border-bottom">
                                                    <span class="badge badge-pill {{ $tindakan->status === 'Ranap' ? 'badge-success' : 'badge-info' }}">
                                                        {{ $tindakan->status }}
                                                    </span>
                                                </div>
                                                <div class="row align-items-end">
                                                    <div class="col-12">
                                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Tarif Dokter</small>
                                                        <strong class="text-success" style="font-size: 1.1rem; font-weight: 600;">
                                                            Rp {{ number_format($tindakan->tarif_tindakan_dokter ?? 0, 0, ',', '.') }}
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> Tidak ada data tindakan laboratorium untuk pasien ini.
                                    </div>
                                    @endif
                                </div>
                                @endif

                                <!-- Tab Operasi -->
                                @if($activeTabTindakan === 'operasi')
                                <div class="tab-pane fade show active">
                                    @if(count($tindakanOperasi) > 0)
                                    <!-- Desktop Table View -->
                                    <div class="table-responsive d-none d-md-block">
                                        <table class="table table-sm table-hover table-striped mb-0">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th style="width: 5%;">No</th>
                                                    <th style="width: 25%;">Nama Paket Operasi</th>
                                                    {{-- <th style="width: 12%;">Kode</th> --}}
                                                    <th style="width: 10%;">Tanggal</th>
                                                    <th style="width: 8%;">Jam</th>
                                                    <th style="width: 12%;" class="text-center">Kategori</th>
                                                    <th style="width: 13%;" class="text-center">Peran Dokter</th>
                                                    <th style="width: 10%;" class="text-center">Status</th>
                                                    <th style="width: 15%;" class="text-right">Biaya Dokter</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($tindakanOperasi as $tindakan)
                                                @php
                                                $tglOperasi = date_create($tindakan->tgl_operasi ?? '0000-00-00');
                                                $dateOperasi = date_format($tglOperasi,"d M Y");
                                                $jamOperasi = date_format($tglOperasi,"H:i");
                                                @endphp
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <strong class="text-dark">
                                                            <i class="fas fa-procedures"></i> {{ $tindakan->nm_perawatan ?? '-' }}
                                                        </strong>
                                                    </td>
                                                    {{-- <td>
                                                        <small class="text-muted">
                                                            <i class="fas fa-code"></i> {{ $tindakan->kode_paket ?? '-' }}
                                                        </small>
                                                    </td> --}}
                                                    <td>
                                                        <i class="fas fa-calendar text-muted"></i> {{ $dateOperasi }}
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-clock text-muted"></i> {{ $jamOperasi }}
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-pill badge-info">
                                                            {{ $tindakan->kategori ?? '-' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-pill badge-secondary">
                                                            {{ $tindakan->peran_dokter ?? '-' }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-pill {{ $tindakan->status === 'Ranap' ? 'badge-success' : 'badge-info' }}">
                                                            {{ $tindakan->status }}
                                                        </span>
                                                    </td>
                                                    <td class="text-right">
                                                        <strong class="text-success">
                                                            Rp {{ number_format($tindakan->biaya_dokter ?? 0, 0, ',', '.') }}
                                                        </strong>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Mobile Card View -->
                                    <div class="d-block d-md-none">
                                        @foreach($tindakanOperasi as $tindakan)
                                        @php
                                        $tglOperasi = date_create($tindakan->tgl_operasi ?? '0000-00-00');
                                        $dateOperasi = date_format($tglOperasi,"d M Y");
                                        $jamOperasi = date_format($tglOperasi,"H:i");
                                        @endphp
                                        <div class="card mb-3 shadow-sm border-left-secondary" style="border-left-width: 4px;">
                                            <div class="card-header bg-secondary text-white">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1" style="font-size: 0.95rem; line-height: 1.4;">
                                                            <i class="fas fa-procedures"></i> {{ $tindakan->nm_perawatan ?? '-' }}
                                                        </h6>
                                                        <small class="text-white-50 d-block mt-1">
                                                            <i class="fas fa-code"></i> {{ $tindakan->kode_paket ?? '-' }}
                                                        </small>
                                                    </div>
                                                    <span class="badge badge-light ml-2" style="font-size: 0.85rem;">#{{ $loop->iteration }}</span>
                                                </div>
                                            </div>
                                            <div class="card-body p-3">
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="fas fa-calendar text-muted mr-2"></i>
                                                            <div>
                                                                <small class="text-muted d-block" style="font-size: 0.75rem;">Tanggal</small>
                                                                <strong style="font-size: 0.9rem;">{{ $dateOperasi }}</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="fas fa-clock text-muted mr-2"></i>
                                                            <div>
                                                                <small class="text-muted d-block" style="font-size: 0.75rem;">Jam</small>
                                                                <strong style="font-size: 0.9rem;">{{ $jamOperasi }}</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @if($tindakan->kategori)
                                                <div class="mb-2">
                                                    <span class="badge badge-pill badge-info">
                                                        {{ $tindakan->kategori }}
                                                    </span>
                                                </div>
                                                @endif
                                                @if($tindakan->peran_dokter)
                                                <div class="mb-3 pb-3 border-bottom">
                                                    <span class="badge badge-pill badge-secondary">
                                                        {{ $tindakan->peran_dokter }}
                                                    </span>
                                                </div>
                                                @endif
                                                <div class="mb-2">
                                                    <span class="badge badge-pill {{ $tindakan->status === 'Ranap' ? 'badge-success' : 'badge-info' }}">
                                                        {{ $tindakan->status }}
                                                    </span>
                                                </div>
                                                <div class="row align-items-end mt-3">
                                                    <div class="col-12">
                                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Biaya Dokter</small>
                                                        <strong class="text-success" style="font-size: 1.1rem; font-weight: 600;">
                                                            Rp {{ number_format($tindakan->biaya_dokter ?? 0, 0, ',', '.') }}
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> Tidak ada data tindakan operasi untuk pasien ini.
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Total Summary Card -->
                    <div class="card mt-4 border-left-success shadow-sm" style="border-left-width: 4px;">
                                    <div class="card-body bg-light">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-1 text-muted">
                                                    <i class="fas fa-calculator text-success"></i> Total Biaya Tindakan Dokter (Perkiraan)
                                                </h5>
                                                <small class="text-muted">
                                        Total dari semua kategori tindakan yang tercatat
                                                </small>
                                            </div>
                                            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                                                <h3 class="mb-0 text-primary" style="font-weight: 700;">
                                        Rp {{ number_format($totalSemua['total'], 0, ',', '.') }}
                                                </h3>
                                            </div>
                                        </div>
                            <div class="alert alert-warning mt-3 mb-0" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <strong>Catatan:</strong> Nilai ini hanya merupakan <strong>perkiraan biaya</strong> berdasarkan tarif tindakan dokter. Nilai bisa berubah tergantung pada klaim BPJS atau perawatan lainnya.
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
        </div>
    </div>
</div>

<!-- Overlay Berkas Digital -->
<div class="pdf-viewer-overlay" id="berkasViewerOverlay" style="display: none;">
    <div class="pdf-viewer-container">
        <div class="pdf-viewer-header">
            <span class="pdf-viewer-title" id="berkasViewerTitle">Berkas Digital</span>
            <div class="pdf-viewer-actions">
                <a href="#" id="berkasViewerDownload" class="btn btn-sm btn-light" download title="Download">
                    <i class="fas fa-download"></i>
                </a>
                <a href="#" id="berkasViewerOpen" class="btn btn-sm btn-light" target="_blank" title="Buka Tab Baru">
                    <i class="fas fa-external-link-alt"></i>
                </a>
                <button type="button" class="btn btn-sm btn-danger" onclick="closeBerkasViewer()" title="Tutup">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="pdf-viewer-body" id="berkasViewerBody">
            <!-- konten dimuat via JS -->
        </div>
    </div>
</div>

@push('css')
<style>
    .berkas-item {
        cursor: pointer;
        transition: background-color 0.2s ease;
    }

    .berkas-item:hover {
        background-color: #f8fafc;
    }

    .pasien-tabs-container {
        min-height: 400px;
    }
    
    .pasien-tabs-container .card-body {
        /* Custom scrollbar untuk webkit browsers */
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f7fafc;
    }
    
    .pasien-tabs-container .card-body::-webkit-scrollbar {
        width: 8px;
    }
    
    .pasien-tabs-container .card-body::-webkit-scrollbar-track {
        background: #f7fafc;
        border-radius: 4px;
    }
    
    .pasien-tabs-container .card-body::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 4px;
    }
    
    .pasien-tabs-container .card-body::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }
    
    /* Pastikan tab-pane content bisa di-scroll dengan baik */
    .pasien-tabs-container .tab-pane {
        max-height: 100%;
        overflow-y: auto;
    }
    
    /* Pastikan tab-content tidak overflow */
    .pasien-tabs-container .tab-content {
        overflow: visible;
    }
    
    /* Overlay berkas digital */
    .pdf-viewer-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 2050;
        background: rgba(0,0,0,0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .pdf-viewer-container {
        width: 100%;
        max-width: 1400px;
        height: 95vh;
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 25px 60px rgba(0,0,0,0.45);
    }

    .pdf-viewer-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1.25rem;
        background: linear-gradient(135deg, #343a40 0%, #1f2327 100%);
        color: #fff;
    }

    .pdf-viewer-actions .btn {
        margin-left: 0.5rem;
    }

    .pdf-viewer-close {
        border: none;
        background: rgba(255,255,255,0.2);
        color: #fff;
        padding: 0.35rem 0.6rem;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .pdf-viewer-close:hover {
        background: rgba(255,255,255,0.35);
    }

    .pdf-viewer-body {
        flex: 1;
        background: #000;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .pdf-viewer-body iframe {
        width: 100%;
        height: 100%;
        border: none;
        background: #fff;
    }

    .pdf-viewer-body img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    /* Responsif untuk mobile */
    @media (max-width: 768px) {
        .pasien-tabs-container {
            height: calc(100vh - 150px) !important;
        }
    }
    
    /* Styling untuk accordion filter riwayat */
    #filterRiwayatHeader .btn-link {
        transition: all 0.3s ease;
    }
    
    #filterRiwayatHeader .btn-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    #filterRiwayatHeader .btn-link:focus {
        box-shadow: none;
        outline: none;
    }
    
    #filterRiwayatIcon {
        transition: all 0.3s ease;
    }
    
    /* Style untuk summary cards */
    .description-block {
        text-align: center;
    }
    .description-block .description-percentage {
        font-size: 1.5rem;
        display: block;
        margin-bottom: 0.5rem;
    }
    .description-block .description-header {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0.5rem 0;
    }
    .description-block .description-text {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .nav-link {
        cursor: pointer;
    }
    
    /* Visualisasi Skala Nyeri */
    .pain-scale-visual {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .pain-scale-container {
        position: relative;
    }
    
    .pain-scale-numbers {
        font-size: 0.75rem;
    }
    
    .pain-scale-number {
        flex: 1;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .pain-scale-number.active {
        font-weight: bold;
        font-size: 0.85rem !important;
        color: #dc3545 !important;
        transform: scale(1.2);
    }
    
    .pain-indicator {
        animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
        }
        50% {
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.6);
        }
    }
    
    .pain-scale-bar-wrapper {
        margin: 0.5rem 0;
    }
    
    .pain-scale-background {
        transition: all 0.3s ease;
    }
    
    .pain-scale-filled {
        transition: width 0.6s ease;
    }
    
    .pain-indicator {
        transition: left 0.6s ease;
    }
    
    .pain-indicator::before {
        content: '';
        position: absolute;
        top: -8px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-top: 8px solid #dc3545;
    }
</style>
@endpush

@push('js')
<script>
    let currentFileExtension = '';

    function openBerkasModal(filePath, fileName, fileExtension) {
        currentFileExtension = (fileExtension || '').toLowerCase();
        $('#berkasViewerTitle').text(fileName || 'Berkas Digital');
        $('#berkasViewerDownload').attr('href', filePath);
        $('#berkasViewerOpen').attr('href', filePath);

        const body = $('#berkasViewerBody');
        body.empty();

        if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(currentFileExtension)) {
            const img = $('<img>')
                .attr('src', filePath)
                .attr('alt', fileName || 'Berkas Digital')
                .on('error', function() {
                    body.html('<div class="text-white text-center p-4"><i class="fas fa-exclamation-triangle"></i> Gagal memuat gambar</div>');
                });
            body.append(img);
        } else {
            const iframe = $('<iframe>')
                .attr({
                    src: filePath,
                    title: fileName || 'Berkas Digital'
                })
                .on('error', function() {
                    body.html(`
                        <div class="text-center p-5">
                            <i class="fas fa-file fa-3x mb-3"></i>
                            <p>File tidak dapat ditampilkan di browser</p>
                            <a href="${filePath}" class="btn btn-primary" download>
                                <i class="fas fa-download"></i> Download File
                            </a>
                        </div>
                    `);
                });
            body.append(iframe);
        }

        $('#berkasViewerOverlay').fadeIn(200);
        $('body').css('overflow', 'hidden');
    }

    function closeBerkasViewer() {
        $('#berkasViewerOverlay').fadeOut(200);
        $('#berkasViewerBody').empty();
        $('body').css('overflow', '');
    }

    // Tutup overlay dengan ESC
    $(document).on('keydown.berkasViewer', function(e) {
        if (e.key === 'Escape' && $('#berkasViewerOverlay').is(':visible')) {
            closeBerkasViewer();
        }
    });

    // Tutup overlay saat klik area gelap
    $(document).on('click', '#berkasViewerOverlay', function(e) {
        if (e.target.id === 'berkasViewerOverlay') {
            closeBerkasViewer();
        }
    });

    $(document).ready(function() {
        $("#riwayat-dokter-tabs").select2({
            placeholder: "Pilih Dokter",
            theme: 'bootstrap4',
            width: 'auto',
            allowClear: true,
            dropdownAutoWidth: true
        });

        $('#riwayat-dokter-tabs').on('change', function () {
            @this.set('selectDokter', $(this).val());
        });

        // Reset select2 ketika filter direset
        window.addEventListener('resetSelect2', event => {
            $('#riwayat-dokter-tabs').val(null).trigger('change');
        });

        // Handler untuk accordion filter riwayat
        $('#filterRiwayatCollapse').on('show.bs.collapse', function () {
            $('#filterRiwayatIcon').removeClass('fa-chevron-down').addClass('fa-chevron-up');
        });

        $('#filterRiwayatCollapse').on('hide.bs.collapse', function () {
            $('#filterRiwayatIcon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
        });

        // Klik list berkas untuk buka modal
        $(document).on('click', '.berkas-item', function (event) {
            if ($(event.target).closest('.berkas-item-actions').length) {
                return;
            }
            const filePath = $(this).data('file-path');
            const fileName = $(this).data('file-name');
            const fileExtension = $(this).data('file-extension');
            if (filePath) {
                openBerkasModal(filePath, fileName || 'Berkas', fileExtension || '');
            }
        });

        $(document).on('click', '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox();
        });

        // Sinkronisasi Bootstrap tabs dengan Livewire
        window.addEventListener('activeTabUpdated', event => {
            $('#custom-tabs-pasien-tab a.nav-link').removeClass('active');
            $('#custom-tabs-pasien-tabContent .tab-pane').removeClass('show active');
            
            var activeTab = event.detail.activeTab;
            if (activeTab === 'detail') {
                $('#detail-pasien-tab').addClass('active');
                $('#detail-pasien-content').addClass('show active');
            } else if (activeTab === 'riwayat') {
                $('#riwayat-pasien-tab').addClass('active');
                $('#riwayat-pasien-content').addClass('show active');
            } else if (activeTab === 'tindakan') {
                $('#tindakan-dokter-tab').addClass('active');
                $('#tindakan-dokter-content').addClass('show active');
            }
        });

        // Listener untuk membuka tab riwayat dari tombol di pasien.blade.php
        window.addEventListener('openRiwayatTab', event => {
            @this.setActiveTab('riwayat');
        });

        // Listener untuk Livewire event
        Livewire.on('openRiwayatTab', () => {
            @this.setActiveTab('riwayat');
        });
    });
</script>
@endpush

