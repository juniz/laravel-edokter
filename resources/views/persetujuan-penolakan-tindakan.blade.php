<!DOCTYPE html>
<html>
<head>
    <title>SIMKES Khanza</title>
    <script src="{{ asset('js/form/jquery.min.js') }}"></script>
    <script src="{{ asset('js/form/webcam.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/form/bootstrap.min.css') }}" />
    <style type="text/css">
        #results { padding: 0px; background:#EEFFEE; width: 490; height: 390 }
    </style>
</head>
<body>
    <div class="container">
        <h5 class="text-dark"><center><button class="btn btn-secondary" onclick="window.location.reload();">Refresh</button><br/><br/>Pernyataan Persetujuan/Penolakan Tindakan No. {{ $nopernyataan }}</center></h5>
        <h7 class="text-dark"><center>Tanggal {{$tindakan->tanggal}}</center></h7><br/>
        <form method="POST" action="{{ env('URL_PERSETUJUAN_PENOLAKAN_TINDAKAN') }}" enctype=multipart/form-data>
            @csrf
            <input type="hidden" name="nopernyataan" value="{{$nopernyataan}}">
            <h7 class="text-dark">
                Apabila pasien berusia dibawah 18 tahun atau tidak dapat memberikan persetujuan karena alasan lain (**) tidak dapat menandatangani surat pernyataan ini, 
                Pihak rumah sakit dapat mengambil kebijakan dengan memperoleh tanda tangan dari orang tua, pasangan, anggota keluarga terdekat atau wali pasien.<br/> 
                (**) Sebutkan alasan lainnya : {{$tindakan->alasan_diwakilkan_penerima_informasi}} <br/><br/>
                Yang bertanda tangan di bawah ini saya :
            </h7>
            <table class="default" width="100%" border="0" align="center" cellpadding="3px" cellspacing="0px">
                <tr class="text-dark">
                    <td width="30%">Nama</td>
                    <td width="70%">: 
                        {{$tindakan->penerima_informasi}}
                    </td>
                </tr>
                <tr class="text-dark">
                    <td width="25%">Jenis Kelamin</td>
                    <td width="75%">: {{$tindakan->jk_penerima_informasi}} &nbsp;&nbsp;&nbsp;&nbsp;Tanggal Lahir : {{$tindakan->tanggal_lahir_penerima_informasi}}</td>
                </tr>
                <tr class="text-dark">
                    <td width="25%">Alamat</td>
                    <td width="75%">: {{$tindakan->alamat_penerima_informasi}}</td>
                </tr>
                <tr class="text-dark">
                    <td width="25%">Hubungan dengan pasien</td>
                    <td width="75%">: {{$tindakan->hubungan_penerima_informasi}}</td>
                </tr>
            </table>
            <br/>
            <h7 class="text-dark">
                Dengan ini menyatakan <select name="pilihansetuju" class="text-dark"><option value='Persetujuan'>Persetujuan</option><option value='Penolakan'>Penolakan</option></select> untuk dapat dilakukan tindakan kedokteran berupa : 
            </h7>
            <table class="default" width="100%" border="0" align="center" cellpadding="3px" cellspacing="0px">
                <tr class="text-dark">
                    <td width="30%">Tindakan Kedokteran</td>
                    <td width="60%">: {{$tindakan->tindakan}}</td>
                    <td width="10%"><input type="checkbox" name="tindakan_konfirmasi"></td>
                </tr>
                <tr class="text-dark">
                    <td width="30%">Diagnosa</td>
                    <td width="60%">: {{$tindakan->diagnosa}}</td>
                    <td width="10%"><input type="checkbox" name="diagnosa_konfirmasi"></td>
                </tr>
                <tr class="text-dark">
                    <td width="30%">Indikasi Tindakan</td>
                    <td width="60%">: {{$tindakan->indikasi_tindakan}}</td>
                    <td width="10%"><input type="checkbox" name="indikasi_tindakan_konfirmasi"></td>
                </tr>
                <tr class="text-dark">
                    <td width="30%">Tata Cara</td>
                    <td width="60%">: {{$tindakan->tata_cara}}</td>
                    <td width="10%"><input type="checkbox" name="tata_cara_konfirmasi"></td>
                </tr> 
                <tr class="text-dark">
                    <td width="30%">Tujuan</td>
                    <td width="60%">: {{$tindakan->tujuan}}</td>
                    <td width="10%"><input type="checkbox" name="tujuan_konfirmasi"></td>
                </tr>
                <tr class="text-dark">
                    <td width="30%">Risiko</td>
                    <td width="60%">: {{$tindakan->risiko}}</td>
                    <td width="10%"><input type="checkbox" name="risiko_konfirmasi"></td>
                </tr>
                <tr class="text-dark">
                    <td width="30%">Komplikasi</td>
                    <td width="60%">: {{$tindakan->komplikasi}}</td>
                    <td width="10%"><input type="checkbox" name="komplikasi_konfirmasi"></td>
                </tr>
                <tr class="text-dark">
                    <td width="30%">Prognosis</td>
                    <td width="60%">: {{$tindakan->prognosis}}</td>
                    <td width="10%"><input type="checkbox" name="prognosis_konfirmasi"></td>
                </tr>
                <tr class="text-dark">
                    <td width="30%">Alternatif & Resikonya</td>
                    <td width="60%">: {{$tindakan->alternatif_dan_risikonya}}</td>
                    <td width="10%"><input type="checkbox" name="alternatif_konfirmasi"></td>
                </tr>
                <tr class="text-dark">
                    <td width="30%">Lain-lain</td>
                    <td width="60%">: {{$tindakan->lain_lain}}</td>
                    <td width="10%"><input type="checkbox" name="lain_lain_konfirmasi"></td>
                </tr>
                <tr class="text-dark">
                    <td width="30%">Biaya</td>
                    <td width="60%">: {{$tindakan->biaya}}</td>
                    <td width="10%"><input type="checkbox" name="biaya_konfirmasi"></td>
                </tr>
            </table>
            <br/>
            <h7 class="text-dark">
                Terhadap Pasien : 
            </h7>
            <table class="default" width="100%" border="0" align="center" cellpadding="3px" cellspacing="0px">
                <tr class="text-dark">
                    <td width="30%">Nama Pasien</td>
                    <td width="70%">: {{$pasien->nm_pasien}}</td>
                </tr>
                <tr class="text-dark">
                    <td width="25%">Nomor Rekam Medis</td>
                    <td width="75%">: {{$pasien->no_rkm_medis}}</td>
                </tr>
                <tr class="text-dark">
                    <td width="25%">Jenis Kelamin</td>
                    <td width="75%">: {{$pasien->jk}} &nbsp;&nbsp;&nbsp;&nbsp;Tanggal Lahir : {{$pasien->tgl_lahir}}</td>
                </tr>
            </table>
            <br/>
            <h7 class="text-dark">
                Melalui penyataan ini segala resiko dan yang kemungkinan terjadi sebagai akibat dari pengambilan keputusan ini menjadi tanggung jawab saya pribadi dan keluarga
            </h7>
            <br/><br/>
            <h7 class="text-dark"><center>Yang Membuat Pernyataan</center></h7>
            <div class="row">
                <div class="col-md-6">
                    <div id="my_camera"></div>
                    <input type="hidden" name="image" class="image-tag" onkeydown="setDefault(this, document.getElementById('MsgIsi1'));" id="TxtIsi1">
                </div>
                <div class="col-md-6">
                    <div id="results"><h7 class="text-success"><center>Gambar akan diambil jika anda sudah mengeklik ya</center></h7></div>
                    <span id="MsgIsi1" style="color:#CC0000; font-size:10px;"></span>
                </div>
                <div class="col-md-12 text-center">
                    <br>
                    <input type="button" class="btn btn-warning" value="Ya, Saya sebagai pembuat pernyataan" onClick="take_snapshot()">
                    <button class="btn btn-danger">Simpan</button>
                </div>
            </div>
        </form>
    </div>
    
    <script language="JavaScript">
        Webcam.set({
            width: 490,
            height: 390,
            image_format: 'jpeg',
            jpeg_quality: 90
        });

        Webcam.attach( '#my_camera' );

        function take_snapshot() {
            Webcam.snap( function(data_uri) {
                $(".image-tag").val(data_uri);
                document.getElementById('results').innerHTML = '<img src="'+data_uri+'"/>';
            } );
        }
    </script>
</body>
</html>