<div>
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="resep-tab" data-toggle="tab" data-target="#resep" type="button"
                role="tab" aria-controls="resep" aria-selected="true">Resep</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="copyresep-tab" data-toggle="tab" data-target="#copyresep" type="button"
                role="tab" aria-controls="copyresep" aria-selected="false">Resep Racikan</button>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="resep" role="tabpanel" aria-labelledby="resep-tab">
            <x-adminlte-callout theme="info" title="Input Resep">
                <form method="post" id="resepForm" action="{{url('/api/resep/'.$encryptNoRawat)}}">
                    @csrf
                    <div class="containerResep">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="visible-sm">Nama Obat</label>
                                    <select name="obat[]" class="form-control obat w-100" id="obat"
                                        data-placeholder="Pilih Obat">
                                    </select>
                                </div>
                            </div>
                            <x-adminlte-input id="jumlah" label="Jml" name="jumlah[]" fgroup-class="col-md-2"
                                placeholder="Jml" />
                            <x-adminlte-input id="aturan" label="Aturan Pakai" name="aturan[]" fgroup-class="col-md-4"
                                placeholder="Aturan Pakai" />
                        </div>
                    </div>
                    <div class="row justify-content-end">
                        <x-adminlte-select-bs id="iter" name="iter" fgroup-class="col-md-4 my-auto"
                            data-placeholder="Pilih Iter">
                            <option value="-">Pilih jumlah iter</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            </x-adminlte-select>
                            <x-adminlte-button id="addFormResep" class="md:col-md-1 sm:col-sm-6 add-form-resep"
                                theme="success" label="+" />
                            <x-adminlte-button id="resepButton" class="md:col-md-2 sm:col-sm-6 ml-1" theme="primary"
                                type="submit" label="Simpan" />
                    </div>
                </form>
            </x-adminlte-callout>

            {{-- @if(count($resep) > 0) --}}
            <x-adminlte-callout theme="info">
                {{--
                <livewire:ralan.table-resep :no-rawat="$no_rawat" /> --}}
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                {{-- <th><input type="checkbox" id="checkboxAll"></th> --}}
                                <th>Nama Obat</th>
                                <th>Tanggal / Jam</th>
                                <th>Jumlah</th>
                                <th>Harga Satuan</th>
                                <th>Subtotal</th>
                                <th>Aturan Pakai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="body-resep">
                            @forelse($resep as $r)
                            @php
                                $hargaSatuan = (float) ($r->harga_satuan ?? 0);
                                $subtotal = (float) ($r->jml ?? 0) * $hargaSatuan;
                            @endphp
                            <tr>
                                {{--
                            <tr data-target="{{$r->kode_brng}}" class="cursor-pointer"> --}}
                                {{-- <td><input type="checkbox" id="checkbox-resep" disabled></td> --}}
                                <td>{{$r->nama_brng}}</td>
                                <td>{{$r->tgl_peresepan}} {{$r->jam_peresepan}}</td>
                                <td>{{$r->jml}}</td>
                                <td class="text-right">Rp {{ number_format($hargaSatuan, 0, ',', '.') }}</td>
                                <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                                <td>{{$r->aturan_pakai}}</td>
                                <td>
                                    <button class="btn btn-danger btn-sm"
                                        onclick='hapusObat("{{$r->no_resep}}", "{{$r->kode_brng}}", event)'>Hapus</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div id="resep-total-summary" class="mt-2 p-2 border-top bg-light rounded">
                        <div class="row justify-content-end">
                            <div class="col-md-4 col-sm-6">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td>Total Harga Obat</td>
                                        <td id="summary-total-harga" class="text-right font-weight-bold">Rp {{ number_format($totalHargaObat ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td>PPN (11%)</td>
                                        <td id="summary-total-ppn" class="text-right font-weight-bold">Rp {{ number_format($totalPpn ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr class="border-top">
                                        <td>Total + PPN</td>
                                        <td id="summary-total-dengan-ppn" class="text-right font-weight-bold text-primary">Rp {{ number_format($totalDenganPpn ?? 0, 0, ',', '.') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="container-delete-resep-button">
                        @php
                        if(count($resep) > 0){
                        $no_resep = $resep->pluck('no_resep')->toArray();
                        $no = $no_resep[0];
                        }else{
                        $no = '';
                        }
                        @endphp
                        <button class="btn btn-danger btn-sm btn-block disabled" data-target="{{$no}}"
                            id="delete-batch-button">Hapus
                            Obat</button>
                    </div>
                </div>
            </x-adminlte-callout>
            {{-- @endif --}}
            <x-adminlte-callout theme="info" title="Riwayat Peresepan">
                @php
                $config["responsive"] = true;
                $config['order'] = [[2, 'desc']];
                $config['processing'] = true;
                $config['serverSide'] = true;
                $config['pageLength'] = 5;
                $config['ajax'] = [
                    'url' => url('/api/riwayat-peresepan') . '?no_rm=' . $noRM,
                    'type' => 'GET',
                ];
                $config['columns'] = [
                    ['data' => 0, 'name' => 'no_resep', 'className' => 'align-middle text-center'],
                    ['data' => 1, 'name' => 'nm_dokter', 'className' => 'align-middle text-center'],
                    ['data' => 2, 'name' => 'tgl_peresepan', 'className' => 'align-middle text-center'],
                    ['data' => 3, 'name' => 'detail_resep', 'orderable' => false, 'searchable' => false],
                    ['data' => 4, 'name' => 'aksi', 'orderable' => false, 'searchable' => false, 'className' => 'align-middle text-center'],
                ];
                @endphp
                <x-adminlte-datatable id="tableRiwayatResep" :heads="$heads" :config="$config" head-theme="dark" striped
                    hoverable bordered compressed>
                </x-adminlte-datatable>
            </x-adminlte-callout>

        </div>
        <div class="tab-pane fade" id="copyresep" role="tabpanel" aria-labelledby="copyresep-tab">
            <x-adminlte-callout theme="info" title="Input Resep Racikan">
                <form method="post" id="copyresepForm" action="{{url('/ralan/simpan/copyresep/'.$encryptNoRawat)}}">
                    @csrf
                    <div class="containerCopyResep">
                        <div class="row">
                            <x-adminlte-input id="obat_racikan" label="Nama Racikan" name="nama_racikan"
                                fgroup-class="col-md-12" />
                            <x-adminlte-select-bs id="metode_racikan" name="metode_racikan" label="Metode Racikan"
                                fgroup-class="col-md-6" data-live-search data-live-search-placeholder="Cari..."
                                data-show-tick>
                                @foreach($dataMetodeRacik as $metode)
                                <option value="{{$metode->kd_racik}}">{{$metode->nm_racik}}</option>
                                @endforeach
                            </x-adminlte-select-bs>
                            <x-adminlte-input label="Jumlah" id="jumlah_racikan" value="10" name="jumlah_racikan"
                                fgroup-class="col-md-6" />
                            <x-adminlte-input label="Aturan Pakai" id="aturan_racikan" name="aturan_racikan"
                                fgroup-class="col-md-6" />
                            <x-adminlte-input label="Keterangan" value="Resep Racikan" id="keterangan_racikan"
                                name="keterangan_racikan" fgroup-class="col-md-6" />
                        </div>
                    </div>
                    <div class="containerRacikan">
                        <div class="form-row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label class="d-block">Obat</label>
                                    <select name="obatRacikan[]" class="form-control obat-racikan w-100"
                                        id="obatRacikan" data-placeholder="Pilih Obat">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="stok">Stok</label>
                                    <input id="stok" class="form-control p-1" type="text" name="stok[]" disabled>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="kps">Kps</label>
                                    <input id="kps" class="form-control p-1 text-black" type="text" name="kps[]"
                                        disabled>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="p1">P1</label>
                                    <input id="p1" class="form-control p-1" type="text" name="p1[]" required>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="p2">P2</label>
                                    <input id="p2" class="form-control p-1" oninput="hitungRacikan(0)" type=" text"
                                        name="p2[]" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="kandungan">Kandungan</label>
                                    <input id="kandungan" oninput="hitungJml(0)" class="form-control p-1 kandungan-0"
                                        type="text" name="kandungan[]" required>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="jml">Jml</label>
                                    <input id="jml" class="form-control p-1 jml-0" type="text" name="jml[]" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-end">
                        <div class="col-md-3 mr-auto">
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="satu-resep">
                                <label for="satu-resep">Jadikan Satu Resep</label>
                            </div>
                        </div>
                        <x-adminlte-select-bs id="iterRacikan" name="iterRacikan" fgroup-class="col-md-4 my-auto"
                            data-placeholder="Pilih Iter">
                            <option value="-">Pilih jumlah iter</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                        </x-adminlte-select-bs>
                        <x-adminlte-button id="deleteRacikan" onclick="deleteRowRacikan()"
                            class="md:col-md-1 sm:col-sm-6 delete-form-racikan mr-1" theme="danger" label="-" />
                        <x-adminlte-button id="addRacikan" class="md:col-md-1 sm:col-sm-6 add-form-racikan"
                            theme="success" label="+" />
                        <x-adminlte-button id="resepRacikanButton" class="md:col-md-2 sm:col-sm-6 ml-1" theme="primary"
                            type="submit" label="Simpan" />
                    </div>
                </form>
            </x-adminlte-callout>

            @if(count($resepRacikan) > 0)
            <x-adminlte-callout theme="info">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No Resep</th>
                                <th>Nama Racikan</th>
                                <th>Metode Racikan</th>
                                <th>Jumlah</th>
                                <th>Aturan</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resepRacikan as $r)
                            <tr>
                                <td>{{$r->no_resep}}</td>
                                <td>{{$r->no_racik}}. {{$r->nama_racik}}
                                    @php
                                    $racikan = $resepRacikan->where('no_resep', $r->no_resep)->first();
                                    @endphp
                                    <ul class="p-4">
                                        @if($racikan)
                                        <ul style="padding: 2px">
                                            @foreach($getDetailRacikan($racikan->no_resep) as $ror)
                                            <li>{{$ror->nama_brng}} - {{$ror->p1}}/{{$ror->p2}} - {{$ror->kandungan}} -
                                                {{$ror->jml}}</li>
                                            @endforeach
                                        </ul>
                                        @endif
                                    </ul>
                                </td>
                                <td>{{$r->nm_racik}}</td>
                                <td>{{$r->jml_dr}}</td>
                                <td>{{$r->aturan_pakai}}</td>
                                <td>{{$r->keterangan}}</td>
                                <td>
                                    <button class="btn btn-danger btn-sm"
                                        onclick='hapusRacikan("{{$r->no_resep}}", "{{$r->no_racik}}", event)'>Hapus</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </x-adminlte-callout>
            @endif
        </div>
    </div>
</div>

<x-adminlte-modal id="modalCopyResep" title="Copy Resep" size="lg" theme="teal" icon="fas fa-bell" v-centered
    scrollable>
    <div class="table-responsive">
        <table class="table table-copy-resep">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Jumlah</th>
                    <th scope="col">Nama Obat</th>
                    <th scope="col">Aturan Pakai</th>
                </tr>
            </thead>
            <tbody class="tbBodyCopy">
            </tbody>
        </table>
    </div>
    <x-slot name="footerSlot">
        <x-adminlte-button class="mr-2" id="simpanCopyResep" theme="primary" label="Simpan" data-dismiss="modal" />
        <x-adminlte-button theme="danger" label="Tutup" data-dismiss="modal" />
    </x-slot>
</x-adminlte-modal>

@push('css')
<style>
    .no-border {
        border: 0;
        box-shadow: none;
        /* You may want to include this as bootstrap applies these styles too */
    }

    .cursor-pointer {
        cursor: pointer;
    }
</style>
@endpush

@push('js')
{{-- <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
<script>
    $(document).on("select2:open", () => {
        document.querySelector(".select2-container--open .select2-search__field").focus()
    })
    function getIndexValue(name, index) {
            var doc = document.getElementsByName(name);
            return doc[index].value;
        }

        var i = 0;
        $("#addRacikan").click(function(e){
            e.preventDefault();
            i++;
            var variable = '';
            var variable = '' + 
                            '<div class="row racikan-'+i+'">' + 
                            '                                <div class="col-md-5">' + 
                            '                                    <div class="form-group">' + 
                            '                                        <label class="d-sm-none">Obat</label>' + 
                            '                                        <select name="obatRacikan[]" class="form-control obat-racikan w-100" id="obatRacikan'+i+'" data-placeholder="Pilih Obat">' + 
                            '                                        </select>' + 
                            '                                    </div>' + 
                            '                                </div>' + 
                            '                                <div class="col-md-1">' + 
                            '                                    <div class="form-group">' + 
                            '                                        <label class="d-sm-none stok-'+i+'" for="stok'+i+'">Stok</label>' + 
                            '                                        <input id="stok'+i+'" class="form-control p-1 stok-'+i+'" type="text" name="stok[]" disabled>' + 
                            '                                    </div>' + 
                            '                                </div>' + 
                            '                                <div class="col-md-1">' + 
                            '                                    <div class="form-group">' + 
                            '                                        <label class="d-sm-none" for="kps'+i+'">Kps</label>' + 
                            '                                        <input id="kps'+i+'" class="form-control p-1 kps-'+i+'" type="text" name="kps[]" disabled>' + 
                            '                                    </div>' + 
                            '                                </div>' + 
                            '                                <div class="col-md-1">' + 
                            '                                    <div class="form-group">' + 
                            '                                        <label class="d-sm-none" for="p1'+i+'">P1</label>' + 
                            '                                        <input id="p1'+i+'" class="form-control p-1 p1-'+i+'" type="text" name="p1[]" required>' + 
                            '                                    </div>' + 
                            '                                </div>' + 
                            '                                <div class="col-md-1">' + 
                            '                                    <div class="form-group">' + 
                            '                                        <label class="d-sm-none"  for="p2'+i+'">P2</label>' + 
                            '                                        <input id="p2'+i+'" class="form-control p-1 p2-'+x+'" type="text" oninput="hitungRacikan('+i+')" name="p2[]" required>' + 
                            '                                    </div>' + 
                            '                                </div>' + 
                            '                                <div class="col-md-2">' + 
                            '                                    <div class="form-group">' + 
                            '                                        <label class="d-sm-none" for="kandungan'+i+'">Kandungan</label>' + 
                            '                                        <input id="kandungan'+i+'" class="form-control p-1 kandungan-'+i+'" type="text" oninput="hitungJml('+i+')" name="kandungan[]" required>' + 
                            '                                    </div>' + 
                            '                                </div>' + 
                            '                                <div class="col-md-1">' + 
                            '                                    <div class="form-group">' + 
                            '                                        <label class="d-sm-none" for="jml'+i+'">Jml</label>' + 
                            '                                        <input id="jml'+i+'" class="form-control p-1 jml-'+i+'" oninput="hitungJml('+i+')" type="text" name="jml[]" required>' + 
                            '                                    </div>' + 
                            '                                </div>' + 
                            '                            </div>' + 
                            '';

            $(".containerRacikan").append(variable.trim());
            $('#'+'obatRacikan'+i, ".containerRacikan").select2({
                placeholder: 'Pilih obat',
                ajax: {
                    url: '/api/ralan/'+"{{$poli}}"+'/obat',
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                templateResult: formatData,
                minimumInputLength: 3
            }).on("change", function(e){
                var data = $(this).select2('data');
                var id = $(this).attr('id').replace ( /[^\d.]/g, '' );
                var idRow = parseInt(id);
                $.ajax({
                    url: '/api/obat/'+data[0].id,
                    data:{
                        status:'ralan',
                        kode:"{{$poli}}"
                    },
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('input[id="stok'+idRow+'"]').val(data.stok_akhir);
                        $('input[id="kps'+idRow+'"]').val(data.kapasitas);
                        $('input[id="p1'+idRow+'"]').val('1');
                        $('input[id="p2'+idRow+'"]').val('1');
                    }
                });
            });
        });

        function deleteRowRacikan(){
            $(".racikan-"+i).remove();
            if(i>=1){
                i--;
            }
        }

        function hitungRacikan(index){
            var p1 = getIndexValue('p1[]', index);
            var p2 = getIndexValue('p2[]', index);
            var jmlRacikan = $('#jumlah_racikan').val();
            var kps = getIndexValue('kps[]', index);
            var kandungan = (p1/p2)*kps;
            var kandungan = parseFloat(kandungan);
            var jml = (p1/p2)*jmlRacikan;
            var jml = parseFloat(jml);
            // if(isNaN(kandungan) || isFinite(kandungan)){
            //     var kandungan = 0;
            // }
            // if(isNaN(jml) || isFinite(jml)){
            //     var jml = 0;
            // }
            $(".kandungan-"+index).val(kandungan.toFixed(1));
            $(".jml-"+index).val(jml.toFixed(1));
        }

        function hitungJml(index){
            var kps = getIndexValue('kps[]', index);
            var jmlRacikan = $('#jumlah_racikan').val();
            var kandungan = $(".kandungan-"+index).val();
            var jml = (kandungan/kps)*jmlRacikan;
            var jml = parseFloat(jml);
            // if(isNaN(jml) || isFinite(jml)){
            //     var jml = 0;
            // }
            $(".jml-"+index).val(jml.toFixed(1));
        }

</script>
<script>
    var wrapper = $(".containerResep");
        var add_button = $("#addFormResep");
        var x = 0;
        function formatData (data) {
                    var $data = $(
                        '<b>'+ data.id +'</b> - <i>'+ data.text +'</i>'
                    );
                    return $data;
            };
        
        $(add_button).click(function(e) {
            e.preventDefault();
            var html = '';
            html += '<div class="row">';
            html += '<hr class="d-sm-none">';
            html += '   <div class="col-md-6">';
            html += '       <div class="form-group">';
            html += '            <label class="d-sm-none">Nama Obat</label>';
            html += '            <select name="obat[]" class="form-control obat-'+x+'" id="obat'+x+'" data-placeholder="Pilih Obat">';
            html += '            </select>';
            html += '        </div>';
            html += '    </div>';
            html += '    <div class="col-md-2">';
            html += '        <div class="form-group">';
            html += '            <label class="d-sm-none">Jumlah</label>';
            html += '            <input type="text" name="jumlah[]" class="form-control" id="jumlah'+x+'" placeholder="Jumlah"/>';
            html += '        </div>';
            html += '    </div>';
            html += '    <div class="col-md-4">';
            html += '        <div class="form-group">';
            html += '            <label class="d-sm-none">Aturan Pakai</label>';
            html += '            <div class="input-group">';
            html += '            <input name="aturan[]" id="aturan'+x+'" class="form-control" placeholder="Aturan Pakai">';
            html += '            <div class="input-group-append">';
            html += '                 <button class="btn btn-danger delete" value="row_resep'+x+'">-</button>';
            html += '            </div>';
            html += '            </div>';
            html += '        </div>';
            html += '    </div>';
            html += '</div>';
            $(wrapper).append(html.trim()); 
            $('#'+'obat'+x, wrapper).select2({
                placeholder: 'Pilih obat',
                ajax: {
                    url: '/api/ralan/'+"{{$poli}}"+'/obat',
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                templateResult: formatData,
                minimumInputLength: 3
            });
            x++;
        });

        $(wrapper).on("click", ".delete", function(e) {
            e.preventDefault();
            $(this).closest('.row').remove();
        })

        $(document).ready(function() {
            $('.obat').select2({
                placeholder: 'Pilih obat',
                ajax: {
                    url: '/api/ralan/'+"{{$poli}}"+'/obat',
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) {
                    return {
                        results: data
                    };
                    },
                    cache: true
                },
                templateResult: formatData,
                minimumInputLength: 3
            });

            $('.obat-racikan').select2({
                placeholder: 'Pilih obat racikan',
                ajax: {
                    url: '/api/ralan/'+"{{$poli}}"+'/obat',
                    dataType: 'json',
                    delay: 250,
                    processResults: function (data) {
                    return {
                        results: data
                    };
                    },
                    cache: true
                },
                templateResult: formatData,
                minimumInputLength: 3
            }).on("select2:select", function(e){
                var selected = e.params.data;
                $.ajax({
                    url: '/api/obat/' + selected.id,
                    data:{
                        status:'ralan',
                        kode:"{{$poli}}"
                    },
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        // Safeguard jika data null atau kolom tidak ada
                        var stokAkhir = data && data.stok_akhir ? data.stok_akhir : 0;
                        var kapasitas = data && data.kapasitas ? data.kapasitas : 0;
                        $('#stok').val(stokAkhir);
                        $('#kps').val(kapasitas);
                        $('#p1').val('1');
                        $('#p2').val('1');
                    }
                });
            });

            function formatData (data) {
                var $data = $(
            '<div>' +
            '<b>'+ data.id +'</b> - <i>'+ data.text +'</i>' +
            (data.stok ? ' <span class="text-muted"><b>[Stok: ' + data.stok + ']</b></span>' : '') +
            '</div>'
            );
                    return $data;
            };
        });
</script>

<script>
    function formatRupiah(num) {
        var n = parseFloat(num) || 0;
        return 'Rp ' + Math.round(n).toLocaleString('id-ID');
    }
    function updateResepTotalSummary(totals) {
        if (!totals) totals = {};
        $('#summary-total-harga').text(formatRupiah(totals.total_harga_obat));
        $('#summary-total-ppn').text(formatRupiah(totals.total_ppn));
        $('#summary-total-dengan-ppn').text(formatRupiah(totals.total_dengan_ppn));
    }
    function buildResepRow(item) {
        var harga = parseFloat(item.harga_satuan) || 0;
        var jml = parseFloat(item.jml) || 0;
        var subtotal = harga * jml;
        return '<td>' + (item.nama_brng || '') + '</td>' +
            '<td>' + (item.tgl_peresepan || '') + ' ' + (item.jam_peresepan || '') + '</td>' +
            '<td>' + (item.jml || '') + '</td>' +
            '<td class="text-right">' + formatRupiah(harga) + '</td>' +
            '<td class="text-right">' + formatRupiah(subtotal) + '</td>' +
            '<td>' + (item.aturan_pakai || '') + '</td>' +
            '<td><button class="btn btn-danger btn-sm" onclick="hapusObat(\'' + item.no_resep + '\', \'' + item.kode_brng + '\', event)">Hapus</button></td>';
    }
    function getValue(name) {
            var data = [];
            var doc = document.getElementsByName(name);
            for (var i = 0; i < doc.length; i++) {
                    var a = doc[i].value;
                    data.push(a);
                }

            return data;
        }

        function getCopyResep(no_resep, e) {
            e.preventDefault();
            let _token   = $('meta[name="csrf-token"]').attr('content');
            var trHTML = '';
            $(".table-copy-resep").find("tr.body").remove();
            $.ajax({
                url:'/ralan/copy/'+no_resep,
                type:'GET',
                dataType:'json',
                beforeSend:function() {
                Swal.fire({
                    title: 'Loading....',
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                    });
                },
                success: function(data){
                    // console.log(data);
                    Swal.close();
                    // OPTIMASI: Build HTML string sekali saja, lalu append (O(n))
                    var trHTML = '';
                    data.forEach(function(item) {
                        trHTML += '<tr class="body"><td><input type="text" name="jml_copyresep[]" multiple="multiple" value="' + item.jml + '" size="5"></td>'
                                + '<td><input type="hidden" name="kode_brng_copyresep[]" multiple="multiple" value="' + item.kode_brng +'" > ' + item.nama_brng + '</td>'
                                + '<td><input type="text" name="aturan_copyresep[]" multiple="multiple" value="' + item.aturan_pakai + '"></td></tr>';
                    });
                    $('.tbBodyCopy').append(trHTML);
                    $('#modalCopyResep').modal({
                        backdrop: false,
                        keyboard: false,
                        show: true
                    });
                }
            });
        }

        // FIX: Pastikan fungsi dapat diakses dari tombol yang di-generate oleh DataTables
        window.hapusResep = function(noResep, e) {
            if (e) e.preventDefault();
            Swal.fire({
                title: 'Hapus Resep?',
                text: "Yakin ingin menghapus resep " + noResep + "? Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.value) {
                    let _token = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: '/api/resep/' + noResep,
                        type: 'DELETE',
                        dataType: 'json',
                        data: {
                            _token: _token
                        },
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Loading....',
                                allowEscapeKey: false,
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function(response) {
                            Swal.close();
                            if (response.status == 'sukses') {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: response.pesan || 'Resep berhasil dihapus',
                                    icon: 'success',
                                    timer: 2000,
                                    toast: true,
                                    position: 'center',
                                    showConfirmButton: false,
                                });
                                
                                // Refresh tabel riwayat peresepan
                                if ($.fn.DataTable && $('#tableRiwayatResep').length) {
                                    var table = $('#tableRiwayatResep').DataTable();
                                    if (table && table.ajax) {
                                        table.ajax.reload(null, false);
                                    }
                                }
                            } else {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: response.pesan || 'Gagal menghapus resep',
                                    icon: 'error',
                                    confirmButtonText: 'Ok'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.close();
                            console.error('HAPUS RESEP - Error:', {
                                status: status,
                                error: error,
                                statusCode: xhr.status,
                                statusText: xhr.statusText,
                                responseText: xhr.responseText,
                                responseJSON: xhr.responseJSON
                            });

                            let errorMessage = 'Terjadi kesalahan saat menghapus resep';
                            if (xhr.responseJSON && xhr.responseJSON.pesan) {
                                errorMessage = xhr.responseJSON.pesan;
                            }

                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
                                icon: 'error',
                                confirmButtonText: 'Ok'
                            });
                        }
                    });
                }
            });
        }

        function hapusObat($noResep, $kdObat, e) {
            e.preventDefault();
            Swal.fire({
                title: 'Hapus Obat?',
                text: "Yakin ingin menghapus obat ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.value) {
                    let _token   = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: '/api/obat/'+$noResep+'/'+$kdObat+'/'+"{{$encryptNoRawat}}",
                        type: 'POST',
                        dataType: 'json',
                        data:{_token: _token}, 
                        beforeSend:function() {
                        Swal.fire({
                            title: 'Loading....',
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                            });
                        },
                        success: function(data) {
                            if(data.status == 'sukses'){
                                Swal.close();
                                var fragment = document.createDocumentFragment();
                                var tbody = $('.body-resep')[0];
                                tbody.innerHTML = '';
                                (data.data || []).forEach(function(item) {
                                    var tr = document.createElement('tr');
                                    tr.innerHTML = buildResepRow(item);
                                    fragment.appendChild(tr);
                                });
                                tbody.appendChild(fragment);
                                updateResepTotalSummary(data);
                                }else{
                                    Swal.fire(
                                        'Gagal!',
                                        data.pesan,
                                        'error'
                                    )
                                }
                        },
                        error: function(data) {
                            console.log(data);
                            Swal.fire(
                                'Gagal!',
                                data.pesan ?? 'Obat gagal dihapus.',
                                'error'
                            )
                        }
                    })
                }
            })
        }

        function hapusRacikan($noResep, $noRacik, e) {
            e.preventDefault();
            Swal.fire({
                title: 'Hapus Obat?',
                text: "Yakin ingin menghapus obat ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.value) {
                    let _token   = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: '/ralan/racikan/'+$noResep+'/'+$noRacik,
                        type: 'DELETE',
                        dataType: 'json',
                        data:{_token: _token}, 
                        beforeSend:function() {
                        Swal.fire({
                            title: 'Loading....',
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                            });
                        },
                        success: function(data) {
                            data.status == 'sukses' ? Swal.fire(
                                'Terhapus!',
                                data.pesan,
                                'success'
                            ).then((result) => {
                                if (result.value) {
                                    window.location.reload();
                                }
                            }) : Swal.fire(
                                'Gagal!',
                                data.pesan,
                                'error'
                            ).then((result) => {
                                if (result.value) {
                                    window.location.reload();
                                }
                            })
                        },
                        error: function(data) {
                            console.log(data);
                            Swal.fire(
                                'Gagal!',
                                data.pesan ?? 'Obat gagal dihapus.',
                                'error'
                            )
                        }
                    })
                }
            })
        }

        $("#simpanCopyResep").click(function(e) {
            e.preventDefault();
            let _token   = $('meta[name="csrf-token"]').attr('content');
            let obat = getValue('kode_brng_copyresep[]');
            let jumlah = getValue('jml_copyresep[]');
            let aturan = getValue('aturan_copyresep[]');
            var data = {
                obat:obat,
                jumlah:jumlah,
                aturan_pakai:aturan,
                status:'Ralan',
                kode:"{{$poli}}",
                _token:_token,
            };
            // console.log(data);
            $.ajax({
                type: 'POST',
                url: '/api/resep/'+"{{$encryptNoRawat}}",
                data: data,
                dataType: 'json',
                timeout: 30000, // FIX: Set timeout 30 detik
                beforeSend: function() {
                    $('#modalCopyResep').modal('hide')
                    Swal.fire({
                    title: 'Loading....',
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                    });
            
                },
                beforeSend:function() {
                    Swal.fire({
                        title: 'Loading....',
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                        });
                },
                success: function (response) {
                    // FIX: Tutup loading Swal terlebih dahulu
                    Swal.close();
                    
                    // console.log(response);
                    if(response.status == 'sukses'){
                        var hasDetailStok = response.detail_stok && response.detail_stok.length > 0;
                        var swalConfig = {
                            title: 'Data berhasil disimpan',
                            icon: 'success',
                            confirmButtonText: 'Ok',
                        };
                        if (hasDetailStok) {
                            var htmlContent = '<p>' + (response.pesan || 'Beberapa obat tidak dapat ditambahkan karena stok tidak mencukupi.') + '</p>';
                            htmlContent += '<strong>Obat yang tidak dapat ditambahkan:</strong><ul style="text-align: left; padding-left: 20px; margin-top: 10px;">';
                            response.detail_stok.forEach(function(obat) {
                                var namaObat = obat.nama_brng || obat.kode || 'Unknown';
                                var stokTersedia = obat.stok_tersedia || obat.tersedia || 0;
                                var jumlahDiminta = obat.jumlah_diminta || obat.diminta || 0;
                                htmlContent += '<li style="margin-bottom: 8px;"><strong>' + namaObat + '</strong><br><small style="margin-left: 20px;">Stok: ' + stokTersedia + ' | Diminta: ' + jumlahDiminta + '</small></li>';
                            });
                            htmlContent += '</ul>';
                            swalConfig.html = htmlContent;
                            swalConfig.width = '600px';
                        } else {
                            swalConfig.timer = 3000;
                            swalConfig.toast = true;
                            swalConfig.position = 'center';
                            swalConfig.showConfirmButton = false;
                        }
                        Swal.fire(swalConfig);
                        
                        // OPTIMASI: Batch DOM manipulation dengan DocumentFragment (O(n))
                        var fragment = document.createDocumentFragment();
                        var tbody = $('.body-resep')[0];
                        if (tbody) {
                            tbody.innerHTML = ''; // Clear sekali saja
                            
                            // FIX: Cek apakah response.data ada dan array
                            if (response.data && Array.isArray(response.data) && response.data.length > 0) {
                                response.data.forEach(function(item) {
                                    var tr = document.createElement('tr');
                                    tr.setAttribute('data-target', item.kode_brng);
                                    tr.className = 'cursor-pointer';
                                    tr.innerHTML = buildResepRow(item);
                                    fragment.appendChild(tr);
                                });
                                tbody.appendChild(fragment);
                            }
                            updateResepTotalSummary(response);
                        }
                        
                        // FIX: Refresh tabel riwayat peresepan setelah input resep berhasil menggunakan AJAX reload
                        if ($.fn.DataTable && $('#tableRiwayatResep').length) {
                            var table = $('#tableRiwayatResep').DataTable();
                            if (table && table.ajax) {
                                table.ajax.reload(null, false); // false = keep current page
                            }
                        }
                    }
                    else{
                        // Format pesan dengan detail stok jika tersedia
                        let htmlContent = '<div style="text-align: left;">' + (response.pesan || 'Terjadi kesalahan');
                        if (response.detail_stok && response.detail_stok.length > 0) {
                            htmlContent += '<br><br><strong>Obat yang stoknya tidak mencukupi:</strong><ul style="text-align: left; padding-left: 20px; margin-top: 10px;">';
                            response.detail_stok.forEach(function(obat) {
                                var namaObat = obat.nama_brng || obat.kode || 'Unknown';
                                var stokTersedia = obat.stok_tersedia || obat.tersedia || 0;
                                var jumlahDiminta = obat.jumlah_diminta || obat.diminta || 0;
                                htmlContent += '<li style="margin-bottom: 8px;">';
                                htmlContent += '<strong>' + namaObat + '</strong><br>';
                                htmlContent += '<small style="margin-left: 20px;">Stok tersedia: <strong>' + stokTersedia + '</strong> | Diminta: <strong>' + jumlahDiminta + '</strong></small>';
                                htmlContent += '</li>';
                            });
                            htmlContent += '</ul>';
                        }
                        htmlContent += '</div>';
                        
                        Swal.fire({
                            title: 'Gagal',
                            html: htmlContent,
                            icon: 'error',
                            confirmButtonText: 'Ok',
                            width: '600px'
                        });
                    }
                },
                error: function (xhr, status, error) {
                    // Logging detail error untuk debugging
                    console.error('POST RESEP (Copy) - Error:', {
                        status: status,
                        error: error,
                        statusCode: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        responseJSON: xhr.responseJSON
                    });

                    let htmlContent = '<div style="text-align: left;">';
                    let errorMessage = 'Terjadi kesalahan saat menyimpan resep';
                    
                    if (xhr.responseJSON) {
                        errorMessage = xhr.responseJSON.pesan || xhr.responseJSON.message || errorMessage;
                        htmlContent += '<strong>Error:</strong> ' + errorMessage;
                        
                        if (xhr.responseJSON.detail_stok && xhr.responseJSON.detail_stok.length > 0) {
                            htmlContent += '<br><br><strong>Obat yang stoknya tidak mencukupi:</strong><ul style="text-align: left; padding-left: 20px; margin-top: 10px;">';
                            xhr.responseJSON.detail_stok.forEach(function(obat) {
                                var namaObat = obat.nama_brng || obat.kode || 'Unknown';
                                var stokTersedia = obat.stok_tersedia || obat.tersedia || 0;
                                var jumlahDiminta = obat.jumlah_diminta || obat.diminta || 0;
                                htmlContent += '<li style="margin-bottom: 8px;">';
                                htmlContent += '<strong>' + namaObat + '</strong><br>';
                                htmlContent += '<small style="margin-left: 20px;">Stok tersedia: <strong>' + stokTersedia + '</strong> | Diminta: <strong>' + jumlahDiminta + '</strong></small>';
                                htmlContent += '</li>';
                            });
                            htmlContent += '</ul>';
                        }
                    } else {
                        htmlContent += '<strong>Error:</strong> ' + errorMessage;
                        htmlContent += '<br><br><small>Status: ' + xhr.status + ' ' + xhr.statusText + '</small>';
                    }
                    htmlContent += '</div>';
                    
                    Swal.fire({
                        title: 'Error',
                        html: htmlContent,
                        icon: 'error',
                        confirmButtonText: 'Ok',
                        width: '600px'
                    });
                }
            });
        });

        $("#resepButton").click(function(e) {
            e.preventDefault();
            let _token   = $('meta[name="csrf-token"]').attr('content');
            let obat = getValue('obat[]');
            let jumlah = getValue('jumlah[]');
            let aturan = getValue('aturan[]');
            let iter = $('#iter').val();
            var form = $("#resepForm");
            var data = {
                obat:obat,
                jumlah:jumlah,
                aturan_pakai:aturan,
                iter:iter,
                status:'Ralan',
                kode:"{{$poli}}",
                _token:_token,
            };
            var url = form.attr('action');
            var method = form.attr('method');
            // console.log(data);
            $.ajax({
                type: method,
                url: url,
                data: data,
                dataType: 'json',
                timeout: 30000, // FIX: Set timeout 30 detik untuk menghindari loading terus
                beforeSend: function() {
                    Swal.fire({
                    title: 'Loading....',
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                    });
                },
                success: function (response) {
                    console.log(response);
                    // FIX: Tutup loading Swal terlebih dahulu
                    Swal.close();
                    
                    if(response.status == 'sukses'){
                        var hasDetailStok = response.detail_stok && response.detail_stok.length > 0;
                        var swalConfig = {
                            title: 'Data berhasil disimpan',
                            icon: 'success',
                            confirmButtonText: 'Ok',
                        };
                        if (hasDetailStok) {
                            var htmlContent = '<p>' + (response.pesan || 'Beberapa obat tidak dapat ditambahkan karena stok tidak mencukupi.') + '</p>';
                            htmlContent += '<strong>Obat yang tidak dapat ditambahkan:</strong><ul style="text-align: left; padding-left: 20px; margin-top: 10px;">';
                            response.detail_stok.forEach(function(obat) {
                                var namaObat = obat.nama_brng || obat.kode || 'Unknown';
                                var stokTersedia = obat.stok_tersedia || obat.tersedia || 0;
                                var jumlahDiminta = obat.jumlah_diminta || obat.diminta || 0;
                                htmlContent += '<li style="margin-bottom: 8px;"><strong>' + namaObat + '</strong><br><small style="margin-left: 20px;">Stok: ' + stokTersedia + ' | Diminta: ' + jumlahDiminta + '</small></li>';
                            });
                            htmlContent += '</ul>';
                            swalConfig.html = htmlContent;
                            swalConfig.width = '600px';
                        } else {
                            swalConfig.timer = 3000;
                            swalConfig.toast = true;
                            swalConfig.position = 'center';
                            swalConfig.showConfirmButton = false;
                        }
                        Swal.fire(swalConfig);
                        
                        // OPTIMASI: Batch DOM manipulation dengan DocumentFragment (O(n))
                        var fragment = document.createDocumentFragment();
                        var tbody = $('.body-resep')[0];
                        if (tbody) {
                            tbody.innerHTML = ''; // Clear sekali saja
                            
                            // FIX: Cek apakah response.data ada dan array
                            if (response.data && Array.isArray(response.data) && response.data.length > 0) {
                                response.data.forEach(function(item) {
                                    var tr = document.createElement('tr');
                                    tr.innerHTML = buildResepRow(item);
                                    fragment.appendChild(tr);
                                });
                                tbody.appendChild(fragment);
                            }
                            updateResepTotalSummary(response);
                        }
                        
                        // FIX: Refresh tabel riwayat peresepan setelah input resep berhasil menggunakan AJAX reload
                        if ($.fn.DataTable && $('#tableRiwayatResep').length) {
                            var table = $('#tableRiwayatResep').DataTable();
                            if (table && table.ajax) {
                                table.ajax.reload(null, false); // false = keep current page
                            }
                        }
                    }
                    else{
                        // Format pesan dengan detail stok jika tersedia
                        let htmlContent = '<div style="text-align: left;">' + (response.pesan || 'Terjadi kesalahan');
                        if (response.detail_stok && response.detail_stok.length > 0) {
                            htmlContent += '<br><br><strong>Obat yang stoknya tidak mencukupi:</strong><ul style="text-align: left; padding-left: 20px; margin-top: 10px;">';
                            response.detail_stok.forEach(function(obat) {
                                var namaObat = obat.nama_brng || obat.kode || 'Unknown';
                                var stokTersedia = obat.stok_tersedia || obat.tersedia || 0;
                                var jumlahDiminta = obat.jumlah_diminta || obat.diminta || 0;
                                htmlContent += '<li style="margin-bottom: 8px;">';
                                htmlContent += '<strong>' + namaObat + '</strong><br>';
                                htmlContent += '<small style="margin-left: 20px;">Stok tersedia: <strong>' + stokTersedia + '</strong> | Diminta: <strong>' + jumlahDiminta + '</strong></small>';
                                htmlContent += '</li>';
                            });
                            htmlContent += '</ul>';
                        }
                        htmlContent += '</div>';
                        
                        Swal.fire({
                            title: 'Gagal',
                            html: htmlContent,
                            icon: 'error',
                            confirmButtonText: 'Ok',
                            width: '600px'
                        });
                    }
                },
                error: function (xhr, status, error) {
                    // FIX: Tutup loading Swal terlebih dahulu
                    Swal.close();
                    
                    // Logging detail error untuk debugging
                    console.error('POST RESEP (Main) - Error:', {
                        status: status,
                        error: error,
                        statusCode: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        responseJSON: xhr.responseJSON,
                        requestData: data
                    });

                    let htmlContent = '<div style="text-align: left;">';
                    let errorMessage = 'Terjadi kesalahan saat menyimpan resep';
                    
                    // FIX: Handle timeout khusus
                    if (status === 'timeout') {
                        errorMessage = 'Request timeout - Server tidak merespon dalam waktu 30 detik';
                        htmlContent += '<strong>Error:</strong> ' + errorMessage;
                        htmlContent += '<br><br><small>Kemungkinan penyebab:</small>';
                        htmlContent += '<ul style="text-align: left; padding-left: 20px; margin-top: 10px;">';
                        htmlContent += '<li>Server sedang sibuk atau hang</li>';
                        htmlContent += '<li>Query database terlalu lambat</li>';
                        htmlContent += '<li>Koneksi database bermasalah</li>';
                        htmlContent += '</ul>';
                        htmlContent += '<br><small>Silakan coba lagi atau hubungi administrator.</small>';
                    } else if (xhr.responseJSON) {
                        errorMessage = xhr.responseJSON.pesan || xhr.responseJSON.message || errorMessage;
                        htmlContent += '<strong>Error:</strong> ' + errorMessage;
                        
                        if (xhr.responseJSON.detail_stok && xhr.responseJSON.detail_stok.length > 0) {
                            htmlContent += '<br><br><strong>Obat yang stoknya tidak mencukupi:</strong><ul style="text-align: left; padding-left: 20px; margin-top: 10px;">';
                            xhr.responseJSON.detail_stok.forEach(function(obat) {
                                var namaObat = obat.nama_brng || obat.kode || 'Unknown';
                                var stokTersedia = obat.stok_tersedia || obat.tersedia || 0;
                                var jumlahDiminta = obat.jumlah_diminta || obat.diminta || 0;
                                htmlContent += '<li style="margin-bottom: 8px;">';
                                htmlContent += '<strong>' + namaObat + '</strong><br>';
                                htmlContent += '<small style="margin-left: 20px;">Stok tersedia: <strong>' + stokTersedia + '</strong> | Diminta: <strong>' + jumlahDiminta + '</strong></small>';
                                htmlContent += '</li>';
                            });
                            htmlContent += '</ul>';
                        }
                        
                        if (xhr.responseJSON.error_code) {
                            htmlContent += '<br><br><small>Error Code: ' + xhr.responseJSON.error_code + '</small>';
                        }
                    } else {
                        htmlContent += '<strong>Error:</strong> ' + errorMessage;
                        htmlContent += '<br><br><small>Status: ' + xhr.status + ' ' + xhr.statusText + '</small>';
                        if (xhr.status === 0) {
                            htmlContent += '<br><small>Kemungkinan: Koneksi terputus atau server tidak merespon</small>';
                        } else if (xhr.status === 500) {
                            htmlContent += '<br><small>Server error. Silakan cek log server atau hubungi administrator.</small>';
                        }
                    }
                    htmlContent += '</div>';
                    
                    Swal.fire({
                        title: 'Error',
                        html: htmlContent,
                        icon: 'error',
                        confirmButtonText: 'Ok',
                        width: '600px'
                    });
                }
            });
        });

        $("#resepRacikanButton").click(function(e){
            e.preventDefault();
            let _token   = $('meta[name="csrf-token"]').attr('content');
            let obat = $('#obat_racikan').val();
            let metode = $('#metode_racikan').val();
            let jumlah = $('#jumlah_racikan').val();
            let aturan = $('#aturan_racikan').val();
            let keterangan = $('#keterangan_racikan').val();
            let iterRacikan = $('#iterRacikan').val();
            let kdObat = getValue('obatRacikan[]');
            let p1 = getValue('p1[]');
            let p2 = getValue('p2[]');
            let kandungan = getValue('kandungan[]');
            let jml = getValue('jml[]');
            let satu_resep = $('#satu-resep').is(":checked") ? 1 : 0;
            $.ajax({
                type: 'POST',
                url: '/api/resep/racikan/'+"{{$encryptNoRawat}}",
                data: {
                    nama_racikan:obat,
                    metode_racikan:metode,
                    jumlah_racikan:jumlah,
                    aturan_racikan:aturan,
                    keterangan_racikan:keterangan,
                    iter:iterRacikan,
                    kd_obat:kdObat,
                    p1:p1,
                    p2:p2,
                    kandungan:kandungan,
                    jml:jml,
                    satu_resep:satu_resep,
                    _token:_token,
                },
                dataType: 'json',
                timeout: 30000, // FIX: Set timeout 30 detik
                beforeSend: function() {
                    $('#modalRacikan').modal('hide')
                    Swal.fire({
                    title: 'Loading....',
                    allowEscapeKey: false,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                    });
                },
                success: function (response) {
                    // FIX: Tutup loading Swal terlebih dahulu
                    Swal.close();
                    
                    console.log(response);
                    if(response.status == 'sukses'){
                        Swal.fire({
                            title: 'Sukses',
                            text: 'Data berhasil disimpan',
                            icon: 'success',
                            confirmButtonText: 'Ok'
                        }).then((result) => {
                            if (result.value) {
                                window.location.reload();
                            }
                        });
                    }
                    else{
                        Swal.fire({
                            title: 'Gagal',
                            text: response.message || 'Terjadi kesalahan',
                            icon: 'error',
                            confirmButtonText: 'Ok'
                        });
                    }
                },
                error: function (xhr, status, error) {
                    // FIX: Tutup loading Swal terlebih dahulu
                    Swal.close();
                    
                    console.error('POST RESEP RACIKAN - Error:', {
                        status: status,
                        error: error,
                        statusCode: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        responseJSON: xhr.responseJSON
                    });
                    
                    var errorMessage = 'Terjadi kesalahan';
                    try {
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            var errors = JSON.parse(xhr.responseText);
                            errorMessage = errors.message || errorMessage;
                        }
                    } catch (e) {
                        errorMessage = xhr.statusText || 'Terjadi kesalahan';
                    }
                    
                    Swal.fire({
                        title: 'Error',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'Ok'
                    });
                }
            });
        });

        let listObat = [];

        $('.cursor-pointer').on('click', function(){
            let data = $(this).attr("data-target");
            let name = this.className;
            if(name.includes('bg-danger')){
                $(this).removeClass('bg-danger');
                $(this).closest('tr').find('input:checkbox').prop('checked', false);
                listObat = listObat.filter(function(item) {
                    return item !== data
                })
            }else{
                $(this).addClass('bg-danger');
                $(this).closest('tr').find('input:checkbox').prop('checked', true);
                listObat.push(data);
            }
            let jmlObat = listObat.length;
            if(jmlObat > 0){
                $('#delete-batch-button').removeClass('disabled');
                $('#delete-batch-button').html('Hapus '+ jmlObat +' Obat');
            }else{
                $('#delete-batch-button').addClass('disabled');
                $('#delete-batch-button').html('Hapus Obat');
            }
        })

        $('#checkboxAll').on('change', function(){
            $('input:checkbox').prop('checked', this.checked); 
            if(this.checked){
                $('.cursor-pointer').addClass('bg-danger');
                listObat = [];
                $('.cursor-pointer').each(function(){
                    listObat.push($(this).attr("data-target"));
                })
            }else{
                $('.cursor-pointer').removeClass('bg-danger');
                listObat = [];
            }
            let jmlObat = listObat.length;
            if(jmlObat > 0){
                $('#delete-batch-button').removeClass('disabled');
                $('#delete-batch-button').html('Hapus '+ jmlObat +' Obat');
            }else{
                $('#delete-batch-button').addClass('disabled');
                $('#delete-batch-button').html('Hapus Obat');
            }
        })

        function hapusObatBatch($noResep, $kdObat) {
            Swal.fire({
                title: 'Hapus Obat?',
                text: "Yakin ingin menghapus "+ listObat.length +" obat ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.value) {
                    let _token   = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        url: '/api/obat-batch',
                        method: 'DELETE',
                        type: 'POST',
                        dataType: 'json',
                        data:{
                            _token: _token,
                            obat:listObat,
                            no_rawat:"{{$encryptNoRawat}}",
                            no_resep:$noResep
                        }, 
                        beforeSend:function() {
                        Swal.fire({
                            title: 'Loading....',
                            allowEscapeKey: false,
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                            });
                        },
                        success: function(data) {
                            console.log(data);
                            if(data.status == 'sukses'){
                                Swal.fire({
                                    title: 'Data berhasil dihapus',
                                    icon: 'success',
                                    timer: 3000,
                                    toast: true,
                                    position: 'center',
                                    showConfirmButton: false,
                                });
                                // OPTIMASI: Batch DOM manipulation dengan DocumentFragment (O(n))
                                var fragment = document.createDocumentFragment();
                                var tbody = $('.body-resep')[0];
                                tbody.innerHTML = ''; // Clear sekali saja
                                
                                data.data.forEach(function(item) {
                                    var tr = document.createElement('tr');
                                    tr.setAttribute('data-target', item.kode_brng);
                                    tr.className = 'cursor-pointer';
                                    tr.innerHTML = buildResepRow(item);
                                    fragment.appendChild(tr);
                                });
                                tbody.appendChild(fragment);
                                updateResepTotalSummary(data);
                                listObat = [];
                                $('#delete-batch-button').addClass('disabled');
                                $('#delete-batch-button').html('Hapus Obat'); 
                            }else{
                                Swal.fire(
                                    'Gagal!',
                                    data.pesan,
                                    'error'
                                )
                            }
                        },
                        error: function(data) {
                            console.log(data);
                            Swal.fire(
                                'Gagal!',
                                data.pesan ?? 'Obat gagal dihapus.',
                                'error'
                            )
                        }
                    })
                }
            })
        }

        $('#delete-batch-button').on('click', function(e){
            e.preventDefault();
            let noResep = $(this).attr('data-target');
            hapusObatBatch(noResep, listObat);
        })

</script>

@endpush