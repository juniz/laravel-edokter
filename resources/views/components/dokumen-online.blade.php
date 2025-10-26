{{-- Modal Dokumen Online --}}
<x-adminlte-modal id="modalDokumenOnline" title="Dokumen Online" size="lg" theme="primary" v-centered static-backdrop scrollable>
    <div id="dokumenOnlineContent">
        {{-- Folder List --}}
        <div id="folderList" class="list-group mb-3">
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Memuat folder...</p>
            </div>
        </div>
        
        {{-- Search Box (Document List) --}}
        <div id="searchBoxDocument" style="display: none;" class="mb-3">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-light">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                </div>
                <input type="text" class="form-control" id="searchDocument" placeholder="Cari dokumen...">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" id="clearSearchDocument" style="display: none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <small class="text-muted mt-1 d-block" id="searchResultInfo"></small>
        </div>
        
        {{-- Document List --}}
        <div id="documentList" class="list-group" style="display: none;">
        </div>
        
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" id="breadcrumbNav" style="display: none;">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#" id="btnBackToFolder">Dokumen Online</a></li>
                <li class="breadcrumb-item active" id="currentFolder"></li>
            </ol>
        </nav>
    </div>
    
    <x-slot name="footerSlot">
        <x-adminlte-button theme="danger" label="Tutup" data-dismiss="modal" />
    </x-slot>
</x-adminlte-modal>

{{-- Modal PDF Viewer --}}
<div class="modal fade" id="modalPdfViewer" tabindex="-1" role="dialog" aria-labelledby="modalPdfViewerTitle" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 95vw;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalPdfViewerTitle">
                    <i class="fas fa-file-pdf mr-2"></i>Viewer PDF
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0" style="height: 85vh;">
                <div id="pdfLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Memuat PDF...</span>
                    </div>
                    <p class="mt-3">Memuat dokumen PDF...</p>
                </div>
                
                <iframe id="pdfIframe" src="" style="width: 100%; height: 85vh; border: none; display: none;"></iframe>
                
                <div id="pdfError" class="text-center py-5" style="display: none;">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5>Gagal Memuat PDF</h5>
                    <p class="text-muted">Dokumen tidak dapat ditampilkan dalam viewer.</p>
                    <a id="pdfDownloadLink" href="#" target="_blank" class="btn btn-primary">
                        <i class="fas fa-download"></i> Unduh PDF
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <a id="pdfExternalLink" href="#" target="_blank" class="btn btn-info">
                    <i class="fas fa-external-link-alt"></i> Buka di Tab Baru
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

