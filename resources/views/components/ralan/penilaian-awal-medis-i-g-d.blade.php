@php
    $modalId = 'modalPilihSoap-' . md5($data->no_rawat ?? ($noRawat ?? uniqid()));
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
    @if(!empty($data))
    <x-adminlte-card theme="info" title="Penilaian Awal Keperawatan IGD" icon="fas fa-ambulance" theme-mode="outline" collapsible class="mb-4">
        @php
        $tglPenilaian = date_create($data->tanggal ?? '0000-00-00');
        $datePenilaian = date_format($tglPenilaian, "d M Y H:i");
        @endphp

        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
            <div class="row flex-grow-1">
                <div class="col-md-4">
                    <small class="text-muted d-block">Tanggal Penilaian</small>
                    <strong><i class="fas fa-calendar"></i> {{ $datePenilaian }}</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Informasi</small>
                    <strong><i class="fas fa-info-circle"></i> {{ $data->informasi ?? '-' }}</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Petugas / Perawat</small>
                    <strong><i class="fas fa-user-nurse"></i> {{ $data->nama_petugas ?? $data->nip ?? '-' }}</strong>
                </div>
            </div>
            <div>
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

        <!-- 1. Informasi Umum & Anamnesis -->
        <div class="card mb-3 border-left-info" style="border-left-width: 4px;">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-notes-medical"></i> Riwayat Kesehatan & Anamnesis</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted d-block">Keluhan Utama</small>
                    <div class="border rounded p-2 bg-light">{!! nl2br(e($data->keluhan_utama ?? '-')) !!}</div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Riwayat Penyakit Dahulu (RPD)</small>
                        <div class="border rounded p-2 bg-light">{!! nl2br(e($data->rpd ?? '-')) !!}</div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Riwayat Pengobatan (RPO)</small>
                        <div class="border rounded p-2 bg-light">{!! nl2br(e($data->rpo ?? '-')) !!}</div>
                    </div>
                </div>
                <div class="row mt-2">
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
            </div>
        </div>

        <!-- 2. Pemeriksaan Fisik & Eliminasi -->
        <div class="card mb-3 border-left-primary" style="border-left-width: 4px;">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-stethoscope"></i> Pemeriksaan Fisik & Eliminasi</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
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
                <div class="row mb-3">
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
                <div class="row mb-3">
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
        </div>

        <!-- 3. Pengkajian Nyeri -->
        <div class="card mb-3 border-left-warning" style="border-left-width: 4px;">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Pengkajian Nyeri</h6>
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
                <h6 class="mb-0"><i class="fas fa-user-shield"></i> Risiko Jatuh & Fungsional</h6>
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
                <h6 class="mb-0"><i class="fas fa-brain"></i> Psikososial, Budaya & Edukasi</h6>
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

        <!-- 6. Rencana Keperawatan -->
        @if($data->rencana)
        <div class="card mb-2 border-left-success" style="border-left-width: 4px;">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-tasks"></i> Rencana Keperawatan</h6>
            </div>
            <div class="card-body">
                <div class="border rounded p-2 bg-light">{!! nl2br(e($data->rencana)) !!}</div>
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