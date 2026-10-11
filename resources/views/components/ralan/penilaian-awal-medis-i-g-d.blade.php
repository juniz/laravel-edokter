@php
    $modalId = 'modalPilihSoap-' . md5($data->no_rawat ?? ($dataMedis->no_rawat ?? ($noRawat ?? uniqid())));
    $soapCount = isset($listPemeriksaan) ? count($listPemeriksaan) : 0;
@endphp
<div x-data="{
    listSoap: @js($listPemeriksaan ?? []),
    selectedSoap: @js(isset($listPemeriksaan) && count($listPemeriksaan) > 0 ? (is_array($listPemeriksaan) ? ($listPemeriksaan[0] ?? null) : $listPemeriksaan->first()) : null),
    selectSoap(soap) {
        this.selectedSoap = soap;
        $('#{{ $modalId }}').modal('hide');
    }
}">
    @if(!empty($data) || !empty($dataMedis))
    <x-adminlte-card theme="info" title="Penilaian Awal IGD (Medis & Keperawatan)" icon="fas fa-ambulance" theme-mode="outline" collapsible class="mb-4">
        <!-- Metadata Header: Dokter & Perawat -->
        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom flex-wrap">
            <div class="row flex-grow-1">
                @if(!empty($dataMedis))
                <div class="col-md-5 mb-2">
                    <small class="text-muted d-block">Dokter Pemeriksa</small>
                    <strong><i class="fas fa-user-md text-primary"></i> {{ $dataMedis->nm_dokter ?? $dataMedis->kd_dokter ?? '-' }}</strong>
                    <div class="text-xs text-muted mt-1">
                        <i class="fas fa-calendar-alt"></i> {{ !empty($dataMedis->tanggal) ? date('d M Y H:i', strtotime($dataMedis->tanggal)) : '-' }}
                        &bull; Anamnesis: <span class="badge badge-light border">{{ $dataMedis->anamnesis ?? '-' }}</span>
                        @if(!empty($dataMedis->hubungan))
                        <span class="text-muted">({{ $dataMedis->hubungan }})</span>
                        @endif
                    </div>
                </div>
                @endif

                @if(!empty($data))
                <div class="col-md-5 mb-2">
                    <small class="text-muted d-block">Petugas / Perawat</small>
                    <strong><i class="fas fa-user-nurse text-success"></i> {{ $data->nama_petugas ?? $data->nip ?? '-' }}</strong>
                    <div class="text-xs text-muted mt-1">
                        <i class="fas fa-calendar-alt"></i> {{ !empty($data->tanggal) ? date('d M Y H:i', strtotime($data->tanggal)) : '-' }}
                        &bull; Sumber: <span class="badge badge-light border">{{ $data->informasi ?? '-' }}</span>
                    </div>
                </div>
                @endif
            </div>

            <div class="ml-auto my-1">
                <button type="button" class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#{{ $modalId }}" title="Pilih Data SOAP / Pemeriksaan Ralan">
                    <i class="fas fa-paperclip"></i> SOAP Ralan
                    <span class="badge badge-info ml-1">{{ $soapCount }}</span>
                </button>
            </div>
        </div>

        <!-- Data SOAP Terpilih (Pemeriksaan Ralan) -->
        <template x-if="selectedSoap">
            <div class="card mb-3 border-left-success" style="border-left-width: 4px;">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-2">
                    <h6 class="mb-0">
                        <i class="fas fa-clipboard-check"></i> Data SOAP Terpilih (Pemeriksaan Ralan)
                    </h6>
                    <small>
                        <i class="fas fa-calendar-day"></i> <span x-text="selectedSoap.tgl_perawatan"></span>
                        <i class="fas fa-clock ml-2"></i> <span x-text="selectedSoap.jam_rawat"></span>
                        <span class="ml-2 font-weight-bold" x-text="'| ' + (selectedSoap.nama_petugas || selectedSoap.nip)"></span>
                    </small>
                </div>
                <div class="card-body">
                    <!-- Tanda-tanda Vital -->
                    <div class="row mb-3 pb-2 border-bottom">
                        <div class="col-6 col-md-2 mb-2">
                            <small class="text-muted d-block">Tensi</small>
                            <strong x-text="(selectedSoap.tensi || '-') + ' mmHg'"></strong>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <small class="text-muted d-block">Nadi</small>
                            <strong x-text="(selectedSoap.nadi || '-') + ' x/m'"></strong>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <small class="text-muted d-block">Suhu</small>
                            <strong x-text="(selectedSoap.suhu_tubuh || '-') + ' °C'"></strong>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <small class="text-muted d-block">Respirasi</small>
                            <strong x-text="(selectedSoap.respirasi || '-') + ' x/m'"></strong>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <small class="text-muted d-block">SpO2</small>
                            <strong x-text="(selectedSoap.spo2 || '-') + ' %'"></strong>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <small class="text-muted d-block">Kesadaran / GCS</small>
                            <strong><span x-text="selectedSoap.kesadaran || '-'"></span> (<span x-text="selectedSoap.gcs || '-'"></span>)</strong>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <small class="text-muted d-block">TB / BB</small>
                            <strong><span x-text="selectedSoap.tinggi || '-'"></span> cm / <span x-text="selectedSoap.berat || '-'"></span> kg</strong>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <small class="text-muted d-block">Lingkar Perut</small>
                            <strong x-text="(selectedSoap.lingkar_perut || '-') + ' cm'"></strong>
                        </div>
                        <div class="col-12 col-md-4 mb-2" x-show="selectedSoap.alergi && selectedSoap.alergi !== '-' && selectedSoap.alergi !== 'Tidak Ada'">
                            <small class="text-muted d-block">Alergi</small>
                            <span class="badge badge-danger p-1"><i class="fas fa-exclamation-triangle"></i> <span x-text="selectedSoap.alergi"></span></span>
                        </div>
                    </div>

                    <!-- Catatan S-O-A-P -->
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block font-weight-bold">Subjektif (Keluhan)</small>
                            <div class="border rounded p-2 bg-light" style="white-space: pre-wrap;" x-text="selectedSoap.keluhan || '-'"></div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block font-weight-bold">Objektif (Pemeriksaan)</small>
                            <div class="border rounded p-2 bg-light" style="white-space: pre-wrap;" x-text="selectedSoap.pemeriksaan || '-'"></div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block font-weight-bold">Asesmen (Penilaian)</small>
                            <div class="border rounded p-2 bg-light" style="white-space: pre-wrap;" x-text="selectedSoap.penilaian || '-'"></div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block font-weight-bold">Plan (Instruksi / RTL)</small>
                            <div class="border rounded p-2 bg-light">
                                <div x-show="selectedSoap.instruksi"><strong>Instruksi:</strong> <span style="white-space: pre-wrap;" x-text="selectedSoap.instruksi"></span></div>
                                <div x-show="selectedSoap.rtl" class="mt-1"><strong>RTL:</strong> <span style="white-space: pre-wrap;" x-text="selectedSoap.rtl"></span></div>
                                <div x-show="selectedSoap.evaluasi" class="mt-1"><strong>Evaluasi:</strong> <span style="white-space: pre-wrap;" x-text="selectedSoap.evaluasi"></span></div>
                                <div x-show="!selectedSoap.instruksi && !selectedSoap.rtl && !selectedSoap.evaluasi">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- 1. Riwayat Kesehatan & Anamnesis -->
        <div class="card mb-3 border-left-info" style="border-left-width: 4px;">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-notes-medical"></i> I. Riwayat Kesehatan & Anamnesis</h6>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Keluhan Utama (Dokter)</small>
                        <div class="border rounded p-2 bg-light">{!! nl2br(e($dataMedis->keluhan_utama ?? ($data->keluhan_utama ?? '-'))) !!}</div>
                    </div>
                    @if(!empty($data->keluhan_utama) && !empty($dataMedis->keluhan_utama) && $data->keluhan_utama !== $dataMedis->keluhan_utama)
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Keluhan Utama (Keperawatan)</small>
                        <div class="border rounded p-2 bg-light">{!! nl2br(e($data->keluhan_utama)) !!}</div>
                    </div>
                    @endif
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Riwayat Penyakit Sekarang (RPS)</small>
                        <div class="border rounded p-2 bg-light">{!! nl2br(e($dataMedis->rps ?? '-')) !!}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Riwayat Penyakit Dahulu (RPD)</small>
                        <div class="border rounded p-2 bg-light">{!! nl2br(e($dataMedis->rpd ?? ($data->rpd ?? '-'))) !!}</div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Riwayat Penyakit Keluarga (RPK)</small>
                        <div class="border rounded p-2 bg-light">{!! nl2br(e($dataMedis->rpk ?? '-')) !!}</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Riwayat Pengobatan (RPO)</small>
                        <div class="border rounded p-2 bg-light">{!! nl2br(e($dataMedis->rpo ?? ($data->rpo ?? '-'))) !!}</div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Riwayat Alergi</small>
                        <div class="border rounded p-2 bg-light">
                            @php $alergiVal = $dataMedis->alergi ?? '-'; @endphp
                            @if(!empty($alergiVal) && $alergiVal !== '-' && strtolower($alergiVal) !== 'tidak ada')
                                <span class="badge badge-danger p-1"><i class="fas fa-exclamation-triangle"></i> {{ $alergiVal }}</span>
                            @else
                                {{ $alergiVal ?: '-' }}
                            @endif
                        </div>
                    </div>
                </div>
                @if(!empty($data->status_kehamilan) && $data->status_kehamilan !== '-')
                <div class="row mt-2 pt-2 border-top">
                    <div class="col-md-3 mb-2">
                        <small class="text-muted d-block">Status Kehamilan</small>
                        <strong>{{ $data->status_kehamilan ?? '-' }}</strong>
                    </div>
                    @if(($data->status_kehamilan ?? '') == 'Hamil')
                    <div class="col-md-2 mb-2">
                        <small class="text-muted d-block">Gravida</small>
                        <strong>{{ $data->gravida ?? '-' }}</strong>
                    </div>
                    <div class="col-md-2 mb-2">
                        <small class="text-muted d-block">Para</small>
                        <strong>{{ $data->para ?? '-' }}</strong>
                    </div>
                    <div class="col-md-2 mb-2">
                        <small class="text-muted d-block">Abortus</small>
                        <strong>{{ $data->abortus ?? '-' }}</strong>
                    </div>
                    <div class="col-md-3 mb-2">
                        <small class="text-muted d-block">HPHT</small>
                        <strong>{{ $data->hpht ?? '-' }}</strong>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>

        <!-- 2. Pemeriksaan Fisik & Tanda Vital -->
        <div class="card mb-3 border-left-primary" style="border-left-width: 4px;">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-stethoscope"></i> II. Pemeriksaan Fisik & Tanda Vital</h6>
            </div>
            <div class="card-body">
                @if(!empty($dataMedis))
                <!-- TTV & Status Generalis Dokter -->
                <div class="mb-3">
                    <h6 class="font-weight-bold text-secondary mb-2"><i class="fas fa-heartbeat"></i> Tanda-Tanda Vital & Status Generalis (Dokter)</h6>
                    <div class="row">
                        <div class="col-6 col-md-2 mb-2">
                            <small class="text-muted d-block">Keadaan Umum</small>
                            <strong>{{ $dataMedis->keadaan ?? '-' }}</strong>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <small class="text-muted d-block">Kesadaran</small>
                            <strong>{{ $dataMedis->kesadaran ?? '-' }}</strong>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <small class="text-muted d-block">GCS (E,V,M)</small>
                            <strong>{{ $dataMedis->gcs ?? '-' }}</strong>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <small class="text-muted d-block">TD</small>
                            <strong>{{ $dataMedis->td ?? '-' }} mmHg</strong>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <small class="text-muted d-block">Nadi</small>
                            <strong>{{ $dataMedis->nadi ?? '-' }} x/m</strong>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <small class="text-muted d-block">RR</small>
                            <strong>{{ $dataMedis->rr ?? '-' }} x/m</strong>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <small class="text-muted d-block">Suhu</small>
                            <strong>{{ $dataMedis->suhu ?? '-' }} °C</strong>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <small class="text-muted d-block">SpO2</small>
                            <strong>{{ $dataMedis->spo ?? '-' }} %</strong>
                        </div>
                        <div class="col-6 col-md-2 mb-2">
                            <small class="text-muted d-block">TB / BB</small>
                            <strong>{{ $dataMedis->tb ?? '-' }} cm / {{ $dataMedis->bb ?? '-' }} kg</strong>
                        </div>
                    </div>
                </div>

                <!-- 8 Organ Sistemik (Dokter) -->
                <div class="mb-3 pt-2 border-top">
                    <h6 class="font-weight-bold text-secondary mb-2"><i class="fas fa-child"></i> Pemeriksaan Sistemik Organ (Dokter)</h6>
                    <div class="row">
                        @php
                            $organList = [
                                'Kepala' => $dataMedis->kepala ?? null,
                                'Mata' => $dataMedis->mata ?? null,
                                'Gigi & Mulut' => $dataMedis->gigi ?? null,
                                'Leher' => $dataMedis->leher ?? null,
                                'Thoraks' => $dataMedis->thoraks ?? null,
                                'Abdomen' => $dataMedis->abdomen ?? null,
                                'Genital' => $dataMedis->genital ?? null,
                                'Ekstremitas' => $dataMedis->ekstremitas ?? null,
                            ];
                        @endphp
                        @foreach($organList as $organName => $organVal)
                        <div class="col-6 col-md-3 mb-2">
                            <small class="text-muted d-block">{{ $organName }}</small>
                            @if(empty($organVal) || $organVal === '-')
                                <span class="text-muted">-</span>
                            @elseif($organVal === 'Normal')
                                <span class="badge badge-success px-2 py-1"><i class="fas fa-check"></i> Normal</span>
                            @elseif($organVal === 'Abnormal')
                                <span class="badge badge-danger px-2 py-1"><i class="fas fa-exclamation-circle"></i> Abnormal</span>
                            @elseif($organVal === 'Tidak Diperiksa')
                                <span class="badge badge-secondary px-2 py-1">Tidak Diperiksa</span>
                            @else
                                <span class="badge badge-light border px-2 py-1">{{ $organVal }}</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Keterangan Fisik & Status Lokalis (Dokter) -->
                @if(!empty($dataMedis->ket_fisik) || !empty($dataMedis->ket_lokalis))
                <div class="row pt-2 border-top mb-3">
                    @if(!empty($dataMedis->ket_fisik))
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block font-weight-bold">Keterangan Pemeriksaan Fisik</small>
                        <div class="border rounded p-2 bg-light">{!! nl2br(e($dataMedis->ket_fisik)) !!}</div>
                    </div>
                    @endif
                    @if(!empty($dataMedis->ket_lokalis))
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block font-weight-bold">Status Lokalis</small>
                        <div class="border rounded p-2 bg-light">{!! nl2br(e($dataMedis->ket_lokalis)) !!}</div>
                    </div>
                    @endif
                </div>
                @endif
                @endif

                <!-- Fisik & Eliminasi Perawat -->
                @if(!empty($data))
                <div class="pt-2 {{ !empty($dataMedis) ? 'border-top' : '' }}">
                    <h6 class="font-weight-bold text-secondary mb-2"><i class="fas fa-user-nurse"></i> Pemeriksaan Fisik & Eliminasi (Keperawatan)</h6>
                    <div class="row mb-2">
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Tekanan</small>
                            <strong>{{ $data->tekanan ?? '-' }}</strong>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Pupil</small>
                            <strong>{{ $data->pupil ?? '-' }}</strong>
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Neurosensorik</small>
                            <strong>{{ $data->neurosensorik ?? '-' }}</strong>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-3 mb-2">
                            <small class="text-muted d-block">Integumen</small>
                            <strong>{{ $data->integumen ?? '-' }}</strong>
                        </div>
                        <div class="col-md-3 mb-2">
                            <small class="text-muted d-block">Turgor</small>
                            <strong>{{ $data->turgor ?? '-' }}</strong>
                        </div>
                        <div class="col-md-3 mb-2">
                            <small class="text-muted d-block">Edema</small>
                            <strong>{{ $data->edema ?? '-' }}</strong>
                        </div>
                        <div class="col-md-3 mb-2">
                            <small class="text-muted d-block">Mukosa</small>
                            <strong>{{ $data->mukosa ?? '-' }}</strong>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Perdarahan</small>
                            <strong>{{ $data->perdarahan ?? '-' }}</strong>
                            @if(($data->perdarahan ?? '') == 'Ada')
                            <span class="ml-2">({{ $data->jumlah_perdarahan ?? '0' }} cc, Warna: {{ $data->warna_perdarahan ?? '-' }})</span>
                            @endif
                        </div>
                        <div class="col-md-4 mb-2">
                            <small class="text-muted d-block">Intoksikasi</small>
                            <strong>{{ $data->intoksikasi ?? '-' }}</strong>
                        </div>
                    </div>
                    <div class="row border-top pt-2">
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block"><i class="fas fa-toilet"></i> BAB</small>
                            <strong>{{ $data->bab ?? '-' }} x/hari</strong>
                            @if($data->xbab || $data->kbab || $data->wbab)
                            <div><small>Ket: {{ $data->xbab ?? '-' }}, Konsistensi: {{ $data->kbab ?? '-' }}, Warna: {{ $data->wbab ?? '-' }}</small></div>
                            @endif
                        </div>
                        <div class="col-md-6 mb-2">
                            <small class="text-muted d-block"><i class="fas fa-restroom"></i> BAK</small>
                            <strong>{{ $data->bak ?? '-' }} x/hari</strong>
                            @if($data->xbak || $data->wbak || $data->lbak)
                            <div><small>Ket: {{ $data->xbak ?? '-' }}, Warna: {{ $data->wbak ?? '-' }}, Lain: {{ $data->lbak ?? '-' }}</small></div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if(!empty($data))
        <!-- 3. Pengkajian Nyeri -->
        <div class="card mb-3 border-left-warning" style="border-left-width: 4px;">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> III. Pengkajian Nyeri</h6>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Status Nyeri</small>
                        <strong>{{ $data->nyeri ?? '-' }}</strong>
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Skala Nyeri</small>
                        <span class="badge badge-{{ ($data->skala_nyeri ?? 0) > 4 ? 'danger' : 'warning' }} p-2">
                            Skala {{ $data->skala_nyeri ?? '0' }} / 10
                        </span>
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Lokasi Nyeri</small>
                        <strong>{{ $data->lokasi ?? '-' }}</strong> (Menyebar: {{ $data->menyebar ?? '-' }})
                    </div>
                </div>
                @if(($data->nyeri ?? '') != 'Tidak Ada Nyeri')
                <div class="row mb-2">
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Penyebab (Provokes)</small>
                        <strong>{{ $data->provokes ?? '-' }}</strong> @if($data->ket_provokes) ({{ $data->ket_provokes }}) @endif
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Rasa Nyeri (Quality)</small>
                        <strong>{{ $data->quality ?? '-' }}</strong> @if($data->ket_quality) ({{ $data->ket_quality }}) @endif
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Durasi</small>
                        <strong>{{ $data->durasi ?? '-' }}</strong>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Nyeri Hilang Bila</small>
                        <strong>{{ $data->nyeri_hilang ?? '-' }}</strong> @if($data->ket_nyeri) ({{ $data->ket_nyeri }}) @endif
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Dilaporkan ke Dokter</small>
                        <strong>{{ $data->pada_dokter ?? '-' }}</strong> @if($data->ket_dokter) ({{ $data->ket_dokter }}) @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- 4. Pengkajian Risiko Jatuh & Fungsional -->
        <div class="card mb-3 border-left-danger" style="border-left-width: 4px;">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0"><i class="fas fa-user-shield"></i> III.B Risiko Jatuh & Fungsional</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Kemampuan Mandiri</small>
                        <strong>{{ $data->kemampuan ?? '-' }}</strong>
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Aktivitas</small>
                        <strong>{{ $data->aktifitas ?? '-' }}</strong>
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Alat Bantu</small>
                        <strong>{{ $data->alat_bantu ?? '-' }}</strong> @if($data->ket_bantu) ({{ $data->ket_bantu }}) @endif
                    </div>
                </div>
                <div class="border-top pt-2">
                    <small class="text-muted d-block mb-1"><strong>Screening Risiko Jatuh:</strong></small>
                    <div class="row">
                        <div class="col-md-4 mb-1">Cara berjalan tak seimbang (a): <strong>{{ $data->berjalan_a ?? '-' }}</strong></div>
                        <div class="col-md-4 mb-1">Memegang penopang (b): <strong>{{ $data->berjalan_b ?? '-' }}</strong></div>
                        <div class="col-md-4 mb-1">Penopang/lainnya (c): <strong>{{ $data->berjalan_c ?? '-' }}</strong></div>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted d-block">Hasil Screening</small>
                        <span class="badge badge-{{ str_contains($data->hasil ?? '', 'Resiko tinggi') ? 'danger' : (str_contains($data->hasil ?? '', 'Resiko rendah') ? 'warning' : 'success') }} p-2">
                            {{ $data->hasil ?? '-' }}
                        </span>
                        @if(($data->lapor ?? '') == 'Ya')
                        <span class="ml-2 text-muted">(Dilaporkan: {{ $data->ket_lapor ?? '-' }})</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Psikososial, Budaya & Edukasi -->
        <div class="card mb-3 border-left-secondary" style="border-left-width: 4px;">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="fas fa-brain"></i> III.C Psikososial, Budaya & Edukasi</h6>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Status Psikologis</small>
                        <strong>{{ $data->psikologis ?? '-' }}</strong>
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Gangguan Jiwa</small>
                        <strong>{{ $data->jiwa ?? '-' }}</strong>
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Perilaku</small>
                        <strong>{{ $data->perilaku ?? '-' }}</strong> @if($data->sebutkan) ({{ $data->sebutkan }}) @endif
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Hubungan Pasien</small>
                        <strong>{{ $data->hubungan ?? '-' }}</strong>
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Tinggal Dengan</small>
                        <strong>{{ $data->tinggal_dengan ?? '-' }}</strong> @if($data->ket_tinggal) ({{ $data->ket_tinggal }}) @endif
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block">Budaya / Nilai Khusus</small>
                        <strong>{{ $data->budaya ?? '-' }}</strong> @if($data->ket_budaya) ({{ $data->ket_budaya }}) @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Pendidikan Penanggung Jawab</small>
                        <strong>{{ $data->pendidikan_pj ?? '-' }}</strong> @if($data->ket_pendidikan_pj) ({{ $data->ket_pendidikan_pj }}) @endif
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Edukasi Kepada</small>
                        <strong>{{ $data->edukasi ?? '-' }}</strong> @if($data->ket_edukasi) ({{ $data->ket_edukasi }}) @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- IV. Pemeriksaan Penunjang (Dokter) -->
        @if(!empty($dataMedis) && (!empty($dataMedis->ekg) || !empty($dataMedis->rad) || !empty($dataMedis->lab)))
        <div class="card mb-3 border-left-secondary" style="border-left-width: 4px;">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="fas fa-x-ray"></i> IV. Pemeriksaan Penunjang (EKG, Radiologi, Lab)</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @if(!empty($dataMedis->ekg))
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block font-weight-bold"><i class="fas fa-heartbeat text-danger"></i> EKG</small>
                        <div class="border rounded p-2 bg-light">{!! nl2br(e($dataMedis->ekg)) !!}</div>
                    </div>
                    @endif
                    @if(!empty($dataMedis->rad))
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block font-weight-bold"><i class="fas fa-radiation text-warning"></i> Radiologi</small>
                        <div class="border rounded p-2 bg-light">{!! nl2br(e($dataMedis->rad)) !!}</div>
                    </div>
                    @endif
                    @if(!empty($dataMedis->lab))
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block font-weight-bold"><i class="fas fa-vial text-info"></i> Laboratorium</small>
                        <div class="border rounded p-2 bg-light">{!! nl2br(e($dataMedis->lab)) !!}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- V. Diagnosis, Tatalaksana & Rencana Asuhan -->
        @if((!empty($dataMedis) && (!empty($dataMedis->diagnosis) || !empty($dataMedis->tata))) || (!empty($data) && !empty($data->rencana)))
        <div class="card mb-2 border-left-success" style="border-left-width: 4px;">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-file-medical-alt"></i> V. Diagnosis, Tatalaksana & Rencana Asuhan</h6>
            </div>
            <div class="card-body">
                @if(!empty($dataMedis->diagnosis))
                <div class="mb-3">
                    <small class="text-muted d-block font-weight-bold"><i class="fas fa-diagnoses text-primary"></i> Diagnosis / Asesmen Medis (Dokter)</small>
                    <div class="border rounded p-2 bg-light">{!! nl2br(e($dataMedis->diagnosis)) !!}</div>
                </div>
                @endif
                @if(!empty($dataMedis->tata))
                <div class="mb-3">
                    <small class="text-muted d-block font-weight-bold"><i class="fas fa-prescription-bottle-alt text-warning"></i> Tatalaksana / Instruksi Medis (Dokter)</small>
                    <div class="border rounded p-2 bg-light">{!! nl2br(e($dataMedis->tata)) !!}</div>
                </div>
                @endif
                @if(!empty($data->rencana))
                <div class="mb-2">
                    <small class="text-muted d-block font-weight-bold"><i class="fas fa-user-nurse text-success"></i> Rencana Asuhan Keperawatan</small>
                    <div class="border rounded p-2 bg-light">{!! nl2br(e($data->rencana)) !!}</div>
                </div>
                @endif
            </div>
        </div>
        @endif
    </x-adminlte-card>

    <!-- Modal Selector SOAP Pemeriksaan Ralan -->
    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="{{ $modalId }}Label">
                        <i class="fas fa-clipboard-list"></i> Pilih Data SOAP Pemeriksaan Ralan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    @if(isset($listPemeriksaan) && count($listPemeriksaan) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 text-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Waktu</th>
                                    <th>Petugas / Dokter</th>
                                    <th>TTV (Td/Nd/Sh/Rr)</th>
                                    <th>Keluhan</th>
                                    <th class="text-center" style="width: 90px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($listPemeriksaan as $idx => $soap)
                                <tr>
                                    <td>
                                        <strong>{{ date('d-m-Y', strtotime($soap->tgl_perawatan)) }}</strong><br>
                                        <small class="text-muted">{{ $soap->jam_rawat }}</small>
                                    </td>
                                    <td>{{ $soap->nama_petugas ?? $soap->nip ?? '-' }}</td>
                                    <td>
                                        <small>
                                             TD: {{ $soap->tensi ?: '-' }} | N: {{ $soap->nadi ?: '-' }}<br>
                                             S: {{ $soap->suhu_tubuh ?: '-' }} | RR: {{ $soap->respirasi ?: '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 250px;" title="{{ $soap->keluhan }}">
                                            {{ $soap->keluhan ?: '-' }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button type="button"
                                            class="btn btn-xs btn-primary"
                                            @click="selectSoap(listSoap[{{ $loop->index }}])">
                                            <i class="fas fa-check"></i> Pilih
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                        Belum ada riwayat data SOAP pemeriksaan ralan pada nomor rawat ini.
                    </div>
                    @endif
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>