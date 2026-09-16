<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Penilaian Pre Operasi - {{ $penilaian->no_rawat ?? '' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            margin: 5mm 10mm;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5px;
            color: #000;
            line-height: 1.25;
            background: #fff;
        }

        .print-container {
            width: 100%;
        }

        /* Header / Kop */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .hospital-logo {
            max-width: 48px;
            max-height: 48px;
            height: auto;
            display: block;
            margin: 0 auto 2px;
            border: none;
            outline: none;
        }

        .kop-text {
            font-size: 9px;
            font-weight: bold;
            line-height: 1.2;
            text-align: center;
        }

        .kop-text .hospital-name {
            font-size: 10px;
            text-decoration: underline;
        }

        .doc-box {
            border: 1.5px solid #000;
            padding: 3px 6px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            float: right;
            white-space: nowrap;
        }

        /* Form Title */
        .form-title {
            text-align: center;
            font-size: 11.5px;
            font-weight: bold;
            text-decoration: underline;
            margin: 3px 0 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Metadata Table */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-bottom: 5px;
            font-size: 9px;
        }

        .meta-table td {
            border: 1px solid #000;
            padding: 2.5px 5px;
            vertical-align: middle;
        }

        .meta-label {
            font-weight: bold;
            width: 125px;
            background-color: #f7f7f7;
        }

        .meta-val {
            font-weight: normal;
        }

        .barcode-cell {
            text-align: center;
            vertical-align: middle;
            padding: 2px;
            background-color: #fff;
        }

        .barcode-img {
            width: 120px;
            height: 22px;
            display: block;
            margin: 0 auto 1px;
        }

        .barcode-text {
            font-family: 'Courier New', Courier, monospace;
            font-size: 8.5px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        /* Assessment Table */
        .assessment-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-bottom: 5px;
            font-size: 9px;
            table-layout: fixed;
        }

        .assessment-table td {
            border: 1px solid #000;
            padding: 3.5px 6px;
            vertical-align: top;
        }

        .field-title {
            font-weight: bold;
            background-color: #f7f7f7;
            width: 28%;
            color: #111;
        }

        .field-val {
            width: 72%;
            line-height: 1.3;
            word-wrap: break-word;
        }

        /* Signature Footer */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px;
        }

        .signature-table td {
            vertical-align: top;
            font-size: 9px;
        }

        .tte-box-table {
            margin: 2px auto;
            border: 1px dashed #555;
            border-radius: 4px;
            background-color: #fafafa;
            border-collapse: collapse;
        }

        .tte-box-table td {
            padding: 2.5px 5px;
            vertical-align: middle;
        }

        .tte-qr-img {
            width: 46px;
            height: 46px;
            display: block;
        }

        .tte-meta {
            font-size: 7.5px;
            line-height: 1.2;
            color: #222;
            text-align: left;
        }

        .tte-title {
            font-size: 7px;
            color: #555;
            font-style: italic;
        }

        .tte-doc-name {
            font-weight: bold;
            color: #000;
            font-size: 8px;
        }

        .tte-footer-name {
            font-weight: bold;
            margin-top: 2px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    @php
        $tglLahirRaw = $penilaian->pasien_tgl_lahir ?? $penilaian->tgl_lahir ?? null;
        $tglLahirFormatted = $tglLahirRaw ? \Carbon\Carbon::parse($tglLahirRaw)->format('d-m-Y') : ($tglLahirFormatted ?? '-');
        $noRm = $penilaian->no_rkm_medis ?? $penilaian->no_rm ?? ($noRm ?? '-');
        $namaPasien = $penilaian->nama_pasien ?? $penilaian->nm_pasien ?? '-';
        $namaDokter = $penilaian->nama_dokter ?? $penilaian->nm_dokter ?? ($penilaian->nama_operator ?? '-');
        $kdDokter = $penilaian->kd_dokter ?? $penilaian->operator_id ?? '-';
        $umur = $umurPasien ?? (isset($penilaian->umurdaftar) ? ($penilaian->umurdaftar . ' ' . ($penilaian->sttsumur ?? '')) : '-');
        $jk = $penilaian->jk ?? ($jenisKelamin ?? '-');
        if ($jk === 'L') {
            $jkFormatted = 'Laki-laki';
        } elseif ($jk === 'P') {
            $jkFormatted = 'Perempuan';
        } else {
            $jkFormatted = $jk;
        }

        $tanggalAss = !empty($tanggalPenilaian) ? $tanggalPenilaian : (!empty($penilaian->tanggal) ? \Carbon\Carbon::parse($penilaian->tanggal)->isoFormat('D MMMM Y HH:mm') : \Carbon\Carbon::now()->isoFormat('D MMMM Y HH:mm'));

        $barcodeSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="22" viewBox="0 0 120 22"><rect x="0" width="2" height="22" fill="#000"/><rect x="4" width="3" height="22" fill="#000"/><rect x="9" width="2" height="22" fill="#000"/><rect x="13" width="3" height="22" fill="#000"/><rect x="18" width="1" height="22" fill="#000"/><rect x="21" width="3" height="22" fill="#000"/><rect x="26" width="2" height="22" fill="#000"/><rect x="30" width="4" height="22" fill="#000"/><rect x="36" width="2" height="22" fill="#000"/><rect x="40" width="3" height="22" fill="#000"/><rect x="45" width="1" height="22" fill="#000"/><rect x="48" width="4" height="22" fill="#000"/><rect x="54" width="2" height="22" fill="#000"/><rect x="58" width="3" height="22" fill="#000"/><rect x="63" width="2" height="22" fill="#000"/><rect x="67" width="4" height="22" fill="#000"/><rect x="73" width="1" height="22" fill="#000"/><rect x="76" width="3" height="22" fill="#000"/><rect x="81" width="2" height="22" fill="#000"/><rect x="85" width="4" height="22" fill="#000"/><rect x="91" width="2" height="22" fill="#000"/><rect x="95" width="3" height="22" fill="#000"/><rect x="100" width="1" height="22" fill="#000"/><rect x="103" width="4" height="22" fill="#000"/><rect x="109" width="2" height="22" fill="#000"/><rect x="113" width="3" height="22" fill="#000"/><rect x="118" width="2" height="22" fill="#000"/></svg>';
        $barcodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($barcodeSvg);
    @endphp

    <div class="print-container">
        {{-- Header / Kop --}}
        <table class="header-table">
            <tr>
                <td style="width: 70%; vertical-align: top; text-align: left;">
                    <table style="border-collapse: collapse; margin-left: 0; text-align: center;">
                        <tr>
                            <td style="text-align: center; padding: 0;">
                                @if(!empty($setting->logo_base64))
                                    <img src="{{ $setting->logo_base64 }}" class="hospital-logo" alt="Logo">
                                @elseif(file_exists(public_path('img/logo/logo-kop.png')))
                                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/logo/logo-kop.png'))) }}" class="hospital-logo" alt="Logo">
                                @elseif(file_exists(public_path('img/logo/logo.png')))
                                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/logo/logo.png'))) }}" class="hospital-logo" alt="Logo">
                                @endif
                                <div class="kop-text">
                                    <div>{{ $setting->header_title_1 ?? 'POLRI DAERAH JAWA TIMUR' }}</div>
                                    <div>{{ $setting->header_title_2 ?? 'BIDANG KEDOKTERAN DAN KESEHATAN' }}</div>
                                    <div class="hospital-name">{{ $setting->hospital_name ?? 'RUMAH SAKIT BHAYANGKARA TK. III NGANJUK' }}</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 30%; vertical-align: top; text-align: right;">
                    <div class="doc-box">
                        RM. 41 / Halaman 1
                    </div>
                </td>
            </tr>
        </table>

        {{-- Form Title --}}
        <div class="form-title">
            FORMULIR PENILAIAN PRE OPERASI
        </div>

        {{-- Patient Metadata Table --}}
        <table class="meta-table">
            <tr>
                <td class="meta-label">NO. RM</td>
                <td class="meta-val" style="width: 40%;">: <strong>{{ $noRm }}</strong></td>
                <td rowspan="3" class="barcode-cell" style="width: 35%;">
                    <img src="{{ $barcodeBase64 }}" class="barcode-img" alt="Barcode">
                    <div class="barcode-text">*{{ $noRm }}*</div>
                </td>
            </tr>
            <tr>
                <td class="meta-label">NAMA PASIEN</td>
                <td class="meta-val">: <strong>{{ $namaPasien }}</strong> ({{ $umur }})</td>
            </tr>
            <tr>
                <td class="meta-label">TGL. LAHIR / JK</td>
                <td class="meta-val">: {{ $tglLahirFormatted }} / {{ $jkFormatted }}</td>
            </tr>
            <tr>
                <td class="meta-label">NO. RAWAT</td>
                <td class="meta-val" colspan="2">: {{ $penilaian->no_rawat ?? '-' }}</td>
            </tr>
            <tr>
                <td class="meta-label">TANGGAL PENILAIAN</td>
                <td class="meta-val" colspan="2">: {{ $tanggalAss }}</td>
            </tr>
            <tr>
                <td class="meta-label">DOKTER OPERATOR</td>
                <td class="meta-val" colspan="2">: {{ $namaDokter }}</td>
            </tr>
        </table>

        {{-- Assessment Sections Table (7 Fields) --}}
        <table class="assessment-table">
            <tr>
                <td class="field-title">1. RINGKASAN KLINIK</td>
                <td class="field-val">{!! !empty($penilaian->ringkasan_klinik) ? nl2br(e($penilaian->ringkasan_klinik)) : '-' !!}</td>
            </tr>
            <tr>
                <td class="field-title">2. PEMERIKSAAN FISIK</td>
                <td class="field-val">{!! !empty($penilaian->pemeriksaan_fisik) ? nl2br(e($penilaian->pemeriksaan_fisik)) : '-' !!}</td>
            </tr>
            <tr>
                <td class="field-title">3. PEMERIKSAAN DIAGNOSTIK</td>
                <td class="field-val">{!! !empty($penilaian->pemeriksaan_diagnostik) ? nl2br(e($penilaian->pemeriksaan_diagnostik)) : '-' !!}</td>
            </tr>
            <tr>
                <td class="field-title">4. DIAGNOSA PRE OPERASI</td>
                <td class="field-val">{!! !empty($penilaian->diagnosa_pre_operasi) ? nl2br(e($penilaian->diagnosa_pre_operasi)) : '-' !!}</td>
            </tr>
            <tr>
                <td class="field-title">5. RENCANA TINDAKAN BEDAH</td>
                <td class="field-val">{!! !empty($penilaian->rencana_tindakan_bedah) ? nl2br(e($penilaian->rencana_tindakan_bedah)) : '-' !!}</td>
            </tr>
            <tr>
                <td class="field-title">6. HAL-HAL YANG PERLU DIPERSIAPKAN</td>
                <td class="field-val">{!! !empty($penilaian->hal_hal_yang_perludi_persiapkan) ? nl2br(e($penilaian->hal_hal_yang_perludi_persiapkan)) : '-' !!}</td>
            </tr>
            <tr>
                <td class="field-title">7. TERAPI PRE OPERASI</td>
                <td class="field-val">{!! !empty($penilaian->terapi_pre_operasi) ? nl2br(e($penilaian->terapi_pre_operasi)) : '-' !!}</td>
            </tr>
        </table>

        {{-- Signature Box (Bottom Right) --}}
        <table class="signature-table">
            <tr>
                <td style="width: 48%;"></td>
                <td style="width: 52%; text-align: center;">
                    <div>Nganjuk, {{ $tanggalAss }}</div>
                    <div style="font-weight: bold; margin-bottom: 2px;">Dokter Operator</div>
                    <table class="tte-box-table">
                        <tr>
                            <td style="text-align: center;">
                                @if(!empty($qrBase64))
                                    <img src="{{ $qrBase64 }}" class="tte-qr-img" alt="QR TTE">
                                @endif
                            </td>
                            <td>
                                <div class="tte-meta">
                                    <div class="tte-title">Ditandatangani secara elektronik:</div>
                                    <div class="tte-doc-name">{{ $namaDokter }}</div>
                                    <div>Kode Dokter: {{ $kdDokter }}</div>
                                    <div>Waktu TTD: {{ $tanggalAss }}</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <div class="tte-footer-name">( {{ $namaDokter }} )</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
