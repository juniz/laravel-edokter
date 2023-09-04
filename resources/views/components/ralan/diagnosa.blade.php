<div>
    <x-adminlte-card title="Diagnosa" theme="info" icon="fas fa-lg fa-file-medical" collapsible="collapsed" maximizable>
        <form action="">
            <div class="form-group">
                <label for="diagnosa">Diagnosa</label>
                <select id="diagnosa" class="form-control" name="diagnosa"></select>
            </div>
            <div class="form-group">
                <label for="prioritas">Prioritas</label>
                <select id="prioritas" class="form-control" name="prioritas">
                    <option value="1">Diagnosa Ke-1</option>
                    <option value="2">Diagnosa Ke-2</option>
                    <option value="3">Diagnosa Ke-3</option>
                    <option value="4">Diagnosa Ke-4</option>
                    <option value="5">Diagnosa Ke-5</option>
                    <option value="6">Diagnosa Ke-6</option>
                    <option value="7">Diagnosa Ke-7</option>
                    <option value="8">Diagnosa Ke-8</option>
                    <option value="9">Diagnosa Ke-9</option>
                    <option value="10">Diagnosa Ke-10</option>
                </select>
            </div>
        </form>
    </x-adminlte-card>
</div>

@push('js')
<script>
    $(function () {
        $('#diagnosa').select2({
            placeholder: 'Pilih Diagnosa',
            ajax: {
                url: "{{ route('diagnosa') }}",
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results: data.map(function (item) {
                            return {
                                id: item.kd_penyakit,
                                text: item.nm_penyakit
                            }
                        })
                    };
                },
                cache: true
            }
        });
    });
</script>
@endpush