<div>
    <form wire:submit.prevent='simpan'>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="diagnosa_pra_tindakan">Diagnosa Pra Tindakan</label>
                    <input wire:model.defer='diagnosa_pra_tindakan' type="text" id="diagnosa_pra_tindakan"
                        class="form-control @error('diagnosa_pra_tindakan') is-invalid @enderror"
                        name="diagnosa_pra_tindakan" maxlength="50">
                    @error('diagnosa_pra_tindakan') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="diagnosa_pasca_tindakan">Diagnosa Pasca Tindakan</label>
                    <input wire:model.defer='diagnosa_pasca_tindakan' type="text" id="diagnosa_pasca_tindakan"
                        class="form-control @error('diagnosa_pasca_tindakan') is-invalid @enderror"
                        name="diagnosa_pasca_tindakan" maxlength="50">
                    @error('diagnosa_pasca_tindakan') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="tindakan_medik">Tindakan Medik</label>
                    <textarea wire:model.defer='tindakan_medik' id="tindakan_medik"
                        class="form-control @error('tindakan_medik') is-invalid @enderror" name="tindakan_medik"
                        rows="3" maxlength="300"></textarea>
                    @error('tindakan_medik') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="uraian">Uraian</label>
                    <textarea wire:model.defer='uraian' id="uraian"
                        class="form-control @error('uraian') is-invalid @enderror" name="uraian" rows="5"
                        maxlength="3000"></textarea>
                    @error('uraian') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="hasil">Hasil</label>
                    <textarea wire:model.defer='hasil' id="hasil"
                        class="form-control @error('hasil') is-invalid @enderror" name="hasil" rows="4"
                        maxlength="1000"></textarea>
                    @error('hasil') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="kesimpulan">Kesimpulan</label>
                    <textarea wire:model.defer='kesimpulan' id="kesimpulan"
                        class="form-control @error('kesimpulan') is-invalid @enderror" name="kesimpulan" rows="3"
                        maxlength="500"></textarea>
                    @error('kesimpulan') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="row">
            {{-- <div class="col-4 col-md-4">
                <button wire:click='resetInput' type="button" class="btn btn-block btn-danger">Reset</button>
            </div> --}}
            <div class="col-4 col-sm-4">
                <button type="submit" class="btn btn-block @if($modeEdit) btn-info @else btn-primary @endif">
                    <i class="fas fa-save"></i> @if($modeEdit) Ubah @else Simpan @endif
                </button>
            </div>
        </div>
    </form>
    <h5 class="mt-3">Data Laporan Tindakan</h5>
    <div wire:init='getData' class="table-responsive">
        <table class="table">
            <thead class="thead-dark">
                <tr>
                    {{-- <th scope="col">#</th> --}}
                    <th scope="col">Tanggal</th>
                    <th scope="col">Diagnosa Pra</th>
                    <th scope="col">Diagnosa Pasca</th>
                    <th scope="col">Tindakan Medik</th>
                    <th scope="col">Uraian</th>
                    <th scope="col">Hasil</th>
                    <th scope="col">Kesimpulan</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $item)
                <tr>
                    {{-- <td>{{ $loop->iteration }}</td> --}}
                    <td>{{ $item->tanggal }}</td>
                    <td>{{ Str::limit($item->diagnosa_pra_tindakan, 20) }}</td>
                    <td>{{ Str::limit($item->diagnosa_pasca_tindakan, 20) }}</td>
                    <td>{{ Str::limit($item->tindakan_medik, 30) }}</td>
                    <td>{{ Str::limit($item->uraian, 30) }}</td>
                    <td>{{ Str::limit($item->hasil, 20) }}</td>
                    <td>{{ Str::limit($item->kesimpulan, 20) }}</td>
                    <td>
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <button wire:click='edit("{{ $item->tanggal }}")' type="button"
                                class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                            <button wire:click='confirmHapus("{{ $item->tanggal }}")' type="button"
                                class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">Data masih kosong</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
