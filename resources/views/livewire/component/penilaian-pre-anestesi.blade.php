<div>
    <form wire:submit.prevent="{{ $modeEdit ? 'update' : 'simpan' }}">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-1"></i>
                    {{ $modeEdit ? 'Edit Penilaian Pre Anestesi' : 'Input Penilaian Pre Anestesi' }}
                </h3>
            </div>
            <div class="card-body">
                <!-- Section 1: Informasi Operasi & Diagnosa -->
                <h5 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-notes-medical mr-1"></i> Informasi Operasi & Diagnosa</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tanggal Operasi</label>
                            <input type="datetime-local" class="form-control @error('tanggal_operasi') is-invalid @enderror" wire:model.defer="tanggal_operasi">
                            @error('tanggal_operasi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Diagnosa</label>
                            <input type="text" class="form-control @error('diagnosa') is-invalid @enderror" wire:model.defer="diagnosa" placeholder="Diagnosa pre anestesi" maxlength="100">
                            @error('diagnosa') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Rencana Tindakan</label>
                            <input type="text" class="form-control @error('rencana_tindakan') is-invalid @enderror" wire:model.defer="rencana_tindakan" placeholder="Rencana tindakan" maxlength="100">
                            @error('rencana_tindakan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Vital Signs (Tanda Vital) -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-heartbeat mr-1"></i> Tanda Vital</h5>
                <div class="row">
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>TB (cm)</label>
                            <input type="text" class="form-control @error('tb') is-invalid @enderror" wire:model.defer="tb" placeholder="cm" maxlength="5">
                            @error('tb') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>BB (kg)</label>
                            <input type="text" class="form-control @error('bb') is-invalid @enderror" wire:model.defer="bb" placeholder="kg" maxlength="5">
                            @error('bb') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>TD (mmHg)</label>
                            <input type="text" class="form-control @error('td') is-invalid @enderror" wire:model.defer="td" placeholder="120/80" maxlength="8">
                            @error('td') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>SpO2 / IO2 (%)</label>
                            <input type="text" class="form-control @error('io2') is-invalid @enderror" wire:model.defer="io2" placeholder="%" maxlength="5">
                            @error('io2') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="form-group">
                            <label>Nadi (x/mnt)</label>
                            <input type="text" class="form-control @error('nadi') is-invalid @enderror" wire:model.defer="nadi" placeholder="x/menit" maxlength="5">
                            @error('nadi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="form-group">
                            <label>Pernapasan (x/mnt)</label>
                            <input type="text" class="form-control @error('pernapasan') is-invalid @enderror" wire:model.defer="pernapasan" placeholder="x/menit" maxlength="5">
                            @error('pernapasan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>Suhu (°C)</label>
                            <input type="text" class="form-control @error('suhu') is-invalid @enderror" wire:model.defer="suhu" placeholder="°C" maxlength="5">
                            @error('suhu') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 3: Pemeriksaan Fisik -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-stethoscope mr-1"></i> Pemeriksaan Fisik</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Kardiovaskuler</label>
                            <input type="text" class="form-control @error('fisik_cardiovasculer') is-invalid @enderror" wire:model.defer="fisik_cardiovasculer" maxlength="100">
                            @error('fisik_cardiovasculer') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Paru</label>
                            <input type="text" class="form-control @error('fisik_paru') is-invalid @enderror" wire:model.defer="fisik_paru" maxlength="100">
                            @error('fisik_paru') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Abdomen</label>
                            <input type="text" class="form-control @error('fisik_abdomen') is-invalid @enderror" wire:model.defer="fisik_abdomen" maxlength="100">
                            @error('fisik_abdomen') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Ekstrimitas</label>
                            <input type="text" class="form-control @error('fisik_extrimitas') is-invalid @enderror" wire:model.defer="fisik_extrimitas" maxlength="100">
                            @error('fisik_extrimitas') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Endokrin</label>
                            <input type="text" class="form-control @error('fisik_endokrin') is-invalid @enderror" wire:model.defer="fisik_endokrin" maxlength="100">
                            @error('fisik_endokrin') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Ginjal</label>
                            <input type="text" class="form-control @error('fisik_ginjal') is-invalid @enderror" wire:model.defer="fisik_ginjal" maxlength="100">
                            @error('fisik_ginjal') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Obat-obatan</label>
                            <input type="text" class="form-control @error('fisik_obatobatan') is-invalid @enderror" wire:model.defer="fisik_obatobatan" maxlength="100">
                            @error('fisik_obatobatan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Laboratorium</label>
                            <input type="text" class="form-control @error('fisik_laborat') is-invalid @enderror" wire:model.defer="fisik_laborat" maxlength="100">
                            @error('fisik_laborat') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Penunjang Lainnya</label>
                            <input type="text" class="form-control @error('fisik_penunjang') is-invalid @enderror" wire:model.defer="fisik_penunjang" maxlength="100">
                            @error('fisik_penunjang') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 4: Riwayat Penyakit & Kebiasaan -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-history mr-1"></i> Riwayat Penyakit & Kebiasaan</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Alergi Obat</label>
                            <input type="text" class="form-control @error('riwayat_penyakit_alergiobat') is-invalid @enderror" wire:model.defer="riwayat_penyakit_alergiobat" maxlength="50">
                            @error('riwayat_penyakit_alergiobat') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Alergi Lainnya</label>
                            <input type="text" class="form-control @error('riwayat_penyakit_alergilainnya') is-invalid @enderror" wire:model.defer="riwayat_penyakit_alergilainnya" maxlength="50">
                            @error('riwayat_penyakit_alergilainnya') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Terapi Saat Ini</label>
                            <input type="text" class="form-control @error('riwayat_penyakit_terapi') is-invalid @enderror" wire:model.defer="riwayat_penyakit_terapi" maxlength="100">
                            @error('riwayat_penyakit_terapi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Merokok -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Merokok</label>
                            <select class="form-control @error('riwayat_kebiasaan_merokok') is-invalid @enderror" wire:model.defer="riwayat_kebiasaan_merokok">
                                <option value="Tidak">Tidak</option>
                                <option value="Ya">Ya</option>
                            </select>
                            @error('riwayat_kebiasaan_merokok') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Ket. Merokok</label>
                            <input type="text" class="form-control @error('riwayat_kebiasaan_ket_merokok') is-invalid @enderror" wire:model.defer="riwayat_kebiasaan_ket_merokok" placeholder="Ket" maxlength="5">
                            @error('riwayat_kebiasaan_ket_merokok') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Alkohol -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Alkohol</label>
                            <select class="form-control @error('riwayat_kebiasaan_alkohol') is-invalid @enderror" wire:model.defer="riwayat_kebiasaan_alkohol">
                                <option value="Tidak">Tidak</option>
                                <option value="Ya">Ya</option>
                            </select>
                            @error('riwayat_kebiasaan_alkohol') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Ket. Alkohol</label>
                            <input type="text" class="form-control @error('riwayat_kebiasaan_ket_alkohol') is-invalid @enderror" wire:model.defer="riwayat_kebiasaan_ket_alkohol" placeholder="Ket" maxlength="5">
                            @error('riwayat_kebiasaan_ket_alkohol') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Kebiasaan Obat -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Konsumsi Obat</label>
                            <select class="form-control @error('riwayat_kebiasaan_obat') is-invalid @enderror" wire:model.defer="riwayat_kebiasaan_obat">
                                <option value="-">-</option>
                                <option value="Obat Obatan">Obat Obatan</option>
                                <option value="Vitamin">Vitamin</option>
                                <option value="Jamu Jamuan">Jamu Jamuan</option>
                            </select>
                            @error('riwayat_kebiasaan_obat') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Ket. Obat</label>
                            <input type="text" class="form-control @error('riwayat_kebiasaan_ket_obat') is-invalid @enderror" wire:model.defer="riwayat_kebiasaan_ket_obat" placeholder="Ket obat" maxlength="100">
                            @error('riwayat_kebiasaan_ket_obat') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 5: Riwayat Medis Spesifik -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-user-md mr-1"></i> Riwayat Medis</h5>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Kardiovaskuler</label>
                            <input type="text" class="form-control @error('riwayat_medis_cardiovasculer') is-invalid @enderror" wire:model.defer="riwayat_medis_cardiovasculer" maxlength="100">
                            @error('riwayat_medis_cardiovasculer') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Respiratory</label>
                            <input type="text" class="form-control @error('riwayat_medis_respiratory') is-invalid @enderror" wire:model.defer="riwayat_medis_respiratory" maxlength="100">
                            @error('riwayat_medis_respiratory') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Endocrine</label>
                            <input type="text" class="form-control @error('riwayat_medis_endocrine') is-invalid @enderror" wire:model.defer="riwayat_medis_endocrine" maxlength="100">
                            @error('riwayat_medis_endocrine') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Lainnya</label>
                            <input type="text" class="form-control @error('riwayat_medis_lainnya') is-invalid @enderror" wire:model.defer="riwayat_medis_lainnya" maxlength="100">
                            @error('riwayat_medis_lainnya') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 6: Rencana Anestesi & Penilaian ASA -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-syringe mr-1"></i> Rencana Anestesi & Penilaian ASA</h5>
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>ASA Score</label>
                            <select class="form-control @error('asa') is-invalid @enderror" wire:model.defer="asa">
                                <option value="">-- Pilih --</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="E">E</option>
                            </select>
                            @error('asa') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Waktu Puasa</label>
                            <input type="datetime-local" class="form-control @error('puasa') is-invalid @enderror" wire:model.defer="puasa">
                            @error('puasa') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Rencana Anestesi</label>
                            <select class="form-control @error('rencana_anestesi') is-invalid @enderror" wire:model.defer="rencana_anestesi">
                                <option value="">-- Pilih --</option>
                                <option value="GA">GA</option>
                                <option value="RA Spinal">RA Spinal</option>
                                <option value="RA Epidural">RA Epidural</option>
                                <option value="RA Combined">RA Combined</option>
                                <option value="Blok Syaraf">Blok Syaraf</option>
                            </select>
                            @error('rencana_anestesi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Rencana Perawatan</label>
                            <input type="text" class="form-control @error('rencana_perawatan') is-invalid @enderror" wire:model.defer="rencana_perawatan" placeholder="Rencana perawatan" maxlength="40">
                            @error('rencana_perawatan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Catatan Khusus</label>
                            <input type="text" class="form-control @error('catatan_khusus') is-invalid @enderror" wire:model.defer="catatan_khusus" placeholder="Catatan khusus pre anestesi" maxlength="100">
                            @error('catatan_khusus') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-right">
                @if($modeEdit)
                    <button type="button" class="btn btn-secondary mr-1" wire:click="resetInput">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save mr-1"></i> Update Penilaian</button>
                @else
                    <button type="button" class="btn btn-default mr-1" wire:click="resetInput">Reset</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Penilaian</button>
                @endif
            </div>
        </div>
    </form>

    <!-- Table Riwayat Penilaian Pre Anestesi -->
    <div class="card card-outline card-info mt-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history mr-1"></i>
                Riwayat Penilaian Pre Anestesi
            </h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 150px;">Tanggal Input</th>
                        <th>Dokter Penilai</th>
                        <th>Tgl Operasi</th>
                        <th>Diagnosa & Rencana</th>
                        <th>ASA</th>
                        <th>Anestesi</th>
                        <th style="width: 120px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y H:i') }}</td>
                            <td>{{ $item->nm_dokter ?? $item->kd_dokter }}</td>
                            <td>{{ $item->tanggal_operasi ? \Carbon\Carbon::parse($item->tanggal_operasi)->format('d-m-Y H:i') : '-' }}</td>
                            <td>
                                <strong>Diag:</strong> {{ $item->diagnosa ?? '-' }}<br>
                                <small class="text-muted"><strong>Tindakan:</strong> {{ $item->rencana_tindakan ?? '-' }}</small>
                            </td>
                            <td><span class="badge badge-info">{{ $item->asa ?? '-' }}</span></td>
                            <td>{{ $item->rencana_anestesi ?? '-' }}</td>
                            <td class="text-center">
                                @if($item->kd_dokter == session('username'))
                                    <button class="btn btn-xs btn-warning" wire:click="edit('{{ $item->tanggal }}')" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-xs btn-danger" wire:click="confirmHapus('{{ $item->tanggal }}')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @else
                                    <span class="badge badge-secondary" title="Tidak memiliki akses edit/hapus">Read Only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada data penilaian pre anestesi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
