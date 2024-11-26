<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Echo AR</title>
    {{-- <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}"> --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <style>
        html, body {
            width:  210mm;
            height: 297mm;
            font: 12px 'Arial Narrow', Arial, sans-serif; 
            font-stretch: condensed;
            font-weight: bold;
        }
        table {
        page-break-inside: auto;
        }
        tr {
        page-break-inside: avoid;
        page-break-after: auto;
        }
        thead {
        display: table-header-group;
        }
        tfoot {
        display: table-footer-group;
        }
    </style>
</head>
<body>
    <div class="row">
        <div class="col-5 text-center">
            <img src="{{ asset('img/logo/logo-kop.png') }}" alt="kop" sizes="30">
        </div>
        <div class="col-7"></div>
    </div>
    <div class="row">
        <div class="col-5 text-center fw-bold" style="font-size:12px;">
            POLRI DAERAH JAWA TIMUR<br>
            BIDANG KEDOKTERAN DAN KESEHATAN<br>
            <u>RUMAH SAKIT BHAYANGKARA TK. III NGANJUK</u>
        </div>
        <div class="col-7">
        </div>
    </div>
    <div class="row p-3" style="font-size:12px;">
        <div class="col-9 text-center"><u>LAPORAN HASIL PEMERIKSAAN ECHOKARDIOGRAFI</u></div>
    </div>
    <div class="mx-4 mt-3">
        <table style="width: 100%;font-size:12px;">
            <tbody>
                <tr>
                    <td width="10%">Nama</td>
                    <td width="40%">: {{ $pasien->nm_pasien }}</td>
                    <td width="10%">No. RM</td>
                    <td width="40%">: {{ $pasien->no_rkm_medis }}</td>
                </tr>
                <tr>
                    <td width="10%">Alamat</td>
                    <td width="40%">: {{ $pasien->alamat }}</td>
                    <td width="10%">Umur</td>
                    <td width="40%">: {{ $pasien->umur }}</td>
                </tr>
                <tr>
                    <td width="10%">Tanggal</td>
                    <td width="40%">: {{ date('d-m-Y') }}</td>
                    <td width="10%">Pengirim</td>
                    <td width="40%">: {{ $pengirim }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="mx-4 mt-3" style="font-size:12px;">
        <p>{!! $isi!!}</p>
    </div>
    <div class="row mt-3">
        <div class="col-7"></div>
        <div class="col-5" style="font-size:12px;">
            Salam Sejawat,<br>
            <br><br><br>
            <u>({{ $dokter->nm_dokter }})</u><br>
            SIP. {{ $dokter->no_ijn_praktek }}
        </div>
    </div>
    <script>
		window.print();
	</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
</body>
</html>