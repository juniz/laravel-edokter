<div class="pasien-tabs-container" style="height: calc(100vh - 200px); display: flex; flex-direction: column;">
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
                                        <x-adminlte-card theme="primary" title="SOAP/CPPT" icon="fas fa-clipboard-list" theme-mode="outline" collapsible="collapsed">
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
                                        <div class="card card-outline card-primary mb-3 collapsed-card">
                                            <div class="card-header">
                                                <h3 class="card-title"><i class="fas fa-diagnoses mr-2"></i>Diagnosa</h3>
                                                <div class="card-tools">
                                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body p-0" style="display: none;">
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
                                        <div class="card card-outline card-success mb-3 collapsed-card">
                                            <div class="card-header">
                                                <h3 class="card-title"><i class="fas fa-pills mr-2"></i>Obat Ralan</h3>
                                                <div class="card-tools">
                                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body p-0" style="display: none;">
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
                                        <div class="card card-outline card-success mb-3 collapsed-card">
                                            <div class="card-header">
                                                <h3 class="card-title"><i class="fas fa-pills mr-2"></i>Obat Ranap</h3>
                                                <div class="card-tools">
                                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body p-0" style="display: none;">
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
                                        <x-adminlte-card theme="dark" title="Laboratorium" icon="fas fa-flask" theme-mode="outline" collapsible="collapsed">
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
                                        <x-adminlte-card theme="dark" title="Radiologi" icon="fas fa-x-ray" collapsible="collapsed" theme-mode="outline">
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
                                        <x-adminlte-card theme="info" title="Berkas Digital" icon="fas fa-file-alt" theme-mode="outline" collapsible="collapsed">
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
                    $tindakanDokter = $this->getTindakanDokter($noRawat);
                    $totalBiaya = $this->getTotalTindakanDokter($noRawat);
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

                    @if(count($tindakanDokter) > 0)
                    <!-- Summary Card -->
                    <div class="card mb-4 border-left-success" style="border-left-width: 4px;">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-calculator"></i> Ringkasan Biaya Tindakan Dokter
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <i class="fas fa-list-check fa-3x text-success"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-0">Total Tindakan</h6>
                                            <h3 class="mb-0 text-success">{{ count($tindakanDokter) }} Tindakan</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <i class="fas fa-money-bill-wave fa-3x text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="text-muted mb-0">Total Biaya (Perkiraan)</h6>
                                            <h3 class="mb-0 text-primary">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-warning mt-3 mb-0" role="alert">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <strong>Catatan:</strong> Nilai ini hanya merupakan <strong>perkiraan biaya</strong> berdasarkan tarif tindakan dokter. 
                                Biaya aktual mungkin berbeda tergantung kebijakan rumah sakit dan diskon yang berlaku.
                            </div>
                        </div>
                    </div>

                    <!-- Detail Tindakan -->
                    <x-adminlte-card theme="primary" title="Detail Tindakan Dokter" icon="fas fa-stethoscope" theme-mode="outline" collapsible="collapsed" maximizable>
                        <!-- Card View untuk Semua Device -->
                        <div class="row">
                            @foreach($tindakanDokter as $tindakan)
                            @php
                            $tglTindakan = date_create($tindakan->tgl_perawatan ?? '0000-00-00');
                            $dateTindakan = date_format($tglTindakan,"d M Y");
                            $statusBayar = $tindakan->stts_bayar ?? 'Belum';
                            $badgeStatus = $statusBayar == 'Sudah' ? 'success' : ($statusBayar == 'Suspen' ? 'warning' : 'secondary');
                            @endphp
                            <div class="col-12 col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 shadow-sm border-left-primary" style="border-left-width: 4px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)'">
                                    <div class="card-header bg-primary text-white">
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
                                        <!-- Tanggal & Jam -->
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

                                        <!-- Dokter -->
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

                                        <!-- Tarif & Status -->
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
                            </div>
                            @endforeach
                        </div>
                        
                        <!-- Total Summary Card -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-left-success shadow-sm" style="border-left-width: 4px;">
                                    <div class="card-body bg-light">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h5 class="mb-1 text-muted">
                                                    <i class="fas fa-calculator text-success"></i> Total Biaya Tindakan Dokter (Perkiraan)
                                                </h5>
                                                <small class="text-muted">
                                                    Total dari {{ count($tindakanDokter) }} tindakan yang tercatat
                                                </small>
                                            </div>
                                            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                                                <h3 class="mb-0 text-primary" style="font-weight: 700;">
                                                    Rp {{ number_format($totalBiaya, 0, ',', '.') }}
                                                </h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-adminlte-card>
                    @else
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-stethoscope fa-4x text-muted mb-3" style="opacity: 0.5;"></i>
                        </div>
                        <h4 class="text-muted mb-2">
                            <i class="fas fa-info-circle"></i> Tidak Ada Data Tindakan Dokter
                        </h4>
                        <p class="text-muted">
                            Belum ada tindakan dokter yang tercatat untuk pasien ini.
                        </p>
                    </div>
                    @endif
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

