<div class="berkas-rm-container" wire:key="berkas-rm-{{ $rm ?? 'default' }}">
    @if(!$isInitialized)
    {{-- Placeholder saat belum di-initialize (lazy load) --}}
    <div class="lazy-load-placeholder">
        <div class="placeholder-content">
            <div class="placeholder-icon">
                <i class="fas fa-folder-open fa-4x text-muted"></i>
            </div>
            <h5 class="text-muted mt-3 mb-2">Berkas Rekam Medis</h5>
            <p class="text-muted mb-4">Klik tombol di bawah untuk memuat data berkas</p>
            <button class="btn btn-primary btn-load-berkas" onclick="loadBerkasData()">
                <i class="fas fa-sync-alt"></i> Muat Data Berkas
            </button>
            <p class="text-muted small mt-3 mb-0">
                <i class="fas fa-info-circle"></i> Data akan dimuat saat modal dibuka
            </p>
        </div>
    </div>
    @elseif($isLoading)
    <div class="loading-wrapper">
        <div class="loading-content">
            <div class="spinner-border text-info" role="status" style="width: 3rem; height: 3rem;">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-3 text-muted mb-0">Memuat berkas...</p>
        </div>
    </div>
    @else
    @if(isset($berkasGrouped) && $berkasGrouped && $berkasGrouped->count() > 0)
    
    {{-- Header Info --}}
    <div class="berkas-header mb-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center">
                <i class="fas fa-folder-open text-info mr-2" style="font-size: 1.5rem;"></i>
                <div>
                    <h6 class="mb-0 font-weight-bold">Berkas Rekam Medis</h6>
                    <small class="text-muted">Total: {{ count($berkas) }} berkas dalam {{ $berkasGrouped->count() }} kelompok</small>
                </div>
            </div>
            <div class="berkas-legend d-flex align-items-center mt-2 mt-md-0">
                <span class="badge badge-light mr-2"><i class="fas fa-file-pdf text-danger mr-1"></i> PDF</span>
                <span class="badge badge-light"><i class="fas fa-file-image text-success mr-1"></i> Gambar</span>
            </div>
        </div>
    </div>

    {{-- View Toggle --}}
    <div class="view-toggle mb-3">
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary active" data-view="grid" onclick="toggleBerkasView('grid')" title="Tampilan Grid">
                <i class="fas fa-th-large"></i> <span class="d-none d-sm-inline ml-1">Grid</span>
            </button>
            <button type="button" class="btn btn-outline-secondary" data-view="list" onclick="toggleBerkasView('list')" title="Tampilan List">
                <i class="fas fa-list"></i> <span class="d-none d-sm-inline ml-1">List</span>
            </button>
        </div>
    </div>

    {{-- Grouped Berkas by Kode --}}
    <div class="berkas-groups-container">
        @foreach($berkasGrouped as $groupIndex => $group)
        <div class="berkas-group mb-4" data-group="{{ $group['kode'] }}">
            {{-- Group Header --}}
            <div class="berkas-group-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-folder text-primary mr-2"></i>
                        <h6 class="mb-0 font-weight-bold">{{ $group['nama'] }}</h6>
                        <span class="badge badge-info ml-2">{{ $group['count'] }} berkas</span>
                    </div>
                    <button class="btn btn-sm btn-link text-decoration-none group-toggle" data-target="group-{{ $groupIndex }}">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
            
            {{-- Group Content --}}
            <div class="berkas-group-content collapse show" id="group-{{ $groupIndex }}">
                <div class="berkas-grid" data-group-container="{{ $group['kode'] }}">
                    @foreach($group['berkas'] as $index => $item)
                    @php
                        $isPdf = strtolower(substr($item->lokasi_file, -3)) == 'pdf';
                        $fileName = basename($item->lokasi_file);
                        // Gunakan proxy untuk PDF, langsung untuk gambar
                        if ($isPdf) {
                            // URL encode path file untuk proxy
                            // Pastikan path relatif dari basePath di proxy
                            $fileUrl = "https://simrs.rsbhayangkaranganjuk.com/pdf-proxy.php?file=" . urlencode($item->lokasi_file);
                        } else {
                            $fileUrl = "https://simrs.rsbhayangkaranganjuk.com/webapps/berkasrawat/{$item->lokasi_file}";
                        }
                        $fileExt = strtoupper(pathinfo($item->lokasi_file, PATHINFO_EXTENSION));
                        $fileDate = isset($item->created_at) ? \Carbon\Carbon::parse($item->created_at)->isoFormat('DD MMM YYYY') : '-';
                    @endphp
                    
                    <div class="berkas-card {{ $isPdf ? 'is-pdf' : 'is-image' }}" data-file-url="{{ $fileUrl }}" data-file-type="{{ $isPdf ? 'pdf' : 'image' }}" data-file-name="{{ $fileName }}">
                        {{-- Thumbnail Preview --}}
                        <div class="thumbnail-wrapper">
                            <span class="berkas-badge">{{ $index + 1 }}</span>
                            <span class="file-ext-badge {{ $isPdf ? 'badge-pdf' : 'badge-img' }}">{{ $fileExt }}</span>
                            
                @if($isPdf)
                <div class="thumbnail-pdf" data-url="{{ $fileUrl }}" data-title="{{ $fileName }}">
                    {{-- PDF Preview Canvas menggunakan PDF.js - lazy load saat scroll --}}
                    <canvas 
                        class="pdf-thumb lazy-pdf" 
                        data-src="{{ $fileUrl }}"
                        data-loaded="false">
                    </canvas>
                    {{-- PDF Icon untuk fallback --}}
                    <div class="pdf-preview-icon">
                        <i class="fas fa-file-pdf"></i>
                    </div>
                    {{-- Loading indicator --}}
                    <div class="pdf-loading">
                        <div class="spinner-border spinner-border-sm text-danger" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    <div class="pdf-overlay">
                        <i class="fas fa-search-plus"></i>
                        <span>Klik untuk membuka</span>
                    </div>
                </div>
                @else
                            <div class="thumbnail-image">
                                <a href="{{ $fileUrl }}" class="lightbox-trigger" data-gallery="berkas-gallery" data-width="1280" data-height="700" data-title="{{ $fileName }}" onclick="return false;">
                                    <img data-src="{{ $fileUrl }}" alt="{{ $fileName }}" loading="lazy" class="lazy-image" onerror="this.onerror=null; this.src='/images/no-image.png'; this.parentElement.classList.add('img-error');">
                                    <div class="thumbnail-overlay">
                                        <i class="fas fa-search-plus"></i>
                                        <span>Klik untuk memperbesar</span>
                                    </div>
                                </a>
                            </div>
                            @endif
                        </div>
                        
                        {{-- File Info --}}
            <div class="file-info">
                <div class="file-info-main">
                    <div class="file-icon">
                        @if($isPdf)
                            <i class="fas fa-file-pdf text-danger"></i>
                        @else
                            <i class="fas fa-file-image text-success"></i>
                        @endif
                    </div>
                    <div class="file-details">
                        <span class="file-name" title="{{ $fileName }}">{{ $fileName }}</span>
                        <span class="file-meta">
                            <i class="far fa-calendar-alt mr-1"></i>{{ $fileDate }}
                        </span>
                    </div>
                </div>
            </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- PDF Viewer Modal --}}
    <div class="pdf-viewer-overlay" id="pdfViewerOverlay" style="display: none;">
        <div class="pdf-viewer-container">
            <div class="pdf-viewer-header">
                <span class="pdf-viewer-title">Dokumen PDF</span>
                <div class="pdf-viewer-actions">
                    <a href="#" id="pdfViewerDownload" class="pdf-viewer-download" target="_blank" download>
                        <i class="fas fa-download"></i>
                    </a>
                    <a href="#" id="pdfViewerOpenNew" class="pdf-viewer-open-new" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                    <button class="pdf-viewer-close" onclick="closePdfViewer()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="pdf-viewer-content">
                <div id="pdfViewerCanvasContainer" style="width: 100%; height: 100%; overflow: auto; position: relative;">
                    <canvas id="pdfViewerCanvas"></canvas>
                </div>
                {{-- Dock kontrol fixed position di luar container --}}
                <div id="pdfViewerControls" class="pdf-viewer-controls">
                    <button id="pdfViewerPrev" class="pdf-control-btn" title="Halaman Sebelumnya">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span id="pdfViewerPageInfo" class="pdf-page-info">Halaman <span id="pdfCurrentPage">1</span> dari <span id="pdfTotalPages">1</span></span>
                    <button id="pdfViewerNext" class="pdf-control-btn" title="Halaman Berikutnya">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <div class="pdf-control-separator"></div>
                    <button id="pdfViewerZoomOut" class="pdf-control-btn" title="Zoom Out (Ctrl + Scroll untuk zoom dengan mouse)">
                        <i class="fas fa-search-minus"></i>
                    </button>
                    <span id="pdfViewerZoomInfo" class="pdf-zoom-info"><span id="pdfZoomLevel">100</span>%</span>
                    <button id="pdfViewerZoomIn" class="pdf-control-btn" title="Zoom In (Ctrl + Scroll untuk zoom dengan mouse)">
                        <i class="fas fa-search-plus"></i>
                    </button>
                </div>
                <p class="pdf-viewer-fallback" id="pdfViewerFallback" style="display: none;">
                    Browser Anda tidak mendukung preview PDF. 
                    <a href="#" id="pdfViewerFallbackLink" target="_blank">Klik di sini untuk membuka PDF</a>
                </p>
            </div>
        </div>
    </div>

    @else
    <div class="empty-state">
        <i class="fas fa-folder-open"></i>
        <h5>Tidak Ada Berkas</h5>
        <p class="text-muted mb-0">Belum ada berkas rekam medis yang tersedia untuk pasien ini.</p>
    </div>
    @endif
    @endif
</div>

@section('plugins.EkkoLightBox', true)

@push('css')
<style>
    .lightbox {
        z-index: 100000;
    }
    
    /* Container */
    .berkas-rm-container {
        min-height: 200px;
    }
    
    /* Loading */
    .loading-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 300px;
    }
    .loading-content {
        text-align: center;
    }
    
    /* Header */
    .berkas-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1rem;
        border-radius: 8px;
        border-left: 4px solid #17a2b8;
    }
    .berkas-legend .badge {
        font-weight: normal;
        padding: 0.4rem 0.6rem;
    }
    
    /* View Toggle */
    .view-toggle {
        display: flex;
        justify-content: flex-end;
    }
    .view-toggle .btn.active {
        background: #17a2b8;
        color: #fff;
        border-color: #17a2b8;
    }
    
    /* Groups Container */
    .berkas-groups-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    /* Group Header */
    .berkas-group {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
        border: 1px solid #e9ecef;
    }
    .berkas-group-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1rem 1.25rem;
        border-bottom: 2px solid #dee2e6;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .berkas-group-header:hover {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    }
    .berkas-group-header h6 {
        color: #2c3e50;
        font-size: 1rem;
    }
    .berkas-group-header .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
    }
    .group-toggle {
        color: #6c757d;
        transition: transform 0.3s ease;
    }
    .group-toggle:hover {
        color: #495057;
    }
    .group-toggle[aria-expanded="false"] i {
        transform: rotate(-90deg);
    }
    .berkas-group-content {
        padding: 1.25rem;
    }
    
    /* Grid View */
    .berkas-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.25rem;
    }
    
    /* List View */
    .berkas-grid.list-view {
        display: flex !important;
        flex-direction: column !important;
        gap: 0.75rem !important;
        grid-template-columns: none !important;
    }
    .berkas-groups-container .berkas-grid.list-view {
        display: flex !important;
        flex-direction: column !important;
    }
    .berkas-grid.list-view .berkas-card {
        display: flex !important;
        flex-direction: row !important;
        align-items: stretch !important;
        border-top: none !important;
        border-left: 4px solid;
    }
    .berkas-grid.list-view .berkas-card.is-pdf {
        border-left-color: #dc3545;
    }
    .berkas-grid.list-view .berkas-card.is-image {
        border-left-color: #28a745;
    }
    .berkas-grid.list-view .berkas-card:hover {
        transform: translateX(4px);
    }
    .berkas-grid.list-view .thumbnail-wrapper {
        width: 120px !important;
        min-width: 120px !important;
        max-width: 120px !important;
        height: 90px !important;
        border-radius: 0 !important;
        flex-shrink: 0;
    }
    .berkas-grid.list-view .thumbnail-pdf,
    .berkas-grid.list-view .thumbnail-image {
        width: 100%;
        height: 100%;
    }
    .berkas-grid.list-view .berkas-badge {
        width: 20px;
        height: 20px;
        font-size: 0.65rem;
        top: 5px;
        left: 5px;
    }
    .berkas-grid.list-view .file-ext-badge {
        top: 5px;
        right: 5px;
        font-size: 0.55rem;
        padding: 0.15rem 0.35rem;
    }
    .berkas-grid.list-view .thumbnail-pdf .pdf-icon-wrapper > i {
        font-size: 2rem;
    }
    .berkas-grid.list-view .pdf-lines {
        display: none;
    }
    .berkas-grid.list-view .thumbnail-actions {
        display: none;
    }
    .berkas-grid.list-view .thumbnail-overlay span {
        display: none;
    }
    .berkas-grid.list-view .thumbnail-overlay i {
        font-size: 1.5rem;
    }
    .berkas-grid.list-view .file-info {
        flex: 1 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 0.75rem 1rem !important;
        border-top: none !important;
    }
    .berkas-grid.list-view .file-info-main {
        flex: 1;
        margin-bottom: 0 !important;
    }
    .berkas-grid.list-view .file-icon {
        width: 36px;
        height: 36px;
    }
    .berkas-grid.list-view .file-details {
        flex-direction: row !important;
        gap: 1.5rem !important;
        align-items: center !important;
    }
    .berkas-grid.list-view .file-name {
        max-width: 350px;
        font-size: 0.9rem;
    }
    .berkas-grid.list-view .file-meta {
        white-space: nowrap;
    }
    .berkas-grid.list-view .file-actions {
        flex-shrink: 0;
    }
    
    /* Card */
    .berkas-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
        user-select: none;
        -webkit-user-select: none;
    }
    .berkas-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .berkas-card:active {
        transform: translateY(-2px);
    }
    .berkas-card.is-pdf {
        border-top: 4px solid #dc3545;
    }
    .berkas-card.is-image {
        border-top: 4px solid #28a745;
    }
    
    /* Thumbnail Wrapper */
    .thumbnail-wrapper {
        position: relative;
        height: 200px;
        background: linear-gradient(145deg, #f5f7fa 0%, #e4e8ec 100%);
        overflow: hidden;
    }
    
    /* Badges */
    .berkas-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: #17a2b8;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 700;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }
    .file-ext-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        z-index: 10;
    }
    .file-ext-badge.badge-pdf {
        background: #dc3545;
        color: #fff;
    }
    .file-ext-badge.badge-img {
        background: #28a745;
        color: #fff;
    }
    
    /* Thumbnail PDF */
    .thumbnail-pdf {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: linear-gradient(145deg, #fff5f5 0%, #ffe8e8 100%);
        position: relative;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
        user-select: none;
        -webkit-user-select: none;
    }
    .pdf-preview-icon {
        display: none; /* Hidden by default, shown on mobile via media query */
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        z-index: 1;
        position: absolute;
        top: 0;
        left: 0;
        background: linear-gradient(145deg, #fff5f5 0%, #ffe8e8 100%);
    }
    .pdf-preview-icon i {
        font-size: 4rem;
        color: #dc3545;
        opacity: 0.8;
    }
    .pdf-loading {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 3;
    }
    .pdf-loading.show {
        display: block;
    }
    .pdf-thumb {
        width: 100%;
        height: 100%;
        border: none;
        background: #fff;
        pointer-events: none; /* biar klik masuk ke container, bukan canvas */
        display: block;
        position: relative;
        z-index: 0;
        opacity: 0;
        transition: opacity 0.3s ease;
        object-fit: contain;
    }
    .pdf-thumb.loaded {
        opacity: 1;
    }
    /* Canvas untuk PDF.js thumbnail */
    .pdf-thumb canvas {
        width: 100% !important;
        height: 100% !important;
        object-fit: contain;
        display: block;
    }
    /* PDF preview dengan lazy loading - sembunyikan jika belum dimuat */
    .pdf-thumb.lazy-pdf[data-loaded="false"] {
        display: none;
    }
    .pdf-thumb.lazy-pdf[data-loaded="error"] {
        display: none;
    }
    .pdf-thumb.lazy-pdf[data-loaded="true"] {
        display: block;
        opacity: 0;
    }
    .pdf-thumb.lazy-pdf[data-loaded="true"].loaded {
        opacity: 1;
    }
    /* Tampilkan icon PDF saat belum dimuat */
    .thumbnail-pdf .pdf-preview-icon {
        display: flex;
    }
    /* Sembunyikan icon saat PDF sudah dimuat */
    .thumbnail-pdf .pdf-thumb[data-loaded="true"] ~ .pdf-preview-icon,
    .thumbnail-pdf .pdf-thumb.loaded ~ .pdf-preview-icon {
        display: none !important;
    }
    /* Fallback dengan :has() untuk browser modern */
    @supports selector(:has(*)) {
        .thumbnail-pdf:has(.pdf-thumb[data-loaded="false"]) .pdf-preview-icon,
        .thumbnail-pdf:has(.pdf-thumb[data-loaded="error"]) .pdf-preview-icon {
            display: flex !important;
        }
        .thumbnail-pdf:has(.pdf-thumb[data-loaded="true"]) .pdf-preview-icon {
            display: none !important;
        }
    }
    .pdf-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.1) 50%, transparent 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: auto;
        cursor: pointer;
        z-index: 2;
    }
    .pdf-overlay i {
        color: #fff;
        font-size: 2rem;
        margin-bottom: 0.25rem;
    }
    .pdf-overlay span {
        color: #fff;
        font-size: 0.8rem;
    }
    .thumbnail-pdf:hover .pdf-overlay,
    .thumbnail-pdf:active .pdf-overlay {
        opacity: 1;
    }
    /* List view PDF thumbnail - tampilkan preview di semua device */
    .berkas-grid.list-view .thumbnail-pdf {
        display: block !important;
    }
    .berkas-grid.list-view .thumbnail-pdf .pdf-preview-icon {
        display: none !important;
    }
    .berkas-grid.list-view .thumbnail-pdf .pdf-thumb {
        display: block !important;
        visibility: visible !important;
    }
    /* List view image thumbnail - tampilkan preview di semua device */
    .berkas-grid.list-view .thumbnail-image {
        display: block !important;
    }
    .berkas-grid.list-view .thumbnail-image img {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    /* Thumbnail Image - lazy loading */
    .thumbnail-image {
        width: 100%;
        height: 100%;
        position: relative;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
        user-select: none;
        -webkit-user-select: none;
        display: block !important;
    }
    .thumbnail-image a {
        display: block;
        width: 100%;
        height: 100%;
        text-decoration: none;
        -webkit-tap-highlight-color: transparent;
    }
    .thumbnail-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease, opacity 0.3s ease;
        display: block !important;
        pointer-events: none;
    }
    /* Lazy image - sembunyikan sampai dimuat */
    .thumbnail-image img.lazy-image:not([data-loaded="true"]) {
        opacity: 0;
        visibility: hidden;
    }
    .thumbnail-image img.lazy-image[data-loaded="true"] {
        opacity: 1;
        visibility: visible;
    }
    /* List view image thumbnail - selalu tampil */
    .berkas-grid.list-view .thumbnail-image {
        display: block !important;
    }
    .berkas-grid.list-view .thumbnail-image img {
        object-fit: cover;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    /* Desktop & Mobile: container selalu tampil, preview menggunakan lazy loading */
    @media (min-width: 769px) {
        .thumbnail-image {
            display: block !important;
        }
    }
    @media (max-width: 768px) {
        .thumbnail-image {
            display: block !important;
        }
    }
    .thumbnail-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.2) 50%, transparent 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .thumbnail-overlay i {
        color: #fff;
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        transform: scale(0.8);
        transition: transform 0.3s ease;
    }
    .thumbnail-overlay span {
        color: #fff;
        font-size: 0.8rem;
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease 0.1s;
    }
    .thumbnail-image:hover .thumbnail-overlay {
        opacity: 1;
    }
    .thumbnail-image:hover .thumbnail-overlay i {
        transform: scale(1);
    }
    .thumbnail-image:hover .thumbnail-overlay span {
        opacity: 1;
        transform: translateY(0);
    }
    .thumbnail-image:hover img {
        transform: scale(1.1);
    }
    .thumbnail-image.img-error {
        background: #f8f9fa;
    }
    .thumbnail-image.img-error img {
        object-fit: contain;
        padding: 1rem;
    }
    
    /* File Info */
    .file-info {
        padding: 1rem;
        background: #fff;
        border-top: 1px solid #f0f0f0;
    }
    .file-info-main {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }
    .file-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        flex-shrink: 0;
    }
    .file-icon i {
        font-size: 1.25rem;
    }
    .file-details {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .file-name {
        font-size: 0.875rem;
        font-weight: 600;
        color: #2c3e50;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }
    .file-meta {
        font-size: 0.75rem;
        color: #6c757d;
        display: flex;
        align-items: center;
    }
    .file-actions {
        display: flex;
        gap: 0.5rem;
        justify-content: flex-end;
    }
    .file-actions .btn-xs {
        padding: 0.35rem 0.65rem;
        font-size: 0.75rem;
        border-radius: 6px;
    }
    
    /* PDF Viewer */
    .pdf-viewer-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.9);
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        -webkit-overflow-scrolling: touch;
        overflow-y: auto;
    }
    .pdf-viewer-container {
        width: 100%;
        max-width: 1400px;
        height: 95vh;
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 60px rgba(0,0,0,0.4);
    }
    .pdf-viewer-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.25rem;
        background: linear-gradient(135deg, #343a40 0%, #23272b 100%);
        color: #fff;
        flex-shrink: 0;
    }
    .pdf-viewer-title {
        font-weight: 600;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .pdf-viewer-title::before {
        content: '\f1c1';
        font-family: 'Font Awesome 5 Free';
        font-weight: 400;
        color: #dc3545;
        flex-shrink: 0;
    }
    .pdf-viewer-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-left: 1rem;
    }
    .pdf-viewer-download,
    .pdf-viewer-open-new {
        background: rgba(255,255,255,0.1);
        border: none;
        color: #fff;
        font-size: 1rem;
        cursor: pointer;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        transition: all 0.2s;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .pdf-viewer-download:hover,
    .pdf-viewer-open-new:hover {
        background: rgba(255,255,255,0.2);
        color: #fff;
        text-decoration: none;
    }
    .pdf-viewer-close {
        background: rgba(255,255,255,0.1);
        border: none;
        color: #fff;
        font-size: 1.25rem;
        cursor: pointer;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .pdf-viewer-close:hover {
        background: rgba(255,255,255,0.2);
    }
    .pdf-viewer-content {
        flex: 1;
        width: 100%;
        position: relative;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
        min-height: 0;
    }
    /* PDF.js Canvas Container */
    #pdfViewerCanvasContainer {
        background: #525252;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        padding: 20px;
        padding-bottom: 90px; /* Space untuk fixed dock controls */
        min-height: 100%;
        height: 100%;
        position: relative;
        overflow-y: auto;
        overflow-x: auto;
        cursor: default;
    }
    
    #pdfViewerCanvas {
        max-width: none; /* Allow canvas to grow beyond container */
        max-height: none; /* Allow canvas to grow beyond container */
        width: auto !important;
        height: auto !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        background: #fff;
        display: block !important;
        margin: 20px auto;
        visibility: visible !important;
        opacity: 1 !important;
        cursor: default;
        user-select: none;
        -webkit-user-select: none;
    }
    
    /* Canvas saat zoom - pastikan bisa di-scroll */
    #pdfViewerCanvasContainer:has(#pdfViewerCanvas[style*="width"]) {
        overflow: auto;
    }
    
    /* PDF Viewer Controls - Fixed Position Dock */
    .pdf-viewer-controls {
        position: fixed !important;
        bottom: 20px !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(0,0,0,0.85);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        padding: 0.75rem 1.25rem;
        border-radius: 30px;
        z-index: 100001 !important; /* Di atas modal overlay (99999) */
        box-shadow: 0 4px 20px rgba(0,0,0,0.5);
        transition: all 0.3s ease;
        pointer-events: auto;
        /* Pastikan dock selalu terlihat */
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    /* Separator antara page controls dan zoom controls */
    .pdf-control-separator {
        width: 1px;
        height: 24px;
        background: rgba(255,255,255,0.3);
        margin: 0 0.25rem;
    }
    
    .pdf-control-btn {
        background: rgba(255,255,255,0.2);
        border: none;
        color: #fff;
        padding: 0.4rem 0.6rem;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.9rem;
    }
    
    .pdf-control-btn:hover:not(:disabled) {
        background: rgba(255,255,255,0.3);
    }
    
    .pdf-control-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .pdf-page-info,
    .pdf-zoom-info {
        color: #fff;
        font-size: 0.85rem;
        padding: 0 0.5rem;
        white-space: nowrap;
        font-weight: 500;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }
    
    /* Hover effect untuk dock */
    .pdf-viewer-controls:hover {
        background: rgba(0,0,0,0.95);
        box-shadow: 0 6px 25px rgba(0,0,0,0.7);
        transform: translateX(-50%) translateY(-2px) !important;
    }
    
    /* Pastikan dock tidak terpengaruh oleh transform canvas */
    .pdf-viewer-content {
        position: relative;
    }
    
    /* Animasi saat dock muncul */
    @keyframes dockFadeIn {
        from {
            opacity: 0;
            transform: translateX(-50%) translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }
    
    .pdf-viewer-controls {
        animation: dockFadeIn 0.3s ease-out;
    }
    
    /* Hover effect untuk dock */
    .pdf-viewer-controls:hover {
        background: rgba(0,0,0,0.95) !important;
        box-shadow: 0 6px 25px rgba(0,0,0,0.7) !important;
        transform: translateX(-50%) translateY(-2px) !important;
    }
    .pdf-viewer-fallback {
        padding: 2rem;
        text-align: center;
        color: #6c757d;
    }
    .pdf-viewer-fallback a {
        color: #17a2b8;
        text-decoration: underline;
    }
    .pdf-viewer-fallback a:hover {
        color: #138496;
    }
    
    /* Empty State */
    /* Lazy Load Placeholder */
    .lazy-load-placeholder {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 400px;
        padding: 3rem 2rem;
    }
    .placeholder-content {
        text-align: center;
        max-width: 400px;
    }
    .placeholder-icon {
        opacity: 0.5;
        animation: pulse 2s ease-in-out infinite;
    }
    @keyframes pulse {
        0%, 100% {
            opacity: 0.5;
        }
        50% {
            opacity: 0.8;
        }
    }
    .btn-load-berkas {
        padding: 0.75rem 2rem;
        font-size: 1rem;
        font-weight: 500;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    .btn-load-berkas:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .btn-load-berkas:active {
        transform: translateY(0);
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6c757d;
    }
    .empty-state i {
        font-size: 5rem;
        color: #dee2e6;
        margin-bottom: 1.5rem;
    }
    .empty-state h5 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .berkas-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        .thumbnail-wrapper {
            height: 150px;
        }
        /* Mobile: container selalu tampil, preview menggunakan lazy loading */
        .thumbnail-pdf {
            display: block !important;
        }
        .thumbnail-image {
            display: block !important;
        }
        
        /* PDF Viewer Modal untuk Mobile */
        .pdf-viewer-overlay {
            padding: 0;
        }
        .pdf-viewer-container {
            width: 100%;
            height: 100vh;
            max-width: 100%;
            border-radius: 0;
            display: flex;
            flex-direction: column;
        }
        .pdf-viewer-header {
            padding: 0.75rem 1rem;
            flex-wrap: wrap;
        }
        .pdf-viewer-title {
            font-size: 0.9rem;
            width: 100%;
            margin-bottom: 0.5rem;
        }
        .pdf-viewer-actions {
            width: 100%;
            justify-content: flex-end;
            margin-left: 0;
            margin-top: 0.5rem;
        }
        .pdf-viewer-download,
        .pdf-viewer-open-new,
        .pdf-viewer-close {
            padding: 0.4rem 0.6rem;
            font-size: 0.9rem;
        }
        .pdf-viewer-content {
            flex: 1;
            overflow: auto;
            -webkit-overflow-scrolling: touch;
        }
        #pdfViewerCanvasContainer {
            padding: 10px;
            padding-bottom: 80px; /* Space untuk fixed dock controls di mobile */
        }
        #pdfViewerCanvas {
            max-height: calc(100vh - 150px);
        }
        .pdf-viewer-controls {
            position: fixed !important;
            bottom: 15px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.25rem;
            padding: 0.5rem 0.8rem;
            width: auto;
            max-width: 95%;
            z-index: 100001 !important;
        }
        .pdf-control-btn {
            padding: 0.35rem 0.5rem;
            font-size: 0.85rem;
        }
        .pdf-page-info,
        .pdf-zoom-info {
            font-size: 0.75rem;
            padding: 0 0.3rem;
        }
        .pdf-control-separator {
            height: 20px;
            margin: 0 0.15rem;
        }
        .pdf-icon-wrapper > i {
            font-size: 2.5rem;
        }
        .pdf-lines span:nth-child(1) { width: 35px; }
        .pdf-lines span:nth-child(2) { width: 28px; }
        .pdf-lines span:nth-child(3) { width: 32px; }
        .berkas-header {
            padding: 0.75rem;
        }
        .file-info {
            padding: 0.75rem;
        }
        .file-info-main {
            margin-bottom: 0.5rem;
        }
        .file-icon {
            width: 32px;
            height: 32px;
        }
        .file-icon i {
            font-size: 1rem;
        }
        .file-name {
            font-size: 0.8rem;
        }
        .berkas-grid.list-view .thumbnail-wrapper {
            width: 80px !important;
            min-width: 80px !important;
            max-width: 80px !important;
            height: 70px !important;
        }
        .berkas-grid.list-view .file-details {
            flex-direction: column !important;
            gap: 0.25rem !important;
            align-items: flex-start !important;
        }
        .berkas-grid.list-view .file-name {
            max-width: 180px;
        }
        .berkas-grid.list-view .file-info {
            padding: 0.5rem 0.75rem !important;
        }
    }
    
    @media (max-width: 576px) {
        .berkas-grid {
            grid-template-columns: 1fr;
        }
        .berkas-grid.list-view {
            display: flex !important;
            flex-direction: column !important;
        }
        .berkas-grid.list-view .berkas-card {
            flex-direction: row !important;
        }
        .berkas-grid.list-view .thumbnail-wrapper {
            width: 70px !important;
            min-width: 70px !important;
            max-width: 70px !important;
            height: 60px !important;
        }
        .berkas-grid.list-view .file-name {
            max-width: 150px;
            font-size: 0.8rem;
        }
        .berkas-grid.list-view .file-actions .btn-xs {
            padding: 0.25rem 0.5rem;
            font-size: 0.7rem;
        }
        .thumbnail-wrapper {
            height: 180px;
        }
        .view-toggle {
            justify-content: center;
        }
    }
</style>
@endpush

@push('js')
<!-- PDF.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    // Set worker path untuk PDF.js - harus dilakukan setelah library dimuat
    (function() {
        function initPdfJsWorker() {
            if (typeof pdfjsLib !== 'undefined') {
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                console.log('PDF.js worker initialized');
            } else {
                // Retry jika library belum dimuat
                setTimeout(initPdfJsWorker, 100);
            }
        }
        initPdfJsWorker();
    })();
</script>
<script>
    (function() {
        // Prevent duplicate initialization
        if (window.berkasRmInitialized) return;
        window.berkasRmInitialized = true;
        
        // Check if PDF.js is loaded
        if (typeof pdfjsLib === 'undefined') {
            console.error('PDF.js library tidak dimuat. Pastikan library sudah di-include.');
        } else {
            console.log('PDF.js library loaded successfully');
        }
        
        // Deteksi mobile device - buat global
        window.isMobileDevice = function() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || 
                   (window.innerWidth <= 768 && 'ontouchstart' in window);
        };
        
        // Deteksi Android khusus - buat global
        window.isAndroid = function() {
            return /Android/i.test(navigator.userAgent);
        };
        
        // Alias untuk penggunaan lokal
        var isMobileDevice = window.isMobileDevice;
        var isAndroid = window.isAndroid;
        
        // PDF.js variables untuk modal viewer
        var currentPdfDoc = null;
        var currentPageNum = 1;
        var currentScale = 1.0;
        var pdfRendering = false;
        var pdfPageNumPending = null;
        var currentPdfBlobUrl = null; // Untuk menyimpan blob URL
        
        // Fungsi helper untuk fetch PDF sebagai blob (mengatasi CORS)
        function fetchPdfAsBlob(url) {
            return new Promise(function(resolve, reject) {
                console.log('Processing PDF URL:', url);
                
                // Cek apakah URL sudah menggunakan proxy
                var isProxyUrl = url.includes('pdf-proxy.php');
                
                // Cek apakah URL adalah cross-origin
                var urlObj = new URL(url, window.location.href);
                var isCrossOrigin = urlObj.origin !== window.location.origin;
                
                // Jika menggunakan proxy, langsung gunakan URL karena proxy sudah handle CORS
                if (isProxyUrl) {
                    console.log('Proxy URL detected, using directly');
                    resolve(url);
                    return;
                }
                
                if (!isCrossOrigin) {
                    // Jika same-origin, langsung gunakan URL
                    console.log('Same-origin URL, using directly');
                    resolve(url);
                    return;
                }
                
                // Untuk cross-origin tanpa proxy, coba fetch sebagai blob
                console.log('Cross-origin URL, fetching as blob...');
                fetch(url, {
                    method: 'GET',
                    mode: 'cors',
                    credentials: 'omit',
                    cache: 'default',
                    headers: {
                        'Accept': 'application/pdf,application/octet-stream,*/*'
                    }
                }).then(function(response) {
                    if (!response.ok) {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                    // Cek content type
                    var contentType = response.headers.get('content-type');
                    if (contentType && !contentType.includes('pdf') && !contentType.includes('octet-stream')) {
                        console.warn('Unexpected content type:', contentType);
                    }
                    return response.blob();
                }).then(function(blob) {
                    // Validasi bahwa ini adalah PDF
                    if (blob.size === 0) {
                        throw new Error('PDF file is empty');
                    }
                    console.log('PDF blob created, size:', blob.size, 'bytes');
                    var blobUrl = URL.createObjectURL(blob);
                    resolve(blobUrl);
                }).catch(function(error) {
                    console.error('Error fetching PDF as blob:', error);
                    console.error('Error details:', {
                        name: error.name,
                        message: error.message
                    });
                    // Reject untuk trigger fallback
                    reject(error);
                });
            });
        }
        
        // Fungsi untuk render halaman PDF menggunakan PDF.js
        function renderPdfPage(num, scale) {
            if (!currentPdfDoc) {
                console.error('PDF document tidak tersedia');
                return;
            }
            
            // Pastikan scale valid
            if (typeof scale !== 'number' || scale <= 0) {
                console.warn('Invalid scale:', scale, ', using currentScale:', currentScale);
                scale = currentScale || 1.0;
            }
            
            // Update currentScale untuk konsistensi
            currentScale = scale;
            
            if (pdfRendering) {
                pdfPageNumPending = num;
                // Update scale untuk pending render
                if (scale !== currentScale) {
                    currentScale = scale;
                }
                return;
            }
            pdfRendering = true;
            
            // Show loading indicator
            var canvas = document.getElementById('pdfViewerCanvas');
            if (canvas) {
                var context = canvas.getContext('2d');
                context.fillStyle = '#f0f0f0';
                context.fillRect(0, 0, canvas.width || 100, canvas.height || 100);
            }
            
            currentPdfDoc.getPage(num).then(function(page) {
                // Gunakan scale yang diberikan untuk membuat viewport
                var viewport = page.getViewport({ scale: scale });
                var canvas = document.getElementById('pdfViewerCanvas');
                
                if (!canvas) {
                    console.error('Canvas tidak ditemukan');
                    pdfRendering = false;
                    return;
                }
                
                var context = canvas.getContext('2d');
                
                // Set canvas dimensions
                var outputScale = window.devicePixelRatio || 1;
                var scaledWidth = viewport.width;
                var scaledHeight = viewport.height;
                
                console.log('Rendering PDF page', num, 'with scale', scale);
                console.log('Viewport size:', scaledWidth, 'x', scaledHeight);
                console.log('Output scale (DPI):', outputScale);
                console.log('Zoom level:', Math.round(scale * 100) + '%');
                
                // Set canvas display size
                canvas.style.width = scaledWidth + 'px';
                canvas.style.height = scaledHeight + 'px';
                
                // Set canvas internal size untuk high DPI
                var internalWidth = Math.floor(scaledWidth * outputScale);
                var internalHeight = Math.floor(scaledHeight * outputScale);
                canvas.width = internalWidth;
                canvas.height = internalHeight;
                
                console.log('Canvas size set to:', internalWidth, 'x', internalHeight, '(internal)');
                console.log('Canvas display size:', scaledWidth, 'x', scaledHeight);
                
                // Scale context untuk high DPI displays
                context.scale(outputScale, outputScale);
                
                // Clear canvas dengan background putih
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, scaledWidth, scaledHeight);
                
                var renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };
                
                var renderTask = page.render(renderContext);
                
                renderTask.promise.then(function() {
                    console.log('PDF page rendered successfully, canvas size:', canvas.width, 'x', canvas.height);
                    pdfRendering = false;
                    if (pdfPageNumPending !== null) {
                        renderPdfPage(pdfPageNumPending, currentScale);
                        pdfPageNumPending = null;
                    }
                    
                    // Update page info
                    $('#pdfCurrentPage').text(currentPageNum);
                    $('#pdfTotalPages').text(currentPdfDoc.numPages);
                    $('#pdfZoomLevel').text(Math.round(currentScale * 100));
                    
                    // Update button states
                    $('#pdfViewerPrev').prop('disabled', currentPageNum <= 1);
                    $('#pdfViewerNext').prop('disabled', currentPageNum >= currentPdfDoc.numPages);
                    
                    // Ensure canvas is visible
                    $(canvas).css({
                        'display': 'block',
                        'visibility': 'visible',
                        'opacity': '1'
                    });
                }).catch(function(error) {
                    console.error('Error dalam render task:', error);
                    pdfRendering = false;
                    var errorMsg = error.message || 'Gagal merender halaman PDF';
                    $('#pdfViewerFallback').html(
                        '<div class="text-center p-5">' +
                        '<i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i>' +
                        '<p class="mb-3">' + errorMsg + '</p>' +
                        '<a href="' + url + '" class="btn btn-primary" target="_blank" download>' +
                        '<i class="fas fa-download"></i> Download PDF</a>' +
                        '</div>'
                    ).show();
                });
            }).catch(function(error) {
                console.error('Error rendering PDF page:', error);
                pdfRendering = false;
                var errorMsg = error.message || 'Gagal memuat halaman PDF';
                $('#pdfViewerFallback').html(
                    '<p>Error: ' + errorMsg + '</p>' +
                    '<a href="#" id="pdfViewerFallbackLink" target="_blank">Klik di sini untuk membuka PDF</a>'
                ).show();
            });
        }
        
        // Fungsi untuk render thumbnail PDF menggunakan PDF.js
        function renderPdfThumbnail(canvas, url) {
            if (typeof pdfjsLib === 'undefined') {
                console.error('PDF.js tidak tersedia');
                return;
            }
            
            var $canvas = $(canvas);
            var $container = $canvas.closest('.thumbnail-pdf');
            
            // Show loading
            $container.find('.pdf-loading').addClass('show');
            
            // Fetch PDF sebagai blob untuk menghindari CORS
            fetchPdfAsBlob(url).then(function(blobUrl) {
                // Load PDF dari blob URL
                var loadingTask = pdfjsLib.getDocument({
                    url: blobUrl,
                    httpHeaders: {},
                    withCredentials: false,
                    verbosity: 0
                });
                
                return loadingTask.promise;
            }).catch(function(error) {
                console.warn('Blob fetch failed, trying direct URL:', error);
                // Fallback: coba langsung dengan URL asli
                var loadingTask = pdfjsLib.getDocument({
                    url: url,
                    httpHeaders: {},
                    withCredentials: false,
                    verbosity: 0
                });
                return loadingTask.promise;
            }).then(function(pdf) {
                return pdf.getPage(1);
            }).then(function(page) {
                var viewport = page.getViewport({ scale: 1.0 });
                var context = canvas.getContext('2d');
                
                // Set canvas size sesuai thumbnail
                var containerWidth = $container.width() || 200;
                var containerHeight = $container.height() || 200;
                var scale = Math.min(containerWidth / viewport.width, containerHeight / viewport.height, 1.0);
                
                var scaledViewport = page.getViewport({ scale: scale });
                
                // Set canvas dimensions dengan high DPI support
                var outputScale = window.devicePixelRatio || 1;
                var scaledWidth = scaledViewport.width;
                var scaledHeight = scaledViewport.height;
                
                // Set canvas display size
                canvas.style.width = scaledWidth + 'px';
                canvas.style.height = scaledHeight + 'px';
                
                // Set canvas internal size untuk high DPI
                canvas.width = Math.floor(scaledWidth * outputScale);
                canvas.height = Math.floor(scaledHeight * outputScale);
                
                // Scale context untuk high DPI displays
                context.scale(outputScale, outputScale);
                
                // Clear canvas dengan background putih
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, scaledWidth, scaledHeight);
                
                var renderContext = {
                    canvasContext: context,
                    viewport: scaledViewport
                };
                
                return page.render(renderContext).promise;
            }).then(function() {
                $canvas.addClass('loaded');
                $canvas.attr('data-loaded', 'true');
                $container.find('.pdf-loading').removeClass('show');
                $container.find('.pdf-preview-icon').hide();
            }).catch(function(error) {
                console.error('Error rendering PDF thumbnail:', error);
                $container.find('.pdf-loading').removeClass('show');
                $canvas.attr('data-loaded', 'error');
                // Tetap tampilkan icon jika error
                $container.find('.pdf-preview-icon').show();
            });
        }
        
        // PDF Viewer functions - menggunakan PDF.js
        window.openPdfViewer = function(url, title) {
            var pdfTitle = title || 'Dokumen PDF';
            
            if (typeof pdfjsLib === 'undefined') {
                alert('PDF.js library tidak dimuat. Silakan refresh halaman.');
                return;
            }
            
            // Set title
            $('.pdf-viewer-title').text(pdfTitle);
            
            // Set download dan open new tab links
            $('#pdfViewerDownload').attr('href', url);
            $('#pdfViewerOpenNew').attr('href', url);
            $('#pdfViewerFallbackLink').attr('href', url);
            
            // Hide fallback
            $('#pdfViewerFallback').hide();
            
            // Reset state
            currentPageNum = 1;
            pdfRendering = false;
            pdfPageNumPending = null;
            
            // Calculate initial scale berdasarkan container size
            var container = document.getElementById('pdfViewerCanvasContainer');
            if (container) {
                var containerWidth = container.clientWidth - 40; // minus padding
                var containerHeight = container.clientHeight - 100; // minus controls
                // Scale akan dihitung setelah PDF dimuat
            }
            
            // Set initial scale
            currentScale = 1.0;
            
            // Clear canvas dan show loading indicator
            var canvas = document.getElementById('pdfViewerCanvas');
            if (canvas) {
                // Set temporary size untuk loading indicator
                canvas.width = 800;
                canvas.height = 600;
                canvas.style.width = '800px';
                canvas.style.height = '600px';
                
                var context = canvas.getContext('2d');
                context.fillStyle = '#f0f0f0';
                context.fillRect(0, 0, 800, 600);
                context.fillStyle = '#333';
                context.font = '16px Arial';
                context.textAlign = 'center';
                context.fillText('Memuat PDF...', 400, 300);
            }
            
            // Load PDF document - fetch sebagai blob untuk menghindari CORS
            console.log('Loading PDF from URL:', url);
            
            // Fetch PDF sebagai blob terlebih dahulu
            fetchPdfAsBlob(url).then(function(blobUrl) {
                console.log('PDF blob URL created:', blobUrl);
                
                // Simpan blob URL untuk cleanup nanti
                if (currentPdfBlobUrl) {
                    URL.revokeObjectURL(currentPdfBlobUrl);
                }
                currentPdfBlobUrl = blobUrl;
                
                // Load PDF dari blob URL
                var loadingTask = pdfjsLib.getDocument({
                    url: blobUrl,
                    httpHeaders: {},
                    withCredentials: false,
                    verbosity: 1 // Enable logging untuk debugging
                });
                
                return loadingTask.promise;
            }).catch(function(error) {
                console.warn('Blob fetch failed, trying direct URL:', error);
                // Fallback: coba langsung dengan URL asli
                var loadingTask = pdfjsLib.getDocument({
                    url: url,
                    httpHeaders: {},
                    withCredentials: false,
                    verbosity: 1
                });
                return loadingTask.promise;
            }).then(function(pdfDoc) {
                console.log('PDF loaded successfully, pages:', pdfDoc.numPages);
                currentPdfDoc = pdfDoc;
                $('#pdfTotalPages').text(pdfDoc.numPages);
                currentPageNum = 1;
                
                // Calculate optimal scale berdasarkan container dan halaman pertama
                pdfDoc.getPage(1).then(function(firstPage) {
                    console.log('First page loaded, calculating scale...');
                    var viewport = firstPage.getViewport({ scale: 1.0 });
                    var container = document.getElementById('pdfViewerCanvasContainer');
                    
                    if (container) {
                        var containerWidth = container.clientWidth - 40; // minus padding
                        var containerHeight = container.clientHeight - 100; // minus controls
                        console.log('Container size:', containerWidth, 'x', containerHeight);
                        console.log('Page viewport size:', viewport.width, 'x', viewport.height);
                        
                        var scaleX = containerWidth / viewport.width;
                        var scaleY = containerHeight / viewport.height;
                        // Gunakan scale yang lebih kecil agar PDF muat di container
                        currentScale = Math.min(scaleX, scaleY, 1.5); // Max 1.5x untuk readability
                        console.log('Calculated scale:', currentScale);
                    } else {
                        currentScale = 1.0;
                        console.log('Container not found, using default scale 1.0');
                    }
                    
                    // Render halaman pertama dengan scale yang sudah dihitung
                    renderPdfPage(1, currentScale);
                }).catch(function(error) {
                    console.error('Error getting first page:', error);
                    currentScale = 1.0;
                    renderPdfPage(1, 1.0); // Fallback ke scale 1.0
                });
            }).catch(function(error) {
                console.error('Error loading PDF:', error);
                console.error('Error details:', {
                    name: error.name,
                    message: error.message,
                    stack: error.stack
                });
                
                var errorMsg = 'Gagal memuat dokumen PDF';
                if (error.name === 'InvalidPDFException') {
                    errorMsg = 'File PDF tidak valid atau rusak';
                } else if (error.name === 'MissingPDFException') {
                    errorMsg = 'File PDF tidak ditemukan';
                } else if (error.name === 'UnexpectedResponseException') {
                    errorMsg = 'Tidak dapat mengakses file PDF (CORS atau server error)';
                } else if (error.message) {
                    errorMsg = error.message;
                }
                
                $('#pdfViewerFallback').html(
                    '<div class="text-center p-5">' +
                    '<i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i>' +
                    '<p class="mb-3"><strong>' + errorMsg + '</strong></p>' +
                    '<p class="mb-3 text-muted small">URL: ' + url + '</p>' +
                    '<a href="' + url + '" class="btn btn-primary mr-2" target="_blank" download>' +
                    '<i class="fas fa-download"></i> Download PDF</a>' +
                    '<a href="' + url + '" class="btn btn-secondary" target="_blank">' +
                    '<i class="fas fa-external-link-alt"></i> Buka di Tab Baru</a>' +
                    '</div>'
                ).show();
                
                // Clear canvas
                if (canvas) {
                    var context = canvas.getContext('2d');
                    canvas.width = 0;
                    canvas.height = 0;
                    canvas.style.width = '0px';
                    canvas.style.height = '0px';
                }
            });
            
            // Tampilkan modal
            $('#pdfViewerOverlay').fadeIn(200);
            $('body').css('overflow', 'hidden');
        };
        
        window.closePdfViewer = function() {
            $('#pdfViewerOverlay').fadeOut(200);
            
            // Clear PDF document
            if (currentPdfDoc) {
                currentPdfDoc.destroy();
                currentPdfDoc = null;
            }
            
            // Revoke blob URL untuk free memory
            if (currentPdfBlobUrl) {
                URL.revokeObjectURL(currentPdfBlobUrl);
                currentPdfBlobUrl = null;
            }
            
            // Clear canvas
            var canvas = document.getElementById('pdfViewerCanvas');
            if (canvas) {
                var context = canvas.getContext('2d');
                canvas.width = 0;
                canvas.height = 0;
                canvas.style.width = '0px';
                canvas.style.height = '0px';
            }
            
            // Reset state
            currentPageNum = 1;
            currentScale = 1.0;
            pdfRendering = false;
            pdfPageNumPending = null;
            
            $('body').css('overflow', '');
        };
        
        // PDF Viewer Controls - initialize setelah DOM ready
        function initPdfViewerControls() {
            $(document).off('click.pdfprev').on('click.pdfprev', '#pdfViewerPrev', function() {
                if (currentPageNum <= 1) return;
                currentPageNum--;
                renderPdfPage(currentPageNum, currentScale);
            });
            
            $(document).off('click.pdfnext').on('click.pdfnext', '#pdfViewerNext', function() {
                if (currentPdfDoc && currentPageNum >= currentPdfDoc.numPages) return;
                currentPageNum++;
                renderPdfPage(currentPageNum, currentScale);
            });
            
            // Zoom In
            $(document).off('click.pdfzoomin').on('click.pdfzoomin', '#pdfViewerZoomIn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (!currentPdfDoc) return;
                if (currentScale >= 3.0) {
                    console.log('Zoom max reached:', currentScale);
                    return;
                }
                
                var oldScale = currentScale;
                currentScale = Math.min(currentScale + 0.25, 3.0);
                console.log('Zoom In:', oldScale, '->', currentScale);
                
                // Update zoom level display immediately
                $('#pdfZoomLevel').text(Math.round(currentScale * 100));
                
                // Re-render dengan scale baru
                renderPdfPage(currentPageNum, currentScale);
            });
            
            // Zoom Out
            $(document).off('click.pdfzoomout').on('click.pdfzoomout', '#pdfViewerZoomOut', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (!currentPdfDoc) return;
                if (currentScale <= 0.5) {
                    console.log('Zoom min reached:', currentScale);
                    return;
                }
                
                var oldScale = currentScale;
                currentScale = Math.max(currentScale - 0.25, 0.5);
                console.log('Zoom Out:', oldScale, '->', currentScale);
                
                // Update zoom level display immediately
                $('#pdfZoomLevel').text(Math.round(currentScale * 100));
                
                // Re-render dengan scale baru
                renderPdfPage(currentPageNum, currentScale);
            });
            
            // Mouse wheel zoom dengan Ctrl/Cmd
            var zoomWheelTimeout = null;
            var lastWheelTime = 0;
            $(document).off('wheel.pdfzoom').on('wheel.pdfzoom', '#pdfViewerCanvasContainer, #pdfViewerCanvas', function(e) {
                // Hanya aktif jika modal terbuka dan PDF sudah dimuat
                if (!$('#pdfViewerOverlay').is(':visible') || !currentPdfDoc) {
                    return;
                }
                
                // Cek apakah Ctrl/Cmd ditekan (untuk zoom dengan scroll)
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var now = Date.now();
                    var delta = e.originalEvent.deltaY || e.originalEvent.wheelDelta || 0;
                    var zoomFactor = 0.15; // 15% per scroll untuk lebih responsif
                    
                    var oldScale = currentScale;
                    
                    // Invert delta untuk wheelDelta (beberapa browser)
                    if (e.originalEvent.wheelDelta) {
                        delta = -delta;
                    }
                    
                    if (delta < 0) {
                        // Scroll up = zoom in
                        currentScale = Math.min(currentScale + zoomFactor, 3.0);
                    } else if (delta > 0) {
                        // Scroll down = zoom out
                        currentScale = Math.max(currentScale - zoomFactor, 0.5);
                    } else {
                        return; // No change
                    }
                    
                    // Throttle untuk performa (max 10 renders per second)
                    if (now - lastWheelTime < 100) {
                        return;
                    }
                    lastWheelTime = now;
                    
                    // Clear previous timeout
                    clearTimeout(zoomWheelTimeout);
                    
                    // Update zoom level display immediately untuk feedback
                    $('#pdfZoomLevel').text(Math.round(currentScale * 100));
                    
                    // Render dengan sedikit delay untuk smooth experience
                    zoomWheelTimeout = setTimeout(function() {
                        if (oldScale !== currentScale) {
                            console.log('Mouse wheel zoom:', oldScale, '->', currentScale);
                            // Re-render dengan scale baru
                            renderPdfPage(currentPageNum, currentScale);
                        }
                    }, 100); // 100ms delay untuk smooth zoom
                }
            });
            
            // Double click untuk reset zoom
            $(document).off('dblclick.pdfzoomreset').on('dblclick.pdfzoomreset', '#pdfViewerCanvas', function(e) {
                if (!currentPdfDoc) return;
                
                e.preventDefault();
                e.stopPropagation();
                
                // Reset ke scale 1.0
                currentScale = 1.0;
                console.log('Double click: Reset zoom to 1.0');
                
                // Update zoom level display
                $('#pdfZoomLevel').text('100');
                
                // Re-render dengan scale 1.0
                renderPdfPage(currentPageNum, currentScale);
            });
        }
        
        // Initialize controls saat DOM ready
        $(document).ready(function() {
            initPdfViewerControls();
        });
        
        // View toggle function
        window.toggleBerkasView = function(view) {
            var $containers = $('.berkas-grid[data-group-container]');
            var $buttons = $('.view-toggle .btn');
            
            console.log('Switching view to:', view);
            
            // Update button active state
            $buttons.removeClass('active');
            $buttons.filter('[data-view="' + view + '"]').addClass('active');
            
            // Toggle view class on all group containers
            $containers.each(function() {
                if (view === 'list') {
                    $(this).addClass('list-view');
                } else {
                    $(this).removeClass('list-view');
                }
            });
            
            // Save preference
            try {
                localStorage.setItem('berkas-view-mode', view);
            } catch(e) {
                console.log('LocalStorage not available');
            }
        };
        
        
        // Global observers untuk lazy loading
        var pdfObserver = null;
        var imageObserver = null;
        
        // Lazy loading dengan Intersection Observer untuk PDF dan gambar
        window.initLazyLoading = function() {
            // Check if Intersection Observer is supported
            if (!('IntersectionObserver' in window)) {
                // Fallback: load all previews immediately
                loadAllPreviews();
                return;
            }
            
            // Buat observer untuk PDF jika belum ada
            if (!pdfObserver) {
                pdfObserver = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            var canvas = entry.target;
                            var $canvas = $(canvas);
                            var src = $canvas.data('src');
                            
                            if (src && $canvas.attr('data-loaded') === 'false') {
                                // Check if PDF.js is available
                                if (typeof pdfjsLib === 'undefined') {
                                    console.warn('PDF.js tidak tersedia, skip loading PDF thumbnail');
                                    $canvas.attr('data-loaded', 'error');
                                    pdfObserver.unobserve(entry.target);
                                    return;
                                }
                                
                                console.log('IntersectionObserver: Loading PDF thumbnail:', src);
                                
                                // Render PDF thumbnail menggunakan PDF.js
                                renderPdfThumbnail(canvas, src);
                                
                                // Stop observing this element
                                pdfObserver.unobserve(entry.target);
                            }
                        }
                    });
                }, {
                    root: null, // Use viewport as root
                    rootMargin: '200px', // Start loading 200px before entering viewport
                    threshold: 0.01
                });
                console.log('PDF Observer created');
            }
            
            // Buat observer untuk gambar jika belum ada
            if (!imageObserver) {
                imageObserver = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            var $img = $(entry.target);
                            var src = $img.data('src');
                            
                            if (src && $img.attr('data-loaded') !== 'true') {
                                console.log('IntersectionObserver: Loading image:', src);
                                
                                // Load image
                                $img.attr('src', src);
                                $img.attr('data-loaded', 'true');
                                
                                $img.on('load', function() {
                                    $(this).css({
                                        'opacity': '1',
                                        'visibility': 'visible'
                                    });
                                });
                                
                                // Stop observing this element
                                imageObserver.unobserve(entry.target);
                            }
                        }
                    });
                }, {
                    root: null, // Use viewport as root
                    rootMargin: '200px', // Start loading 200px before entering viewport
                    threshold: 0.01
                });
                console.log('Image Observer created');
            }
            
            // Fungsi untuk check apakah elemen sudah di viewport atau mendekati viewport
            function isInViewport(element) {
                var rect = element.getBoundingClientRect();
                var windowHeight = window.innerHeight || document.documentElement.clientHeight;
                var windowWidth = window.innerWidth || document.documentElement.clientWidth;
                var margin = 200; // Margin untuk preload
                
                return (
                    rect.top < windowHeight + margin &&
                    rect.bottom > -margin &&
                    rect.left < windowWidth + margin &&
                    rect.right > -margin
                );
            }
            
            // Load elemen yang sudah di viewport langsung tanpa observer
            var pdfCount = 0;
            var pdfObservedCount = 0;
            $('.pdf-thumb.lazy-pdf[data-loaded="false"]').each(function() {
                pdfCount++;
                var canvas = this;
                var $canvas = $(canvas);
                var src = $canvas.data('src');
                
                if (!src) {
                    console.warn('PDF canvas tidak memiliki data-src:', this);
                    return;
                }
                
                // Check if PDF.js is available
                if (typeof pdfjsLib === 'undefined') {
                    console.warn('PDF.js tidak tersedia, skip loading PDF thumbnail');
                    $canvas.attr('data-loaded', 'error');
                    return;
                }
                
                if (isInViewport(this)) {
                    console.log('Loading PDF thumbnail immediately (in viewport):', src);
                    renderPdfThumbnail(canvas, src);
                } else if (!$canvas.data('observed')) {
                    // Observe elemen yang belum di viewport
                    try {
                        pdfObserver.observe(this);
                        $canvas.data('observed', true);
                        pdfObservedCount++;
                        console.log('Observing PDF:', src);
                    } catch(e) {
                        console.error('Error observing PDF:', e, src);
                    }
                }
            });
            console.log('PDF lazy loading initialized:', pdfCount, 'total,', pdfObservedCount, 'observed');
            
            // Load gambar yang sudah di viewport langsung tanpa observer
            var imgCount = 0;
            var imgObservedCount = 0;
            $('.thumbnail-image img.lazy-image:not([data-loaded="true"])').each(function() {
                imgCount++;
                var $img = $(this);
                var src = $img.data('src');
                
                if (!src) {
                    console.warn('Image tidak memiliki data-src:', this);
                    return;
                }
                
                if (isInViewport(this)) {
                    console.log('Loading image immediately (in viewport):', src);
                    $img.attr('src', src);
                    $img.attr('data-loaded', 'true');
                    $img.on('load', function() {
                        $(this).css({
                            'opacity': '1',
                            'visibility': 'visible'
                        });
                    });
                } else if (!$img.data('observed')) {
                    // Observe elemen yang belum di viewport
                    try {
                        imageObserver.observe(this);
                        $img.data('observed', true);
                        imgObservedCount++;
                        console.log('Observing image:', src);
                    } catch(e) {
                        console.error('Error observing image:', e, src);
                    }
                }
            });
            console.log('Image lazy loading initialized:', imgCount, 'total,', imgObservedCount, 'observed');
        };
        
        // Fallback function untuk browser yang tidak support Intersection Observer
        function loadAllPreviews() {
            // Load all PDFs menggunakan PDF.js
            $('.pdf-thumb.lazy-pdf[data-loaded="false"]').each(function() {
                var canvas = this;
                var $canvas = $(canvas);
                var src = $canvas.data('src');
                
                // Check if PDF.js is available
                if (typeof pdfjsLib === 'undefined') {
                    console.warn('PDF.js tidak tersedia, skip loading PDF thumbnail');
                    $canvas.attr('data-loaded', 'error');
                    return;
                }
                
                if (src) {
                    renderPdfThumbnail(canvas, src);
                }
            });
            
            // Load all images
            $('.thumbnail-image img.lazy-image:not([data-loaded="true"])').each(function() {
                var $img = $(this);
                var src = $img.data('src');
                if (src) {
                    $img.attr('src', src);
                    $img.attr('data-loaded', 'true');
                    $img.on('load', function() {
                        $(this).css({
                            'opacity': '1',
                            'visibility': 'visible'
                        });
                    });
                }
            });
        }
        
        // Initialize lazy loading saat document ready
        $(document).ready(function() {
            initLazyLoading();
            
            // Juga inisialisasi setelah sedikit delay untuk memastikan DOM sudah siap
            setTimeout(function() {
                initLazyLoading();
            }, 500);
        });
        
        // Re-initialize lazy loading saat scroll (untuk memastikan elemen baru terdeteksi)
        var scrollTimeout;
        function handleScroll() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function() {
                initLazyLoading();
            }, 150);
        }
        
        // Listen scroll pada window dan container
        $(window).on('scroll', handleScroll);
        $(document).on('scroll', '.berkas-rm-container, .berkas-groups-container, .berkas-grid', handleScroll);
        
        // Juga reinitialize saat resize
        $(window).on('resize', function() {
            setTimeout(function() {
                initLazyLoading();
            }, 200);
        });
        
        // Close PDF viewer on escape key
        $(document).off('keydown.pdfviewer').on('keydown.pdfviewer', function(e) {
            if (e.key === 'Escape' && $('#pdfViewerOverlay').is(':visible')) {
                closePdfViewer();
            }
        });
        
        // Close PDF viewer when clicking overlay
        $(document).off('click.pdfoverlay').on('click.pdfoverlay', '#pdfViewerOverlay', function(e) {
            if (e.target === this) {
                closePdfViewer();
            }
        });
        
        // View toggle (Grid/List) - using event delegation
        $(document).off('click.viewtoggle').on('click.viewtoggle', '.view-toggle .btn', function(e) {
            e.preventDefault();
            var view = $(this).data('view');
            toggleBerkasView(view);
        });
        
        // Group toggle accordion
        $(document).off('click.grouptoggle').on('click.grouptoggle', '.group-toggle', function(e) {
            e.preventDefault();
            var target = $(this).data('target');
            var $content = $('#' + target);
            var isExpanded = $content.hasClass('show');
            
            if (isExpanded) {
                $content.collapse('hide');
                $(this).attr('aria-expanded', 'false');
            } else {
                $content.collapse('show');
                $(this).attr('aria-expanded', 'true');
            }
        });
        
        // Update toggle icon on collapse events
        $(document).on('shown.bs.collapse hidden.bs.collapse', '.berkas-group-content', function() {
            var $toggle = $(this).closest('.berkas-group').find('.group-toggle');
            var isExpanded = $(this).hasClass('show');
            $toggle.attr('aria-expanded', isExpanded);
        });
        
        // Lightbox for images with gallery support
        // Use custom class instead of data-toggle to prevent double trigger
        function handleImageClick(event) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            
            var $link = $(this).closest('.lightbox-trigger');
            if (!$link.length) {
                // Jika klik pada card atau thumbnail, cari link terdekat
                $link = $(this).closest('.berkas-card.is-image').find('.lightbox-trigger').first();
            }
            
            if (!$link.length) {
                return false;
            }
            
            var gallery = $link.data('gallery') || 'berkas-gallery';
            var width = $link.data('width') || 1280;
            var height = $link.data('height') || 700;
            var title = $link.data('title') || '';
            
            // Initialize ekkoLightbox only once per element
            if (!$link.data('ekkoLightbox-initialized')) {
                $link.ekkoLightbox({
                    alwaysShowClose: true,
                    showArrows: true,
                    wrapping: true,
                    loadingMessage: 'Memuat...',
                    gallery: gallery,
                    width: width,
                    height: height,
                    title: title
                });
                $link.data('ekkoLightbox-initialized', true);
            } else {
                // If already initialized, just trigger it
                $link.ekkoLightbox();
            }
            
            return false;
        }
        
        // Click handler untuk gambar (support touch untuk mobile/tablet)
        $(document).off('click.lightbox').on('click.lightbox', '.berkas-rm-container .lightbox-trigger, .berkas-card.is-image .thumbnail-image, .berkas-card.is-image .thumbnail-overlay, .berkas-card.is-image', function(event) {
            // Skip jika klik pada file-info (untuk aksi lain)
            if ($(event.target).closest('.file-info').length) {
                return true;
            }
            return handleImageClick.call(this, event);
        });
        
        // Touch handler khusus untuk mobile/tablet (prevent default behavior)
        var touchStartTime = 0;
        var touchStartPos = null;
        $(document).off('touchstart.imagetouch touchend.imagetouch').on('touchstart.imagetouch', '.berkas-card.is-image', function(event) {
            touchStartTime = Date.now();
            var touch = event.originalEvent.touches[0];
            touchStartPos = { x: touch.clientX, y: touch.clientY };
        }).on('touchend.imagetouch', '.berkas-card.is-image', function(event) {
            if (!touchStartPos) return;
            
            var touch = event.originalEvent.changedTouches[0];
            var touchEndPos = { x: touch.clientX, y: touch.clientY };
            var timeDiff = Date.now() - touchStartTime;
            var distance = Math.sqrt(Math.pow(touchEndPos.x - touchStartPos.x, 2) + Math.pow(touchEndPos.y - touchStartPos.y, 2));
            
            // Hanya trigger jika tap cepat (bukan swipe) dan tidak klik pada file-info
            if (timeDiff < 300 && distance < 10 && !$(event.target).closest('.file-info').length) {
                event.preventDefault();
                event.stopPropagation();
                handleImageClick.call(this, event);
            }
            
            touchStartTime = 0;
            touchStartPos = null;
        });
        
        // PDF viewer - klik pada kartu atau thumbnail PDF (support touch events untuk mobile/tablet)
        function handlePdfClick(event) {
            // Skip jika klik pada file-info (untuk aksi lain)
            if ($(event.target).closest('.file-info').length) {
                return true;
            }
            
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();
            
            var $card = $(this).closest('.berkas-card');
            if (!$card.length && $(this).hasClass('berkas-card')) {
                $card = $(this);
            }
            
            var url = $card.data('file-url') || $card.find('.thumbnail-pdf').data('url');
            var title = $card.data('file-name') || $card.find('.file-name').attr('title') || $card.find('.file-name').text() || 'Dokumen PDF';
            
            if (url) {
                openPdfViewer(url, title);
            }
            
            return false;
        }
        
        // Click handler untuk PDF
        $(document).off('click.pdfview').on('click.pdfview', '.berkas-card.is-pdf, .thumbnail-pdf, .thumbnail-pdf .pdf-overlay, .thumbnail-pdf .pdf-preview-icon', handlePdfClick);
        
        // Touch handler khusus untuk mobile/tablet PDF (prevent default behavior)
        var pdfTouchStartTime = 0;
        var pdfTouchStartPos = null;
        $(document).off('touchstart.pdftouch touchend.pdftouch').on('touchstart.pdftouch', '.berkas-card.is-pdf', function(event) {
            pdfTouchStartTime = Date.now();
            var touch = event.originalEvent.touches[0];
            pdfTouchStartPos = { x: touch.clientX, y: touch.clientY };
        }).on('touchend.pdftouch', '.berkas-card.is-pdf', function(event) {
            if (!pdfTouchStartPos) return;
            
            var touch = event.originalEvent.changedTouches[0];
            var touchEndPos = { x: touch.clientX, y: touch.clientY };
            var timeDiff = Date.now() - pdfTouchStartTime;
            var distance = Math.sqrt(Math.pow(touchEndPos.x - pdfTouchStartPos.x, 2) + Math.pow(touchEndPos.y - pdfTouchStartPos.y, 2));
            
            // Hanya trigger jika tap cepat (bukan swipe) dan tidak klik pada file-info
            if (timeDiff < 300 && distance < 10 && !$(event.target).closest('.file-info').length) {
                event.preventDefault();
                event.stopPropagation();
                handlePdfClick.call(this, event);
            }
            
            pdfTouchStartTime = 0;
            pdfTouchStartPos = null;
        });
        
        // Function to find and call BerkasRm component untuk lazy load
        function callBerkasRmInitialize(rm) {
            if (!rm || typeof Livewire === 'undefined') {
                console.log('Invalid RM or Livewire not available');
                return false;
            }
            
            console.log('Attempting to initialize BerkasRm, RM:', rm);
            
            // Find BerkasRm component specifically inside the modal container
            var container = document.querySelector('#berkas-rm-container');
            if (!container) {
                console.log('Container #berkas-rm-container not found');
                // Fallback: use event
                Livewire.emit('initializeBerkas', rm);
                if (typeof Livewire.dispatch !== 'undefined') {
                    Livewire.dispatch('initializeBerkas', rm);
                }
                return false;
            }
            
            var berkasRmComponent = container.querySelector('[wire\\:id]');
            if (!berkasRmComponent) {
                console.log('BerkasRm component not found in container');
                // Fallback: use event
                Livewire.emit('initializeBerkas', rm);
                if (typeof Livewire.dispatch !== 'undefined') {
                    Livewire.dispatch('initializeBerkas', rm);
                }
                return false;
            }
            
            var componentId = berkasRmComponent.getAttribute('wire:id');
            console.log('Found component ID:', componentId);
            
            try {
                var component = Livewire.find(componentId);
                if (component && typeof component.call === 'function') {
                    console.log('Calling initializeBerkas on BerkasRm component, RM:', rm);
                    component.call('initializeBerkas', rm);
                    return true;
                } else {
                    console.log('Component found but call method not available');
                    // Fallback: use event
                    Livewire.emit('initializeBerkas', rm);
                    if (typeof Livewire.dispatch !== 'undefined') {
                        Livewire.dispatch('initializeBerkas', rm);
                    }
                    return false;
                }
            } catch(e) {
                console.log('Error calling component:', e);
                // Fallback: use event
                Livewire.emit('initializeBerkas', rm);
                if (typeof Livewire.dispatch !== 'undefined') {
                    Livewire.dispatch('initializeBerkas', rm);
                }
                return false;
            }
        }
        
        // Function to find and call BerkasRm component (legacy, untuk backward compatibility)
        function callBerkasRmSetRm(rm) {
            // Gunakan initializeBerkas untuk lazy load
            return callBerkasRmInitialize(rm);
        }
        
        // Global function untuk load berkas data (dipanggil dari button placeholder)
        window.loadBerkasData = function() {
            var rm = $('#btn-rm').data('rm') || $('#modal-rm').data('rm');
            if (rm) {
                console.log('Loading berkas data for RM:', rm);
                callBerkasRmInitialize(rm);
            } else {
                console.warn('RM tidak ditemukan untuk load data');
                // Coba ambil dari semua button dengan data-rm
                var $btnRm = $('[data-rm]').first();
                if ($btnRm.length) {
                    rm = $btnRm.data('rm');
                    console.log('Found RM from button:', rm);
                    callBerkasRmInitialize(rm);
                } else {
                    alert('Nomor RM tidak ditemukan. Silakan tutup dan buka kembali modal.');
                }
            }
        };
        
        // Button RM click handler - lazy load saat modal dibuka
        $(document).off('click.btnrm').on('click.btnrm', '#btn-rm', function(event) {
            event.preventDefault();
            var rm = $(this).data('rm');
            console.log('RM Button clicked, RM:', rm);
            
            // Simpan RM ke modal untuk referensi
            $('#modal-rm').data('rm', rm);
            
            // Show modal first
            $('#modal-rm').modal('show');
        });
        
        // Handle modal shown event untuk lazy load data
        $('#modal-rm').off('shown.bs.modal.berkas').on('shown.bs.modal.berkas', function() {
            var rm = $('#btn-rm').data('rm') || $('#modal-rm').data('rm');
            console.log('Modal shown, initializing BerkasRm with RM:', rm);
            
            if (rm) {
                // Wait a bit for Livewire to initialize, lalu load data
                setTimeout(function() {
                    callBerkasRmInitialize(rm);
                }, 200);
            } else {
                console.warn('RM tidak ditemukan saat modal dibuka');
            }
        });
        
        // Handle modal hidden event untuk cleanup (optional)
        $('#modal-rm').off('hidden.bs.modal.berkas').on('hidden.bs.modal.berkas', function() {
            console.log('Modal hidden');
            // Bisa tambahkan cleanup logic di sini jika diperlukan
        });
    })();
    
    // Initialize on document ready and after Livewire updates
    $(document).ready(function() {
        // Restore view preference on load
        setTimeout(function() {
            try {
                var savedView = localStorage.getItem('berkas-view-mode');
                if (savedView === 'list') {
                    toggleBerkasView('list');
                }
            } catch(e) {
                console.log('LocalStorage not available');
            }
        }, 100);
    });
    
    // Re-apply view preference and reinitialize lazy loading after Livewire update
    if (typeof Livewire !== 'undefined') {
        document.addEventListener('livewire:load', function() {
            Livewire.hook('message.processed', function() {
                setTimeout(function() {
                    // Reset observed flag untuk elemen baru setelah Livewire update
                    $('.pdf-thumb.lazy-pdf[data-loaded="false"]').removeData('observed');
                    $('.thumbnail-image img.lazy-image:not([data-loaded="true"])').removeData('observed');
                    
                    // Reinitialize lazy loading setelah Livewire update
                    initLazyLoading();
                    
                    // Restore view preference
                    try {
                        var savedView = localStorage.getItem('berkas-view-mode');
                        if (savedView === 'list') {
                            var $container = $('#berkasContainer');
                            if ($container.length && !$container.hasClass('list-view')) {
                                $container.addClass('list-view');
                                $('.view-toggle .btn').removeClass('active');
                                $('.view-toggle .btn[data-view="list"]').addClass('active');
                            }
                        }
                    } catch(e) {}
                }, 200);
            });
        });
    }
</script>
@endpush