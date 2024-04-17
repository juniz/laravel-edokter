<div>
    <form wire:submit.prevent='simpan'>
        <h6>A. JENIS INFORMASI</h6>
        <div class="row">
            <div class="col-md-6">
                <x-ui.textarea label='Diagnosa' id='diagnosa' model='diagnosa' live />
            </div>
            <div class="col-md-6">
                <x-ui.textarea label='Tindakan Dokter' id='tindakan_dokter' model='tindakan_dokter' live />
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <x-ui.textarea label='Indikasi Tindakan' id='indikasi_tindakan' model='indikasi_tindakan' live />
            </div>
            <div class="col-md-6">
                <x-ui.textarea label='Tata Cara' id='tata_cara' model='tata_cara' live />
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <x-ui.textarea label='Tujuan' id='tujuan' model='tujuan' live />
            </div>
            <div class="col-md-6">
                <x-ui.textarea label='Risiko' id='risiko' model='risiko' live />
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <x-ui.textarea label='Komplikasi' id='komplikasi' model='komplikasi' live />
            </div>
            <div class="col-md-6">
                <x-ui.textarea label='Progonis' id='progonis' model='progonis' live />
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <x-ui.textarea label='Alternatif & Resikonya' id='alternatif' model='alternatif' live />
            </div>
            <div class="col-md-6">
                <x-ui.textarea label='Lain-lain' id='lain' model='lain' live />
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <x-ui.input label='Biaya' id="biaya" type="number" model='biaya' live />
            </div>
        </div>
        <h6>B. PENERIMA INFORMASI</h6>
        <div class="row">
            <div class="col-md-6">
                <x-ui.select label='Hubungan Dengan Pasien' id='hubungan' model='hubungan' >
                    <option value="Diri Sendiri">Diri Sendiri</option>
                    <option value="Orang Tua">Orang Tua</option>
                    <option value="Anak">Anak</option>
                    <option value="Saudara Kandung">Saudara kandung</option>
                    <option value="Teman">Teman</option>
                    <option value="Lain-lain">Lain-lain</option>
                </x-ui.select>
            </div>
            <div class="col-md-6">
                <x-ui.input label='Alamat' id="alamat" type="text" model='alamat' live />
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <x-ui.input label='Alasan Jika Diwakilkan' id="alasan" type="text" model='alasan' live />
            </div>
            <div class="col-md-3">
                <x-ui.input label='Tgl. Lahir' id="tgl_lahir" type="date" model='tgl_lahir' live />
            </div>
            <div class="col-md-3">
                <x-ui.input label='No. HP' id="no_hp" type="text" model='no_hp' live />
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <x-ui.input label='Penerima Informasi' id="penerima_informasi" type="text" model='penerima_informasi' live />
            </div>
            <div class="col-md-3">
                <x-ui.select label='Jenis Kelamin' id='jk' model='jk' >
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </x-ui.select>
            </div>
            <div class="col-md-3">
                <x-ui.input label='Umur(Tahun)' id="umur" type="text" model='umur' live />
            </div>
        </div>
        <h6>C. SAKSI-SAKSI</h6>
        <div class="row">
            <div wire:ignore class="col-md-6">
                <label for="saksi2">Saksi II Perawat</label>
                <select name="saksi2" id="saksi2" class="form-select"></select>
            </div>
            <div class="col-md-6">
                <x-ui.input label='Saksi I Keluarga' id="saksi1" type="text" model='saksi1' live />
            </div>
        </div>
        <div class="row">
            <div class="col-4 col-md-4 mt-3">
                <button type="reset" wire:click='confirmHapus' class="btn btn-danger btn-block">Hapus</button>
            </div>
            <div class="col-4 col-md-4 mt-3">
                <button type="submit" class="btn btn-primary btn-block">Simpan</button>
            </div>
            <div class="col-4 col-md-4 mt-3">
                <button type="button" class="btn btn-secondary btn-block btn-foto-persetujuan-penolakan">Foto persetujuan</button>
            </div>
        </div>
    </form>
</div>

@push('js')
<script>
    document.addEventListener('livewire:load', function () {
        $('#saksi2').select2({
            placeholder: 'Pilih Saksi II Perawat',
            minimumInputLength: 3,
            ajax: {
                url: "{{ route('pegawai') }}",
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });
    });
    $('#saksi2').on('change', function (e) {
        @this.set('saksi2', e.target.value);
    });
    Livewire.on('saksi2', function (data) {
        $('#saksi2').val(data).trigger('change');
    });
    $('.btn-persetujuan-penolakan-tindakan').on('click', function() {
        var id = $(this).attr('id');
        @this.set('no_rawat', id);
        $('#modal-persetujuan-penolakan-tindakan').modal('show');
    });
    Livewire.on('closeModalPersetujuanTindakan', function() {
        $('#modal-persetujuan-penolakan-tindakan').modal('hide');
    });
    $('.btn-foto-persetujuan-penolakan').on('click', function(event){
        var nopernyataan = @this.nopernyataan;
        var no_rawat = @this.no_rawat;
        window.open("{{ url('/persetujuan-penolakan-tindakan') }}?nopernyataan="+nopernyataan+"&no_rawat="+no_rawat, '_blank');
    })
</script>
@endpush
