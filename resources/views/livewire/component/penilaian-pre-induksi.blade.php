<div>
    <form wire:submit.prevent="{{ $modeEdit ? 'update' : 'simpan' }}">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-1"></i>
                    {{ $modeEdit ? 'Edit Penilaian Pre Induksi' : 'Input Penilaian Pre Induksi' }}
                </h3>
            </div>
            <div class="card-body">
                <!-- Section 1: Informasi Waktu Assessment -->
                <h5 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-clock mr-1"></i> Informasi Waktu Assessment</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tanggal & Jam Assessment</label>
                            <input type="datetime-local" class="form-control @error('tanggal') is-invalid @enderror" wire:model.defer="tanggal">
                            @error('tanggal') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Tanda Vital & Penunjang -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-heartbeat mr-1"></i> Tanda Vital & Penunjang</h5>
                <div class="row">
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>TD (mmHg)</label>
                            <input type="text" class="form-control @error('tensi') is-invalid @enderror" wire:model.defer="tensi" placeholder="120/80" maxlength="8">
                            @error('tensi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>Nadi (x/mnt)</label>
                            <input type="text" class="form-control @error('nadi') is-invalid @enderror" wire:model.defer="nadi" placeholder="80" maxlength="5">
                            @error('nadi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>RR (x/mnt)</label>
                            <input type="text" class="form-control @error('rr') is-invalid @enderror" wire:model.defer="rr" placeholder="20" maxlength="5">
                            @error('rr') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>Suhu (°C)</label>
                            <input type="text" class="form-control @error('suhu') is-invalid @enderror" wire:model.defer="suhu" placeholder="36.5" maxlength="5">
                            @error('suhu') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>EKG</label>
                            <input type="text" class="form-control @error('ekg') is-invalid @enderror" wire:model.defer="ekg" placeholder="Hasil EKG" maxlength="50">
                            @error('ekg') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Lain-lain Penunjang</label>
                            <input type="text" class="form-control @error('lain_lain') is-invalid @enderror" wire:model.defer="lain_lain" placeholder="Pemeriksaan penunjang lainnya" maxlength="50">
                            @error('lain_lain') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 3: Asesmen & Perencanaan -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-clipboard-check mr-1"></i> Asesmen & Perencanaan</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Asesmen</label>
                            <select class="form-control @error('asesmen') is-invalid @enderror" wire:model.defer="asesmen">
                                <option value="">-- Pilih Asesmen --</option>
                                <option value="Sesuai Asesmen Pre Sedasi/Anestesi">Sesuai Asesmen Pre Sedasi/Anestesi</option>
                                <option value="Tidak Sesuai Asesmen Pre Sedasi/Anestesi">Tidak Sesuai Asesmen Pre Sedasi/Anestesi</option>
                            </select>
                            @error('asesmen') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Perencanaan</label>
                            <textarea class="form-control @error('perencanaan') is-invalid @enderror" wire:model.defer="perencanaan" rows="2" placeholder="Catatan perencanaan" maxlength="300"></textarea>
                            @error('perencanaan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 4: Akses Vaskuler & Posisi -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-procedures mr-1"></i> Akses Vaskuler & Posisi</h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Infus Perifer</label>
                            <input type="text" class="form-control @error('infus_perifier') is-invalid @enderror" wire:model.defer="infus_perifier" placeholder="Lokasi & ukuran infus" maxlength="300">
                            @error('infus_perifier') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>CVC</label>
                            <input type="text" class="form-control @error('cvc') is-invalid @enderror" wire:model.defer="cvc" placeholder="Keterangan CVC" maxlength="70">
                            @error('cvc') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Posisi Pasien</label>
                            <select class="form-control @error('posisi') is-invalid @enderror" wire:model.defer="posisi">
                                <option value="">-- Pilih Posisi --</option>
                                <option value="Supine">Supine</option>
                                <option value="Lithotomi">Lithotomi</option>
                                <option value="Lateral">Lateral</option>
                                <option value="Prone">Prone</option>
                                <option value="Perlindungan Mata">Perlindungan Mata</option>
                                <option value="Kanan">Kanan</option>
                                <option value="Kiri">Kiri</option>
                                <option value="Lain-lain">Lain-lain</option>
                            </select>
                            @error('posisi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 5: Premedikasi & Induksi -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-syringe mr-1"></i> Premedikasi & Induksi</h5>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Premedikasi</label>
                            <select class="form-control @error('premedikasi') is-invalid @enderror" wire:model.defer="premedikasi">
                                <option value="">-- Pilih Rute --</option>
                                <option value="Oral">Oral</option>
                                <option value="IM">IM</option>
                                <option value="IV">IV</option>
                            </select>
                            @error('premedikasi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ket. Premedikasi</label>
                            <input type="text" class="form-control @error('premedikasi_keterangan') is-invalid @enderror" wire:model.defer="premedikasi_keterangan" placeholder="Obat / Dosis" maxlength="50">
                            @error('premedikasi_keterangan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Induksi</label>
                            <select class="form-control @error('induksi') is-invalid @enderror" wire:model.defer="induksi">
                                <option value="">-- Pilih Jenis --</option>
                                <option value="Intravena">Intravena</option>
                                <option value="Inhalasi">Inhalasi</option>
                            </select>
                            @error('induksi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ket. Induksi</label>
                            <input type="text" class="form-control @error('induksi_keterangan') is-invalid @enderror" wire:model.defer="induksi_keterangan" placeholder="Keterangan induksi" maxlength="70">
                            @error('induksi_keterangan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 6: Tatalaksana Jalan Napas & Intubasi -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-head-side-mask mr-1"></i> Tatalaksana Jalan Napas & Intubasi</h5>
                <div class="row">
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>Face Mask No</label>
                            <input type="text" class="form-control @error('face_mask_no') is-invalid @enderror" wire:model.defer="face_mask_no" placeholder="No" maxlength="20">
                            @error('face_mask_no') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>Nasopharing No</label>
                            <input type="text" class="form-control @error('nasopharing_no') is-invalid @enderror" wire:model.defer="nasopharing_no" placeholder="No" maxlength="20">
                            @error('nasopharing_no') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>ETT No</label>
                            <input type="text" class="form-control @error('ett_no') is-invalid @enderror" wire:model.defer="ett_no" placeholder="No" maxlength="20">
                            @error('ett_no') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>ETT Jenis</label>
                            <input type="text" class="form-control @error('ett_jenis') is-invalid @enderror" wire:model.defer="ett_jenis" placeholder="Jenis ETT" maxlength="20">
                            @error('ett_jenis') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>ETT Fiksasi</label>
                            <input type="text" class="form-control @error('ett_viksasi') is-invalid @enderror" wire:model.defer="ett_viksasi" placeholder="Fiksasi (cm)" maxlength="25">
                            @error('ett_viksasi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>LMA No</label>
                            <input type="text" class="form-control @error('lma_no') is-invalid @enderror" wire:model.defer="lma_no" placeholder="No" maxlength="20">
                            @error('lma_no') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>LMA Jenis</label>
                            <input type="text" class="form-control @error('lma_jenis') is-invalid @enderror" wire:model.defer="lma_jenis" placeholder="Jenis LMA" maxlength="20">
                            @error('lma_jenis') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>Tracheostomi</label>
                            <input type="text" class="form-control @error('tracheostomi') is-invalid @enderror" wire:model.defer="tracheostomi" placeholder="Tracheostomi" maxlength="60">
                            @error('tracheostomi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>Glidescopi</label>
                            <input type="text" class="form-control @error('glidescopi') is-invalid @enderror" wire:model.defer="glidescopi" placeholder="Glidescopi" maxlength="60">
                            @error('glidescopi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>Bronchoscopi Fiberoptik</label>
                            <input type="text" class="form-control @error('bronchoscopi_fiberoptik') is-invalid @enderror" wire:model.defer="bronchoscopi_fiberoptik" placeholder="Bronchoscopi" maxlength="60">
                            @error('bronchoscopi_fiberoptik') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-8 col-12">
                        <div class="form-group">
                            <label>Lain-lain Tatalaksana Jalan Napas</label>
                            <input type="text" class="form-control @error('lain_lain_tatalaksana') is-invalid @enderror" wire:model.defer="lain_lain_tatalaksana" placeholder="Keterangan tambahan" maxlength="100">
                            @error('lain_lain_tatalaksana') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Evaluasi Intubasi & Ventilasi -->
                <h6 class="text-secondary font-weight-bold border-bottom pb-1 mt-3 mb-3"><i class="fas fa-lungs mr-1"></i> Evaluasi Intubasi & Ventilasi</h6>
                <div class="row">
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>Intubasi Sesudah Tidur</label>
                            <select class="form-control @error('intubasi_sesudah_tidur') is-invalid @enderror" wire:model.defer="intubasi_sesudah_tidur">
                                <option value="">-- Pilih --</option>
                                <option value="Ya">Ya</option>
                                <option value="Tidak">Tidak</option>
                            </select>
                            @error('intubasi_sesudah_tidur') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>Intubasi Oral</label>
                            <select class="form-control @error('intubasi_oral') is-invalid @enderror" wire:model.defer="intubasi_oral">
                                <option value="">-- Pilih --</option>
                                <option value="Ya">Ya</option>
                                <option value="Tidak">Tidak</option>
                            </select>
                            @error('intubasi_oral') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>Intubasi Tracheostomi</label>
                            <select class="form-control @error('intubasi_tracheostomi') is-invalid @enderror" wire:model.defer="intubasi_tracheostomi">
                                <option value="">-- Pilih --</option>
                                <option value="Ya">Ya</option>
                                <option value="Tidak">Tidak</option>
                            </select>
                            @error('intubasi_tracheostomi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="form-group">
                            <label>Ventilasi</label>
                            <input type="text" class="form-control @error('ventilasi') is-invalid @enderror" wire:model.defer="ventilasi" placeholder="Ket. Ventilasi" maxlength="100">
                            @error('ventilasi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>Sulit Ventilasi</label>
                            <input type="text" class="form-control @error('sulit_ventilasi') is-invalid @enderror" wire:model.defer="sulit_ventilasi" placeholder="Catatan jika sulit" maxlength="100">
                            @error('sulit_ventilasi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>Sulit Intubasi</label>
                            <input type="text" class="form-control @error('sulit_intubasi') is-invalid @enderror" wire:model.defer="sulit_intubasi" placeholder="Catatan jika sulit" maxlength="100">
                            @error('sulit_intubasi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>Ket. Intubasi</label>
                            <input type="text" class="form-control @error('intubasi_keterangan') is-invalid @enderror" wire:model.defer="intubasi_keterangan" placeholder="Keterangan intubasi" maxlength="200">
                            @error('intubasi_keterangan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 7: Teknik Regional -->
                <h5 class="text-primary border-bottom pb-2 mb-3 mt-4"><i class="fas fa-band-aid mr-1"></i> Teknik Regional</h5>
                <div class="row">
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>Jenis Teknik Regional</label>
                            <input type="text" class="form-control @error('teknik_regional_jenis') is-invalid @enderror" wire:model.defer="teknik_regional_jenis" placeholder="Jenis teknik regional" maxlength="100">
                            @error('teknik_regional_jenis') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>Lokasi Regional</label>
                            <input type="text" class="form-control @error('teknik_regional_lokasi') is-invalid @enderror" wire:model.defer="teknik_regional_lokasi" placeholder="Lokasi anatomis" maxlength="40">
                            @error('teknik_regional_lokasi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>Jenis Jarum</label>
                            <input type="text" class="form-control @error('teknik_regional_jenis_jarum') is-invalid @enderror" wire:model.defer="teknik_regional_jenis_jarum" placeholder="Jenis & ukuran jarum" maxlength="30">
                            @error('teknik_regional_jenis_jarum') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>Kateter</label>
                            <select class="form-control @error('teknik_regional_kateter') is-invalid @enderror" wire:model.defer="teknik_regional_kateter">
                                <option value="">-- Pilih --</option>
                                <option value="Ya">Ya</option>
                                <option value="Tidak">Tidak</option>
                            </select>
                            @error('teknik_regional_kateter') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>Fiksasi Kateter</label>
                            <input type="text" class="form-control @error('teknik_regional_kateter_viksasi') is-invalid @enderror" wire:model.defer="teknik_regional_kateter_viksasi" placeholder="Kedalaman fiksasi" maxlength="40">
                            @error('teknik_regional_kateter_viksasi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label>Hasil Regional</label>
                            <input type="text" class="form-control @error('teknik_regional_hasil') is-invalid @enderror" wire:model.defer="teknik_regional_hasil" placeholder="Hasil blokade/anestesi" maxlength="100">
                            @error('teknik_regional_hasil') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label>Obat-obatan Regional</label>
                            <textarea class="form-control @error('teknik_regional_obat_obatan') is-invalid @enderror" wire:model.defer="teknik_regional_obat_obatan" rows="2" placeholder="Nama obat & dosis" maxlength="400"></textarea>
                            @error('teknik_regional_obat_obatan') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="form-group">
                            <label>Komplikasi Regional</label>
                            <textarea class="form-control @error('teknik_regional_komplikasi') is-invalid @enderror" wire:model.defer="teknik_regional_komplikasi" rows="2" placeholder="Komplikasi yang terjadi" maxlength="200"></textarea>
                            @error('teknik_regional_komplikasi') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="card-footer text-right">
                @if($modeEdit)
                    <button type="button" class="btn btn-secondary mr-2" wire:click="batal">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i> Update
                    </button>
                @else
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                @endif
            </div>
        </div>
    </form>

    <!-- Table Riwayat Penilaian Pre Induksi -->
    <div class="card card-outline card-info mt-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history mr-1"></i> Riwayat Penilaian Pre Induksi
            </h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Dokter</th>
                        <th>Tensi</th>
                        <th>Nadi</th>
                        <th>RR</th>
                        <th>Suhu</th>
                        <th>Asesmen</th>
                        <th>Posisi</th>
                        <th>Induksi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y H:i') }}</td>
                            <td>{{ $item->nm_dokter ?? $item->kd_dokter }}</td>
                            <td>{{ $item->tensi ?? '-' }}</td>
                            <td>{{ $item->nadi ?? '-' }}</td>
                            <td>{{ $item->rr ?? '-' }}</td>
                            <td>{{ $item->suhu ?? '-' }}</td>
                            <td>{{ $item->asesmen ?? '-' }}</td>
                            <td>{{ $item->posisi ?? '-' }}</td>
                            <td>{{ $item->induksi ?? '-' }}</td>
                            <td>
                                <button type="button" class="btn btn-xs btn-warning" wire:click="edit('{{ $item->tanggal }}')">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button" class="btn btn-xs btn-danger" wire:click="confirmHapus('{{ $item->tanggal }}')">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-3">
                                Belum ada data penilaian pre induksi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
