<div>
    <form wire:submit.prevent='simpan'>
        <div class="row">
            <div class="col-6 col-md-6">
                <x-ui.input-datetime label="Tanggal" id="tanggal_ortho" model='tanggal_ortho' />
            </div>
            <div class="col-6 col-md-6">
                <div class="form-group">
                    <label for="informasi">Anamnesis</label>
                    <div class="row">
                        <div class="col-6 col-md-6">
                            <x-ui.select id="informasi" model='informasi'>
                                <option value="Autoanamnesis">Autoanamnesis</option>
                                <option value="Alloanamnesis">Alloanamnesis</option>
                            </x-ui.select>
                        </div>
                        <div class="col-6 col-md-6">
                            <x-ui.input id="ket_informasi" type="text" model='ket_informasi' />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h6 class="text-bold">I. RIWAYAT KESEHATAN</h6>
        <div class="row">
            <div class="col-6 col-md-6">
                <x-ui.textarea label="Keluhan Utama" id="keluhan_utama" model='keluhan_utama' />
            </div>
            <div class="col-6 col-md-6">
                <x-ui.textarea label="Riwayat Penyakit Sekarang" id="penyakit_sekarang" model='penyakit_sekarang' />
            </div>
        </div>
        <div class="row">
            <div class="col-6 col-md-6">
                <x-ui.textarea label="Riwayat Pengobatan" id="riwayat_pengobatan" model='riwayat_pengobatan' />
            </div>
            <div class="col-6 col-md-6">
                <x-ui.textarea label="Riwayat Penyakit Dahulu" id="penyakit_dahulu" model='penyakit_dahulu' />
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-12">
                <x-ui.input label="Riwayat Alergi" id="riwayat_alergi" type="text" model='riwayat_alergi' />
            </div>
        </div>

        <h6 class="mt-3 text-bold">II. PEMERIKSAAN FISIK</h6>
        <div class="row">
            <div class="col-6 col-md-3">
                <x-ui.select label="Kesadaran" id="kesadaran" model='kesadaran'>
                    <option value="Compos Mentis">Compos Mentis</option>
                    <option value="Apatis">Apatis</option>
                    <option value="Somnolen">Somnolen</option>
                    <option value="Sopor">Sopor</option>
                    <option value="Koma">Koma</option>
                </x-ui.select>
            </div>
            <div class="col-6 col-md-3">
                <x-ui.input label="TD (mmHg)" id="td" type="text" model='td' />
            </div>
            <div class="col-6 col-md-3">
                <x-ui.input label="Nadi (x/menit)" id="nadi" type="text" model='nadi' />
            </div>
            <div class="col-6 col-md-3">
                <x-ui.input label="Suhu (C)" id="suhu" type="text" model='suhu' />
            </div>
            <div class="col-6 col-md-3">
                <x-ui.input label="RR (x/menit)" id="rr" type="text" model='rr' />
            </div>
        </div>
        <div class="row">
            <div class="col-6 col-md-4">
                <x-ui.input label="Status Nutrisi" id="status_nutrisi" type="text" model='status_nutrisi' />
            </div>
            <div class="col-6 col-md-2">
                <x-ui.input label="BB (Kg)" id="bb" type="text" model='bb' />
            </div>
            <div class="col-6 col-md-4">
                <x-ui.input label="Nyeri" id="nyeri" type="text" model='nyeri' />
            </div>
            <div class="col-6 col-md-2">
                <x-ui.input label="GCS (E,V,M)" id="gcs" type="text" model='gcs' />
            </div>
        </div>
        <div class="row">
            <div class="col-6 col-md-3">
                <x-ui.select label="Kepala" id="kepala" model='kepala'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
            <div class="col-6 col-md-3">
                <x-ui.select label="Abdomen" id="abdomen" model='abdomen'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
            <div class="col-6 col-md-3">
                <x-ui.select label="Columna Vertebralis" id="columna" model='columna'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
            <div class="col-6 col-md-3">
                <x-ui.select label="Muskuloskeletal" id="muskuloskeletal" model='muskuloskeletal'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
        </div>
        <div class="row">
            <div class="col-6 col-md-3">
                <x-ui.select label="Ekstremitas" id="ekstremitas" model='ekstremitas'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
            <div class="col-6 col-md-3">
                <x-ui.select label="Thoraks" id="thoraks" model='thoraks'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
            <div class="col-6 col-md-3">
                <x-ui.select label="Genetalia Os Pubis" id="genetalia" model='genetalia'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
            <div class="col-6 col-md-3">
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-12">
                <x-ui.textarea label="Keterangan" id="ket_fisik" model='ket_fisik' />
            </div>
        </div>
        <h6 class="mt-3 text-bold">III. STATUS LOKALIS</h6>
        <div class="row">
            <div class="col-md-12">
                <img class="img-fluid" src="{{ asset('assets/medis/medis-ralan.png') }}" alt="awal-medis">
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-12">
                <x-ui.textarea label="Keterangan" id="ket_lokalis" model='ket_lokalis' />
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-12">
                <x-ui.textarea label="IV. PEMERIKSAAN PENUNJANG" id="penunjang" model='penunjang' />
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-12">
                <x-ui.textarea label="V. DIAGNOSIS/ASESMEN" id="diagnosis" model='diagnosis' />
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-12">
                <x-ui.textarea label="VI. TATALAKSANA" id="tatalaksana" model='tatalaksana' />
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-12">
                <x-ui.textarea label="VII. KONSUL/RUJUK" id="konsul" model='konsul' />
            </div>
        </div>

        <div class="row">
            <div class="col-6 col-md-6 mt-3">
                <button type="reset" wire:click='confirmHapus' class="btn btn-danger btn-block">Hapus</button>
            </div>
            <div class="col-6 col-md-6 mt-3">
                <button type="submit" class="btn btn-primary btn-block">Simpan</button>
            </div>
        </div>
    </form>
</div>

@push('js')
<script>
    $(".btn-awal-medis-ortho").on('click', function(){
        alert('ok');
        var id = $(this).attr('id');
        @this.set('no_rawat', id);
        $("#modal-awal-medis-ortho").modal('show');
    });
</script>
@endpush