<div>
    <x-adminlte-modal id="modalRiwayatPemeriksaanRalan" title="Riwayat Pemeriksaan" size="xl" theme="info" v-centered
        static-backdrop scrollable wire:ignore.self>
        <livewire:component.riwayat :noRawat="request()->get('no_rawat')" />
        
        <x-slot name="footerSlot">
            <x-adminlte-button theme="danger" label="Tutup" data-dismiss="modal" />
        </x-slot>
    </x-adminlte-modal>
</div>

@push('js')
<script>
    $(document).ready(function () {
            $('#example').DataTable();
        });

        $(document).on('click', '[data-toggle="lightbox"]', function(event) {
                event.preventDefault();
                $(this).ekkoLightbox();
        });
</script>
@endpush