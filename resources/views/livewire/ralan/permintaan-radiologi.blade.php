<div @if($isCollapsed) class="card card-info collapsed-card" @else class="card card-info" @endif>
    <div class="card-header bg-info">
        <h3 class="card-title">
            <i class="fas fa-lg fa-x-ray mr-2"></i> 
            <strong>Permintaan Radiologi</strong>
        </h3>
        <div class="card-tools">
            <button type="button" wire:click="collapsed" class="btn btn-tool" data-card-widget="collapse">
                <i wire:ignore class="fas fa-lg fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Form Input -->
        <div class="card card-outline card-primary mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-plus-circle mr-2"></i> Form Permintaan Baru
                </h5>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="savePermintaanRadiologi">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="klinis" class="form-label">
                                <i class="fas fa-stethoscope text-primary mr-1"></i> 
                                <strong>Klinis</strong>
                            </label>
                            <input type="text" 
                                   class="form-control @error('klinis') is-invalid @enderror" 
                                   wire:model.defer="klinis" 
                                   id="klinis" 
                                   name="klinis"
                                   placeholder="Masukkan diagnosa klinis" />
                            @error('klinis') 
                                <div class="invalid-feedback d-block">{{ $message }}</div> 
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="info" class="form-label">
                                <i class="fas fa-info-circle text-info mr-1"></i> 
                                <strong>Info Tambahan</strong>
                            </label>
                            <input type="text" 
                                   class="form-control @error('info') is-invalid @enderror" 
                                   wire:model.defer="info" 
                                   id="info" 
                                   name="info"
                                   placeholder="Masukkan informasi tambahan" />
                            @error('info') 
                                <div class="invalid-feedback d-block">{{ $message }}</div> 
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Jenis Pemeriksaan Section -->
                    <div class="mb-4">
                        <label class="form-label mb-3">
                            <i class="fas fa-list-check text-success mr-1"></i> 
                            <strong>Jenis Pemeriksaan</strong>
                        </label>
                        @error('jns_pemeriksaan') 
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle mr-2"></i>{{ $message }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @enderror
                        
                        <div id="select2-container-radiologi" class="select2-radiologi-wrapper">
                            @for($i = 0; $i < $select2Count; $i++)
                            <div class="select2-item-wrapper mb-3" 
                                 wire:key="select2-rad-{{ $i }}"
                                 data-select2-index="{{ $i }}">
                                <div class="card border-left-primary shadow-sm">
                                    <div class="card-body p-3">
                                        <div class="row align-items-center">
                                            <div class="col-md-10">
                                                <label class="small text-muted mb-1 d-block">
                                                    <i class="fas fa-x-ray text-primary mr-1"></i>
                                                    Pemeriksaan #{{ $i + 1 }}
                                                </label>
                                                <div wire:ignore>
                                                    <select class="form-control jenis-radiologi-select" 
                                                            id="jenis_rad_{{ $i }}" 
                                                            data-index="{{ $i }}"
                                                            data-select2-id="{{ $i }}"></select>
                                                </div>
                                                @error('jns_pemeriksaan.'.$i) 
                                                    <span class="text-danger small d-block mt-1">
                                                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                                    </span> 
                                                @enderror
                                            </div>
                                            <div class="col-md-2 text-right">
                                                <div class="btn-group-vertical" role="group">
                                                    <button type="button" 
                                                            class="btn btn-success btn-sm mb-1" 
                                                            wire:click="addSelect2"
                                                            title="Tambah Pemeriksaan"
                                                            style="min-width: 40px;">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                    @if($i > 0)
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm" 
                                                            wire:click="removeSelect2({{ $i }})"
                                                            title="Hapus Pemeriksaan"
                                                            style="min-width: 40px;">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endfor
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end pt-3 border-top">
                        <button class="btn btn-primary btn-lg px-4" type="submit">
                            <i class="fas fa-save mr-2"></i> Simpan Permintaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Daftar Permintaan -->
        <div class="card card-outline card-info">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-list mr-2"></i> Daftar Permintaan Radiologi
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 15%;">No. Order</th>
                                <th style="width: 20%;">Informasi</th>
                                <th style="width: 25%;">Klinis</th>
                                <th style="width: 30%;">Pemeriksaan</th>
                                <th style="width: 10%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($permintaanRad as $item)
                                <tr>
                                    <td>
                                        <span class="badge badge-info">
                                            <i class="fas fa-hashtag mr-1"></i>{{ $item->noorder }}
                                        </span>
                                    </td>
                                    <td>{{ $item->informasi_tambahan }}</td>
                                    <td>{{ $item->diagnosa_klinis }}</td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach ($this->getDetailPemeriksaan($item->noorder) as $pemeriksaan)
                                                <span class="badge badge-primary">
                                                    <i class="fas fa-x-ray mr-1"></i>{{ $pemeriksaan->nm_perawatan }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-danger btn-sm" 
                                                wire:click="deletePermintaanRadiologi('{{ $item->noorder }}')"
                                                onclick="return confirm('Apakah anda yakin ingin menghapus permintaan ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="empty-state-radiologi">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">Belum ada permintaan radiologi</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('css')
<style>
    .select2-radiologi-wrapper {
        min-height: 60px;
    }
    
    .select2-item-wrapper {
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .select2-item-wrapper .card {
        transition: all 0.3s ease;
    }
    
    .select2-item-wrapper .card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .select2-item-wrapper .border-left-primary {
        border-left: 4px solid #007bff !important;
    }
    
    .empty-state-radiologi {
        padding: 2rem;
    }
    
    .table th {
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge {
        font-size: 0.85rem;
        padding: 0.4rem 0.75rem;
    }
    
    .btn-group-vertical .btn {
        border-radius: 4px;
    }
    
    .form-label {
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #495057;
    }
    
    .card-header.bg-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    }
    
    .card-header.bg-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
    }
</style>
@endpush

@push('js')
<script>
    (function() {
        var $ = window.jQuery || window.$;
        if (!$) {
            console.error('jQuery belum dimuat');
            return;
        }

        window.addEventListener('swal', function(e){
            Swal.fire(e.detail);
        });

        // Track select2 yang sudah diinisialisasi
        window.select2Initialized = window.select2Initialized || {};

        // Fungsi untuk initialize select2
        window.initRadiologiSelect2 = function(index) {
            var selector = '#jenis_rad_' + index;
            var $select = $(selector);
            
            if (!$select.length) {
                return false;
            }
            
            // Skip jika sudah diinisialisasi dan masih valid
            if (window.select2Initialized[index] && $select.hasClass('select2-hidden-accessible')) {
                return true;
            }
            
            // Hapus select2 jika sudah diinisialisasi tapi tidak valid
            if ($select.hasClass('select2-hidden-accessible')) {
                try {
                    $select.select2('destroy');
                } catch(e) {
                    // Ignore error jika select2 sudah di-destroy
                }
            }

            try {
                $select.select2({
                    placeholder: 'Pilih Jenis Pemeriksaan',
                    allowClear: true,
                    width: '100%',
                    ajax: {
                        url: '/api/jns_perawatan_rad',
                        dataType: 'json',
                        delay: 250,
                        processResults: function (data) {
                            return {
                                results: data
                            };
                        },
                        cache: true
                    },
                    templateResult: function(data) {
                        if (data.loading) {
                            return data.text;
                        }
                        return $('<b>'+ data.id +'</b> - <i>'+ data.text +'</i>');
                    },
                    templateSelection: function(data) {
                        return data.text || data.id;
                    },
                    minimumInputLength: 3
                });

                // Remove existing handlers untuk menghindari duplicate
                $select.off('select2:select select2:clear');

                // Handle change event dengan SweetAlert konfirmasi
                $select.on('select2:select', function (e) {
                    var selectedValue = e.params.data.id;
                    var selectedText = e.params.data.text;
                    var currentIndex = parseInt($(this).data('index'));
                    
                    // Set nilai ke Livewire
                    @this.call('setJnsPemeriksaan', currentIndex, selectedValue);
                    
                    // Tampilkan SweetAlert konfirmasi untuk menambah select2 baru
                    Swal.fire({
                        title: 'Jenis Pemeriksaan Dipilih',
                        html: '<div class="text-left"><strong class="text-primary">' + selectedText + '</strong> telah dipilih.</div><br>Apakah anda ingin menambahkan jenis pemeriksaan lagi?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="fas fa-plus"></i> Ya, Tambah Lagi',
                        cancelButtonText: '<i class="fas fa-times"></i> Tidak',
                        reverseButtons: true,
                        focusConfirm: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Tambah select2 baru
                            @this.call('addSelect2');
                        }
                    });
                });

                // Handle clear event
                $select.on('select2:clear', function (e) {
                    var currentIndex = parseInt($(this).data('index'));
                    @this.call('setJnsPemeriksaan', currentIndex, null);
                });

                window.select2Initialized[index] = true;
                return true;
            } catch(e) {
                console.error('Error initializing select2:', e);
                return false;
            }
        };

        // Initialize semua select2 yang belum diinisialisasi
        window.reinitializeAllSelect2 = function() {
            $('.jenis-radiologi-select').each(function() {
                var index = parseInt($(this).data('index'));
                if (!isNaN(index)) {
                    window.initRadiologiSelect2(index);
                }
            });
        };

        // Initialize select2 pertama saat document ready
        $(document).ready(function() {
            setTimeout(function() {
                window.reinitializeAllSelect2();
            }, 600);
        });

        // Handle Livewire events - lebih agresif untuk memastikan select2 muncul
        if (typeof Livewire !== 'undefined') {
            // Reinitialize setelah Livewire update
            Livewire.hook('message.processed', function(message, component) {
                setTimeout(function() {
                    window.reinitializeAllSelect2();
                }, 500);
            });
            
            // Reinitialize setelah Livewire component di-update
            Livewire.hook('element.updated', function(el, component) {
                // Check jika ada select2 di dalam element yang di-update
                var hasSelect2 = $(el).find('.jenis-radiologi-select').length > 0 || 
                                 $(el).hasClass('select2-item-wrapper') ||
                                 $(el).closest('.select2-item-wrapper').length > 0;
                
                if (hasSelect2) {
                    setTimeout(function() {
                        window.reinitializeAllSelect2();
                    }, 500);
                }
            });
            
            // Hook untuk setelah DOM di-update
            Livewire.hook('morph.updated', function(el) {
                if ($(el).find('.jenis-radiologi-select').length > 0) {
                    setTimeout(function() {
                        window.reinitializeAllSelect2();
                    }, 500);
                }
            });
        }
        
        // Handle Livewire DOM updates
        document.addEventListener('livewire:load', function() {
            setTimeout(function() {
                window.reinitializeAllSelect2();
            }, 800);
        });
        
        document.addEventListener('livewire:update', function() {
            setTimeout(function() {
                window.reinitializeAllSelect2();
            }, 500);
        });
        
        // Observer untuk mendeteksi perubahan DOM
        if (window.MutationObserver) {
            var observer = new MutationObserver(function(mutations) {
                var shouldReinit = false;
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length > 0) {
                        $(mutation.addedNodes).each(function() {
                            if ($(this).find('.jenis-radiologi-select').length > 0 || 
                                $(this).hasClass('select2-item-wrapper')) {
                                shouldReinit = true;
                            }
                        });
                    }
                });
                
                if (shouldReinit) {
                    setTimeout(function() {
                        window.reinitializeAllSelect2();
                    }, 500);
                }
            });
            
            // Observe container setelah DOM ready
            $(document).ready(function() {
                var container = document.getElementById('select2-container-radiologi');
                if (container) {
                    observer.observe(container, {
                        childList: true,
                        subtree: true
                    });
                }
            });
        }

        // Handle reset form
        window.addEventListener('select2Rad:reset', function() {
            // Destroy semua select2 yang ada
            $('.jenis-radiologi-select').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    try {
                        $(this).select2('destroy');
                    } catch(e) {}
                }
            });
            
            // Reset tracking
            window.select2Initialized = {};
            
            // Reinitialize select2 pertama setelah reset
            setTimeout(function() {
                window.reinitializeAllSelect2();
            }, 300);
        });

        // Handle remove select2
        window.addEventListener('select2Rad:remove', function(e) {
            var removedIndex = e.detail.removedIndex;
            var newCount = e.detail.newCount;
            
            // Destroy semua select2 yang ada karena setelah re-index semua index berubah
            // Ini mencegah memory leak dan stale instances
            $('.jenis-radiologi-select').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    try {
                        $(this).select2('destroy');
                    } catch(e) {
                        // Ignore error jika select2 sudah di-destroy
                    }
                }
            });
            
            // Reset tracking karena semua index berubah setelah re-index
            window.select2Initialized = {};
            
            // Reinitialize semua select2 dengan index yang baru setelah DOM update
            // Delay untuk memastikan Livewire sudah selesai update DOM
            setTimeout(function() {
                window.reinitializeAllSelect2();
            }, 400);
        });

        // Handle add select2 event
        window.addEventListener('select2Rad:add', function(e) {
            var index = e.detail.index;
            // Coba beberapa kali dengan delay yang berbeda untuk memastikan DOM sudah siap
            setTimeout(function() {
                window.initRadiologiSelect2(index);
            }, 300);
            setTimeout(function() {
                window.initRadiologiSelect2(index);
            }, 600);
            setTimeout(function() {
                window.initRadiologiSelect2(index);
            }, 1000);
        });
        
        // Listen untuk Livewire component update events
        document.addEventListener('livewire:component-updated', function(event) {
            setTimeout(function() {
                window.reinitializeAllSelect2();
            }, 500);
        });

    })();
</script>
@endpush
