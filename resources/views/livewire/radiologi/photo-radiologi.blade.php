<div>
    <div class="container">
        <div class="d-inline-flex p-2">
            @forelse($photoRadiologi as $gambar)
            <a href="{{ env('URL_RADIOLOGI').$gambar->lokasi_gambar }}"
                data-toggle="lightbox" data-gallery="example-gallery">
                <img src="{{ env('URL_RADIOLOGI').$gambar->lokasi_gambar }}"
                    class="img-fluid p-2">
            </a>
            @empty
            <div class="justify-content-center">
                <p>Belum ada gambar radiologi</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

@push('js')
<script>

    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
            event.preventDefault();
            $(this).ekkoLightbox();
    });
</script>
@endpush
