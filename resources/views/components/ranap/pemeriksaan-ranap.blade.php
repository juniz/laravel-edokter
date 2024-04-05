<div>
    <x-adminlte-card title="Pemeriksaan" theme="info" icon="fas fa-lg fa-book-medical" collapsible maximizable>
        <x-adminlte-card theme="dark" title="Input Pemeriksaan" theme-mode="outline" maximizable collapsible="collapsed">
            <form id="pemeriksaanForm">
                <div class="row">
                    <x-adminlte-textarea name="keluhan" label="Subjek" fgroup-class="col-md-6" rows="4">                        
                    </x-adminlte-textarea>
                    <x-adminlte-textarea name="pemeriksaan" label="Objek" fgroup-class="col-md-6" rows="4">  
                    </x-adminlte-textarea>
                </div>
                <div class="row">
                    <x-adminlte-textarea name="penilaian" label="Asesmen" fgroup-class="col-md-6" rows="2">
                    </x-adminlte-textarea>
                    <x-adminlte-textarea name="instruksi" label="Instruksi" fgroup-class="col-md-6" rows="2">
                    </x-adminlte-textarea>
                </div>
                <div class="row">
                    <x-adminlte-textarea name="rtl" label="Plan" fgroup-class="col-md-6" rows="2">
                    </x-adminlte-textarea>
                    <x-adminlte-textarea name="alergi" label="Alergi" fgroup-class="col-md-6" rows="2">
                    </x-adminlte-textarea>
                </div>
                <div class="row">
                    <x-adminlte-input name="suhu" label="Suhu" fgroup-class="col-md-3" />
                    <x-adminlte-input name="berat" label="Berat" fgroup-class="col-md-3" />
                    <x-adminlte-input name="tinggi" label="Tinggi Badan" fgroup-class="col-md-3" />
                    <x-adminlte-input name="gcs" label="GCS (E, V, M)" fgroup-class="col-md-3" />
                </div>
                <div class="row">
                    <x-adminlte-input name="spo2" label="SP02" fgroup-class="col-md-2" />
                    <x-adminlte-input name="tensi" label="Tensi" fgroup-class="col-md-2" />
                    <x-adminlte-input name="nadi" label="Nadi" fgroup-class="col-md-2" />
                    <x-adminlte-input name="respirasi" label="Respirasi" fgroup-class="col-md-3" />  
                    <x-adminlte-select-bs name="kesadaran" label="Kesadaran" fgroup-class="col-md-3">
                        <option value="Compos Mentis">Compos Mentis</option>
                        <option value="Apatis">Apatis</option>
                        <option value="Delirium">Delirium</option>
                        <option value="Somnolence">Somnolence</option>
                        <option value="Sopor">Sopor</option>
                        <option value="Coma">Coma</option>
                    </x-adminlte-select-bs>            
                </div>
                <x-adminlte-textarea name="evaluasi" label="Evaluasi" fgroup-class="col-md-12" rows="2">
                </x-adminlte-textarea>
                <div class="row justify-content-end">
                    <x-adminlte-button id="copyPemeriksaanButton" class="col-2" theme="warning" label="Copy" icon="fas fa-sign-in"/>
                    <x-adminlte-button id="pemeriksaanButton" class="col-2 ml-1" theme="primary" label="Simpan" icon="fas fa-sign-in"/>
                </div>
            </form>
        </x-adminlte-card>
        <x-adminlte-card theme="info" title="Riwayat" theme-mode="outline" header-class="rounded-bottom" collapsible>
            @php
                $config["responsive"] = true;
                $config['order'] = [[1, 'desc'], [2, 'desc']];
            @endphp
            <x-adminlte-datatable id="tableRiwayatPemeriksaanRanap" :heads="$heads" head-theme="dark" :config="$config" striped hoverable bordered compressed>
                @foreach($riwayat as $row)
                    <tr>
                        <td>{{ $row->nama }}</td>
                        <td>{{ $row->tgl_perawatan }}</td>
                        <td>{{ $row->jam_rawat }}</td>
                        <td>{{ $row->keluhan }}</td>
                        <td>{{ $row->suhu_tubuh }}</td>
                        <td>{{ $row->tensi }}</td>
                        <td>{{ $row->nadi }}</td>
                        <td>
                            <button class="btn btn-xs btn-default text-primary mx-1 shadow" onclick="showModalEdit('{{$row->no_rawat}}' ,'{{$row->tgl_perawatan}}', '{{$row->jam_rawat}}')" title="Edit">
                                <i class="fa fa-lg fa-fw fa-pen"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </x-adminlte-datatable>
        </x-adminlte-card>
    </x-adminlte-card>
</div>

<x-adminlte-modal id="editPemeriksaan" title="Edit Pemeriksaan" theme="info" size='lg' v-centered static-backdrop scrollable>
    <div></div>
    {{-- <x-adminlte-button class="d-flex ml-auto" id="editPemeriksaanButton" theme="primary" label="Simpan" icon="fas fa-sign-in"/> --}}
</x-adminlte-modal>

@push('js')
    {{-- <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}
    <script>
        function showModalEdit(noRawat, tgl, jam){
            $.ajax({
                url: "{{url('/ranap/pemeriksaan')}}"+"/"+"{{$encryptNoRawat}}"+"/"+tgl+"/"+jam,
                type: "GET",
                beforeSend : function() {
                    Swal.fire({
                        title: 'Loading....',
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response){
                    Swal.close();
                    var html = '' + 
                                    '<input id="nip" name="nip" type="hidden" value="'+response.data.nip+'" class="form-control">' +
                                    '<input id="editjam" name="editjam" type="hidden" value="'+response.data.jam_rawat+'" class="form-control">' +                    
                                    '<input id="edittgl" name="edittgl" type="hidden" value="'+response.data.tgl_perawatan+'" class="form-control">' + 
                                    '<div class="row">' + 
                                    '	<div class="form-group col-md-6">' + 
                                    '		<label for="editkeluhan"> Subjek </label>' + 
                                    '		<div class="input-group">' + 
                                    '			<textarea id="editkeluhan" name="editkeluhan" class="form-control" rows="4">'+response.data.keluhan+'</textarea>' + 
                                    '		</div>' + 
                                    '	</div>' + 
                                    '	<div class="form-group col-md-6">' + 
                                    '		<label for="editpemeriksaan"> Objek </label>' + 
                                    '		<div class="input-group">' + 
                                    '			<textarea id="editpemeriksaan" name="editpemeriksaan" class="form-control" rows="4">'+response.data.pemeriksaan+'</textarea>' + 
                                    '		</div>' + 
                                    '	</div>' + 
                                    '</div>' + 
                                    '<div class="row">' + 
                                    '	<div class="form-group col-md-6">' + 
                                    '		<label for="editpenilaian"> Asesmen </label>' + 
                                    '		<div class="input-group">' + 
                                    '			<textarea id="editpenilaian" name="editpenilaian" class="form-control" rows="2">'+response.data.penilaian+'</textarea>' + 
                                    '		</div>' + 
                                    '	</div>' + 
                                    '	<div class="form-group col-md-6">' + 
                                    '		<label for="editinstruksi"> Instruksi </label>' + 
                                    '		<div class="input-group">' + 
                                    '			<textarea id="editinstruksi" name="editinstruksi" class="form-control" rows="2">'+response.data.instruksi+'</textarea>' + 
                                    '		</div>' + 
                                    '	</div>' + 
                                    '</div>' + 
                                    '<div class="row">' + 
                                    '	<div class="form-group col-md-6">' + 
                                    '		<label for="editrtl"> Plan </label>' + 
                                    '		<div class="input-group">' + 
                                    '			<textarea id="editrtl" name="editrtl" class="form-control" rows="2">'+response.data.rtl+'</textarea>' + 
                                    '		</div>' + 
                                    '	</div>' + 
                                    '	<div class="form-group col-md-6">' + 
                                    '		<label for="editalergi"> Alergi </label>' + 
                                    '		<div class="input-group">' + 
                                    '			<textarea id="editalergi" name="editalergi" class="form-control" rows="2">'+response.data.alergi+'</textarea>' + 
                                    '		</div>' + 
                                    '	</div>' + 
                                    '</div>' + 
                                    '<div class="row">' + 
                                    '	<div class="form-group col-md-3">' + 
                                    '		<label for="editsuhu"> Suhu Badan (C) </label>' + 
                                    '		<div class="input-group">' + 
                                    '			<input id="editsuhu" name="editsuhu" value="'+response.data.suhu_tubuh+'" class="form-control"> </div>' + 
                                    '	</div>' + 
                                    '	<div class="form-group col-md-3">' + 
                                    '		<label for="editberat"> Berat (Kg) </label>' + 
                                    '		<div class="input-group">' + 
                                    '			<input id="editberat" name="editberat" value="'+response.data.berat+'" class="form-control"> </div>' + 
                                    '	</div>' + 
                                    '	<div class="form-group col-md-3">' + 
                                    '		<label for="edittinggi"> Tinggi Badan (Cm) </label>' + 
                                    '		<div class="input-group">' + 
                                    '			<input id="edittinggi" name="edittinggi" value="'+response.data.tinggi+'" class="form-control"> </div>' + 
                                    '	</div>' + 
                                    '	<div class="form-group col-md-3">' + 
                                    '		<label for="editgcs"> GCS (E, V, M) </label>' + 
                                    '		<div class="input-group">' + 
                                    '			<input id="editgcs" name="editgcs" value="'+response.data.gcs+'" class="form-control"> </div>' + 
                                    '	</div>' + 
                                    '</div>' + 
                                    '<div class="row">' + 
                                    '	<div class="form-group col-md-2">' + 
                                    '		<label for="edittensi"> SP02 </label>' + 
                                    '		<div class="input-group">' + 
                                    '			<input id="editspo2" name="editspo2" value="'+response.data.spo2+'" class="form-control"> </div>' + 
                                    '	</div>' + 
                                    '	<div class="form-group col-md-2">' + 
                                    '		<label for="edittensi"> Tensi </label>' + 
                                    '		<div class="input-group">' + 
                                    '			<input id="edittensi" name="edittensi" value="'+response.data.tensi+'" class="form-control"> </div>' + 
                                    '	</div>' + 
                                    '	<div class="form-group col-md-2">' + 
                                    '		<label for="editnadi"> Nadi (per Menit) </label>' + 
                                    '		<div class="input-group">' + 
                                    '			<input id="editnadi" name="editnadi" value="'+response.data.nadi+'" class="form-control"> </div>' + 
                                    '	</div>' + 
                                    '	<div class="form-group col-md-3">' + 
                                    '		<label for="editrespirasi"> Respirasi (per Menit) </label>' + 
                                    '		<div class="input-group">' + 
                                    '			<input id="editrespirasi" name="editrespirasi" value="'+response.data.respirasi+'" class="form-control"> </div>' + 
                                    '	</div>' + 
                                    '	<div class="form-group col-md-3">' + 
                                    '		<label for="editkesadaran"> Kesadaran </label>' + 
                                    '		<div class="input-group">' + 
                                    '			<div class="dropdown bootstrap-select form-control">' + 
                                    '				<select id="editkesadaran" name="editkesadaran" class="form-control" tabindex="-98">' + 
                                    '					<option>Compos Mentis</option>' + 
                                    '					<option>Somnolence</option>' + 
                                    '					<option>Sopor</option>' + 
                                    '					<option>Coma</option>' + 
                                    '				</select>' + 
                                    '				<button type="button" class="btn dropdown-toggle btn-light" data-toggle="dropdown" role="combobox" aria-owns="bs-select-2" aria-haspopup="listbox" aria-expanded="false" data-id="editkesadaran" title="Compos Mentis">' + 
                                    '					<div class="filter-option">' + 
                                    '						<div class="filter-option-inner">' + 
                                    '							<div class="filter-option-inner-inner">Compos Mentis</div>' + 
                                    '						</div>' + 
                                    '					</div>' + 
                                    '				</button>' + 
                                    '				<div class="dropdown-menu ">' + 
                                    '					<div class="inner show" role="listbox" id="bs-select-2" tabindex="-1">' + 
                                    '						<ul class="dropdown-menu inner show" role="presentation"></ul>' + 
                                    '					</div>' + 
                                    '				</div>' + 
                                    '			</div>' + 
                                    '		</div>' + 
                                    '	</div>' + 
                                    '</div>' + 
                                    '<div class="form-group col-md-12">' + 
                                    '		<label for="editrtl"> Evaluasi </label>' + 
                                    '		<div class="input-group">' + 
                                    '			<textarea id="edievaluasi" name="editevaluasi" class="form-control" rows="2">'+response.data.evaluasi+'</textarea>' + 
                                    '		</div>' + 
                                    '	</div>' + 
                                    '<button type="button" class="btn btn-primary d-flex ml-auto" onclick="edit(event)"> <i class="fas fa-sign-in"></i> Perbaharui </button>' + 
                                    '';
                    $('#editPemeriksaan').find('.modal-body').html(html);
                    $('#editPemeriksaan').modal('show');
                },
                error: function(data){
                    Swal.close();
                    console.log(data);
                }
            });
        }

        function edit(e){
            e.preventDefault();
            let tgl_perawatan = $("input[name=edittgl]").val();
            let jam_rawat = $("input[name=editjam]").val();

            var data = {
                kesadaran:$("select[name=editkesadaran]").val(),
                keluhan:$("textarea[name=editkeluhan]").val(),
                pemeriksaan:$("textarea[name=editpemeriksaan]").val(),
                penilaian:$("textarea[name=editpenilaian]").val(),
                suhu:$("input[name=editsuhu]").val(),
                berat:$("input[name=editberat]").val(),
                tinggi:$("input[name=edittinggi]").val(),
                tensi:$("input[name=edittensi]").val(),
                nadi:$("input[name=editnadi]").val(),
                respirasi:$("input[name=editrespirasi]").val(),
                instruksi:$("textarea[name=editinstruksi]").val(),
                alergi:$("textarea[name=editalergi]").val(),
                rtl:$("textarea[name=editrtl]").val(),
                gcs:$("input[name=editgcs]").val(),
                evaluasi:$("textarea[name=edievaluasi]").val(),
                spo2:$("input[name=editspo2]").val(),
                _token:$('meta[name="csrf-token"]').attr('content'),
            };
            $.ajax({
                url:"{{url('/ranap/pemeriksaan/edit')}}"+"/"+"{{$encryptNoRawat}}"+"/"+tgl_perawatan+"/"+jam_rawat,
                method:"POST",
                data:data,
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
                success:function(response){
                    Swal.fire({
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'Tutup'
                    }).then((result) => {
                                    if (result.value) {
                                        window.location.reload();
                                    }});
                },
                error:function(error){
                    console.log(error);
                    Swal.fire({
                    title: 'Error!',
                    text: error.message,
                    icon: 'error',
                    confirmButtonText: 'Tutup'
                    });
                }
            });
        }

        $('#copyPemeriksaanButton').click(function (e){
            e.preventDefault();
            $.ajax({
                url:"{{url('/api/pemeriksaan')}}"+"/"+"{{$encryptNoRawat}}",
                method:"GET",
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

                success:function(response){
                    Swal.close();
                    console.log(response);
                    $("select[name=kesadaran]").val(response.data.kesadaran);
                    $("textarea[name=keluhan]").val(response.data.keluhan);
                    $("textarea[name=pemeriksaan]").val(response.data.pemeriksaan);
                    $("textarea[name=penilaian]").val(response.data.penilaian);
                    $("input[name=suhu]").val(response.data.suhu);
                    $("input[name=berat]").val(response.data.berat);
                    $("input[name=tinggi]").val(response.data.tinggi);
                    $("input[name=tensi]").val(response.data.tensi);
                    $("input[name=nadi]").val(response.data.nadi);
                    $("input[name=respirasi]").val(response.data.respirasi);
                    $("textarea[name=instruksi]").val(response.data.instruksi);
                    $("textarea[name=alergi]").val(response.data.alergi);
                    $("textarea[name=rtl]").val(response.data.rtl);
                    $("input[name=gcs]").val(response.data.gcs);
                    $("input[name=spo2]").val(response.data.spo2);
                    // $("textarea[name=edievaluasi]").val(response.data.evaluasi);
                    $("input[name=spo2]").val(response.data.spo2);
                },
                error:function(error){
                    console.log(error);
                    Swal.fire({
                        title: 'Error!',
                        text: error.message,
                        icon: 'error',
                        confirmButtonText: 'Tutup'
                    });
                }
            });
        });

        $("#pemeriksaanButton").click(function(event){
            event.preventDefault();
            var select = document.getElementById('kesadaran');
            var option = select.options[select.selectedIndex];
            let kesadaran = option.text;
            let keluhan = $("textarea[name=keluhan]").val();
            let pemeriksaan = $("textarea[name=pemeriksaan]").val();
            let penilaian = $("textarea[name=penilaian]").val();
            let suhu = $("input[name=suhu]").val();
            let berat = $("input[name=berat]").val();
            let tinggi = $("input[name=tinggi]").val();
            let tensi = $("input[name=tensi]").val();
            let nadi = $("input[name=nadi]").val();
            let respirasi = $("input[name=respirasi]").val();
            let instruksi = $("textarea[name=instruksi]").val();
            let alergi = $("textarea[name=alergi]").val();
            let rtl = $("textarea[name=rtl]").val();
            let gcs = $("input[name=gcs]").val();
            let spo2 = $("input[name=spo2]").val();
            let evaluasi = $("textarea[name=evaluasi]").val();
            let _token   = $('meta[name="csrf-token"]').attr('content');
            // alert("{{request()->get('no_rawat')}}");
            $.ajax({
            url: "/ranap/pemeriksaan/submit",
            type:"POST",
            data:{
                no_rawat:"{{$encryptNoRawat}}",
                keluhan:keluhan,
                pemeriksaan:pemeriksaan,
                penilaian:penilaian,
                suhu:suhu,
                berat:berat,
                tinggi:tinggi,
                tensi:tensi,
                nadi:nadi,
                respirasi:respirasi,
                instruksi:instruksi,
                kesadaran:kesadaran,
                alergi:alergi,
                rtl:rtl,
                gcs:gcs,
                spo2:spo2,
                evaluasi:evaluasi,
                _token: _token
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
            success:function(response){
                console.log(response);
                // var res = $.parseJSON(response);
                Swal.fire({
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'Tutup'
                }).then((result) => {
                                    if (result.value) {
                                        window.location.reload();
                                    }});
            },
            error: function(error) {
            console.log(error);
            var errors = $.parseJSON(error.responseText);
                Swal.fire({
                    title: 'Error!',
                    text: errors.message,
                    icon: 'error',
                    confirmButtonText: 'Tutup'
                });
            }
            });
        });
    </script>
@endpush