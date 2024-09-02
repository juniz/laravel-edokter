<div>
    <div>
        {!! $result !!}
    </div>
    <div wire:loading wire:target='gemini'>
        <div class="d-flex flex-row justify-content-center">
            Loading ......
        </div>
    </div>
    <div>
        <div class="w-100 position-static">
            <input type="text" class="form-control" wire:keydown.enter='gemini' wire:model.debounce500ms='promp' placeholder="Masukkan pertanyaan di sini">
        </div>
    </div>
    {{-- <x-slot name="footerSlot">
        
        <x-adminlte-button theme="danger" label="Dismiss" data-dismiss="modal"/>
    </x-slot> --}}
</div>
