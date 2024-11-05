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
                    <td>{{ $item->no_permintaan }}</td>
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
</div>
