<div>
    <x-adminlte-card>
        <form wire:submit.prevent='simpan'>
            <x-ui.textarea model="bacaanRadiologi" rows='20' />
            <div class="d-flex justify-content-end" style="gap:4px">
                {{-- <button type="button" class="btn btn-block btn-warning" data-toggle="modal" data-target="#modal-template-radiologi">
                    Template
                </button> --}}
                <x-adminlte-button label="Template" theme="warning" icon="fas fa-save" data-toggle="modal" data-target="#modal-template-radiologi" type="button" />
                <x-adminlte-button label="Simpan" theme="primary" icon="fas fa-save" type="submit" />
            </div>
        </form>
    </x-adminlte-card>
    <livewire:radiologi.template-radiologi />
</div>
