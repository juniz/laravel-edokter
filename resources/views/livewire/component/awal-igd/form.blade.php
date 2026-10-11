<div>
    <form wire:submit.prevent='simpan'>
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <div>
                <span class="badge badge-secondary p-2">No. Rawat: {{ $no_rawat ?: '-' }}</span>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-info" wire:click="openModalSoap" wire:loading.attr="disabled">
                    <i class="fas fa-paperclip mr-1"></i> Salin SOAP Pemeriksaan Ralan
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-6 col-md-6">
                <x-ui.input-datetime label="Tanggal" id="tanggal_igd" model='tanggal' />
            </div>
            <div class="col-6 col-md-6">
                <div class="form-group">
                    <label for="informasi">Anamnesis</label>
                    <div class="row">
                        <div class="col-6 col-md-6">
                            <x-ui.select id="anamnesis" model='anamnesis'>
                                <option value="Autoanamnesis">Autoanamnesis</option>
                                <option value="Alloanamnesis">Alloanamnesis</option>
                            </x-ui.select>
                        </div>
                        <div class="col-6 col-md-6">
                            <x-ui.input id="hubungan" type="text" model='hubungan' />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($dataPerawat))
        @php
            $namaPerawat = data_get($dataPerawat, 'nama_petugas') ?: data_get($dataPerawat, 'nip');
            $tglPerawat = data_get($dataPerawat, 'tanggal');
            $keluhanPerawat = data_get($dataPerawat, 'keluhan_utama');
            $rpdPerawat = data_get($dataPerawat, 'rpd');
            $rpoPerawat = data_get($dataPerawat, 'rpo');
            $nyeriPerawat = data_get($dataPerawat, 'nyeri');
            $skalaNyeriPerawat = data_get($dataPerawat, 'skala_nyeri');
            $hasilJatuhPerawat = data_get($dataPerawat, 'hasil');
            $kehamilanPerawat = data_get($dataPerawat, 'status_kehamilan');
            $rencanaPerawat = data_get($dataPerawat, 'rencana');
        @endphp
        <div class="card card-outline card-success mb-3 shadow-sm">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="card-title text-sm mb-0">
                    <i class="fas fa-user-nurse text-success mr-1"></i> <strong>Asesmen Awal Keperawatan IGD</strong>
                    <span class="badge badge-light border ml-1">{{ $namaPerawat ?: '-' }}</span>
                    <small class="text-muted ml-2">({{ !empty($tglPerawat) ? date('d/m/Y H:i', strtotime($tglPerawat)) : '-' }})</small>
                </h6>
                <div class="card-tools">
                    <button type="button" class="btn btn-xs btn-outline-success mr-2" wire:click="salinDariPerawat" title="Salin Keluhan, RPD & RPO ke Form Dokter">
                        <i class="fas fa-copy mr-1"></i> Salin ke Form Dokter
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body py-2 px-3 text-sm">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted d-block">Keluhan Utama (Perawat):</small>
                        <div class="font-weight-bold text-dark">{{ $keluhanPerawat ?: '-' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <small class="text-muted d-block">Riwayat Penyakit Dahulu:</small>
                        <div>{{ $rpdPerawat ?: '-' }}</div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <small class="text-muted d-block">Riwayat Pengobatan:</small>
                        <div>{{ $rpoPerawat ?: '-' }}</div>
                    </div>
                </div>
                <div class="row pt-2 border-top">
                    <div class="col-6 col-md-3 mb-1">
                        <small class="text-muted d-block">Pengkajian Nyeri:</small>
                        <span>{{ $nyeriPerawat ?: 'Tidak' }} (Skala: {{ $skalaNyeriPerawat ?: '0' }})</span>
                    </div>
                    <div class="col-6 col-md-3 mb-1">
                        <small class="text-muted d-block">Risiko Jatuh:</small>
                        <span>{{ $hasilJatuhPerawat ?: '-' }}</span>
                    </div>
                    <div class="col-6 col-md-3 mb-1">
                        <small class="text-muted d-block">Status Kehamilan:</small>
                        <span>{{ $kehamilanPerawat ?: '-' }}</span>
                    </div>
                    <div class="col-6 col-md-3 mb-1">
                        <small class="text-muted d-block">Rencana Keperawatan:</small>
                        <span>{{ $rencanaPerawat ?: '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mt-2 mb-2">
            <h6 class="text-bold mb-0">I. RIWAYAT KESEHATAN</h6>
            <button type="button" class="btn btn-xs btn-outline-info" wire:click="openModalSoap" wire:loading.attr="disabled">
                <i class="fas fa-paperclip"></i> Ambil SOAP
            </button>
        </div>
        <div class="row">
            <div class="col-6 col-md-6">
                <x-ui.textarea label="Keluhan Utama" id="keluhan_utama" model='keluhan_utama' />
            </div>
            <div class="col-6 col-md-6">
                <x-ui.textarea label="Riwayat Penyakit Sekarang" id="rps" model='rps' />
            </div>
        </div>
        <div class="row">
            <div class="col-6 col-md-6">
                <x-ui.textarea label="Riwayat Penyakit Keluarga" id="rpk" model='rpk' />
            </div>
            <div class="col-6 col-md-6">
                <x-ui.textarea label="Riwayat Penyakit Dahulu" id="rpd" model='rpd' />
            </div>
        </div>
        <div class="row">
            <div class="col-6 col-md-6">
                <x-ui.textarea label="Riwayat Pengobatan" id="rpo" model='rpo' />
            </div>
            <div class="col-6 col-md-6">
                <x-ui.input label="Riwayat Alergi" id="alergi" type="text" model='alergi' />
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
            <h6 class="text-bold mb-0">II. PEMERIKSAAN FISIK</h6>
            <button type="button" class="btn btn-xs btn-outline-info" wire:click="openModalSoap" wire:loading.attr="disabled">
                <i class="fas fa-paperclip"></i> Ambil TTV / Fisik
            </button>
        </div>
        <div class="row">
            <div class="col-3 col-md-3">
                <x-ui.select label="Keadaa Umum" id="keadaan_umum" model='keadaan_umum' >
                    <option value="Sehat">Sehat</option>
                    <option value="Sakit Ringan">Sakit Ringan</option>
                    <option value="Sakit Sedang">Sakit Sedang</option>
                    <option value="Sakit Berat">Sakit Berat</option>
                </x-ui.select>
            </div>
            <div class="col-3 col-md-3">
                <x-ui.select label="Kesadaran" id="kesadaran" model='kesadaran' >
                    <option value="Compos Mentis">Compos Mentis</option>
                    <option value="Apatis">Apatis</option>
                    <option value="Somnolen">Somnolen</option>
                    <option value="Sopor">Sopor</option>
                    <option value="Coma">Koma</option>
                </x-ui.select>
            </div>
            <div class="col-2 col-md-2">
                <x-ui.input label="GCS (E,V,M)" id="gcs" type="text" model='gcs' />
            </div>
            <div class="col-2 col-md-2">
                <x-ui.input label="TB (Cm)" id="tb" type="text" model='tb' />
            </div>
            <div class="col-2 col-md-2">
                <x-ui.input label="BB (Kg)" id="bb" type="text" model='bb' />
            </div>
        </div>
        <div class="row">
            <div class="col-3 col-md-3">
                <x-ui.input label="TD (mmHg)" id="td" type="text" model='td' />
            </div>
            <div class="col-3 col-md-3">
                <x-ui.input label="Nadi (x/menit)" id="nadi" type="text" model='nadi' />
            </div>
            <div class="col-2 col-md-2">
                <x-ui.input label="RR (x/menit)" id="rr" type="text" model='rr' />
            </div>
            <div class="col-2 col-md-2">
                <x-ui.input label="Suhu (C)" id="suhu" type="text" model='suhu' />
            </div>
            <div class="col-2 col-md-2">
                <x-ui.input label="SpO2" id="spo2" type="text" model='spo2' />
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <x-ui.select label="Kepala" id="kepala" model='kepala'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
            <div class="col-md-3">
                <x-ui.select label="Thoraks" id="thoraks" model='thoraks'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
            <div class="col-md-3">
                <x-ui.select label="Mata" id="mata" model='mata'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
            <div class="col-md-3">
                <x-ui.select label="Abdomen" id="abdomen" model='abdomen'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <x-ui.select label="Gigi & Mulut" id="gigi" model='gigi'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
            <div class="col-md-3">
                <x-ui.select label="Genital & Anus" id="genital" model='genital'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
            <div class="col-md-3">
                <x-ui.select label="Leher" id="leher" model='leher'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
            <div class="col-md-3">
                <x-ui.select label="Ekstremitas" id="ekstremitas" model='ekstremitas'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <x-ui.textarea id="ket_fisik" model='ket_fisik' />
            </div>
        </div>
        <h6 class="mt-3 text-bold">III. STATUS LOKALIS</h6>
        <div class="row">
            <div class="col-md-12">
                <img class="img-fluid" src="{{ asset('assets/medis/medis-ralan.png') }}" alt="awal-medis">
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-12">
                <x-ui.textarea label="Keterangan" id="ket_lokalis" model='ket_lokalis' />
            </div>
        </div>
        <h6 class="mt-3 text-bold">IV. PEMERIKSAAN PENUNJANG</h6>
        <div class="row">
            <div class="col-md-4">
                <x-ui.textarea label="EKG" id="ekg" model='ekg' />
            </div>
            <div class="col-md-4">
                <x-ui.textarea label="Radiologi" id="radiologi" model='radiologi' />
            </div>
            <div class="col-md-4">
                <x-ui.textarea label="Laborat" id="laborat" model='laborat' />
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-12">
                <x-ui.textarea label="V. DIAGNOSIS/ASESMEN" id="diagnosis" model='diagnosis' />
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-12">
                <x-ui.textarea label="VI. TATALAKSANA" id="tatalaksana" model='tatalaksana' rows='5' />
            </div>
        </div>
        

        <div class="row">
            <div class="col-6 col-md-6 mt-3">
                <button type="reset" wire:click='confirmHapus' class="btn btn-danger btn-block">Hapus</button>
            </div>
            <div class="col-6 col-md-6 mt-3">
                <button type="submit" class="btn btn-primary btn-block">Simpan</button>
            </div>
        </div>
    </form>

    <!-- Tabel Riwayat Penilaian Medis IGD Pasien -->
    <div class="card card-outline card-info mt-4 shadow-sm">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <h6 class="card-title text-sm mb-0">
                <i class="fas fa-history text-info mr-1"></i> <strong>Riwayat Penilaian Awal Medis IGD Pasien</strong>
            </h6>
            <span class="badge badge-info">{{ count($dataRiwayat) }} Catatan</span>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped mb-0 text-sm">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th style="width: 170px;">Waktu & No. Rawat</th>
                        <th style="width: 180px;">Dokter Pemeriksa</th>
                        <th>Keluhan & TTV</th>
                        <th>Diagnosis & Tatalaksana</th>
                        <th class="text-center" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataRiwayat as $index => $row)
                    @php
                        $isCurrentVisit = (data_get($row, 'no_rawat') === $no_rawat);
                        $tglRow = data_get($row, 'tanggal');
                    @endphp
                    <tr class="{{ $isCurrentVisit ? 'table-info' : '' }}">
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ !empty($tglRow) ? date('d/m/Y H:i', strtotime($tglRow)) : '-' }}</strong>
                            <small class="text-muted d-block">{{ data_get($row, 'no_rawat') }}</small>
                            @if($isCurrentVisit)
                                <span class="badge badge-success">Kunjungan Ini</span>
                            @else
                                <span class="badge badge-secondary">Kunjungan Lalu</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ data_get($row, 'nm_dokter') ?? data_get($row, 'kd_dokter') ?? '-' }}</strong>
                            <div class="text-xs text-muted">{{ data_get($row, 'anamnesis') ?? '-' }}</div>
                        </td>
                        <td>
                            <div class="font-weight-bold mb-1">{{ data_get($row, 'keluhan_utama') ?: '-' }}</div>
                            <div class="text-xs text-muted">
                                TD: {{ data_get($row, 'td') ?: '-' }} | N: {{ data_get($row, 'nadi') ?: '-' }} | S: {{ data_get($row, 'suhu') ?: '-' }} | RR: {{ data_get($row, 'rr') ?: '-' }} | SpO2: {{ data_get($row, 'spo') ?: '-' }}
                            </div>
                        </td>
                        <td>
                            <div><strong>Diagnosis:</strong> {{ data_get($row, 'diagnosis') ?: '-' }}</div>
                            <div class="text-xs text-muted"><strong>Tatalaksana:</strong> {{ \Illuminate\Support\Str::limit(data_get($row, 'tata') ?: '-', 80) }}</div>
                        </td>
                        <td class="text-center align-middle">
                            @if($isCurrentVisit)
                                <button type="button" class="btn btn-xs btn-warning mb-1" wire:click="editRiwayat('{{ data_get($row, 'no_rawat') }}')" title="Muat ke Form">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </button>
                            @else
                                <button type="button" class="btn btn-xs btn-outline-primary" wire:click="salinDariRiwayat('{{ data_get($row, 'no_rawat') }}')" title="Salin Data Riwayat ke Form">
                                    <i class="fas fa-copy mr-1"></i> Salin
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            <i class="fas fa-info-circle mr-1"></i> Belum ada riwayat penilaian medis IGD untuk pasien ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Selector SOAP Pemeriksaan Ralan -->
    <div wire:ignore.self class="modal fade" id="modal-soap-picker-igd" tabindex="-1" role="dialog" aria-labelledby="modalSoapPickerIgdLabel" aria-hidden="true" style="z-index: 1060; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalSoapPickerIgdLabel">
                        <i class="fas fa-clipboard-list mr-1"></i> Pilih Data SOAP Pemeriksaan Ralan
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    @if(!empty($listSoap) && count($listSoap) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 text-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Waktu</th>
                                    <th>Petugas / Dokter</th>
                                    <th>TTV (TD/N/S/RR/SpO2)</th>
                                    <th>Keluhan & Fisik</th>
                                    <th class="text-center" style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($listSoap as $soap)
                                @php
                                    $tglPerawatan = data_get($soap, 'tgl_perawatan');
                                    $jamRawat = data_get($soap, 'jam_rawat');
                                    $namaPetugas = data_get($soap, 'nama_petugas') ?? data_get($soap, 'nip') ?? '-';
                                    $tensi = data_get($soap, 'tensi') ?: '-';
                                    $nadi = data_get($soap, 'nadi') ?: '-';
                                    $suhuTubuh = data_get($soap, 'suhu_tubuh') ?: '-';
                                    $respirasi = data_get($soap, 'respirasi') ?: '-';
                                    $spo2Val = data_get($soap, 'spo2') ?: '-';
                                    $gcsVal = data_get($soap, 'gcs') ?: '-';
                                    $keluhanVal = data_get($soap, 'keluhan');
                                    $pemeriksaanVal = data_get($soap, 'pemeriksaan');
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $tglPerawatan ? date('d-m-Y', strtotime($tglPerawatan)) : '-' }}</strong><br>
                                        <small class="text-muted">{{ $jamRawat }}</small>
                                    </td>
                                    <td>{{ $namaPetugas }}</td>
                                    <td>
                                        <small>
                                            TD: {{ $tensi }} | N: {{ $nadi }}<br>
                                            S: {{ $suhuTubuh }} | RR: {{ $respirasi }}<br>
                                            SpO2: {{ $spo2Val }} | GCS: {{ $gcsVal }}
                                        </small>
                                    </td>
                                    <td>
                                        <small class="d-block text-truncate" style="max-width: 250px;" title="{{ $keluhanVal }}">
                                            <strong>K:</strong> {{ $keluhanVal ?: '-' }}
                                        </small>
                                        <small class="d-block text-truncate" style="max-width: 250px;" title="{{ $pemeriksaanVal }}">
                                            <strong>P:</strong> {{ $pemeriksaanVal ?: '-' }}
                                        </small>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button type="button"
                                            wire:click="pilihSoap('{{ $tglPerawatan }}', '{{ $jamRawat }}')"
                                            class="btn btn-sm btn-primary">
                                            <i class="fas fa-check"></i> Salin
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
</div>

@push('js')
<script>
    $(".btn-awal-igd").on('click', function(){
        var id = $(this).attr('id');
        @this.set('no_rawat', id);
        $("#modal-awal-medis-igd").modal('show');
    });

    window.addEventListener('open-modal-soap-igd', function () {
        $('#modal-soap-picker-igd').modal('show');
    });

    window.addEventListener('close-modal-soap-igd', function () {
        $('#modal-soap-picker-igd').modal('hide');
    });
</script>
@endpush
