<div>
    <div wire:ignore.self id="change-umur" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="my-modal-title">Update Umur Pasien</h5>
                    <button class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent='simpan'>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tahun">Tahun (Th)</label>
                                    <input type="number" 
                                           class="form-control @error('tahun') is-invalid @enderror" 
                                           id="tahun" 
                                           wire:model='tahun' 
                                           min="0" 
                                           required>
                                    @error('tahun')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="bulan">Bulan (Bl)</label>
                                    <input type="number" 
                                           class="form-control @error('bulan') is-invalid @enderror" 
                                           id="bulan" 
                                           wire:model='bulan' 
                                           min="0" 
                                           max="11" 
                                           required>
                                    @error('bulan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hari">Hari (Hr)</label>
                                    <input type="number" 
                                           class="form-control @error('hari') is-invalid @enderror" 
                                           id="hari" 
                                           wire:model='hari' 
                                           min="0" 
                                           max="30" 
                                           required>
                                    @error('hari')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        @if($tgl_lahir)
                        <div class="alert alert-success">
                            <small><i class="fas fa-check-circle"></i> <strong>Umur yang benar telah dihitung otomatis</strong> dari tanggal lahir: <strong>{{ \Carbon\Carbon::parse($tgl_lahir)->isoFormat('D MMMM Y') }}</strong></small>
                        </div>
                        @else
                        <div class="alert alert-warning">
                            <small><i class="fas fa-exclamation-triangle"></i> <strong>Peringatan:</strong> Tanggal lahir tidak tersedia. Silakan isi umur secara manual.</small>
                        </div>
                        @endif
                        <div class="alert alert-info">
                            <small><i class="fas fa-info-circle"></i> Format umur saat ini: <strong>{{ $tahun ?? 0 }} Th {{ $bulan ?? 0 }} Bl {{ $hari ?? 0 }} Hr</strong></small>
                            <br>
                            <small>Anda dapat mengubah nilai di atas jika diperlukan, kemudian klik Simpan.</small>
                        </div>
                        <div class="d-flex flex-row">
                            <div class="ml-auto">
                                <button class="btn btn-primary" type="submit">Simpan</button>
                                <button class="btn btn-secondary" data-dismiss="modal" type="button">Batal</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:load', function () {
        @if(config('livewire.inject_assets', true))
        Livewire.on('setRmUmur', function (noRm, umur, tglLahir) {
            // Panggil method setUmur di backend yang akan menghitung umur otomatis dari tanggal lahir
            @this.call('setUmur', noRm, umur, tglLahir);
            setTimeout(function() {
                $('#change-umur').modal('show');
            }, 100);
        });
        @endif
    });
</script>

