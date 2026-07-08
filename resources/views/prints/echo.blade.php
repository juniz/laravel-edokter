<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Echo AR</title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 25mm;
        }
        * {
            box-sizing: border-box;
        }
        h1, h2, h3, h4, h5, h6, p, table, tr, td, ol, ul, li {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Arial Narrow', Arial, sans-serif;
            font-size: 12px;
            font-weight: bold;
            color: #000;
            line-height: 1.3;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 6px;
        }
        .header-table td {
            vertical-align: middle;
            padding: 4px;
        }
        .header-logo {
            width: 120px;
            text-align: center;
        }
        .header-logo img {
            max-width: 110px;
            height: auto;
        }
        .header-info {
            text-align: center;
            font-size: 12px;
        }
        .header-info .rs-name {
            text-decoration: underline;
        }
        .header-right {
            width: 40%;
        }
        .title-section {
            text-align: center;
            margin: 8px 0;
            font-size: 13px;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .patient-table {
            width: 100%;
            font-size: 12px;
            margin: 8px 0;
        }
        .patient-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .content-section {
            margin: 12px 0;
            padding-left: 16px;
            font-size: 12px;
        }
        .content-section p {
            margin-bottom: 4px;
        }
        .ttd-section {
            margin-top: 16px;
            width: 100%;
        }
        .ttd-section td {
            padding: 4px;
            font-size: 12px;
        }
        .ttd-right {
            text-align: left;
            width: 45%;
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

    {{-- Header / Kop surat --}}
    <table cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom: 12px; border: none;">
        <tr>
            <td style="width: 50%; text-align: center; border: none; padding: 0;">
                @if(!empty($logo_base64))
                    <img src="{{ $logo_base64 }}" alt="Logo RS" style="max-width: 80px; height: auto; margin-bottom: 4px;">
                @endif
                <div style="font-size: 11px; font-weight: bold; line-height: 1.3; text-align: center;">
                    POLRI DAERAH JAWA TIMUR<br>
                    BIDANG KEDOKTERAN DAN KESEHATAN<br>
                    <span style="text-decoration: underline;">RUMAH SAKIT BHAYANGKARA TK. III NGANJUK</span>
                </div>
            </td>
            <td style="width: 50%; border: none;"></td>
        </tr>
    </table>

    <div class="title-section">
        LAPORAN HASIL PEMERIKSAAN ECHOKARDIOGRAFI
    </div>

    <table class="patient-table" cellspacing="0" cellpadding="0">
        <tbody>
            <tr>
                <td width="10%">Nama</td>
                <td width="40%">: {{ $pasien->nm_pasien }}</td>
                <td width="10%">No. RM</td>
                <td width="40%">: {{ $pasien->no_rkm_medis }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>: {{ $pasien->alamat }}</td>
                <td>Umur</td>
                <td>: {{ $pasien->umur }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: {{ date('d-m-Y') }}</td>
                <td>Pengirim</td>
                <td>: {{ $pengirim }}</td>
            </tr>
        </tbody>
    </table>

    <div class="content-section">
        {!! $isi !!}
    </div>

    <table class="ttd-section" cellspacing="0" cellpadding="0">
        <tr>
            <td style="width: 55%; vertical-align: top;"></td>
            <td class="ttd-right" style="vertical-align: top;">
                Salam Sejawat,<br>
                <br><br><br>
                <u>({{ $dokter->nm_dokter }})</u><br>
                SIP. {{ $dokter->no_ijn_praktek }}
            </td>
        </tr>
    </table>

</body>
</html>