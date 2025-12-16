<div>
    <form wire:submit.prevent='simpanPemeriksaan'>
        <div class="row">
            <div class="form-group col-md-6">
                <label for="">Subjek</label>
                <textarea wire:model.defer='keluhan' class="form-control @error('keluhan') is-invalid @enderror" name="" id="" rows="4"></textarea>
                @error('keluhan') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="form-group col-md-6">
                <label for="">Objek</label>
                <textarea wire:model.defer='pemeriksaan' class="form-control  @error('pemeriksaan') is-invalid @enderror" name="" id="" rows="4"></textarea>
                @error('pemeriksaan') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label for="">Asesmen</label>
                <textarea wire:model.defer='penilaian' class="form-control @error('penilaian') is-invalid @enderror" name="" id="" rows="2"></textarea>
                @error('penilaian') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="form-group col-md-6">
                <label for="">Instruksi</label>
                <textarea wire:model.defer='instruksi' class="form-control @error('instruksi') is-invalid @enderror" name="" id="" rows="2"></textarea>
                @error('instruksi') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-6">
                <label for="">Plan</label>
                <textarea wire:model.defer='rtl' class="form-control @error('rtl') is-invalid @enderror" name="" id="" rows="1"></textarea>
                @error('rtl') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="form-group col-md-6">
                <label for="">Alergi</label>
                <textarea wire:model.defer='alergi' class="form-control" name="" id="" rows="1"></textarea>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-3">
                <label for="">Suhu</label>
                <input type="text" wire:model.defer='suhu' class="form-control" name="" id="" aria-describedby="helpId"
                    placeholder="">
            </div>
            <div class="form-group col-md-3">
                <label for="">Berat</label>
                <input type="text" wire:model.defer='berat' class="form-control" name="" id="" aria-describedby="helpId"
                    placeholder="">
            </div>
            <div class="form-group col-md-3">
                <label for="">Tinggi Badan</label>
                <input type="text" wire:model.defer='tinggi' class="form-control" name="" id=""
                    aria-describedby="helpId" placeholder="">
            </div>
            <div class="form-group col-md-3">
                <label for="">GCS (E, V, M)</label>
                <input type="text" wire:model.defer='gcs' class="form-control" name="" id="" aria-describedby="helpId"
                    placeholder="">
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-2">
                <label for="">SPO2</label>
                <input type="text" wire:model.defer='spo2' class="form-control" name="" id="" aria-describedby="helpId"
                    placeholder="">
            </div>
            <div class="form-group col-md-2">
                <label for="">Tensi</label>
                <input type="text" wire:model.defer='tensi' class="form-control" name="" id="" aria-describedby="helpId"
                    placeholder="">
            </div>
            <div class="form-group col-md-2">
                <label for="">Nadi</label>
                <input type="text" wire:model.defer='nadi' class="form-control" name="" id="" aria-describedby="helpId"
                    placeholder="">
            </div>
            <div class="form-group col-md-3">
                <label for="">Respirasi</label>
                <input type="text" wire:model.defer='respirasi' class="form-control" name="" id=""
                    aria-describedby="helpId" placeholder="">
            </div>
            <div class="form-group col-md-3">
                <label for="">Kesadaran</label>
                <select class="form-control" wire:model.defer='kesadaran' name="" id="">
                    @if(!$kesadaran) <option value="{{$kesadaran}}">{{$kesadaran}}</option> @endif
                    <option value="Compos Mentis">Compos Mentis</option>
                    <option value="Apatis">Apatis</option>
                    <option value="Delirium">Delirium</option>
                    <option value="Somnolence">Somnolence</option>
                    <option value="Sopor">Sopor</option>
                    <option value="Coma">Coma</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="">Evaluasi</label>
            <textarea wire:model.defer='evaluasi' class="form-control" name="" id="" rows="1"></textarea>
        </div>
        <div class="d-flex justify-content-between align-items-center" style="gap: 10px">
            <div>
                <button type="button" wire:click='loadDataTerakhir' class="btn btn-info btn-sm">
                    <i class="fas fa-history"></i> Muat Data Terakhir
                </button>
                <button type="button" wire:click='resetForm' class="btn btn-secondary btn-sm">
                    <i class="fas fa-refresh"></i> Reset Form
                </button>
            </div>
            <div class="d-flex" style="gap: 10px">
                <button type="submit" class="btn btn-primary">Simpan</button>
                {{-- <button type="button" wire:click='geminiSoap' class="btn btn-secondary">AI</button> --}}
            </div>
        </div>
    </form>
    <h5 class="pt-4">Riwayat Pemeriksaan</h5>
    <div class="row">
        @forelse ($listPemeriksaan as $item)
        @php
            $cardKey = $item->tgl_perawatan . '_' . $item->jam_rawat;
        @endphp
        <div class="col-12 mb-3">
            <div class="card shadow-sm border-0" style="background: #f8f9fa;">
                <div class="card-header p-0 border-0 bg-transparent">
                    <div class="btn btn-link w-100 text-start d-flex justify-content-between align-items-center py-3 px-3" style="cursor:pointer;" wire:click="toggleCollapse('{{ $item->tgl_perawatan }}', '{{ $item->jam_rawat }}')">
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center mb-2 justify-content-between" style="gap: 10px">
                                <div class="d-flex align-items-center" style="gap: 10px">
                                    <span class="badge badge-secondary me-2">
                                        <i class="fas fa-user-md"></i>
                                    </span>
                                    <strong>
                                        {{ $item->nama }}
                                    </strong>
                                </div>
                                <span class="text-muted small ms-2">
                                    <i class="far fa-clock"></i> {{ $item->tgl_perawatan }} {{ $item->jam_rawat }}
                                </span>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                @if($item->suhu_tubuh)
                                <span class="badge badge-info" title="Suhu">
                                    <i class="fas fa-thermometer-half"></i> {{ $item->suhu_tubuh }}°C
                                </span>
                                @endif
                                @if($item->tensi)
                                <span class="badge badge-danger" title="Tensi">
                                    <i class="fas fa-heartbeat"></i> {{ $item->tensi }} mmHg
                                </span>
                                @endif
                                @if($item->nadi)
                                <span class="badge badge-warning" title="Nadi">
                                    <i class="fas fa-heart"></i> {{ $item->nadi }}/min
                                </span>
                                @endif
                                @if($item->respirasi)
                                <span class="badge badge-success" title="Respirasi">
                                    <i class="fas fa-lungs"></i> {{ $item->respirasi }}/min
                                </span>
                                @endif
                                @if($item->spo2)
                                <span class="badge badge-primary" title="SpO2">
                                    <i class="fas fa-wind"></i> {{ $item->spo2 }}%
                                </span>
                                @endif
                                @if($item->gcs)
                                <span class="badge badge-dark" title="GCS">
                                    <i class="fas fa-brain"></i> {{ $item->gcs }}
                                </span>
                                @endif
                                @if($item->kesadaran)
                                <span class="badge badge-secondary" title="Kesadaran">
                                    <i class="fas fa-eye"></i> {{ $item->kesadaran }}
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="ml-2">
                            <i class="fas fa-chevron-{{ (isset($collapsedCards[$cardKey]) && !$collapsedCards[$cardKey]) ? 'up' : 'down' }}"></i>
                        </div>
                    </div>
                </div>
                @if(isset($collapsedCards[$cardKey]) && !$collapsedCards[$cardKey])
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-2">
                            <span class="text-muted small"><i class="fas fa-comment-medical"></i> Keluhan</span>
                            <div class="border rounded p-2 bg-white">{!! nl2br(e($item->keluhan)) !!}</div>
                        </div>
                        <div class="col-12 mb-2">
                            <span class="text-muted small"><i class="fas fa-stethoscope"></i> Pemeriksaan</span>
                            <div class="border rounded p-2 bg-white">{!! nl2br(e($item->pemeriksaan)) !!}</div>
                        </div>
                        <div class="col-12 mb-2">
                            <span class="text-muted small"><i class="fas fa-clipboard-list"></i> Asesmen</span>
                            <div class="border rounded p-2 bg-white">{!! nl2br(e($item->penilaian)) !!}</div>
                        </div>
                        <div class="col-12 mb-2">
                            <span class="text-muted small"><i class="fas fa-clipboard-list"></i> Plan</span>
                            <div class="border rounded p-2 bg-white">{!! nl2br(e($item->rtl)) !!}</div>
                        </div>
                        <div class="col-12 mb-2">
                            <span class="text-muted small"><i class="fas fa-tasks"></i> Instruksi</span>
                            <div class="border rounded p-2 bg-white">{!! nl2br(e($item->instruksi)) !!}</div>
                        </div>
                        <div class="col-12 mb-2">
                            <span class="text-muted small"><i class="fas fa-notes-medical"></i> Evaluasi</span>
                            <div class="border rounded p-2 bg-white">{!! nl2br(e($item->evaluasi)) !!}</div>
                        </div>
                    </div>
                    <div class="row text-center mt-2">
                        <div class="col-6 col-md-3 mb-2">
                            <div class="vital-box-simple">
                                <span class="vital-icon"><i class="fas fa-heartbeat"></i></span>
                                <span class="vital-label">Tensi</span>
                                <span class="vital-value">{{ $item->tensi }}</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <div class="vital-box-simple">
                                <span class="vital-icon"><i class="fas fa-heart"></i></span>
                                <span class="vital-label">Nadi</span>
                                <span class="vital-value">{{ $item->nadi }}/menit</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <div class="vital-box-simple">
                                <span class="vital-icon"><i class="fas fa-thermometer-half"></i></span>
                                <span class="vital-label">Suhu</span>
                                <span class="vital-value">{{ $item->suhu_tubuh }}°C</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <div class="vital-box-simple">
                                <span class="vital-icon"><i class="fas fa-lungs"></i></span>
                                <span class="vital-label">Respirasi</span>
                                <span class="vital-value">{{ $item->respirasi }}/menit</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button wire:click='$emit("openModalEditPemeriksaan", "{{$item->no_rawat}}", "{{$item->tgl_perawatan}}","{{$item->jam_rawat}}")'
                            class="btn btn-sm btn-outline-warning me-1">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button wire:click='confirmHapus("{{$item->no_rawat}}", "{{$item->tgl_perawatan}}","{{$item->jam_rawat}}")'
                            class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Data Pemeriksaan Kosong
            </div>
        </div>
        @endforelse
    </div>
</div>

@livewire('ranap.modal.edit-pemeriksaan')

@section('js')
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}
    <script>
        window.addEventListener('swal:pemeriksaan', function(e) {
            // Jika event adalah konfirmasi hapus (ada showCancelButton), handle konfirmasi
            if (e.detail.showCancelButton) {
                Swal.fire(e.detail).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('hapus');
                    }
                });
            } else {
                Swal.fire(e.detail);
            }
        });
    </script>
@endsection

@section('css')
{{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
<style>
    .vital-box-simple {
        min-height: 90px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
        border-radius: 8px;
        padding: 12px 18px;
        margin-bottom: 8px;
        background: #f1f3f4;
        color: #222;
    }
    .vital-box-simple .vital-icon {
        font-size: 1.5rem;
        color: #888;
        margin-bottom: 2px;
    }
    .vital-box-simple .vital-label {
        font-size: 0.95rem;
        color: #666;
        margin-bottom: 2px;
        font-weight: 400;
    }
    .vital-box-simple .vital-value {
        font-size: 1.2rem;
        font-weight: 600;
        color: #222;
    }
    .badge-status {
        background: #e0e0e0;
        color: #444;
        font-weight: 500;
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 0.95rem;
    }
    .chevron-collapse {
        transition: transform 0.2s;
    }
    .chevron-collapse.collapsed {
        transform: rotate(0deg);
    }
    .chevron-collapse:not(.collapsed) {
        transform: rotate(180deg);
    }
</style>
@endsection

