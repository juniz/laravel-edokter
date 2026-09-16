<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Penilaian Pre Induksi - {{ $penilaian->no_rawat ?? '' }}</title>
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
            font-size: 10px;
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
            margin-bottom: 4px;
            font-size: 8.5px;
        }

        .meta-table td {
            border: 1px solid #000;
            padding: 2px 4px;
            vertical-align: middle;
        }

        .meta-label {
            font-weight: bold;
            width: 130px;
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
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        /* Assessment Table */
        .assessment-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-bottom: 4px;
            font-size: 8.5px;
            table-layout: fixed;
        }

        .assessment-table td {
            border: 1px solid #000;
            padding: 2px 4px;
            vertical-align: top;
        }

        .section-header td {
            background-color: #e9ecef;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            padding: 2.5px 4px;
            letter-spacing: 0.3px;
        }

        .field-title {
            font-weight: bold;
            background-color: #f7f7f7;
            color: #111;
        }

        .field-val {
            line-height: 1.25;
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
            font-size: 8.5px;
        }

        .tte-box-table {
            margin: 2px auto;
            border: 1px dashed #555;
            border-radius: 4px;
            background-color: #fafafa;
            border-collapse: collapse;
        }

        .tte-box-table td {
            padding: 2px 4px;
            vertical-align: middle;
            border: none;
        }

        .tte-qr-img {
            width: 44px;
            height: 44px;
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
            font-size: 8.5px;
        }
    </style>
</head>
<body>
    @php
        $tglLahirRaw = $penilaian->pasien_tgl_lahir ?? $penilaian->tgl_lahir ?? null;
        $tglLahirFormatted = $tglLahirRaw ? \Carbon\Carbon::parse($tglLahirRaw)->format('d-m-Y') : ($tglLahirFormatted ?? '-');
        $noRm = $penilaian->no_rkm_medis ?? $penilaian->no_rm ?? ($noRm ?? '-');
        $namaPasien = $penilaian->nama_pasien ?? $penilaian->nm_pasien ?? '-';
        $namaDokter = $penilaian->nama_dokter ?? $penilaian->nm_dokter ?? ($penilaian->nama_anestesi ?? '-');
        $kdDokter = $penilaian->kd_dokter ?? ($kdDokter ?? '-');
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

        if (empty($barcodeBase64)) {
            $barcodeSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="22" viewBox="0 0 120 22"><rect x="0" width="2" height="22" fill="#000"/><rect x="4" width="3" height="22" fill="#000"/><rect x="9" width="2" height="22" fill="#000"/><rect x="13" width="3" height="22" fill="#000"/><rect x="18" width="1" height="22" fill="#000"/><rect x="21" width="3" height="22" fill="#000"/><rect x="26" width="2" height="22" fill="#000"/><rect x="30" width="4" height="22" fill="#000"/><rect x="36" width="2" height="22" fill="#000"/><rect x="40" width="3" height="22" fill="#000"/><rect x="45" width="1" height="22" fill="#000"/><rect x="48" width="4" height="22" fill="#000"/><rect x="54" width="2" height="22" fill="#000"/><rect x="58" width="3" height="22" fill="#000"/><rect x="63" width="2" height="22" fill="#000"/><rect x="67" width="4" height="22" fill="#000"/><rect x="73" width="1" height="22" fill="#000"/><rect x="76" width="3" height="22" fill="#000"/><rect x="81" width="2" height="22" fill="#000"/><rect x="85" width="4" height="22" fill="#000"/><rect x="91" width="2" height="22" fill="#000"/><rect x="95" width="3" height="22" fill="#000"/><rect x="100" width="1" height="22" fill="#000"/><rect x="103" width="4" height="22" fill="#000"/><rect x="109" width="2" height="22" fill="#000"/><rect x="113" width="3" height="22" fill="#000"/><rect x="118" width="2" height="22" fill="#000"/></svg>';
            $barcodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($barcodeSvg);
        }
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
                <td style="width: 30%;"></td>
            </tr>
        </table>

        {{-- Form Title --}}
        <div class="form-title">
            FORMULIR PENILAIAN PRE INDUKSI
        </div>

        {{-- Patient Metadata Table --}}
        <table class="meta-table">
            <tr>
                <td class="meta-label">NO. RM</td>
                <td class="meta-val" style="width: 42%;">: <strong>{{ $noRm }}</strong></td>
                <td rowspan="3" class="barcode-cell" style="width: 33%;">
                    <img src="{{ $barcodeBase64 }}" class="barcode-img" alt="Barcode">
                    <div class="barcode-text">*{{ $noRm }}*</div>
                </td>
            </tr>
            <tr>
                <td class="meta-label">NAMA PASIEN</td>
                <td class="meta-val">: <strong>{{ $namaPasien }}</strong></td>
            </tr>
            <tr>
                <td class="meta-label">TGL. LAHIR / UMUR</td>
                <td class="meta-val">: {{ $tglLahirFormatted }} / {{ $umur }}</td>
            </tr>
            <tr>
                <td class="meta-label">JENIS KELAMIN</td>
                <td class="meta-val" colspan="2">: {{ $jkFormatted }}</td>
            </tr>
            <tr>
                <td class="meta-label">NO. RAWAT</td>
                <td class="meta-val" colspan="2">: {{ $penilaian->no_rawat ?? '-' }}</td>
            </tr>
            <tr>
                <td class="meta-label">TANGGAL ASSESSMENT</td>
                <td class="meta-val" colspan="2">: {{ $tanggalAss }}</td>
            </tr>
            <tr>
                <td class="meta-label">DOKTER ANESTESI</td>
                <td class="meta-val" colspan="2">: {{ $namaDokter }}</td>
            </tr>
        </table>

        {{-- Assessment Sections Table (6 Sections, all fields) --}}
        <table class="assessment-table">
            <colgroup>
                <col style="width: 22%;">
                <col style="width: 28%;">
                <col style="width: 22%;">
                <col style="width: 28%;">
            </colgroup>

            {{-- 1. Tanda Vital & Penunjang --}}
            <tr class="section-header">
                <td colspan="4">1. TANDA VITAL & PENUNJANG</td>
            </tr>
            <tr>
                <td class="field-title">Tekanan Darah (TD)</td>
                <td class="field-val">{{ !empty($penilaian->tensi) ? $penilaian->tensi . ' mmHg' : '-' }}</td>
                <td class="field-title">Nadi</td>
                <td class="field-val">{{ !empty($penilaian->nadi) ? $penilaian->nadi . ' x/menit' : '-' }}</td>
            </tr>
            <tr>
                <td class="field-title">Laju Napas (RR)</td>
                <td class="field-val">{{ !empty($penilaian->rr) ? $penilaian->rr . ' x/menit' : '-' }}</td>
                <td class="field-title">Suhu</td>
                <td class="field-val">{{ !empty($penilaian->suhu) ? $penilaian->suhu . ' °C' : '-' }}</td>
            </tr>
            <tr>
                <td class="field-title">EKG</td>
                <td class="field-val">{{ $penilaian->ekg ?? '-' }}</td>
                <td class="field-title">Pemeriksaan Lain</td>
                <td class="field-val">{{ $penilaian->lain_lain ?? '-' }}</td>
            </tr>

            {{-- 2. Asesmen & Perencanaan --}}
            <tr class="section-header">
                <td colspan="4">2. ASESMEN & PERENCANAAN</td>
            </tr>
            <tr>
                <td class="field-title">Asesmen</td>
                <td class="field-val" colspan="3">{{ $penilaian->asesmen ?? '-' }}</td>
            </tr>
            <tr>
                <td class="field-title">Perencanaan</td>
                <td class="field-val" colspan="3">{!! !empty($penilaian->perencanaan) ? nl2br(e($penilaian->perencanaan)) : '-' !!}</td>
            </tr>

            {{-- 3. Akses Vena & Posisi --}}
            <tr class="section-header">
                <td colspan="4">3. AKSES VENA & POSISI</td>
            </tr>
            <tr>
                <td class="field-title">Infus Perifer</td>
                <td class="field-val">{{ $penilaian->infus_perifier ?? '-' }}</td>
                <td class="field-title">CVC</td>
                <td class="field-val">{{ $penilaian->cvc ?? '-' }}</td>
            </tr>
            <tr>
                <td class="field-title">Posisi Pasien</td>
                <td class="field-val" colspan="3">{{ $penilaian->posisi ?? '-' }}</td>
            </tr>

            {{-- 4. Premedikasi & Induksi --}}
            <tr class="section-header">
                <td colspan="4">4. PREMEDIKASI & INDUKSI</td>
            </tr>
            <tr>
                <td class="field-title">Premedikasi</td>
                <td class="field-val">{{ $penilaian->premedikasi ?? '-' }}{{ !empty($penilaian->premedikasi_keterangan) ? ' (' . $penilaian->premedikasi_keterangan . ')' : '' }}</td>
                <td class="field-title">Induksi</td>
                <td class="field-val">{{ $penilaian->induksi ?? '-' }}{{ !empty($penilaian->induksi_keterangan) ? ' (' . $penilaian->induksi_keterangan . ')' : '' }}</td>
            </tr>

            {{-- 5. Tata Laksana Jalan Nafas & Intubasi --}}
            <tr class="section-header">
                <td colspan="4">5. TATA LAKSANA JALAN NAFAS & INTUBASI</td>
            </tr>
            <tr>
                <td class="field-title">Face Mask</td>
                <td class="field-val">No: {{ $penilaian->face_mask_no ?? '-' }}</td>
                <td class="field-title">Nasopharing</td>
                <td class="field-val">No: {{ $penilaian->nasopharing_no ?? '-' }}</td>
            </tr>
            <tr>
                <td class="field-title">ETT</td>
                <td class="field-val" colspan="3">
                    No: {{ $penilaian->ett_no ?? '-' }} &nbsp;|&nbsp;
                    Jenis: {{ $penilaian->ett_jenis ?? '-' }} &nbsp;|&nbsp;
                    Fiksasi: {{ !empty($penilaian->ett_viksasi) ? $penilaian->ett_viksasi . ' cm' : '-' }}
                </td>
            </tr>
            <tr>
                <td class="field-title">LMA</td>
                <td class="field-val" colspan="3">
                    No: {{ $penilaian->lma_no ?? '-' }} &nbsp;|&nbsp;
                    Jenis: {{ $penilaian->lma_jenis ?? '-' }}
                </td>
            </tr>
            <tr>
                <td class="field-title">Tracheostomi</td>
                <td class="field-val">{{ $penilaian->tracheostomi ?? '-' }}</td>
                <td class="field-title">Glidescopi</td>
                <td class="field-val">{{ $penilaian->glidescopi ?? '-' }}</td>
            </tr>
            <tr>
                <td class="field-title">Bronchoscopi Fiberoptik</td>
                <td class="field-val">{{ $penilaian->bronchoscopi_fiberoptik ?? '-' }}</td>
                <td class="field-title">Lain-lain Tatalaksana</td>
                <td class="field-val">{{ $penilaian->lain_lain_tatalaksana ?? '-' }}</td>
            </tr>
            <tr>
                <td class="field-title">Evaluasi Intubasi</td>
                <td class="field-val" colspan="3">
                    Sesudah Tidur: {{ $penilaian->intubasi_sesudah_tidur ?? '-' }} &nbsp;|&nbsp;
                    Oral: {{ $penilaian->intubasi_oral ?? '-' }} &nbsp;|&nbsp;
                    Tracheostomi: {{ $penilaian->intubasi_tracheostomi ?? '-' }}
                </td>
            </tr>
            <tr>
                <td class="field-title">Ventilasi & Kesulitan</td>
                <td class="field-val" colspan="3">
                    Ventilasi: {{ $penilaian->ventilasi ?? '-' }} &nbsp;|&nbsp;
                    Sulit Ventilasi: {{ $penilaian->sulit_ventilasi ?? '-' }} &nbsp;|&nbsp;
                    Sulit Intubasi: {{ $penilaian->sulit_intubasi ?? '-' }}
                </td>
            </tr>
            <tr>
                <td class="field-title">Keterangan Intubasi</td>
                <td class="field-val" colspan="3">{!! !empty($penilaian->intubasi_keterangan) ? nl2br(e($penilaian->intubasi_keterangan)) : '-' !!}</td>
            </tr>

            {{-- 6. Teknik Regional --}}
            <tr class="section-header">
                <td colspan="4">6. TEKNIK REGIONAL</td>
            </tr>
            <tr>
                <td class="field-title">Jenis Teknik Regional</td>
                <td class="field-val">{{ $penilaian->teknik_regional_jenis ?? '-' }}</td>
                <td class="field-title">Lokasi Regional</td>
                <td class="field-val">{{ $penilaian->teknik_regional_lokasi ?? '-' }}</td>
            </tr>
            <tr>
                <td class="field-title">Jenis & Ukuran Jarum</td>
                <td class="field-val">{{ $penilaian->teknik_regional_jenis_jarum ?? '-' }}</td>
                <td class="field-title">Kateter & Fiksasi</td>
                <td class="field-val">Kateter: {{ $penilaian->teknik_regional_kateter ?? '-' }} &nbsp;|&nbsp; Fiksasi: {{ $penilaian->teknik_regional_kateter_viksasi ?? '-' }}</td>
            </tr>
            <tr>
                <td class="field-title">Obat-obatan Regional</td>
                <td class="field-val" colspan="3">{!! !empty($penilaian->teknik_regional_obat_obatan) ? nl2br(e($penilaian->teknik_regional_obat_obatan)) : '-' !!}</td>
            </tr>
            <tr>
                <td class="field-title">Komplikasi Regional</td>
                <td class="field-val" colspan="3">{!! !empty($penilaian->teknik_regional_komplikasi) ? nl2br(e($penilaian->teknik_regional_komplikasi)) : '-' !!}</td>
            </tr>
            <tr>
                <td class="field-title">Hasil Blokade / Regional</td>
                <td class="field-val" colspan="3">{{ $penilaian->teknik_regional_hasil ?? '-' }}</td>
            </tr>
        </table>

        {{-- Doctor TTE Signature Box (Bottom Right) --}}
        <table class="signature-table">
            <tr>
                <td style="width: 48%;"></td>
                <td style="width: 52%; text-align: center;">
                    <div>Nganjuk, {{ $tanggalAss }}</div>
                    <div style="font-weight: bold; margin-bottom: 2px;">Dokter Anestesi</div>
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
