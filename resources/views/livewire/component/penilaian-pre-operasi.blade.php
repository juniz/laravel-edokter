<div>
    <form wire:submit.prevent="simpan">
        <div class="form-group">
            <div class="d-flex justify-content-between align-items-center">
                <label for="ringkasan_klinik">Ringkasan Klinik</label>
                <small class="text-muted">{{ strlen($ringkasan_klinik ?? '') }}/500</small>
            </div>
            <textarea wire:model.defer="ringkasan_klinik" id="ringkasan_klinik" class="form-control @error('ringkasan_klinik') is-invalid @enderror" rows="3" maxlength="500" placeholder="Masukkan ringkasan klinik..."></textarea>
            @error('ringkasan_klinik') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <div class="d-flex justify-content-between align-items-center">
                <label for="pemeriksaan_fisik">Pemeriksaan Fisik</label>
                <small class="text-muted">{{ strlen($pemeriksaan_fisik ?? '') }}/500</small>
            </div>
            <textarea wire:model.defer="pemeriksaan_fisik" id="pemeriksaan_fisik" class="form-control @error('pemeriksaan_fisik') is-invalid @enderror" rows="3" maxlength="500" placeholder="Masukkan pemeriksaan fisik..."></textarea>
            @error('pemeriksaan_fisik') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <div class="d-flex justify-content-between align-items-center">
                <label for="pemeriksaan_diagnostik">Pemeriksaan Diagnostik</label>
                <small class="text-muted">{{ strlen($pemeriksaan_diagnostik ?? '') }}/500</small>
            </div>
            <textarea wire:model.defer="pemeriksaan_diagnostik" id="pemeriksaan_diagnostik" class="form-control @error('pemeriksaan_diagnostik') is-invalid @enderror" rows="3" maxlength="500" placeholder="Masukkan pemeriksaan diagnostik..."></textarea>
            @error('pemeriksaan_diagnostik') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <div class="d-flex justify-content-between align-items-center">
                <label for="diagnosa_pre_operasi">Diagnosa Pre Operasi</label>
                <small class="text-muted">{{ strlen($diagnosa_pre_operasi ?? '') }}/500</small>
            </div>
            <textarea wire:model.defer="diagnosa_pre_operasi" id="diagnosa_pre_operasi" class="form-control @error('diagnosa_pre_operasi') is-invalid @enderror" rows="3" maxlength="500" placeholder="Masukkan diagnosa pre operasi..."></textarea>
            @error('diagnosa_pre_operasi') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <div class="d-flex justify-content-between align-items-center">
                <label for="rencana_tindakan_bedah">Rencana Tindakan Bedah</label>
                <small class="text-muted">{{ strlen($rencana_tindakan_bedah ?? '') }}/500</small>
            </div>
            <textarea wire:model.defer="rencana_tindakan_bedah" id="rencana_tindakan_bedah" class="form-control @error('rencana_tindakan_bedah') is-invalid @enderror" rows="3" maxlength="500" placeholder="Masukkan rencana tindakan bedah..."></textarea>
            @error('rencana_tindakan_bedah') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <div class="d-flex justify-content-between align-items-center">
                <label for="hal_hal_yang_perludi_persiapkan">Hal-hal Yang Perlu Dipersiapkan</label>
                <small class="text-muted">{{ strlen($hal_hal_yang_perludi_persiapkan ?? '') }}/500</small>
            </div>
            <textarea wire:model.defer="hal_hal_yang_perludi_persiapkan" id="hal_hal_yang_perludi_persiapkan" class="form-control @error('hal_hal_yang_perludi_persiapkan') is-invalid @enderror" rows="3" maxlength="500" placeholder="Masukkan hal-hal yang perlu dipersiapkan..."></textarea>
            @error('hal_hal_yang_perludi_persiapkan') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <div class="d-flex justify-content-between align-items-center">
                <label for="terapi_pre_operasi">Terapi Pre Operasi</label>
                <small class="text-muted">{{ strlen($terapi_pre_operasi ?? '') }}/500</small>
            </div>
            <textarea wire:model.defer="terapi_pre_operasi" id="terapi_pre_operasi" class="form-control @error('terapi_pre_operasi') is-invalid @enderror" rows="3" maxlength="500" placeholder="Masukkan terapi pre operasi..."></textarea>
            @error('terapi_pre_operasi') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="d-flex justify-content-end mb-4">
            @if($modeEdit)
                <button type="button" wire:click="resetInput" class="btn btn-secondary mr-2">
                    <i class="fas fa-times mr-1"></i> Batal
                </button>
            @endif
            <button type="submit" class="btn {{ $modeEdit ? 'btn-warning' : 'btn-primary' }}" wire:loading.attr="disabled">
                <span wire:loading wire:target="simpan" class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                <i wire:loading.remove wire:target="simpan" class="{{ $modeEdit ? 'fas fa-edit' : 'fas fa-save' }} mr-1"></i>
                {{ $modeEdit ? 'Ubah Penilaian' : 'Simpan Penilaian' }}
            </button>
        </div>
    </form>

    <div class="card card-outline card-info mt-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-history mr-1"></i> Riwayat Penilaian Pre Operasi</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="width: 160px;">Tanggal</th>
                        <th style="width: 200px;">Dokter Operator</th>
                        <th>Ringkasan / Diagnosa / Rencana</th>
                        <th style="width: 120px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y H:i:s') }}</td>
                            <td>{{ $item->nm_dokter ?? $item->kd_dokter }}</td>
                            <td>
                                <div><strong>Ringkasan Klinik:</strong> {{ $item->ringkasan_klinik ?: '-' }}</div>
                                <div><strong>Pemeriksaan Fisik:</strong> {{ $item->pemeriksaan_fisik ?: '-' }}</div>
                                <div><strong>Pemeriksaan Diagnostik:</strong> {{ $item->pemeriksaan_diagnostik ?: '-' }}</div>
                                <div><strong>Diagnosa Pre Operasi:</strong> {{ $item->diagnosa_pre_operasi ?: '-' }}</div>
                                <div><strong>Rencana Tindakan Bedah:</strong> {{ $item->rencana_tindakan_bedah ?: '-' }}</div>
                                <div><strong>Hal-hal Yang Perlu Dipersiapkan:</strong> {{ $item->hal_hal_yang_perludi_persiapkan ?: '-' }}</div>
                                <div><strong>Terapi Pre Operasi:</strong> {{ $item->terapi_pre_operasi ?: '-' }}</div>
                            </td>
                            <td class="text-center">
                                @if($item->kd_dokter == session('username'))
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" wire:click="edit('{{ $item->tanggal }}')" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" wire:click="confirmHapus('{{ $item->tanggal }}')" class="btn btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                @else
                                    <span class="badge badge-secondary" title="Hanya dokter pembuat yang dapat mengubah/menghapus">Read-only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Belum ada riwayat penilaian pre operasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
