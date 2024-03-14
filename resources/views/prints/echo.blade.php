<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Echo AR</title>
    {{-- <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}"> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <style>
        @page {
            size:    A4 portrait;
            margin: 20mm;
        }

        body {
            width:   210mm;
            height:  297mm;
        }
        p {
            margin-bottom: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px important!;
        }
        img {
            border: none;
            outline: none;
        }
        .judul {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .header {
            margin-top: 20px;
            margin-bottom: 20px;
            margin-left: 0;
            width: 50%;
            text-align: center;
        }
        @media print {
            html, body {
                width: 210mm;
                height: 297mm;
            }
        }`
    </style>
</head>
<body class="antialiasing">
    <div class="header">
        <div class="d-flex flex-row">
            <div class="mx-auto">
                <img src="{{ asset('img/logo/logo-kop.png') }}" alt="kop" sizes="30">
            </div>
        </div>
        <div class="text-center">
            <p>POLRI DAERAH JAWA TIMUR</p>
            <p>BIDANG KEDOKTERAN DAN KESEHATAN</p>
            <p><u>RS. BHAYANGKARA MOESTADJAB NGANJUK</u></p>
        </div>
    </div>
    <div class="judul">
        <p><u>LAPORAN HASIL PEMERIKSAAN ECHOKARDIOGRAFI</u></p>
    </div>
    {{-- <div class="mx-4 mt-3">
        <table style="width: 100%">
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
                    <td width="40%">: </td>
                </tr>
            </tbody>
        </table>
    </div> --}}
    {{-- <div class="mx-4 mt-3">
        <p>{!! $isi !!}</p>
    </div>
    <div class="d-flex flex-row-reverse">
        <div class="col-md-4">
            <p>Salam Sejawat,</p>
            <br><br><br>
            <p><u>({{ $dokter->nm_dokter }})</u></p>
            <p>STR. {{ $dokter->no_ijn_praktek }}</p>
        </div>
    </div> --}}
</body>
</html>