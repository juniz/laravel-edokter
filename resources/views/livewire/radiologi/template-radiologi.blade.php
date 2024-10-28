<div wire:ignore.self id="modal-template-radiologi" data-backdrop="false" class="modal fade" role="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="my-modal-title">Template Radiologi</h5>
                <button class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-12">
                        <input wire:model.debounce.500ms='search' type="text" placeholder="Cari template ...." class="form-control" id="cari_rad" id="cari_rad">
                    </div>
                </div>
                <div class="list-group">
                    @forelse ($datas as $item)
                        <button wire:click='$emit("pilihTemplateRadiologi", "{{ $item->no_template ?? '' }}")' type="button" class="list-group-item list-group-item-action">{{ $item->nama_pemeriksaan ?? '' }}</button>
                    @empty
                        
                    @endforelse
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        window.addEventListener('closeModalTemplateRadiologi', function(){
            $('#modal-template-radiologi').modal('hide');
        });
    </script>
@endpush
