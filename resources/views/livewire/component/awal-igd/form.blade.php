<div>
    <form wire:submit.prevent='simpan'>
        <div class="row">
            <div class="col-6 col-md-6">
                <x-ui.input-datetime label="Tanggal" id="tanggal_igd" model='tanggal' />
            </div>
            <div class="col-6 col-md-6">
                <div class="form-group">
                    <label for="informasi">Anamnesis</label>
                    <div class="row">
                        <div class="col-6 col-md-6">
                            <x-ui.select id="anamnesis" model='anamnesis'>
                                <option value="Autoanamnesis">Autoanamnesis</option>
                                <option value="Alloanamnesis">Alloanamnesis</option>
                            </x-ui.select>
                        </div>
                        <div class="col-6 col-md-6">
                            <x-ui.input id="hubungan" type="text" model='hubungan' />
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
                <x-ui.textarea label="Riwayat Penyakit Sekarang" id="rps" model='rps' />
            </div>
        </div>
        <div class="row">
            <div class="col-6 col-md-6">
                <x-ui.textarea label="Riwayat Penyakit Keluarga" id="rpk" model='rpk' />
            </div>
            <div class="col-6 col-md-6">
                <x-ui.textarea label="Riwayat Penyakit Dahulu" id="rpd" model='rpd' />
            </div>
        </div>
        <div class="row">
            <div class="col-6 col-md-6">
                <x-ui.textarea label="Riwayat Pengobatan" id="rpo" model='rpo' />
            </div>
            <div class="col-6 col-md-6">
                <x-ui.input label="Riwayat Alergi" id="alergi" type="text" model='alergi' />
            </div>
        </div>

        <h6 class="mt-3 text-bold">II. PEMERIKSAAN FISIK</h6>
        <div class="row">
            <div class="col-3 col-md-3">
                <x-ui.select label="Keadaa Umum" id="keadaan_umum" model='keadaan_umum' >
                    <option value="Sehat">Sehat</option>
                    <option value="Sakit Ringan">Sakit Ringan</option>
                    <option value="Sakit Sedang">Sakit Sedang</option>
                    <option value="Sakit Berat">Sakit Berat</option>
                </x-ui.select>
            </div>
            <div class="col-3 col-md-3">
                <x-ui.select label="Kesadaran" id="kesadaran" model='kesadaran' >
                    <option value="Compos Mentis">Compos Mentis</option>
                    <option value="Apatis">Apatis</option>
                    <option value="Somnolen">Somnolen</option>
                    <option value="Sopor">Sopor</option>
                    <option value="Coma">Koma</option>
                </x-ui.select>
            </div>
            <div class="col-2 col-md-2">
                <x-ui.input label="GCS (E,V,M)" id="gcs" type="text" model='gcs' />
            </div>
            <div class="col-2 col-md-2">
                <x-ui.input label="TB (Cm)" id="tb" type="text" model='tb' />
            </div>
            <div class="col-2 col-md-2">
                <x-ui.input label="BB (Kg)" id="bb" type="text" model='bb' />
            </div>
        </div>
        <div class="row">
            <div class="col-3 col-md-3">
                <x-ui.input label="TD (mmHg)" id="td" type="text" model='td' />
            </div>
            <div class="col-3 col-md-3">
                <x-ui.input label="Nadi (x/menit)" id="nadi" type="text" model='nadi' />
            </div>
            <div class="col-2 col-md-2">
                <x-ui.input label="RR (x/menit)" id="rr" type="text" model='rr' />
            </div>
            <div class="col-2 col-md-2">
                <x-ui.input label="Suhu (C)" id="suhu" type="text" model='suhu' />
            </div>
            <div class="col-2 col-md-2">
                <x-ui.input label="SpO2" id="spo2" type="text" model='spo2' />
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <x-ui.select label="Kepala" id="kepala" model='kepala'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
            <div class="col-md-3">
                <x-ui.select label="Thoraks" id="thoraks" model='thoraks'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
            <div class="col-md-3">
                <x-ui.select label="Mata" id="mata" model='mata'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
            <div class="col-md-3">
                <x-ui.select label="Abdomen" id="abdomen" model='abdomen'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <x-ui.select label="Gigi & Mulut" id="gigi" model='gigi'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
            <div class="col-md-3">
                <x-ui.select label="Genital & Anus" id="genital" model='genital'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
            <div class="col-md-3">
                <x-ui.select label="Leher" id="leher" model='leher'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
            <div class="col-md-3">
                <x-ui.select label="Ekstremitas" id="ekstremitas" model='ekstremitas'>
                    <option value="Normal">Normal</option>
                    <option value="Abnormal">Abnormal</option>
                    <option value="Tidak Diperiksa">Tidak Diperiksa</option>
                </x-ui.select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <x-ui.textarea id="ket_fisik" model='ket_fisik' />
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
        <h6 class="mt-3 text-bold">IV. PEMERIKSAAN PENUNJANG</h6>
        <div class="row">
            <div class="col-md-4">
                <x-ui.textarea label="EKG" id="ekg" model='ekg' />
            </div>
            <div class="col-md-4">
                <x-ui.textarea label="Radiologi" id="radiologi" model='radiologi' />
            </div>
            <div class="col-md-4">
                <x-ui.textarea label="Laborat" id="laborat" model='laborat' />
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-12">
                <x-ui.textarea label="V. DIAGNOSIS/ASESMEN" id="diagnosis" model='diagnosis' />
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-12">
                <x-ui.textarea label="VI. TATALAKSANA" id="tatalaksana" model='tatalaksana' rows='5' />
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
    $(".btn-awal-igd").on('click', function(){
        var id = $(this).attr('id');
        @this.set('no_rawat', id);
        $("#modal-awal-medis-igd").modal('show');
    });
</script>
@endpush
