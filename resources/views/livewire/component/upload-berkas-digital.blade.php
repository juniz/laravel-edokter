{{-- filepath: /Users/hardiko/Documents/Developer/LARAVEL/laravel-edokter/resources/views/livewire/component/upload-berkas-digital.blade.php --}}
<div wire:ignore>
    {{-- Modal for File Upload --}}
    <x-adminlte-modal id="modal-upload-berkas" title="Upload Berkas Digital" v-centered static-backdrop scrollable>
        <form wire:submit.prevent="uploadFile">
            <div class="form-group">
                <label for="file">Pilih File</label>
                <input type="file" id="file" wire:model="file" class="filepond" />
                @error('file') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="file_name">Nama File</label>
                <input type="text" id="file_name" wire:model.lazy="fileName" class="form-control" placeholder="Masukkan nama file" />
                @error('fileName') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="kdBerkas">Jenis Berkas Digital</label>
                <select id="kdBerkas" wire:model.lazy="kdBerkas" class="form-control">
                    <option value="">Pilih Jenis Berkas Digital</option>
                    @foreach ($masterBerkas as $berkasDigital)
                        <option value="{{ $berkasDigital->kode }}">{{ $berkasDigital->nama }}</option>
                    @endforeach
                </select>
                @error('kdBerkas') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="d-flex justify-content-end">
                <x-adminlte-button type="submit" label="Upload" theme="primary" icon="fas fa-upload" />
            </div>
        </form>
    </x-adminlte-modal>
</div>

@push('css')
    {{-- FilePond CSS --}}
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
@endpush

@push('js')
    {{-- FilePond JS --}}
    <script src="https://unpkg.com/filepond/dist/filepond.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
    <script src="https://unpkg.com/filepond/locale/id-id.js"></script>
    <script>
        document.addEventListener('livewire:load', function () {
            // Initialize FilePond
            const inputElement = document.querySelector('.filepond');
            const pond = FilePond.create(inputElement);

            // Set FilePond to use Indonesian locale
            FilePond.setOptions({
                labelIdle: '<span class="filepond--label-action"> Tambah File </span>',
                server: {
                    process: (fieldName, file, metadata, load, error, progress, abort) => {
                        @this.upload('file', file, load, error, progress);
                    },
                    revert: (filename, load) => {
                        @this.removeUpload('file', filename, load);
                    },
                },
            });
        });
        Livewire.on('close-modal', (event) => {
            $('#'+event.id).modal('hide');
        });
    </script>
@endpush
