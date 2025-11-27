<div>
    <!-- Filter Card -->
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i> Filter Pencarian
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tanggal_mulai"><strong>Tanggal Mulai:</strong></label>
                        <input type="date" 
                               wire:model="tanggalMulai" 
                               id="tanggal_mulai" 
                               class="form-control" 
                               required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tanggal_akhir"><strong>Tanggal Akhir:</strong></label>
                        <input type="date" 
                               wire:model="tanggalAkhir" 
                               id="tanggal_akhir" 
                               class="form-control" 
                               required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button wire:click="resetFilter" class="btn btn-secondary btn-block">
                                <i class="fas fa-redo"></i> Reset Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-walking"></i> Rawat Jalan (Ralan)
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-success">
                                    <i class="fas fa-list"></i>
                                </span>
                                <h5 class="description-header">{{ number_format($totalRalanTindakan) }}</h5>
                                <span class="description-text">Total Tindakan</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="description-block">
                                <span class="description-percentage text-primary">
                                    <i class="fas fa-money-bill-wave"></i>
                                </span>
                                <h5 class="description-header">Rp {{ number_format($totalRalan, 0, ',', '.') }}</h5>
                                <span class="description-text">Total Biaya</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bed"></i> Rawat Inap (Ranap)
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-success">
                                    <i class="fas fa-list"></i>
                                </span>
                                <h5 class="description-header">{{ number_format($totalRanapTindakan) }}</h5>
                                <span class="description-text">Total Tindakan</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="description-block">
                                <span class="description-percentage text-primary">
                                    <i class="fas fa-money-bill-wave"></i>
                                </span>
                                <h5 class="description-header">Rp {{ number_format($totalRanap, 0, ',', '.') }}</h5>
                                <span class="description-text">Total Biaya</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs untuk Ralan dan Ranap -->
    <div class="card card-primary card-tabs">
        <div class="card-header p-0 pt-1">
            <ul class="nav nav-tabs" id="custom-tabs-tindakan-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'ralan' ? 'active' : '' }}" 
                       wire:click="setActiveTab('ralan')" 
                       href="#" 
                       role="tab">
                        <i class="fas fa-walking"></i> Rawat Jalan 
                        @if($activeTab === 'ralan' && $ralanGrouped->count() > 0)
                        <span class="badge badge-light ml-2">{{ $ralanGrouped->total() }} Pasien</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'ranap' ? 'active' : '' }}" 
                       wire:click="setActiveTab('ranap')" 
                       href="#" 
                       role="tab">
                        <i class="fas fa-bed"></i> Rawat Inap 
                        @if($activeTab === 'ranap' && $ranapGrouped->count() > 0)
                        <span class="badge badge-light ml-2">{{ $ranapGrouped->total() }} Pasien</span>
                        @endif
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <!-- Tab Ralan -->
                @if($activeTab === 'ralan')
                <div class="tab-pane fade show active">
                    @if($ralanGrouped->count() > 0)
                        @foreach($ralanGrouped as $pasien)
                        <div class="card mb-4 border-left-info" style="border-left-width: 4px;">
                            <div class="card-header bg-info text-white">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <div>
                                        <h5 class="mb-1">
                                            <i class="fas fa-user"></i> {{ $pasien['nm_pasien'] }}
                                        </h5>
                                        <small class="text-white-50">
                                            <i class="fas fa-id-card"></i> No. RM: {{ $pasien['no_rkm_medis'] }} | 
                                            <i class="fas fa-book-medical"></i> No. Rawat: {{ $pasien['no_rawat'] }} |
                                            <i class="fas fa-hospital"></i> {{ $pasien['nm_poli'] }} |
                                            <i class="fas fa-wallet"></i> {{ $pasien['png_jawab'] }}
                                        </small>
                                    </div>
                                    <div class="mt-2 mt-md-0">
                                        <span class="badge badge-light" style="font-size: 0.9rem; padding: 0.5rem 0.75rem;">
                                            <i class="fas fa-list"></i> {{ $pasien['total_tindakan'] }} Tindakan | 
                                            <i class="fas fa-money-bill-wave"></i> Rp {{ number_format($pasien['total_biaya'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover table-striped mb-0">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th style="width: 5%;">No</th>
                                                <th style="width: 30%;">Nama Tindakan</th>
                                                <th style="width: 18%;">Dokter</th>
                                                <th style="width: 15%;">Tanggal</th>
                                                <th style="width: 12%;">Jam</th>
                                                <th style="width: 20%;" class="text-right">Tarif Tindakan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pasien['tindakan'] as $tindakan)
                                            @php
                                            $tglTindakan = date_create($tindakan->tgl_perawatan ?? '0000-00-00');
                                            $dateTindakan = date_format($tglTindakan,"d M Y");
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <strong class="text-primary">
                                                        <i class="fas fa-procedures"></i> {{ $tindakan->nm_perawatan }}
                                                    </strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-code"></i> {{ $tindakan->kd_jenis_prw }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <i class="fas fa-user-md text-info"></i> {{ $tindakan->nm_dokter }}
                                                </td>
                                                <td>
                                                    <i class="fas fa-calendar text-muted"></i> {{ $dateTindakan }}
                                                </td>
                                                <td>
                                                    <i class="fas fa-clock text-muted"></i> {{ $tindakan->jam_rawat ?? '-' }}
                                                </td>
                                                <td class="text-right">
                                                    <strong class="text-success">
                                                        Rp {{ number_format($tindakan->tarif_tindakandr ?? 0, 0, ',', '.') }}
                                                    </strong>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="thead-light">
                                            <tr>
                                                <th colspan="5" class="text-right">
                                                    <strong>Subtotal:</strong>
                                                </th>
                                                <th class="text-right">
                                                    <strong class="text-success">
                                                        Rp {{ number_format($pasien['total_biaya'], 0, ',', '.') }}
                                                    </strong>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $ralanGrouped->links() }}
                        </div>
                    @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Tidak ada data tindakan dokter untuk periode yang dipilih.
                    </div>
                    @endif
                </div>
                @endif

                <!-- Tab Ranap -->
                @if($activeTab === 'ranap')
                <div class="tab-pane fade show active">
                    @if($ranapGrouped->count() > 0)
                        @foreach($ranapGrouped as $pasien)
                        <div class="card mb-4 border-left-success" style="border-left-width: 4px;">
                            <div class="card-header bg-success text-white">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <div>
                                        <h5 class="mb-1">
                                            <i class="fas fa-user"></i> {{ $pasien['nm_pasien'] }}
                                        </h5>
                                        <small class="text-white-50">
                                            <i class="fas fa-id-card"></i> No. RM: {{ $pasien['no_rkm_medis'] }} | 
                                            <i class="fas fa-book-medical"></i> No. Rawat: {{ $pasien['no_rawat'] }} |
                                            @if(isset($pasien['kd_kamar']) && $pasien['kd_kamar'])
                                            <i class="fas fa-door-open"></i> Kamar: {{ $pasien['kd_kamar'] }} | 
                                            @endif
                                            @if(isset($pasien['nm_bangsal']) && $pasien['nm_bangsal'])
                                            <i class="fas fa-building"></i> Bangsal: {{ $pasien['nm_bangsal'] }} |
                                            @endif
                                            <i class="fas fa-wallet"></i> {{ $pasien['png_jawab'] }}
                                        </small>
                                    </div>
                                    <div class="mt-2 mt-md-0">
                                        <span class="badge badge-light" style="font-size: 0.9rem; padding: 0.5rem 0.75rem;">
                                            <i class="fas fa-list"></i> {{ $pasien['total_tindakan'] }} Tindakan | 
                                            <i class="fas fa-money-bill-wave"></i> Rp {{ number_format($pasien['total_biaya'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover table-striped mb-0">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th style="width: 5%;">No</th>
                                                <th style="width: 30%;">Nama Tindakan</th>
                                                <th style="width: 18%;">Dokter</th>
                                                <th style="width: 15%;">Tanggal</th>
                                                <th style="width: 12%;">Jam</th>
                                                <th style="width: 20%;" class="text-right">Tarif Tindakan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pasien['tindakan'] as $tindakan)
                                            @php
                                            $tglTindakan = date_create($tindakan->tgl_perawatan ?? '0000-00-00');
                                            $dateTindakan = date_format($tglTindakan,"d M Y");
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <strong class="text-primary">
                                                        <i class="fas fa-procedures"></i> {{ $tindakan->nm_perawatan }}
                                                    </strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-code"></i> {{ $tindakan->kd_jenis_prw }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <i class="fas fa-user-md text-info"></i> {{ $tindakan->nm_dokter }}
                                                </td>
                                                <td>
                                                    <i class="fas fa-calendar text-muted"></i> {{ $dateTindakan }}
                                                </td>
                                                <td>
                                                    <i class="fas fa-clock text-muted"></i> {{ $tindakan->jam_rawat ?? '-' }}
                                                </td>
                                                <td class="text-right">
                                                    <strong class="text-success">
                                                        Rp {{ number_format($tindakan->tarif_tindakandr ?? 0, 0, ',', '.') }}
                                                    </strong>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="thead-light">
                                            <tr>
                                                <th colspan="5" class="text-right">
                                                    <strong>Subtotal:</strong>
                                                </th>
                                                <th class="text-right">
                                                    <strong class="text-success">
                                                        Rp {{ number_format($pasien['total_biaya'], 0, ',', '.') }}
                                                    </strong>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $ranapGrouped->links() }}
                        </div>
                    @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Tidak ada data tindakan dokter untuk periode yang dipilih.
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div wire:loading 
         wire:target="tanggalMulai, tanggalAkhir, activeTab, setActiveTab, resetFilter"
         class="loading-overlay">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
</div>

@push('js')
<script>
    (function() {
        function checkAndHideOverlay() {
            const overlay = document.querySelector('.loading-overlay');
            if (!overlay) return;
            
            // Check if wire:loading attribute exists and has value
            const hasLoadingAttr = overlay.hasAttribute('wire:loading');
            const loadingValue = overlay.getAttribute('wire:loading');
            
            // Hide overlay if wire:loading is removed, empty, null, or undefined
            if (!hasLoadingAttr || loadingValue === '' || loadingValue === null || loadingValue === undefined || loadingValue === 'null') {
                overlay.style.display = 'none';
                overlay.style.visibility = 'hidden';
                overlay.style.opacity = '0';
                // Remove the attribute completely if it's empty
                if (hasLoadingAttr && (loadingValue === '' || loadingValue === null || loadingValue === 'null')) {
                    overlay.removeAttribute('wire:loading');
                }
            } else {
                overlay.style.display = 'flex';
                overlay.style.visibility = 'visible';
                overlay.style.opacity = '1';
            }
        }
        
        function initLoadingOverlay() {
            const overlay = document.querySelector('.loading-overlay');
            if (!overlay) return;
            
            // Set initial state
            checkAndHideOverlay();
            
            // Use MutationObserver to watch for attribute changes
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'wire:loading') {
                        checkAndHideOverlay();
                    }
                });
            });
            
            // Start observing
            observer.observe(overlay, {
                attributes: true,
                attributeFilter: ['wire:loading']
            });
            
            // Fallback: Check periodically and hide if needed
            const checkInterval = setInterval(function() {
                checkAndHideOverlay();
            }, 300);
            
            // Clean up interval when page unloads
            window.addEventListener('beforeunload', function() {
                clearInterval(checkInterval);
            });
        }
        
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initLoadingOverlay);
        } else {
            initLoadingOverlay();
        }
        
        // Re-initialize after Livewire updates
        document.addEventListener('livewire:load', function() {
            setTimeout(initLoadingOverlay, 100);
        });
        
        document.addEventListener('livewire:update', function() {
            setTimeout(checkAndHideOverlay, 100);
        });
        
        // Also check after any DOM changes
        document.addEventListener('livewire:dom-updated', function() {
            setTimeout(checkAndHideOverlay, 100);
        });
        
        // Handle pagination clicks for Livewire
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            const href = $(this).attr('href');
            if (!href || href === '#' || href === 'javascript:void(0)') return;
            
            // Extract page number from URL
            let page = null;
            try {
                const url = new URL(href, window.location.origin);
                page = url.searchParams.get('page');
            } catch (err) {
                // If URL parsing fails, try regex
                const pageMatch = href.match(/[?&]page=(\d+)/);
                if (pageMatch && pageMatch[1]) {
                    page = pageMatch[1];
                }
            }
            
            if (page) {
                // Scroll to top before updating page
                window.scrollTo({ top: 0, behavior: 'smooth' });
                // Use Livewire to update page
                @this.call('gotoPage', parseInt(page));
            }
        });
        
        // Scroll to top after Livewire updates (when pagination changes)
        document.addEventListener('livewire:update', function() {
            setTimeout(function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }, 100);
        });
        
        // Also scroll to top when Livewire finishes updating DOM
        document.addEventListener('livewire:dom-updated', function() {
            // Check if pagination was clicked
            if (document.querySelector('.pagination a.active')) {
                setTimeout(function() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }, 50);
            }
        });
    })();
</script>
@endpush

<style>
    .description-block {
        text-align: center;
    }
    .description-block .description-percentage {
        font-size: 1.5rem;
        display: block;
        margin-bottom: 0.5rem;
    }
    .description-block .description-header {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0.5rem 0;
    }
    .description-block .description-text {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .nav-link {
        cursor: pointer;
    }
    
    /* Loading overlay - hidden by default */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        display: none !important;
        justify-content: center;
        align-items: center;
        visibility: hidden;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    /* Show overlay only when wire:loading attribute exists and has non-empty value */
    .loading-overlay[wire\:loading]:not([wire\:loading=""]):not([wire\:loading="null"]) {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    /* Force hide when wire:loading is empty, null, or removed */
    .loading-overlay:not([wire\:loading]),
    .loading-overlay[wire\:loading=""],
    .loading-overlay[wire\:loading="null"] {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
    }
</style>






