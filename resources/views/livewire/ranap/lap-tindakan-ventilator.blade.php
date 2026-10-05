<div>
    {{-- ===== FORM INPUT ===== --}}
    <form wire:submit.prevent="simpan">

        {{-- Row 1: Tanggal Pemasangan & Pelepasan --}}
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="tanggalpemasangan">
                        <i class="fas fa-calendar-plus mr-1 text-info"></i>
                        Tanggal Pemasangan <span class="text-danger">*</span>
                    </label>
                    <input wire:model.lazy="tanggalpemasangan"
                           type="datetime-local"
                           id="tanggalpemasangan"
                           class="form-control @error('tanggalpemasangan') is-invalid @enderror">
                    @error('tanggalpemasangan')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="tanggalpelepasan">
                        <i class="fas fa-calendar-minus mr-1 text-warning"></i>
                        Tanggal Pelepasan <span class="text-danger">*</span>
                    </label>
                    <input wire:model.lazy="tanggalpelepasan"
                           type="datetime-local"
                           id="tanggalpelepasan"
                           class="form-control @error('tanggalpelepasan') is-invalid @enderror">
                    @error('tanggalpelepasan')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Row 2: Durasi (auto) & Dokter --}}
        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="durasi">
                        <i class="fas fa-clock mr-1 text-secondary"></i>
                        Durasi (jam)
                    </label>
                    <div class="input-group">
                        <input wire:model="durasi"
                               type="number"
                               id="durasi"
                               class="form-control bg-light"
                               placeholder="Otomatis"
                               min="0"
                               max="999"
                               readonly>
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fas fa-hourglass-half text-secondary"></i>
                            </span>
                        </div>
                    </div>
                    <small class="text-muted">Dihitung otomatis dari selisih tanggal</small>
                </div>
            </div>
            <div class="col-sm-8">
                <div class="form-group">
                    <label for="kd_dokter">
                        <i class="fas fa-user-md mr-1 text-primary"></i>
                        Dokter Penanggung Jawab <span class="text-danger">*</span>
                    </label>
                    <select wire:model.defer="kd_dokter"
                            id="kd_dokter"
                            class="form-control @error('kd_dokter') is-invalid @enderror">
                        <option value="">-- Pilih Dokter --</option>
                        @foreach($dokterList as $dokter)
                            <option value="{{ $dokter->kd_dokter }}">
                                {{ $dokter->nm_dokter }}
                            </option>
                        @endforeach
                    </select>
                    @error('kd_dokter')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Row 3: Petugas (NIP) --}}
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="nip">
                        <i class="fas fa-user-nurse mr-1 text-success"></i>
                        Petugas / Perawat
                    </label>
                    <select wire:model.defer="nip"
                            id="nip"
                            class="form-control">
                        <option value="">-- Pilih Petugas (Opsional) --</option>
                        @foreach($petugasList as $petugas)
                            <option value="{{ $petugas->nip }}">
                                {{ $petugas->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Row 4: Diagnosa Pra & Pasca Tindakan --}}
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="diagnosa_pra_tindakan">
                        <i class="fas fa-file-medical mr-1 text-danger"></i>
                        Diagnosa Pra Tindakan <span class="text-danger">*</span>
                    </label>
                    <input wire:model.defer="diagnosa_pra_tindakan"
                           type="text"
                           id="diagnosa_pra_tindakan"
                           class="form-control @error('diagnosa_pra_tindakan') is-invalid @enderror"
                           maxlength="50"
                           placeholder="Maks. 50 karakter">
                    @error('diagnosa_pra_tindakan')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="diagnosa_pasca_tindakan">
                        <i class="fas fa-file-medical-alt mr-1 text-success"></i>
                        Diagnosa Pasca Tindakan <span class="text-danger">*</span>
                    </label>
                    <input wire:model.defer="diagnosa_pasca_tindakan"
                           type="text"
                           id="diagnosa_pasca_tindakan"
                           class="form-control @error('diagnosa_pasca_tindakan') is-invalid @enderror"
                           maxlength="50"
                           placeholder="Maks. 50 karakter">
                    @error('diagnosa_pasca_tindakan')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Row 5: Uraian --}}
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="uraian">
                        <i class="fas fa-align-left mr-1 text-info"></i>
                        Uraian Tindakan <span class="text-danger">*</span>
                    </label>
                    <textarea wire:model.defer="uraian"
                              id="uraian"
                              class="form-control @error('uraian') is-invalid @enderror"
                              rows="4"
                              placeholder="Deskripsikan uraian tindakan ventilator..."></textarea>
                    @error('uraian')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Row 6: Hasil --}}
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label for="hasil">
                        <i class="fas fa-clipboard-check mr-1 text-success"></i>
                        Hasil Tindakan <span class="text-danger">*</span>
                    </label>
                    <textarea wire:model.defer="hasil"
                              id="hasil"
                              class="form-control @error('hasil') is-invalid @enderror"
                              rows="3"
                              placeholder="Tuliskan hasil tindakan ventilator..."></textarea>
                    @error('hasil')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="row align-items-center">
            <div class="col-auto">
                <button type="submit"
                        class="btn @if($modeEdit) btn-warning @else btn-primary @endif px-4">
                    <i class="fas fa-save mr-1"></i>
                    @if($modeEdit) Ubah Data @else Simpan @endif
                </button>
            </div>
            @if($modeEdit)
            <div class="col-auto">
                <button wire:click="resetInput" type="button" class="btn btn-secondary px-3">
                    <i class="fas fa-times mr-1"></i> Batal
                </button>
            </div>
            @endif
        </div>
    </form>

    {{-- ===== DATA TABLE & MOBILE CARDS ===== --}}
    <div class="mt-4" wire:init="getData">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center">
                <h6 class="mb-0 font-weight-bold text-info">
                    <i class="fas fa-wind mr-1"></i> Data Tindakan Ventilator
                </h6>
                <span class="badge badge-info ml-2">{{ count($data) }}</span>
            </div>
            <small class="text-muted d-md-none">
                <i class="fas fa-mobile-alt mr-1"></i> Tampilan Ringkas
            </small>
        </div>

        {{-- Desktop View: Table --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table table-sm table-hover table-bordered mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th class="text-center" style="min-width:130px">Tgl Pasang</th>
                        <th class="text-center" style="min-width:130px">Tgl Lepas</th>
                        <th class="text-center" style="width:70px">Durasi</th>
                        <th style="min-width:130px">Dokter</th>
                        <th style="min-width:110px">Petugas</th>
                        <th style="min-width:120px">Dx Pra</th>
                        <th style="min-width:120px">Dx Pasca</th>
                        <th style="min-width:150px">Uraian</th>
                        <th style="min-width:120px">Hasil</th>
                        <th class="text-center" style="width:80px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                    <tr>
                        <td class="text-nowrap small">
                            {{ \Carbon\Carbon::parse($item->tanggalpemasangan)->format('d/m/Y H:i') }}
                        </td>
                        <td class="text-nowrap small">
                            {{ \Carbon\Carbon::parse($item->tanggalpelepasan)->format('d/m/Y H:i') }}
                        </td>
                        <td class="text-center small">
                            @if($item->durasi)
                                <span class="badge badge-secondary">{{ $item->durasi }} jam</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="small">{{ $item->nm_dokter ?? $item->kd_dokter }}</td>
                        <td class="small">{{ $item->nm_petugas ?? ($item->nip ? $item->nip : '—') }}</td>
                        <td class="small" title="{{ $item->diagnosa_pra_tindakan }}">
                            {{ Str::limit($item->diagnosa_pra_tindakan, 22) }}
                        </td>
                        <td class="small" title="{{ $item->diagnosa_pasca_tindakan }}">
                            {{ Str::limit($item->diagnosa_pasca_tindakan, 22) }}
                        </td>
                        <td class="small" title="{{ $item->uraian }}">
                            {{ Str::limit($item->uraian, 35) }}
                        </td>
                        <td class="small" title="{{ $item->hasil }}">
                            {{ Str::limit($item->hasil, 25) }}
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button wire:click="edit('{{ $item->tanggalpemasangan }}')"
                                        type="button"
                                        class="btn btn-warning btn-sm"
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="confirmHapus('{{ $item->tanggalpemasangan }}')"
                                        type="button"
                                        class="btn btn-danger btn-sm"
                                        title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-3">
                            <i class="fas fa-inbox mr-1"></i> Belum ada data laporan tindakan ventilator
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile View: Stacked Card List --}}
        <div class="d-block d-md-none">
            @forelse($data as $item)
            <div class="card card-outline card-info shadow-sm mb-3" style="border-left: 4px solid #17a2b8;">
                <div class="card-body p-3">
                    {{-- Row 1: Durasi & Actions --}}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            @if($item->durasi)
                                <span class="badge badge-info px-2 py-1 font-weight-bold">
                                    <i class="fas fa-clock mr-1"></i> {{ $item->durasi }} Jam
                                </span>
                            @else
                                <span class="badge badge-secondary px-2 py-1">Durasi —</span>
                            @endif
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button wire:click="edit('{{ $item->tanggalpemasangan }}')"
                                    type="button"
                                    class="btn btn-outline-warning btn-sm px-2 py-1"
                                    title="Edit Data">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <button wire:click="confirmHapus('{{ $item->tanggalpemasangan }}')"
                                    type="button"
                                    class="btn btn-outline-danger btn-sm px-2 py-1"
                                    title="Hapus Data">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Row 2: Waktu Pasang & Lepas --}}
                    <div class="bg-light rounded p-2 mb-2 small">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted"><i class="fas fa-calendar-plus text-info mr-1"></i> Pasang:</span>
                            <span class="font-weight-bold">{{ \Carbon\Carbon::parse($item->tanggalpemasangan)->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fas fa-calendar-minus text-warning mr-1"></i> Lepas:</span>
                            <span class="font-weight-bold">{{ \Carbon\Carbon::parse($item->tanggalpelepasan)->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    {{-- Row 3: Tim Medis --}}
                    <div class="mb-2">
                        <div class="small text-truncate">
                            <i class="fas fa-user-md text-primary mr-1" style="width: 14px;"></i>
                            <span class="text-muted">Dokter:</span>
                            <strong>{{ $item->nm_dokter ?? $item->kd_dokter }}</strong>
                        </div>
                        @if($item->nm_petugas || $item->nip)
                        <div class="small text-truncate mt-1">
                            <i class="fas fa-user-nurse text-success mr-1" style="width: 14px;"></i>
                            <span class="text-muted">Petugas:</span>
                            <span>{{ $item->nm_petugas ?? $item->nip }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Row 4: Diagnosa Pra & Pasca --}}
                    <div class="row no-gutters mb-2">
                        <div class="col-6 pr-1">
                            <div class="border rounded p-2 h-100 bg-white">
                                <div class="text-muted small font-weight-bold mb-1" style="font-size: 11px;">
                                    <i class="fas fa-file-medical text-danger mr-1"></i> DX PRA
                                </div>
                                <div class="small text-break">{{ $item->diagnosa_pra_tindakan ?: '—' }}</div>
                            </div>
                        </div>
                        <div class="col-6 pl-1">
                            <div class="border rounded p-2 h-100 bg-white">
                                <div class="text-muted small font-weight-bold mb-1" style="font-size: 11px;">
                                    <i class="fas fa-file-medical-alt text-success mr-1"></i> DX PASCA
                                </div>
                                <div class="small text-break">{{ $item->diagnosa_pasca_tindakan ?: '—' }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Row 5: Uraian & Hasil (Collapsible / Readable) --}}
                    @if($item->uraian)
                    <div class="mb-1">
                        <div class="text-muted small font-weight-bold" style="font-size: 11px;">
                            <i class="fas fa-align-left text-info mr-1"></i> Uraian Tindakan:
                        </div>
                        <p class="small text-muted mb-2 text-break pl-3 border-left" style="white-space: pre-line;">{{ $item->uraian }}</p>
                    </div>
                    @endif

                    @if($item->hasil)
                    <div>
                        <div class="text-muted small font-weight-bold" style="font-size: 11px;">
                            <i class="fas fa-clipboard-check text-success mr-1"></i> Hasil:
                        </div>
                        <p class="small text-muted mb-0 text-break pl-3 border-left" style="white-space: pre-line;">{{ $item->hasil }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="card bg-light border text-center p-3 text-muted">
                <i class="fas fa-inbox fa-2x mb-2 text-secondary"></i>
                <div class="small">Belum ada data laporan tindakan ventilator</div>
            </div>
            @endforelse
        </div>
    </div>
</div>
