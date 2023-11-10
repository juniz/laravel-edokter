<div>
    <form wire:submit.prevent='simpan'>
        <div class="row">
            <div class="col-sm-6">
                <div wire:ignore.self class="form-group">
                    <label for="tanggal_operasi">Tanggal Operasi</label>
                    <div class="input-group date" id="tanggal_operasi" data-target-input="nearest">
                        <input wire:model.defer='tanggal_operasi' type="text" class="form-control datetimepicker-input" data-target="#tanggal_operasi"/>
                        <div class="input-group-append" data-target="#tanggal_operasi" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                    @error('tanggal_operasi') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-sm-6">
                <div wire:ignore.self class="form-group">
                    <label for="tanggal_selesai">Tanggal Selesai</label>
                    <div class="input-group date" id="tanggal_selesai" data-target-input="nearest">
                        <input wire:model.defer='tanggal_selesai' type="text" class="form-control datetimepicker-input" data-target="#tanggal_selesai"/>
                        <div class="input-group-append" data-target="#tanggal_selesai" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                    @error('tanggal_selesai') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="diagnosa_pra_bedah">Diagnosa Pra Bedah</label>
                    <textarea wire:model.defer='diagnosa_pra_bedah' rows="3" id="diagnosa_pra_bedah" class="form-control @error('diagnosa_pra_bedah') is-invalid @enderror" type="text" name="diagnosa_pra_bedah"></textarea>
                    @error('diagnosa_pra_bedah') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="diagnosa_pasca_bedah">Diagnosa Pasca Bedah</label>
                    <textarea wire:model.defer='diagnosa_pasca_bedah' id="diagnosa_pasca_bedah" class="form-control @error('diagnosa_pasca_bedah') is-invalid @enderror" type="text" name="diagnosa_pasca_bedah" rows="3"></textarea>
                    @error('diagnosa_pasca_bedah') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                  <label for="uraian_bedah">Tindakan Bedah</label>
                  <textarea wire:model.defer='uraian_bedah' class="form-control @error('uraian_bedah') is-invalid @enderror" name="uraian_bedah" id="uraian_bedah" rows="3"></textarea>
                    @error('uraian_bedah') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-4 col-md-4">
                <button type="button" class="btn btn-block btn-warning" data-toggle="modal" data-target="#modal-template-operasi">
                    Template Laporan Operasi
                </button>
            </div>
            <div class="col-4 col-sm-4">
                <button wire:click='resetInput' type="reset" class="btn btn-block btn-danger">Reset</button>
            </div>
            <div class="col-4 col-sm-4">
                <button type="submit" class="btn btn-block @if($modeEdit) btn-info @else btn-primary @endif">@if($modeEdit) Ubah @else Simpan @endif</button>
            </div>
        </div>
    </form>
    <h5 class="mt-3">Data Laporan Operasi</h5>
    <div wire:init='getData' class="table-responsive">
        <table class="table">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Tgl Operasi</th>
                    <th scope="col">Tgl Selesai</th>
                    <th scope="col">Diagnosa Pra Bedah</th>
                    <th scope="col">Diagnosa Pasca Bedah</th>
                    <th scope="col">Tindakan Bedah</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->tanggal_operasi }}</td>
                    <td>{{ $item->tanggal_selesai }}</td>
                    <td>{{ $item->diagnosa_pra_bedah }}</td>
                    <td>{{ $item->diagnosa_pasca_bedah }}</td>
                    <td>{{ $item->uraian_bedah }}</td>
                    <td>
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <button wire:click='edit("{{$item->tanggal_operasi}}","{{$item->tanggal_selesai}}")' type="button" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                            <button wire:click='confirmHapus("{{$item->tanggal_operasi}}","{{$item->tanggal_selesai}}")' type="button" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">Data masih kosong</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('js')
    <script>
        $(function () {
            $('#tanggal_operasi').datetimepicker({
                format: 'YYYY-MM-DD HH:mm:ss',
                // defaultDate: moment(),
                allowInputToggle: true,
            });
            $('#tanggal_selesai').datetimepicker({
                format: 'YYYY-MM-DD HH:mm:ss',
                // defaultDate: moment(),
                allowInputToggle: true,
            });

            $('#tanggal_operasi').on('change.datetimepicker', function(e) {
                @this.set('tanggal_operasi', e.date.format('YYYY-MM-DD HH:mm:ss'));
            });

            $('#tanggal_selesai').on('change.datetimepicker', function(e) {
                @this.set('tanggal_selesai', e.date.format('YYYY-MM-DD HH:mm:ss'));
            });
        });
    </script>
@endpush