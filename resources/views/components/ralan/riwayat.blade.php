<div>
    <x-adminlte-modal id="modalRiwayatPemeriksaanRalan" title="Riwayat Pemeriksaan" size="xl" theme="info"
    icon="fas fa-book-medical" v-centered static-backdrop scrollable>
    
        <div class="timeline timeline-inverse">
            @foreach($data as $row)
                @php
                    $pemriksaanRalan = App\Http\Controllers\Ralan\PemeriksaanRalanController::getPemeriksaanRalan($row->no_rawat,$row->status_lanjut);
                    $diagnosa = App\Http\Controllers\Ralan\PemeriksaanRalanController::getDiagnosa($row->no_rawat);
                    $laboratorium = App\Http\Controllers\Ralan\PemeriksaanRalanController::getPemeriksaanLab($row->no_rawat);
                    $tgl = date_create($pemriksaanRalan->tgl_perawatan ?? '0000-00-00');
                    $date = date_format($tgl,"d M Y");
                @endphp
                @isset($pemriksaanRalan)
                    <div class="time-label">
                        <span class="bg-green">{{ $date ?? '' }}</span>
                    </div>
                    <div>
                        <i class="fas fa-stethoscope bg-blue"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="fas fa-clock"></i> {{ $pemriksaanRalan->jam_rawat ?? '' }}</span>
                            <h3 class="timeline-header"><b>Pemeriksaan</b></h3>
                            <div class="timeline-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%">
                                        <tr style="font-weight: bold">
                                            <td>Status</td>
                                            <td>Suhu(C)</td>
                                            <td>Tensi(mmHg)</td>
                                            <td>Nadi(/menit)</td>
                                            <td>RR(/menit)</td>
                                            <td>Tinggi(Cm)</td>
                                            <td>Berat(Kg)</td>
                                            <td>GCS(E,V,M)</td>
                                            <td>Alergi</td>
                                            <td>Kesadaran</td>
                                        </tr>
                                        <tr>
                                            <td>{{$row->status_lanjut}}</td>
                                            <td>{{ $pemriksaanRalan->suhu_tubuh ?? '-' }}</td>
                                            <td>{{ $pemriksaanRalan->tensi ?? '-' }}</td>
                                            <td>{{ $pemriksaanRalan->nadi ?? '-' }}</td>
                                            <td>{{ $pemriksaanRalan->respirasi ?? '-' }}</td>
                                            <td>{{ $pemriksaanRalan->tinggi ?? '-' }}</td>
                                            <td>{{ $pemriksaanRalan->berat ?? '-' }}</td>
                                            <td>{{ $pemriksaanRalan->gcs ?? '-' }}</td>
                                            <td>{{ $pemriksaanRalan->alergi ?? '-' }}</td>
                                            <td>{{ $pemriksaanRalan->kesadaran ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><b>Subjek</b></td>
                                            <td colspan="8"> : {{ $pemriksaanRalan->keluhan ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><b>Objek</b></td>
                                            <td colspan="8"> : {{ $pemriksaanRalan->pemeriksaan ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><b>Asesmen</b></td>
                                            <td colspan="8"> : {{ $pemriksaanRalan->penilaian ?? '' }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"><b>Plan</b></td>
                                            <td colspan="8"> : {{ $pemriksaanRalan->rtl ?? '' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @isset($diagnosa)
                        <div>
                            <!-- Before each timeline item corresponds to one icon on the left scale -->
                            <i class="fas fa-clipboard bg-blue"></i>
                            <!-- Timeline item -->
                                <div class="timeline-item">
                                <!-- Time -->
                                    <span class="time"><i class="fas fa-clock"></i> {{ $pemriksaanRalan->jam_rawat ?? '' }}</span>
                                    <!-- Header. Optional -->
                                    <h3 class="timeline-header"><b>Diagnosa</b></h3>
                                    <!-- Body -->
                                    <div class="timeline-body">
                                        <ul">
                                        @foreach($diagnosa as $diagnosa)
                                            <li>{{$diagnosa->nm_penyakit}} ({{$diagnosa->kd_penyakit}})</li>
                                        @endforeach
                                        </ul>
                                    </div>
                                </div>
                        </div>
                    @endisset
                    @if(count($laboratorium)>0)
                        <div>
                            <!-- Before each timeline item corresponds to one icon on the left scale -->
                            <i class="fas fa-flask bg-blue"></i>
                            <!-- Timeline item -->
                                <div class="timeline-item">
                                <!-- Time -->
                                    <span class="time"><i class="fas fa-clock"></i> {{ $laboratorium->first()->tgl_periksa }} {{ $laboratorium->first()->jam }}</span>
                                    <!-- Header. Optional -->
                                    <h3 class="timeline-header"><b>Laboratorium</b></h3>
                                    <!-- Body -->
                                    <div class="timeline-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama Pemeriksaan</th>
                                                    <th>Hasil</th>
                                                    <th>Satuan</th>
                                                    <th>Nilai Rujukan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($laboratorium as $lab)
                                                    <tr>
                                                        <td>{{$loop->iteration}}</td>
                                                        <td>{{$lab->Pemeriksaan}}</td>
                                                        <td>{{$lab->nilai}}</td>
                                                        <td>{{$lab->satuan}}</td>
                                                        <td>{{$lab->nilai_rujukan}}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                        </div>
                    @endif
                @endisset
            @endforeach
        </div>
        <x-slot name="footerSlot">
            <x-adminlte-button theme="danger" label="Tutup" data-dismiss="modal"/>
        </x-slot>
    </x-adminlte-modal>
</div>