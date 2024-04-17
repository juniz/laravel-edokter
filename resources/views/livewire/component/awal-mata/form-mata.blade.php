<div>
    <form wire:submit.prevent='simpan'>
        <div class="row">
            <div class="col-6 col-md-6">
                <x-ui.input-datetime label="Tanggal" id="tanggal_awal" model='tanggal_awal' />
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
            <div class="col-md-2">
                <x-ui.input label="BB (Kg)" id="bb" type="text" model='bb' />
            </div>
            <div class="col-md-2">
                <x-ui.input label="TD (mmHg)" id="td" type="text" model='td' />
            </div>
            <div class="col-md-3">
                <x-ui.input label="Nadi (x/menit)" id="nadi" type="text" model='nadi' />
            </div>
            <div class="col-md-3">
                <x-ui.input label="RR (x/menit)" id="rr" type="text" model='rr' />
            </div>
            <div class="col-md-2">
                <x-ui.input label="Suhu (C)" id="suhu" type="text" model='suhu' />
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <x-ui.input label="Nyeri" id="nyeri" type="text" model='nyeri' />
            </div>
            <div class="col-md-6">
                <x-ui.input label="Status Nutrisi" id="nutrisi" type="text" model='nutrisi' />
            </div>
        </div>
        
        <h6 class="mt-3 text-bold">III. STATUS OFTAMOLOGIS</h6>
        <div class="row">
            <div class="col-md-4 text-center">
                <p>OD: Mata Kanan</p>
            </div>
            <div class="col-md-4">
            </div>
            <div class="col-md-4 text-center">
                <p>OS: Mata Kiri</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <x-ui.input id="visus_od" type="text" model='visus_od' />
            </div>
            <div class="col-md-4 text-center">
                Visus SC
            </div>
            <div class="col-md-4">
                <x-ui.input id="visus_os" type="text" model='visus_os' />
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <x-ui.input id="cc_od" type="text" model='cc_od' />
            </div>
            <div class="col-md-4 text-center">
                CC
            </div>
            <div class="col-md-4">
                <x-ui.input id="cc_os" type="text" model='cc_os' />
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <x-ui.input id="palpebra_od" type="text" model='palpebra_od' />
            </div>
            <div class="col-md-4 text-center">
                Palpebra
            </div>
            <div class="col-md-4">
                <x-ui.input id="palpebra_os" type="text" model='palpebra_os' />
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <x-ui.input id="conjungtiva_od" type="text" model='conjungtiva_od' />
            </div>
            <div class="col-md-4 text-center">
                Conjungtiva
            </div>
            <div class="col-md-4">
                <x-ui.input id="conjungtiva_os" type="text" model='conjungtiva_os' />
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <x-ui.input id="cornea_od" type="text" model='cornea_od' />
            </div>
            <div class="col-md-4 text-center">
                Cornea
            </div>
            <div class="col-md-4">
                <x-ui.input id="cornea_os" type="text" model='cornea_os' />
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <x-ui.input id="coa_od" type="text" model='coa_od' />
            </div>
            <div class="col-md-4 text-center">
                COA
            </div>
            <div class="col-md-4">
                <x-ui.input id="coa_os" type="text" model='coa_os' />
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <x-ui.input id="pupil_od" type="text" model='pupil_od' />
            </div>
            <div class="col-md-4 text-center">
                Pupil
            </div>
            <div class="col-md-4">
                <x-ui.input id="pupil_os" type="text" model='pupil_os' />
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <x-ui.input id="lensa_od" type="text" model='lensa_od' />
            </div>
            <div class="col-md-4 text-center">
                Lensa
            </div>
            <div class="col-md-4">
                <x-ui.input id="lensa_os" type="text" model='lensa_os' />
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <x-ui.input id="fundus_od" type="text" model='fundus_od' />
            </div>
            <div class="col-md-4 text-center">
                Fundus Media
            </div>
            <div class="col-md-4">
                <x-ui.input id="fundus_os" type="text" model='fundus_os' />
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <x-ui.input id="papil_od" type="text" model='papil_od' />
            </div>
            <div class="col-md-4 text-center">
                Papil
            </div>
            <div class="col-md-4">
                <x-ui.input id="papil_os" type="text" model='papil_os' />
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <x-ui.input id="retina_od" type="text" model='retina_od' />
            </div>
            <div class="col-md-4 text-center">
                Retina
            </div>
            <div class="col-md-4">
                <x-ui.input id="retina_os" type="text" model='retina_os' />
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <x-ui.input id="makula_od" type="text" model='makula_od' />
            </div>
            <div class="col-md-4 text-center">
                Makula
            </div>
            <div class="col-md-4">
                <x-ui.input id="makula_os" type="text" model='makula_os' />
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <x-ui.input id="tio_od" type="text" model='tio_od' />
            </div>
            <div class="col-md-4 text-center">
                TIO
            </div>
            <div class="col-md-4">
                <x-ui.input id="tio_os" type="text" model='tio_os' />
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <x-ui.input id="mbo_od" type="text" model='mbo_od' />
            </div>
            <div class="col-md-4 text-center">
                MBO
            </div>
            <div class="col-md-4">
                <x-ui.input id="mbo_os" type="text" model='mbo_os' />
            </div>
        </div>

        <h6 class="mt-3 text-bold">IV. PEMERIKSAAN PENUNJANG</h6>
        <div class="row">
            <div class="col-md-4">
                <x-ui.textarea label="Laboratorium" id="lab" model='lab' />
            </div>
            <div class="col-md-4">
                <x-ui.textarea label="Radiologi" id="rad" model='rad' />
            </div>
            <div class="col-md-4">
                <x-ui.textarea label="Penunjang Lainnya" id="penunjang_lain" model='penunjang_lain' />
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <x-ui.textarea label="Tes Penglihatan" id="tes_penglihatan" model='tes_penglihatan' />
            </div>
            <div class="col-md-6">
                <x-ui.textarea label="Pemeriksaan Lain" id="pemeriksaan_lain" model='pemeriksaan_lain' />
            </div>
        </div>

        <h6 class="mt-3 text-bold">V. DIAGNOSIS/ASESSMEN</h6>
        <div class="row">
            <div class="col-md-6">
                <x-ui.textarea label="Assesment Kerja" id="assesment_kerja" model='assesment_kerja' />
            </div>
            <div class="col-md-6">
                <x-ui.textarea label="Assesment Banding" id="assesment_banding" model='assesment_banding' />
            </div>
        </div>

        <h6 class="mt-3 text-bold">VI. PERMASALAHAN & TATALAKSANA</h6>
        <div class="row">
            <div class="col-md-6">
                <x-ui.textarea label="Permasalahan" id="permasalahan" model='permasalahan' />
            </div>
            <div class="col-md-6">
                <x-ui.textarea label="Terapi/Pengobatan" id="terapi" model='terapi' />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <x-ui.textarea label="Tindakan/Rencana Tindakan" id="tindakan" model='tindakan' />
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-md-12">
                <x-ui.textarea label="VII. EDUKASI" id="edukasi" model='edukasi' />
            </div>
        </div>

        <div class="row">
            <div class="col-6 col-md-6 mt-3">
                <button type="reset" wire:click='confirmHapus' class="btn btn-danger btn-block">Hapus</button>
            </div>
            <div class="col-6 col-md-6 mt-3">
                <button type="submit" class="btn btn-primary btn-block">{{ $editMode ? 'Ubah' : 'Simpan' }}</button>
            </div>
        </div>
    </form>
</div>

@push('js')
<script>
    $(".btn-awal-mata").on('click', function(){
        var id = $(this).attr('id');
        @this.set('no_rawat', id);
        $("#modal-awal-medis-mata").modal('show');
    });
</script>
@endpush
