@props([
    'id' => null,
    'label' => null,
    'model' => null,
    'ajax' => null,
])

<div wire:ignore class="form-group">
    @if($label)
        <label for="{{$id}}">{{$label}}</label>
    @endif
    <select
        {{ $attributes->merge(['class' => 'form-control']) }}
        id="{{$id}}"
        name="{{$id}}" 
        @if($attributes->has('multiple')) multiple @endif
        @if($model)
        wire:model="{{$model}}"
        @endif
    >
        {{$slot}}
    </select>
</div>

@push('js')
    <script>
        $(document).ready(function() {
            $('#{{$id}}').select2({
                theme: 'bootstrap4',
                placeholder: 'Pilih {{$label}}',
            });
        });
        $('#{{$id}}').on('change', function(e) {
            @this.set('{{$model}}', e.target.value);
        });
    </script>
@endpush