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
                    {{-- PDF Preview menggunakan iframe - lazy load dengan Intersection Observer --}}
                    <iframe 
                        data-src="{{ $fileUrl }}#page=1&zoom=fit"
                        class="pdf-thumb-iframe lazy-pdf-iframe"
                        frameborder="0"
                        scrolling="no"
                        loading="lazy">
                    </iframe>
                    {{-- PDF Icon overlay sebagai placeholder --}}
                    <div class="pdf-preview-icon">
                        <i class="fas fa-file-pdf"></i>
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
                <iframe 
                    id="pdfViewerIframe"
                    src=""
                    style="width: 100%; height: 100%; border: none;"
                    frameborder="0"
                    allowfullscreen>
                </iframe>
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
    /* PDF Thumbnail Iframe */
    .pdf-thumb-iframe {
        width: 100%;
        height: 100%;
        border: none;
        background: #fff;
        display: block;
        position: absolute;
        top: 0;
        left: 0;
        z-index: 0;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none; /* Biar klik masuk ke container untuk overlay */
    }
    /* Iframe yang sudah dimuat */
    .pdf-thumb-iframe[src]:not([src=""]) {
        opacity: 1;
    }
    /* Iframe yang belum dimuat (hanya punya data-src) */
    .pdf-thumb-iframe:not([src]) {
        display: none;
    }
    /* Tampilkan icon PDF sebagai placeholder saat iframe belum dimuat */
    .thumbnail-pdf .pdf-preview-icon {
        display: flex;
        z-index: 1;
    }
    /* Sembunyikan icon saat iframe sudah dimuat dan terlihat */
    .thumbnail-pdf:has(.pdf-thumb-iframe[src]:not([src=""])) .pdf-preview-icon {
        display: none;
    }
    /* Fallback untuk browser yang tidak support :has() */
    @supports not selector(:has(*)) {
        .thumbnail-pdf .pdf-thumb-iframe[src]:not([src=""]) ~ .pdf-preview-icon {
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
        overflow: hidden;
        -webkit-overflow-scrolling: touch;
        min-height: 0;
        background: #525252;
    }
    
    /* PDF Viewer Iframe */
    #pdfViewerIframe {
        width: 100%;
        height: 100%;
        border: none;
        display: block;
        background: #fff;
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
            overflow: hidden;
            -webkit-overflow-scrolling: touch;
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
<script>
    (function() {
        // Pastikan jQuery tersedia sebelum menjalankan seluruh modul
        var $ = window.jQuery || window.$;
        if (!$) {
            console.error('jQuery belum dimuat. Pastikan jQuery tersedia sebelum skrip ini.');
            return;
        }
        // Prevent duplicate initialization
        if (window.berkasRmInitialized) return;
        window.berkasRmInitialized = true;
        
        console.log('Berkas RM initialized with iframe PDF viewer');
        
        // PDF Viewer functions - menggunakan iframe (browser native PDF viewer)
        window.openPdfViewer = function(url, title) {
            var pdfTitle = title || 'Dokumen PDF';
            
            // Set title
            $('.pdf-viewer-title').text(pdfTitle);
            
            // Set download dan open new tab links
            $('#pdfViewerDownload').attr('href', url);
            $('#pdfViewerOpenNew').attr('href', url);
            $('#pdfViewerFallbackLink').attr('href', url);
            
            // Hide fallback
            $('#pdfViewerFallback').hide();
            
            // Set iframe src dengan URL PDF
            var iframe = document.getElementById('pdfViewerIframe');
            if (iframe) {
                // Tambahkan parameter untuk fit page di browser PDF viewer
                var pdfUrl = url;
                if (!pdfUrl.includes('#')) {
                    pdfUrl += '#page=1&zoom=page-fit';
                }
                iframe.src = pdfUrl;
                console.log('Loading PDF in iframe:', pdfUrl);
            }
            
            // Tampilkan modal
            $('#pdfViewerOverlay').fadeIn(200);
            $('body').css('overflow', 'hidden');
        };
        
        window.closePdfViewer = function() {
            $('#pdfViewerOverlay').fadeOut(200);
            
            // Clear iframe src untuk free memory
            var iframe = document.getElementById('pdfViewerIframe');
            if (iframe) {
                iframe.src = '';
            }
            
            $('body').css('overflow', '');
        };
        
        // OLD CODE - REMOVED: Fungsi helper untuk fetch PDF sebagai blob (mengatasi CORS)
        function fetchPdfAsBlob_OLD(url) {
            return new Promise(function(resolve, reject) {
                console.log('Processing PDF URL:', url);
                
                // Cek apakah URL sudah menggunakan proxy
                var isProxyUrl = url.includes('pdf-proxy.php');
                
                // Cek apakah URL adalah cross-origin
                var urlObj = new URL(url, window.location.href);
                var isCrossOrigin = urlObj.origin !== window.location.origin;
                
                // Jika menggunakan proxy, coba langsung gunakan URL karena proxy sudah handle CORS
                // PDF.js bisa langsung memuat dari URL proxy tanpa perlu fetch sebagai blob
                if (isProxyUrl) {
                    console.log('Proxy URL detected, using directly with PDF.js');
                    // Langsung resolve URL karena proxy sudah handle CORS dengan benar
                    resolve(url);
                    return;
                }
                
                // Jika cross-origin (bukan proxy), validasi response terlebih dahulu
                if (isCrossOrigin) {
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
                        // Cek status response
                        if (!response.ok) {
                            // Coba parse error message dari response
                            return response.text().then(function(text) {
                                var errorMsg = 'HTTP error! status: ' + response.status;
                                try {
                                    var errorJson = JSON.parse(text);
                                    if (errorJson.error || errorJson.message) {
                                        errorMsg = (errorJson.error || '') + ' ' + (errorJson.message || '');
                                    }
                                } catch(e) {
                                    // Bukan JSON, coba parse HTML untuk error message
                                    var htmlMatch = text.match(/<title[^>]*>([^<]+)<\/title>/i) || 
                                                   text.match(/<h1[^>]*>([^<]+)<\/h1>/i) ||
                                                   text.match(/<p[^>]*>([^<]+)<\/p>/i);
                                    if (htmlMatch && htmlMatch[1]) {
                                        errorMsg += ' - ' + htmlMatch[1].trim();
                                    } else if (text.length < 500) {
                                        // Jika response pendek, tampilkan sebagian
                                        var cleanText = text.replace(/<[^>]+>/g, '').trim();
                                        if (cleanText.length > 0 && cleanText.length < 200) {
                                            errorMsg += ' - ' + cleanText;
                                        }
                                    }
                                }
                                throw new Error(errorMsg);
                            });
                        }
                        
                        // Cek content type - sangat penting untuk validasi
                        var contentType = response.headers.get('content-type') || '';
                        console.log('Response content-type:', contentType);
                        
                        // Validasi content type
                        if (!contentType.includes('application/pdf') && !contentType.includes('application/octet-stream')) {
                            // Jika bukan PDF, coba baca sebagai text untuk melihat error
                            return response.text().then(function(text) {
                                var errorMsg = 'Response bukan PDF. Content-Type: ' + contentType;
                                
                                // Coba parse JSON error
                                try {
                                    var errorJson = JSON.parse(text);
                                    if (errorJson.error || errorJson.message) {
                                        errorMsg = (errorJson.error || '') + ' ' + (errorJson.message || '');
                                        if (errorJson.debug && errorJson.debug.full_path) {
                                            errorMsg += ' (Path: ' + errorJson.debug.full_path + ')';
                                        }
                                    }
                                } catch(e) {
                                    // Bukan JSON, coba parse HTML untuk error message
                                    var htmlMatch = text.match(/<title[^>]*>([^<]+)<\/title>/i) || 
                                                   text.match(/<h1[^>]*>([^<]+)<\/h1>/i) ||
                                                   text.match(/<p[^>]*class="?error"?[^>]*>([^<]+)<\/p>/i) ||
                                                   text.match(/<div[^>]*class="?error"?[^>]*>([^<]+)<\/div>/i);
                                    if (htmlMatch && htmlMatch[1]) {
                                        errorMsg += ' - ' + htmlMatch[1].trim();
                                    } else {
                                        // Coba cari pesan error umum di HTML
                                        var cleanText = text.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
                                        if (cleanText.length > 0 && cleanText.length < 300) {
                                            errorMsg += ' - ' + cleanText.substring(0, 200);
                                        } else {
                                            errorMsg += ' - Server mengembalikan HTML error page';
                                        }
                                    }
                                }
                                
                                console.error('Proxy returned HTML instead of PDF:', {
                                    contentType: contentType,
                                    status: response.status,
                                    url: url,
                                    responsePreview: text.substring(0, 500)
                                });
                                
                                throw new Error(errorMsg);
                            });
                        }
                        
                        // Convert ke blob
                        return response.blob();
                    }).then(function(blob) {
                        // Validasi ukuran blob
                        if (blob.size === 0) {
                            throw new Error('PDF file is empty');
                        }
                        
                        // Validasi bahwa blob benar-benar PDF dengan membaca header
                        return new Promise(function(resolveBlob, rejectBlob) {
                            var reader = new FileReader();
                            reader.onload = function(e) {
                                var arrayBuffer = e.target.result;
                                var uint8Array = new Uint8Array(arrayBuffer);
                                
                                // Cek PDF signature: %PDF
                                var pdfSignature = String.fromCharCode(uint8Array[0]) + 
                                                  String.fromCharCode(uint8Array[1]) + 
                                                  String.fromCharCode(uint8Array[2]) + 
                                                  String.fromCharCode(uint8Array[3]);
                                
                                if (pdfSignature !== '%PDF') {
                                    console.error('Invalid PDF signature:', pdfSignature);
                                    rejectBlob(new Error('File bukan PDF yang valid. Signature: ' + pdfSignature));
                                    return;
                                }
                                
                                console.log('PDF validated successfully, size:', blob.size, 'bytes');
                                var blobUrl = URL.createObjectURL(blob);
                                resolveBlob(blobUrl);
                            };
                            reader.onerror = function() {
                                rejectBlob(new Error('Gagal membaca file PDF'));
                            };
                            // Baca hanya 4 byte pertama untuk validasi
                            reader.readAsArrayBuffer(blob.slice(0, 4));
                        });
                    }).then(function(blobUrl) {
                        resolve(blobUrl);
                    }).catch(function(error) {
                        console.error('Error fetching PDF as blob:', error);
                        console.error('Error details:', {
                            name: error.name,
                            message: error.message,
                            url: url
                        });
                        reject(error);
                    });
                    
                    return;
                }
                
                // Jika same-origin dan bukan proxy, langsung gunakan URL
                console.log('Same-origin URL, using directly');
                resolve(url);
            });
        }
        
        // OLD CODE REMOVED - PDF.js functions tidak diperlukan lagi karena menggunakan iframe
        // Fungsi untuk render halaman PDF menggunakan PDF.js - REMOVED
        function renderPdfPage_OLD(num, scale) {
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
        
        // OLD CODE REMOVED - Thumbnail menggunakan iframe langsung di HTML
        // Fungsi untuk render thumbnail PDF menggunakan PDF.js - REMOVED
        function renderPdfThumbnail_OLD(canvas, url) {
            if (typeof pdfjsLib === 'undefined') {
                console.error('PDF.js tidak tersedia');
                return;
            }
            
            var $canvas = $(canvas);
            var $container = $canvas.closest('.thumbnail-pdf');
            
            // Show loading
            $container.find('.pdf-loading').addClass('show');
            
            // Untuk proxy URL, langsung gunakan URL karena proxy sudah handle CORS
            // Untuk cross-origin non-proxy, fetch sebagai blob terlebih dahulu
            var isProxyUrl = url.includes('pdf-proxy.php');
            var loadPromise;
            
            if (isProxyUrl) {
                // Langsung load dari proxy URL
                console.log('Loading PDF thumbnail directly from proxy URL');
                var loadingTask = pdfjsLib.getDocument({
                    url: url,
                    httpHeaders: {},
                    withCredentials: false,
                    verbosity: 0,
                    stopAtErrors: false
                });
                loadPromise = loadingTask.promise;
            } else {
                // Fetch sebagai blob untuk cross-origin non-proxy
                loadPromise = fetchPdfAsBlob(url).then(function(blobUrl) {
                    var loadingTask = pdfjsLib.getDocument({
                        url: blobUrl,
                        httpHeaders: {},
                        withCredentials: false,
                        verbosity: 0,
                        stopAtErrors: false
                    });
                    return loadingTask.promise;
                });
            }
            
            loadPromise.then(function(pdf) {
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
                console.error('Error details:', {
                    name: error.name,
                    message: error.message,
                    url: url
                });
                
                $container.find('.pdf-loading').removeClass('show');
                $canvas.attr('data-loaded', 'error');
                
                // Tampilkan icon jika error
                $container.find('.pdf-preview-icon').show();
                
                // Log error untuk debugging
                if (error.name === 'InvalidPDFException' || error.message.includes('Invalid PDF structure')) {
                    console.warn('PDF thumbnail error: File tidak valid atau proxy mengembalikan error');
                }
            });
        }
        
        // OLD CODE REMOVED - openPdfViewer sudah didefinisikan di atas dengan iframe
        // PDF Viewer functions - menggunakan PDF.js - REMOVED
        window.openPdfViewer_OLD = function(url, title) {
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
            
            // Load PDF document
            console.log('Loading PDF from URL:', url);
            
            // Untuk proxy URL, langsung gunakan URL karena proxy sudah handle CORS
            // Untuk cross-origin non-proxy, fetch sebagai blob terlebih dahulu
            var isProxyUrl = url.includes('pdf-proxy.php');
            var loadPromise;
            
            if (isProxyUrl) {
                // Langsung load dari proxy URL
                console.log('Loading PDF directly from proxy URL');
                var loadingTask = pdfjsLib.getDocument({
                    url: url,
                    httpHeaders: {},
                    withCredentials: false,
                    verbosity: 1,
                    stopAtErrors: false
                });
                loadPromise = loadingTask.promise;
            } else {
                // Fetch sebagai blob untuk cross-origin non-proxy
                loadPromise = fetchPdfAsBlob(url).then(function(blobUrl) {
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
                        verbosity: 1,
                        stopAtErrors: false
                    });
                    
                    return loadingTask.promise;
                });
            }
            
            loadPromise.then(function(pdfDoc) {
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
                    stack: error.stack,
                    url: url
                });
                
                var errorMsg = 'Gagal memuat dokumen PDF';
                var errorDetail = '';
                var isProxyUrl = url.includes('pdf-proxy.php');
                
                // Tentukan pesan error berdasarkan jenis error
                if (error.name === 'InvalidPDFException' || error.message.includes('Invalid PDF structure')) {
                    if (isProxyUrl) {
                        // Jika menggunakan proxy dan error Invalid PDF, kemungkinan proxy mengembalikan HTML error
                        errorMsg = 'Proxy mengembalikan response yang bukan PDF';
                        errorDetail = 'Proxy mungkin mengembalikan error HTML atau JSON. ' +
                                     'Coba buka URL langsung di tab baru untuk melihat error detail. ' +
                                     'Pastikan file PDF ada di server dan path benar.';
                        
                        // Coba fetch response untuk melihat error detail
                        fetch(url, {
                            method: 'GET',
                            mode: 'cors',
                            credentials: 'omit'
                        }).then(function(response) {
                            return response.text();
                        }).then(function(text) {
                            console.log('Proxy response preview:', text.substring(0, 500));
                            
                            // Coba parse JSON error
                            try {
                                var errorJson = JSON.parse(text);
                                if (errorJson.error || errorJson.message) {
                                    errorDetail = (errorJson.error || '') + ' ' + (errorJson.message || '');
                                    if (errorJson.debug && errorJson.debug.full_path) {
                                        errorDetail += ' (Path: ' + errorJson.debug.full_path + ')';
                                    }
                                    // Update error message di UI
                                    $('#pdfViewerFallback p:first').html('<strong>' + errorMsg + '</strong>');
                                    $('#pdfViewerFallback p:eq(1)').text(errorDetail);
                                }
                            } catch(e) {
                                // Bukan JSON, mungkin HTML error page
                                var htmlMatch = text.match(/<title[^>]*>([^<]+)<\/title>/i) || 
                                               text.match(/<h1[^>]*>([^<]+)<\/h1>/i);
                                if (htmlMatch && htmlMatch[1]) {
                                    errorDetail = 'Error: ' + htmlMatch[1].trim();
                                    $('#pdfViewerFallback p:first').html('<strong>' + errorMsg + '</strong>');
                                    $('#pdfViewerFallback p:eq(1)').text(errorDetail);
                                }
                            }
                        }).catch(function(fetchError) {
                            console.error('Error fetching proxy response:', fetchError);
                        });
                    } else {
                        errorMsg = 'File PDF tidak valid atau rusak';
                        errorDetail = 'File mungkin corrupt atau bukan file PDF yang valid.';
                    }
                } else if (error.name === 'MissingPDFException') {
                    errorMsg = 'File PDF tidak ditemukan';
                    errorDetail = 'File tidak ditemukan di server. Pastikan path file benar.';
                } else if (error.name === 'UnexpectedResponseException') {
                    errorMsg = 'Tidak dapat mengakses file PDF';
                    errorDetail = 'Server mengembalikan response yang tidak diharapkan. ' +
                                 'Mungkin ada masalah dengan proxy atau server.';
                } else if (error.message.includes('HTTP error') || error.message.includes('status:')) {
                    errorMsg = 'Error saat mengambil file PDF';
                    errorDetail = error.message;
                } else if (error.message.includes('Response bukan PDF') || error.message.includes('Content-Type')) {
                    errorMsg = 'Server mengembalikan response yang bukan PDF';
                    errorDetail = error.message + '. ' +
                                 'Proxy mungkin mengembalikan error JSON atau HTML.';
                } else if (error.message) {
                    errorMsg = 'Error: ' + error.message;
                    errorDetail = 'Terjadi kesalahan saat memuat PDF.';
                }
                
                // Tampilkan error dengan detail
                $('#pdfViewerFallback').html(
                    '<div class="text-center p-5">' +
                    '<i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i>' +
                    '<p class="mb-2"><strong>' + errorMsg + '</strong></p>' +
                    (errorDetail ? '<p class="mb-3 text-muted small">' + errorDetail + '</p>' : '') +
                    '<p class="mb-3 text-muted small" style="word-break: break-all;">URL: ' + url + '</p>' +
                    '<div class="mt-4">' +
                    '<a href="' + url + '" class="btn btn-primary mr-2" target="_blank" download>' +
                    '<i class="fas fa-download"></i> Download PDF</a>' +
                    '<a href="' + url + '" class="btn btn-secondary" target="_blank">' +
                    '<i class="fas fa-external-link-alt"></i> Buka di Tab Baru</a>' +
                    '</div>' +
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
        
        // OLD CODE REMOVED - closePdfViewer sudah didefinisikan di atas dengan iframe
        // PDF Viewer Controls - tidak diperlukan lagi karena menggunakan browser native PDF viewer
        function initPdfViewerControls_OLD() {
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
        
        // PDF Viewer Controls tidak diperlukan lagi karena menggunakan browser native PDF viewer
        
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
        var imageObserver = null;
        var pdfIframeObserver = null;
        
        // Lazy loading dengan Intersection Observer untuk gambar dan PDF iframe
        window.initLazyLoading = function() {
            // Check if Intersection Observer is supported
            if (!('IntersectionObserver' in window)) {
                // Fallback: load all images and PDF iframes immediately
                $('.thumbnail-image img.lazy-image:not([data-loaded="true"])').each(function() {
                    var $img = $(this);
                    var src = $img.data('src');
                    if (src) {
                        $img.attr('src', src);
                        $img.attr('data-loaded', 'true');
                    }
                });
                
                // Load all PDF iframes
                $('.pdf-thumb-iframe.lazy-pdf-iframe:not([src])').each(function() {
                    var iframe = this;
                    var $iframe = $(iframe);
                    var src = $iframe.data('src');
                    if (src) {
                        iframe.src = src;
                        iframe.onload = function() {
                            $iframe.closest('.thumbnail-pdf').find('.pdf-preview-icon').fadeOut(200);
                        };
                    }
                });
                return;
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
            
            // Buat observer untuk PDF iframe jika belum ada
            if (!pdfIframeObserver) {
                pdfIframeObserver = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            var iframe = entry.target;
                            var $iframe = $(iframe);
                            var src = $iframe.data('src');
                            
                            // Hanya load jika belum punya src dan punya data-src
                            if (src && !iframe.src) {
                                console.log('IntersectionObserver: Loading PDF iframe:', src);
                                
                                // Set src untuk load PDF
                                iframe.src = src;
                                
                                // Handle load event untuk hide icon
                                iframe.onload = function() {
                                    $iframe.closest('.thumbnail-pdf').find('.pdf-preview-icon').fadeOut(200);
                                };
                                
                                // Handle error - tetap tampilkan icon
                                iframe.onerror = function() {
                                    console.warn('Error loading PDF iframe:', src);
                                    // Tetap tampilkan icon jika error
                                };
                                
                                // Stop observing this element
                                pdfIframeObserver.unobserve(entry.target);
                            }
                        }
                    });
                }, {
                    root: null, // Use viewport as root
                    rootMargin: '300px', // Start loading 300px before entering viewport (lebih besar untuk PDF karena lebih berat)
                    threshold: 0.01
                });
                console.log('PDF Iframe Observer created');
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
            
            // Load PDF iframe yang sudah di viewport langsung tanpa observer
            var pdfCount = 0;
            var pdfObservedCount = 0;
            $('.pdf-thumb-iframe.lazy-pdf-iframe:not([src])').each(function() {
                pdfCount++;
                var iframe = this;
                var $iframe = $(iframe);
                var src = $iframe.data('src');
                
                if (!src) {
                    console.warn('PDF iframe tidak memiliki data-src:', this);
                    return;
                }
                
                if (isInViewport(this)) {
                    console.log('Loading PDF iframe immediately (in viewport):', src);
                    iframe.src = src;
                    iframe.onload = function() {
                        $iframe.closest('.thumbnail-pdf').find('.pdf-preview-icon').fadeOut(200);
                    };
                } else if (!$iframe.data('observed')) {
                    // Observe elemen yang belum di viewport
                    try {
                        pdfIframeObserver.observe(this);
                        $iframe.data('observed', true);
                        pdfObservedCount++;
                        console.log('Observing PDF iframe:', src);
                    } catch(e) {
                        console.error('Error observing PDF iframe:', e, src);
                    }
                }
            });
            console.log('PDF iframe lazy loading initialized:', pdfCount, 'total,', pdfObservedCount, 'observed');
            
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
            // Load all PDF iframes (fallback untuk browser tanpa Intersection Observer)
            $('.pdf-thumb-iframe.lazy-pdf-iframe:not([src])').each(function() {
                var iframe = this;
                var $iframe = $(iframe);
                var src = $iframe.data('src');
                if (src) {
                    iframe.src = src;
                    iframe.onload = function() {
                        $iframe.closest('.thumbnail-pdf').find('.pdf-preview-icon').fadeOut(200);
                    };
                }
            });
            
            // Load all images
            
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
        
        // Initialize lazy loading untuk images saat document ready
        $(document).ready(function() {
            initLazyLoading();
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
                    $('.thumbnail-image img.lazy-image:not([data-loaded="true"])').removeData('observed');
                    $('.pdf-thumb-iframe.lazy-pdf-iframe:not([src])').removeData('observed');
                    
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