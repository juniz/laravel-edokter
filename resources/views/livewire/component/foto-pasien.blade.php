<div>
    <div wire:ignore.self id="foto-pasien-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="my-modal-title">Fotk Pasien</h5>
                    <button class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- <form wire:submit.prevent='simpan'>
                        <x-ui.input label="No. HP" id="no_hp" type="text" model='no_hp' />
                        <div class="d-flex flex-row">
                            <div class="ml-auto">
                                <button class="btn btn-primary" type="submit">Simpan</button>
                                <button class="btn btn-secondary" data-dismiss="modal" type="button">Batal</button>
                            </div>
                        </div>
                    </form> --}}
                </div>
            </div>
        </div>
    </div>
</div>

@section('plugins.Webcam', true)

@push('js')
<script>
    Webcam.set({
        width: 490,
        height: 350,
        image_format: 'jpeg',
        jpeg_quality: 90
    });

    Webcam.attach('#my_camera');

    function take_snapshot() {
        Webcam.snap(function(data_uri) {
            document.getElementById('my_result').innerHTML = '<img src="' + data_uri + '"/>';
            document.getElementById('foto').value = data_uri;
        });
    }
</script>
@endpush