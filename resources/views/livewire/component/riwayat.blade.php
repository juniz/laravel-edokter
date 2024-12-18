<div>
    <div class="d-flex flex-row">
        <div wire:ignore class="ml-auto">
            <select name="dokter" id="riwayat-dokter" class="form-control">
                <option value="">Pilih Dokter</option>
                @foreach($dokter as $dok)
                <option value="{{$dok->kd_dokter}}">{{$dok->nm_dokter}}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div wire:loading wire:target='init'>
        <div class="d-flex flex-row">
            <div class="mx-auto">
                Loading ...
            </div>
        </div>
    </div>

    @if(count($data)>0)
    <div class="timeline">
            @foreach($data as $row)
            @php
            $pemeriksaan = $this->getPemeriksaanRalan($row->no_rawat,$row->status_lanjut);
            $diagnosa = $this->getDiagnosa($row->no_rawat);
            $tono = $this->getTono($row->no_rawat);
            $laboratorium = $this->getPemeriksaanLab($row->no_rawat);
            $resume = $this->getResume($row->no_rawat);
            $radiologi = $this->getRadiologi($row->no_rawat);
            $gambarRadiologi = $this->getFotoRadiologi($row->no_rawat);
            $tgl = date_create($row->tgl_registrasi ?? '0000-00-00');
            $date = date_format($tgl,"d M Y");
            @endphp

            <div class="time-label">
                <span @if($loop->first) class="bg-green" @else class="bg-yellow" @endif >{{ $date ?? '' }}</span>
            </div>
            <div>
                <i class="fas fa-stethoscope bg-blue"></i>
                <div class="timeline-item">
                    <h3 class="timeline-header d-flex justify-content-between"><b>{{$row->no_rawat}}</b>
                        <b>{{$row->nm_dokter}}</b>
                    </h3>
                    <div class="timeline-body">
                        @if(count($this->getPemeriksaanRalan($row->no_rawat,$row->status_lanjut))>0)
                        <x-adminlte-card theme="dark" title="Pemeriksaan" collapsible maximizable>
                            <div class="table-responsive">
                                @foreach($pemeriksaan as $pemeriksaan)
                                @php
                                $tglPemeriksaan = date_create($pemeriksaan->tgl_perawatan ?? '0000-00-00');
                                $datePemeriksaan = date_format($tglPemeriksaan,"d M Y");
                                @endphp
                                <div class="d-flex justify-content-between">
                                    <h5>{{$datePemeriksaan}}</h5>
                                    <h5>{{$pemeriksaan->jam_rawat}}</h5>
                                </div>
                                <table class="table table-bordered" width="100%">
                                    <tr style="font-weight: bold">
                                        <td>Status</td>
                                        <td>Suhu(C)</td>
                                        <td>Tensi(mmHg)</td>
                                        <td>Nadi(/menit)</td>
                                        <td>RR(/menit)</td>
                                        <td>Tinggi(Cm)</td>
                                        <td>Berat(Kg)</td>
                                        <td>SPO2</td>
                                        <td>GCS(E,V,M)</td>
                                        <td>Kesadaran</td>
                                    </tr>
                                    <tr>
                                        <td>{{$row->status_lanjut}}</td>
                                        <td>{{ $pemeriksaan->suhu_tubuh ?? '-' }}</td>
                                        <td>{{ $pemeriksaan->tensi ?? '-' }}</td>
                                        <td>{{ $pemeriksaan->nadi ?? '-' }}</td>
                                        <td>{{ $pemeriksaan->respirasi ?? '-' }}</td>
                                        <td>{{ $pemeriksaan->tinggi ?? '-' }}</td>
                                        <td>{{ $pemeriksaan->berat ?? '-' }}</td>
                                        <td>{{ $pemeriksaan->spo2 ?? '-' }}</td>
                                        <td>{{ $pemeriksaan->gcs ?? '-' }}</td>
                                        <td>{{ $pemeriksaan->kesadaran ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><b>Alergi</b></td>
                                        <td colspan="9">{{ $pemeriksaan->alergi ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><b>Subjek</b></td>
                                        <td colspan="9">
                                            <pre>{{ $pemeriksaan->keluhan ?? '' }}</pre>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><b>Objek</b></td>
                                        <td colspan="9">
                                            <pre>{{ $pemeriksaan->pemeriksaan ?? '' }}</pre>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><b>Asesmen</b></td>
                                        <td colspan="9">{{ $pemeriksaan->penilaian ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><b>Plan</b></td>
                                        <td colspan="9">{{ $pemeriksaan->rtl ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><b>Instruksi</b></td>
                                        <td colspan="9">{{ $pemeriksaan->instruksi ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><b>Evaluasi</b></td>
                                        <td colspan="9">{{ $pemeriksaan->evaluasi ?? '' }}</td>
                                    </tr>
                                    @if($this->getTono($row->no_rawat))
                                    <tr>
                                        <td colspan="2"><b>Pemeriksaan Tonometri</b></td>
                                        <td colspan="9">
                                            <ul>
                                                <li>Suhu : {{$tono->suhu}}</li>
                                                <li>Tensi : {{$tono->tensi}}</li>
                                                <li>RR : {{$tono->respirasi}}</li>
                                                <li>Nadi : {{$tono->nadi}}</li>
                                                <li>Kanan : {{$tono->tonokanan}}</li>
                                                <li>Kiri : {{$tono->tonokiri}}</li>
                                            </ul>
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td colspan="2"><b>Resume Medis</b></td>
                                        <td colspan="9">
                                            @if(isset($resume))
                                            <ul>
                                                <li>
                                                    <div class="d-flex flex-row">
                                                        <div>Keluhan Utama :</div>
                                                        <div>
                                                            {{$resume->keluhan_utama}}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex flex-row">
                                                        <div>Jalannya Penyakit :</div>
                                                        <div>
                                                            {{$resume->jalannya_penyakit ?? ''}}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex flex-row">
                                                        <div>Pemeriksaan Penunjang :</div>
                                                        <div>
                                                            {{$resume->pemeriksaan_penunjang ?? ''}}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex flex-row">
                                                        <div>Hasil Laborat :</div>
                                                        <div>
                                                            {{$resume->hasil_laborat ?? ''}}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex flex-row">
                                                        <div>Diagnosa Utama :</div>
                                                        <div>
                                                            {{$resume->diagnosa_utama ?? ''}} - {{$resume->kd_diagnosa_utama ?? ''}}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex flex-row">
                                                        <div>Diagnosa Sekunder 1:</div>
                                                        <div>
                                                            {{$resume->diagnosa_sekunder ?? ''}} - {{$resume->kd_diagnosa_sekunder ?? ''}}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex flex-row">
                                                        <div>Diagnosa Sekunder 2:</div>
                                                        <div>
                                                            {{$resume->diagnosa_sekunder1 ?? ''}} - {{$resume->kd_diagnosa_sekunder1 ?? ''}}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex flex-row">
                                                        <div>Diagnosa Sekunder 3:</div>
                                                        <div>
                                                            {{$resume->diagnosa_sekunder2 ?? ''}} - {{$resume->kd_diagnosa_sekunder2 ?? ''}}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex flex-row">
                                                        <div>Diagnosa Sekunder 4:</div>
                                                        <div>
                                                            {{$resume->diagnosa_sekunder3 ?? ''}} - {{$resume->kd_diagnosa_sekunder3 ?? ''}}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex flex-row">
                                                        <div>Diagnosa Sekunder 5:</div>
                                                        <div>
                                                            {{$resume->diagnosa_sekunder4 ?? ''}} - {{$resume->kd_diagnosa_sekunder4 ?? ''}}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex flex-row">
                                                        <div>Prosedur Utama :</div>
                                                        <div>
                                                            {{$resume->prosedur_utama ?? ''}} - {{$resume->kd_prosedur_utama ?? ''}}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex flex-row">
                                                        <div>Prosedur Sekunder 1:</div>
                                                        <div>
                                                            {{$resume->prosedur_sekunder ?? ''}} - {{$resume->kd_prosedur_sekunder ?? ''}}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex flex-row">
                                                        <div>Prosedur Sekunder 2:</div>
                                                        <div>
                                                            {{$resume->prosedur_sekunder1 ?? ''}} - {{$resume->kd_prosedur_sekunder1 ?? ''}}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex flex-row">
                                                        <div>Prosedur Sekunder 3:</div>
                                                        <div>
                                                            {{$resume->prosedur_sekunder2 ?? ''}} - {{$resume->kd_prosedur_sekunder2 ?? ''}}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex flex-row">
                                                        <div>Prosedur Sekunder 4:</div>
                                                        <div>
                                                            {{$resume->prosedur_sekunder3 ?? ''}} - {{$resume->kd_prosedur_sekunder3 ?? ''}}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex flex-row">
                                                        <div>Obat Pulang :</div>
                                                        <div>
                                                            <pre>{{$resume->obat_pulang}}</pre>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                            @else
                                            -
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><b>Diagnosa</b></td>
                                        <td colspan="9">
                                            <ol>
                                                @forelse($this->getDiagnosa($row->no_rawat) as $diag)
                                                <li>{{$diag->nm_penyakit}} ({{$diag->kd_penyakit}})</li>
                                                @empty
                                                <li>-</li>
                                                @endforelse
                                            </ol>
                                        </td>
                                    </tr>
                                </table>
                                @endforeach
                            </div>
                        </x-adminlte-card>
                        @endif

                        @if(count($radiologi)>0)
                        <x-adminlte-card theme="dark" title="Radiologi" collapsible="collapsed" maximizable>
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
                            @foreach($radiologi as $radiologi)
                            <x-adminlte-card title="{{$radiologi->jam}}" theme="dark"
                                footer-class="bg-dark border-top rounded border-light">
                                <pre>{{$radiologi->hasil}}</pre>
                                {{-- <x-slot name="footerSlot">
                                    <x-adminlte-button class="d-flex ml-auto" theme="light" label="Foto"
                                        icon="fas fa-sign-in" />
                                </x-slot> --}}
                            </x-adminlte-card>
                            @endforeach
                        </x-adminlte-card>
                        @endif

                        @if(count($laboratorium)>0)
                        <x-adminlte-card theme="dark" title="Laboratorium" collapsible="collapsed" maximizable>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Pemeriksaan</th>
                                            <th>Tgl Periksa</th>
                                            <th>Jam</th>
                                            <th>Hasil</th>
                                            <th>Satuan</th>
                                            <th>Nilai Rujukan</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($laboratorium as $lab)
                                        <tr
                                            class="@if($lab->keterangan == 'T' || $lab->keterangan == 'H') bg-danger @endif">
                                            <td>{{$loop->iteration}}</td>
                                            <td>{{$lab->Pemeriksaan}}</td>
                                            <td>{{$lab->tgl_periksa}}</td>
                                            <td>{{$lab->jam}}</td>
                                            <td>{{$lab->nilai}}</td>
                                            <td>{{$lab->satuan}}</td>
                                            <td>{{$lab->nilai_rujukan}}</td>
                                            <td>{{$lab->keterangan}}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </x-adminlte-card>
                        @endif

                        {{-- <x-adminlte-card theme="dark" title="Laporan Operasi" collapsible="collapsed" maximizable>
                            <livewire:component.riwayat-operasi :noRawat='$row->no_rawat' />
                        </x-adminlte-card> --}}

                        {{--
                        <x-ralan.penilaian-awal-keperawatan :no-rawat="$row->no_rawat" />
                        <x-ralan.penilaian-awal-keperawatan-gigi-mulut :no-rawat="$row->no_rawat" />
                        <x-ralan.penilaian-awal-keperawatan-kebidanan :no-rawat="$row->no_rawat" />
                        <x-ralan.penilaian-awal-keperawatan-bayi :no-rawat="$row->no_rawat" /> --}}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
    <div class="d-flex flex-row">
        <div class="mx-auto">
            <h3>Data Tidak Ditemukan</h3>
        </div>
    </div>
    @endif
</div>

@push('js')
<script>
    $("#riwayat-dokter").select2({
        placeholder: "Pilih Dokter",
        theme: 'bootstrap4',
        width: '300px',
        allowClear: true
    });

    $('#riwayat-dokter').on('change', function () {
        @this.set('selectDokter', $(this).val());
    });

    $('#modalRiwayatPemeriksaanRalan').on('show.bs.modal', function(){
        Livewire.emit('loadRiwayatPasien');
    })
</script>
@endpush
