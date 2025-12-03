<div class="berkas-rm-container" wire:key="berkas-rm-{{ $rm ?? 'default' }}">
    @if($isLoading)
    <div class="loading-wrapper">
        <div class="loading-content">
            <div class="spinner-border text-info" role="status" style="width: 3rem; height: 3rem;">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-3 text-muted mb-0">Memuat berkas...</p>
        </div>
    </div>
    @else
    @if(isset($berkasGrouped) && $berkasGrouped->count() > 0)
    
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
                        $fileUrl = "https://simrs.rsbhayangkaranganjuk.com/webapps/berkasrawat/{$item->lokasi_file}";
                        $fileExt = strtoupper(pathinfo($item->lokasi_file, PATHINFO_EXTENSION));
                        $fileDate = isset($item->created_at) ? \Carbon\Carbon::parse($item->created_at)->isoFormat('DD MMM YYYY') : '-';
                    @endphp
                    
                    <div class="berkas-card {{ $isPdf ? 'is-pdf' : 'is-image' }}">
                        {{-- Thumbnail Preview --}}
                        <div class="thumbnail-wrapper">
                            <span class="berkas-badge">{{ $index + 1 }}</span>
                            <span class="file-ext-badge {{ $isPdf ? 'badge-pdf' : 'badge-img' }}">{{ $fileExt }}</span>
                            
                @if($isPdf)
                <div class="thumbnail-pdf" data-url="{{ $fileUrl }}">
                    <iframe src="{{ $fileUrl }}#view=FitH&amp;toolbar=0&amp;navpanes=0" class="pdf-thumb" loading="lazy"></iframe>
                    <div class="pdf-overlay">
                        <i class="fas fa-search-plus"></i>
                        <span>Klik untuk membuka</span>
                    </div>
                </div>
                @else
                            <div class="thumbnail-image">
                                <a href="{{ $fileUrl }}" class="lightbox-trigger" data-gallery="berkas-gallery" data-width="1280" data-height="700" data-title="{{ $fileName }}">
                                    <img src="{{ $fileUrl }}" alt="{{ $fileName }}" loading="lazy" onerror="this.onerror=null; this.src='/images/no-image.png'; this.parentElement.classList.add('img-error');">
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
                <button class="pdf-viewer-close" onclick="closePdfViewer()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <iframe id="pdfViewerFrame" src="" frameborder="0"></iframe>
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
    }
    .berkas-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
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
    }
    .pdf-thumb {
        width: 100%;
        height: 100%;
        border: none;
        background: #fff;
        pointer-events: none; /* biar klik masuk ke container, bukan iframe */
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
    .thumbnail-pdf:hover .pdf-overlay {
        opacity: 1;
    }
    
    /* Thumbnail Image */
    .thumbnail-image {
        width: 100%;
        height: 100%;
        position: relative;
    }
    .thumbnail-image a {
        display: block;
        width: 100%;
        height: 100%;
    }
    .thumbnail-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
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
    }
    .pdf-viewer-title {
        font-weight: 600;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .pdf-viewer-title::before {
        content: '\f1c1';
        font-family: 'Font Awesome 5 Free';
        font-weight: 400;
        color: #dc3545;
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
    .pdf-viewer-container iframe {
        flex: 1;
        width: 100%;
        border: none;
    }
    
    /* Empty State */
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
        // Prevent duplicate initialization
        if (window.berkasRmInitialized) return;
        window.berkasRmInitialized = true;
        
        // PDF Viewer functions - define globally
        window.openPdfViewer = function(url, title) {
            $('#pdfViewerFrame').attr('src', url);
            $('.pdf-viewer-title').text(title || 'Dokumen PDF');
            $('#pdfViewerOverlay').fadeIn(200);
            $('body').css('overflow', 'hidden');
        };
        
        window.closePdfViewer = function() {
            $('#pdfViewerOverlay').fadeOut(200);
            $('#pdfViewerFrame').attr('src', '');
            $('body').css('overflow', '');
        };
        
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
        $(document).off('click.lightbox').on('click.lightbox', '.berkas-rm-container .lightbox-trigger', function(event) {
            event.preventDefault();
            event.stopPropagation();
            
            var $link = $(this);
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
        });
        
        // PDF viewer - klik pada kartu atau thumbnail PDF
        $(document).off('click.pdfview').on('click.pdfview', '.berkas-card.is-pdf, .thumbnail-pdf, .thumbnail-pdf .pdf-overlay', function(event) {
            // Hindari konflik dengan link lain jika ada
            event.preventDefault();
            event.stopPropagation();
            var $card = $(this).closest('.berkas-card');
            if (!$card.length && $(this).hasClass('berkas-card')) {
                $card = $(this);
            }
            var $thumb = $card.find('.thumbnail-pdf');
            var url = $thumb.data('url');
            var title = $card.find('.file-name').attr('title') || $card.find('.file-name').text() || 'Dokumen PDF';
            if (url) {
                openPdfViewer(url, title);
            }
        });
        
        // Function to find and call BerkasRm component
        function callBerkasRmSetRm(rm) {
            if (!rm || typeof Livewire === 'undefined') {
                console.log('Invalid RM or Livewire not available');
                return false;
            }
            
            console.log('Attempting to call setRm, RM:', rm);
            
            // Find BerkasRm component specifically inside the modal container
            var container = document.querySelector('#berkas-rm-container');
            if (!container) {
                console.log('Container #berkas-rm-container not found');
                // Fallback: use event
                Livewire.emit('setRm', rm);
                if (typeof Livewire.dispatch !== 'undefined') {
                    Livewire.dispatch('setRm', rm);
                }
                return false;
            }
            
            var berkasRmComponent = container.querySelector('[wire\\:id]');
            if (!berkasRmComponent) {
                console.log('BerkasRm component not found in container');
                // Fallback: use event
                Livewire.emit('setRm', rm);
                if (typeof Livewire.dispatch !== 'undefined') {
                    Livewire.dispatch('setRm', rm);
                }
                return false;
            }
            
            var componentId = berkasRmComponent.getAttribute('wire:id');
            console.log('Found component ID:', componentId);
            
            try {
                var component = Livewire.find(componentId);
                if (component && typeof component.call === 'function') {
                    console.log('Calling setRm on BerkasRm component, RM:', rm);
                    component.call('setRm', rm);
                    return true;
                } else {
                    console.log('Component found but call method not available');
                    // Fallback: use event
                    Livewire.emit('setRm', rm);
                    if (typeof Livewire.dispatch !== 'undefined') {
                        Livewire.dispatch('setRm', rm);
                    }
                    return false;
                }
            } catch(e) {
                console.log('Error calling component:', e);
                // Fallback: use event
                Livewire.emit('setRm', rm);
                if (typeof Livewire.dispatch !== 'undefined') {
                    Livewire.dispatch('setRm', rm);
                }
                return false;
            }
        }
        
        // Button RM click handler
        $(document).off('click.btnrm').on('click.btnrm', '#btn-rm', function(event) {
            event.preventDefault();
            var rm = $(this).data('rm');
            console.log('RM Button clicked, RM:', rm);
            
            // Show modal first
            $('#modal-rm').modal('show');
            
            // Then trigger event after modal is shown
            setTimeout(function() {
                callBerkasRmSetRm(rm);
            }, 300);
        });
        
        // Also handle modal shown event to ensure data loads
        $('#modal-rm').off('shown.bs.modal.berkas').on('shown.bs.modal.berkas', function() {
            var rm = $('#btn-rm').data('rm');
            console.log('Modal shown, RM:', rm);
            
            // Wait a bit for Livewire to initialize
            setTimeout(function() {
                callBerkasRmSetRm(rm);
            }, 100);
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
    
    // Re-apply view preference after Livewire update
    if (typeof Livewire !== 'undefined') {
        document.addEventListener('livewire:load', function() {
            Livewire.hook('message.processed', function() {
                setTimeout(function() {
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
                }, 50);
            });
        });
    }
</script>
@endpush