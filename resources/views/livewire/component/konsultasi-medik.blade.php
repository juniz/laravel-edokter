<div>
    <form wire:submit.prevent='simpan'>
        <div class="row">
            <div class="col-md-6">
                <x-ui.select id="kd_dokter_dikonsuli" label="Dokter yang Dikonsuli" model="kd_dokter_dikonsuli">
                    <option value="">Pilih Dokter</option>
                    @foreach($list_dokter as $dokter)
                        <option value="{{$dokter->kd_dokter}}">{{$dokter->nm_dokter}}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="col-md-6">
                <x-ui.select id='permintaan' label='Permintaan' model='jenis_permintaan'>
                    <option value=''>Pilih Permintaan</option>
                    @foreach($list_jenis_permintaan as $value)
                        <option value='{{$value}}'>{{$value}}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </div>
        <x-ui.input id="diagnosa_kerja" label="Diagnosa Kerja" model="diagnosa_kerja" />
        <x-ui.textarea id="uraian_konsultasi" label="Uraian Konsultasi" model="uraian_konsultasi" rows='5' />
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
    @if(count($riwayatRujukan) != 0)
    <h5 class="mt-4">Riwayat Rujukan Internal</h5>
    @foreach($riwayatRujukan as $data)
            <x-adminlte-card title="Konsul / Rujukan Internal" theme="dark" theme-mode="outline" class="mt-4">
                <table class="table table-bordered table-striped mb-4">
                    <tr>
                        <th>No. Rawat</th>
                        <th>{{$data->no_rawat}}</th>
                    </tr>
                    <tr>
                        <th>Tanggal</th>
                        <th>{{$data->tgl_registrasi}}</th>
                    </tr>
                    <tr>
                        <th>Dokter Perujuk</th>
                        <th>{{ $this->getPerujuk($data->no_rawat) }}</th>
                    </tr>
                    <tr>
                        <th>Poli Tujuan</th>
                        <th>{{$data->nm_poli}}</th>
                    </tr>
                    <tr>
                        <th>Dokter Tujuan</th>
                        <th>{{$data->nm_dokter}}</th>
                    </tr>
                    <tr>
                        <th>Konsul</th>
                        <th>{{$data->konsul}}</th>
                    </tr>
                    <tr>
                        <th>Pemeriksaan</th>
                        <th>{{$data->pemeriksaan}}</th>
                    </tr>
                    <tr>
                        <th>Diagnosa</th>
                        <th>{{$data->diagnosa}}</th>
                    </tr>
                    <tr>
                        <th>Saran</th>
                        <th>{{$data->saran}}</th>
                    </tr>
                </table>
                <x-adminlte-button class="d-flex ml-auto" id="rujukButtonHapus" theme="danger" label="Hapus" onclick="deleteRujukan()" />
            </x-adminlte-card>
        @endforeach
    @endif
    <h5 class="mt-3">Data Permintaan Konsultasi Medik</h5>
    <div wire:init='getDataListKonsultasi' class="table-responsive">
        <table class="table">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">No. Permintaan</th>
                    <th scope="col">Pasien</th>
                    <th scope="col">Dokter Dikonsuli</th>
                    <th scope="col">Permintaan</th>
                    <th scope="col">Diagnosa Kerja Konsul</th>
                    <th scope="col">Uraian Konsultasi</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($list_data_konsultasi as $item)
                <tr>
                    <td><button class="btn btn-secondary" wire:click='getJawaban("{{$item->no_permintaan}}")'>{{ $item->no_permintaan }}</button></td>
                    <td>{{ $item->nm_pasien }}</td>
                    <td>{{ $item->nm_dokter }}</td>
                    <td>{{ $item->jenis_permintaan }}</td>
                    <td>{{ $item->diagnosa_kerja }}</td>
                    <td>{{ $item->uraian_konsultasi }}</td>
                    <td>
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <button wire:click='edit("{{$item->no_permintaan}}")' type="button" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                            <button wire:click='confirmHapus("{{$item->no_permintaan}}")' type="button" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Data masih kosong</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-adminlte-modal wire:ignore.self id="modal-jawaban-konsul" title="Jawaban Konsultasi Medik" size="xl" v-centered scrollable>
        {{-- <livewire:component.skrining.tbc /> --}}
        <h5 class="text-bold">Diagnosa Kerja</h5>
        <p>{{ $jawaban_diagnosa_kerja ?? '-' }}</p>
        <h5 class="text-bold">Uraian Jawaban</h5>
        <p>{{ $jawaban_uraian_konsultasi ?? '-' }}</p>
    </x-adminlte-modal>
</div>

@push('js')
<script>
    Livewire.on('openJawabanKonsultasi', function(event){
        $('#modal-jawaban-konsul').modal('show');
    })
</script>
@endpush
