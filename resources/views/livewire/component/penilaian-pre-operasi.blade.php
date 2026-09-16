<div>
    <div class="alert alert-info border-info d-flex justify-content-between align-items-center mb-3 py-2 px-3 shadow-sm rounded">
        <div>
            <i class="fas fa-magic mr-2"></i>
            <strong>Impor Data Pasien:</strong> Salin data SOAP, TTV, Lab, dan Radiologi tanpa perlu ketik manual.
        </div>
        <button type="button" wire:click="autoFillSemua" class="btn btn-sm btn-info text-white font-weight-bold">
            <i class="fas fa-sync-alt mr-1"></i> Ambil Data Pasien Terbaru
        </button>
    </div>

    <form wire:submit.prevent="{{ $modeEdit ? 'update' : 'simpan' }}">
        <div class="form-group">
            <label for="tanggal_assessment"><i class="fas fa-clock mr-1 text-primary"></i> Tanggal & Jam Assessment</label>
            <input type="datetime-local" id="tanggal_assessment" class="form-control @error('tanggal') is-invalid @enderror" wire:model.defer="tanggal">
            @error('tanggal') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="d-flex align-items-center">
                    <label for="ringkasan_klinik" class="mb-0 mr-2">Ringkasan Klinik</label>
                    @if(!empty($listSoap) && count($listSoap) > 0)
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-xs btn-outline-info dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-download mr-1"></i> Pilih Keluhan SOAP
                            </button>
                            <div class="dropdown-menu shadow dropdown-menu-right" style="max-height: 250px; overflow-y: auto;">
                                @foreach($listSoap as $idx => $soap)
                                    @if(!empty($soap->keluhan))
                                        <a class="dropdown-item py-2 border-bottom" href="#" wire:click.prevent="importKeluhan({{ $idx }})">
                                            <small class="text-muted d-block">{{ \Carbon\Carbon::parse($soap->tgl_perawatan)->format('d/m/Y') }} {{ $soap->jam_rawat }} ({{ strtoupper($soap->sumber) }})</small>
                                            <span>{{ \Illuminate\Support\Str::limit($soap->keluhan, 80) }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <small class="text-muted">{{ strlen($ringkasan_klinik ?? '') }}/500</small>
            </div>
            <textarea wire:model.defer="ringkasan_klinik" id="ringkasan_klinik" class="form-control @error('ringkasan_klinik') is-invalid @enderror" rows="3" maxlength="500" placeholder="Masukkan ringkasan klinik..."></textarea>
            @error('ringkasan_klinik') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="d-flex align-items-center">
                    <label for="pemeriksaan_fisik" class="mb-0 mr-2">Pemeriksaan Fisik</label>
                    @if(!empty($listSoap) && count($listSoap) > 0)
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-xs btn-outline-info dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-download mr-1"></i> Pilih Fisik & TTV
                            </button>
                            <div class="dropdown-menu shadow dropdown-menu-right" style="max-height: 250px; overflow-y: auto;">
                                @foreach($listSoap as $idx => $soap)
                                    <a class="dropdown-item py-2 border-bottom" href="#" wire:click.prevent="importPemeriksaanFisik({{ $idx }})">
                                        <small class="text-muted d-block">{{ \Carbon\Carbon::parse($soap->tgl_perawatan)->format('d/m/Y') }} {{ $soap->jam_rawat }} ({{ strtoupper($soap->sumber) }})</small>
                                        <span>{{ \Illuminate\Support\Str::limit(($soap->pemeriksaan ? $soap->pemeriksaan . ' | ' : '') . 'TD:' . $soap->tensi . ' Nadi:' . $soap->nadi . ' Suhu:' . $soap->suhu_tubuh, 80) }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <small class="text-muted">{{ strlen($pemeriksaan_fisik ?? '') }}/500</small>
            </div>
            <textarea wire:model.defer="pemeriksaan_fisik" id="pemeriksaan_fisik" class="form-control @error('pemeriksaan_fisik') is-invalid @enderror" rows="3" maxlength="500" placeholder="Masukkan pemeriksaan fisik..."></textarea>
            @error('pemeriksaan_fisik') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="d-flex align-items-center">
                    <label for="pemeriksaan_diagnostik" class="mb-0 mr-2">Pemeriksaan Diagnostik</label>
                    <div class="dropdown d-inline-block mr-1">
                        <button class="btn btn-xs btn-outline-info dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-flask mr-1"></i> Pilih Hasil Lab
                        </button>
                        <div class="dropdown-menu shadow" style="max-height: 250px; overflow-y: auto;">
                            <a class="dropdown-item py-2 font-weight-bold border-bottom" href="#" wire:click.prevent="importDiagnostikLab">
                                <i class="fas fa-check-double mr-1 text-info"></i> Semua Hasil Lab
                            </a>
                            @if(!empty($listLab) && count($listLab) > 0)
                                @foreach($listLab as $idx => $lab)
                                    <a class="dropdown-item py-2 border-bottom" href="#" wire:click.prevent="importDiagnostikLab({{ $idx }})">
                                        @if(!empty($lab->tgl_periksa))
                                            <small class="text-muted d-block">{{ \Carbon\Carbon::parse($lab->tgl_periksa)->format('d/m/Y') }} {{ $lab->jam ?? '' }}</small>
                                        @endif
                                        <span><strong>{{ $lab->Pemeriksaan }}:</strong> {{ $lab->nilai }} {{ $lab->satuan }}</span>
                                    </a>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-xs btn-outline-info dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-x-ray mr-1"></i> Pilih Hasil Radiologi
                        </button>
                        <div class="dropdown-menu shadow" style="max-height: 250px; overflow-y: auto;">
                            <a class="dropdown-item py-2 font-weight-bold border-bottom" href="#" wire:click.prevent="importDiagnostikRadiologi">
                                <i class="fas fa-check-double mr-1 text-info"></i> Semua Hasil Radiologi
                            </a>
                            @if(!empty($listRadiologi) && count($listRadiologi) > 0)
                                @foreach($listRadiologi as $idx => $rad)
                                    @if(!empty($rad->hasil))
                                        <a class="dropdown-item py-2 border-bottom" href="#" wire:click.prevent="importDiagnostikRadiologi({{ $idx }})">
                                            @if(!empty($rad->tgl_periksa))
                                                <small class="text-muted d-block">{{ \Carbon\Carbon::parse($rad->tgl_periksa)->format('d/m/Y') }} {{ $rad->jam ?? '' }}</small>
                                            @endif
                                            <span>{{ \Illuminate\Support\Str::limit(trim($rad->hasil), 80) }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
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
            <button type="submit" class="btn {{ $modeEdit ? 'btn-warning' : 'btn-primary' }}" wire:loading.attr="disabled" wire:target="{{ $modeEdit ? 'update' : 'simpan' }}">
                <span wire:loading wire:target="{{ $modeEdit ? 'update' : 'simpan' }}" class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span>
                <i wire:loading.remove wire:target="{{ $modeEdit ? 'update' : 'simpan' }}" class="{{ $modeEdit ? 'fas fa-edit' : 'fas fa-save' }} mr-1"></i>
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
                                <div class="btn-group btn-group-sm">
                                    @if($item->kd_dokter == session('username'))
                                        <button type="button" wire:click="edit('{{ $item->tanggal }}')" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" wire:click="confirmHapus('{{ $item->tanggal }}')" class="btn btn-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        <span class="badge badge-secondary mr-1 d-inline-flex align-items-center" title="Hanya dokter pembuat yang dapat mengubah/menghapus">Read-only</span>
                                    @endif
                                    <a href="{{ route('penilaian-pre-operasi.download-pdf', ['no_rawat' => $item->no_rawat, 'tanggal' => $item->tanggal]) }}" target="_blank" class="btn btn-info" title="Cetak / Download PDF">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </div>
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
