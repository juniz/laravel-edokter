<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Penandaan Area Operasi - {{ $marking->no_rawat ?? '' }}</title>
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
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5px;
            color: #000;
            line-height: 1.2;
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
            max-width: 50px;
            max-height: 50px;
            height: auto;
            display: block;
            margin: 0 auto 2px;
        }

        .kop-text {
            font-size: 9.5px;
            font-weight: bold;
            line-height: 1.25;
            text-align: center;
        }

        .kop-text .hospital-name {
            font-size: 10.5px;
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
            padding: 2px 5px;
            vertical-align: middle;
        }

        .meta-label {
            font-weight: bold;
            width: 125px;
            background-color: #f9f9f9;
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
            width: 130px;
            height: 24px;
            display: block;
            margin: 0 auto 2px;
        }

        .barcode-text {
            font-family: 'Courier New', Courier, monospace;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        /* Diagram Grid Table (2x2) */
        .diagram-main-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4px;
            margin-bottom: 5px;
            table-layout: fixed;
        }

        .diagram-box {
            border: 1px solid #000;
            padding: 3px;
            background: #fff;
            vertical-align: top;
        }

        .box-title {
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            background-color: #eee;
            border-bottom: 1px solid #000;
            padding: 2px 4px;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        .sub-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 3px;
            table-layout: fixed;
        }

        .diagram-item {
            border: 1px solid #ddd;
            padding: 2px;
            background: #fafafa;
            vertical-align: top;
            text-align: center;
        }

        .diagram-item-header {
            font-size: 8.5px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 1px;
            color: #111;
        }

        .direction-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
            font-weight: bold;
            color: #444;
            border-bottom: 1px dashed #ccc;
            margin-bottom: 2px;
        }

        .direction-table td {
            padding: 0 2px;
        }

        .direction-center {
            text-align: center;
            width: 100%;
            font-size: 7.5px;
            font-weight: bold;
            color: #444;
            border-bottom: 1px dashed #ccc;
            margin-bottom: 2px;
            padding: 0 2px;
        }

        .diagram-img {
            display: block;
            margin: 0 auto;
            object-fit: contain;
        }

        .img-badan {
            height: 240px;
            max-width: 95%;
        }

        .img-kepala {
            height: 100px;
            max-width: 95%;
        }

        .img-pair {
            height: 105px;
            max-width: 95%;
        }

        /* Declaration Section */
        .declaration-box {
            border: 1px solid #000;
            padding: 4px 8px;
            text-align: center;
            font-size: 9px;
            margin-bottom: 5px;
            background-color: #fcfcfc;
        }

        /* Signature Footer */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-table td {
            vertical-align: top;
            font-size: 9px;
        }

        .tte-box-table {
            margin: 3px auto;
            border: 1px dashed #666;
            border-radius: 4px;
            background-color: #fafafa;
            border-collapse: collapse;
        }

        .tte-box-table td {
            padding: 3px 6px;
            vertical-align: middle;
        }

        .tte-qr-img {
            width: 50px;
            height: 50px;
            display: block;
        }

        .tte-meta {
            font-size: 8px;
            line-height: 1.3;
            color: #222;
            text-align: left;
        }

        .tte-title {
            font-size: 7.5px;
            color: #555;
            font-style: italic;
        }

        .tte-doc-name {
            font-weight: bold;
            color: #000;
            font-size: 8.5px;
        }

        .tte-footer-name {
            font-weight: bold;
            margin-top: 3px;
            font-size: 9px;
        }
    </style>
</head>
<body>
    @php
        $imgs = $diagrams ?? $images ?? [];
        $getImg = function ($key, $defaultSvg) use ($imgs, $jenisKelaminTitle) {
            if (!empty($imgs[$key]) && is_string($imgs[$key])) {
                return $imgs[$key];
            }
            $localPath = public_path('img/penandaan-area-operasi/' . $defaultSvg);
            if (file_exists($localPath)) {
                $mime = 'image/svg+xml';
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($localPath));
            }
            return '';
        };

        $bodyFrontSvg = ($jenisKelaminTitle === 'LAKI-LAKI') ? 'body-front-male.svg' : 'body-front.svg';
        $bodyBackSvg = ($jenisKelaminTitle === 'LAKI-LAKI') ? 'body-back-male.svg' : 'body-back.svg';

        $imgBodyFront = $getImg('body_front', $bodyFrontSvg);
        $imgBodyBack = $getImg('body_back', $bodyBackSvg);

        $imgHeadLeft = $getImg('head_left_profile', 'head-left-profile.svg');
        $imgHeadRight = $getImg('head_right_profile', 'head-right-profile.svg');
        $imgHeadFront = $getImg('head_front', 'head-front.svg');
        $imgHeadBack = $getImg('head_back', 'head-back.svg');

        $imgFootLeftPlantar = $getImg('foot_left_plantar', 'foot-left-plantar.svg');
        $imgFootRightPlantar = $getImg('foot_right_plantar', 'foot-right-plantar.svg');
        $imgFootLeftDorsal = $getImg('foot_left_dorsal', 'foot-left-dorsal.svg');
        $imgFootRightDorsal = $getImg('foot_right_dorsal', 'foot-right-dorsal.svg');

        $imgHandLeftPalm = $getImg('hand_left_palm', 'hand-left-palm.svg');
        $imgHandRightPalm = $getImg('hand_right_palm', 'hand-right-palm.svg');
        $imgHandLeftDorsal = $getImg('hand_left_dorsal', 'hand-left-dorsal.svg');
        $imgHandRightDorsal = $getImg('hand_right_dorsal', 'hand-right-dorsal.svg');

        $tglLahirRaw = $marking->pasien_tgl_lahir ?? $marking->tgl_lahir ?? null;
        $tglLahirFormatted = $tglLahirRaw ? \Carbon\Carbon::parse($tglLahirRaw)->format('d-m-Y') : '-';
        $noRm = $marking->no_rkm_medis ?? $marking->no_rm ?? '-';
        
        $barcodeSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="130" height="24" viewBox="0 0 130 24"><rect x="0" width="2" height="24" fill="#000"/><rect x="4" width="3" height="24" fill="#000"/><rect x="9" width="2" height="24" fill="#000"/><rect x="13" width="3" height="24" fill="#000"/><rect x="18" width="1" height="24" fill="#000"/><rect x="21" width="3" height="24" fill="#000"/><rect x="26" width="2" height="24" fill="#000"/><rect x="30" width="4" height="24" fill="#000"/><rect x="36" width="2" height="24" fill="#000"/><rect x="40" width="3" height="24" fill="#000"/><rect x="45" width="1" height="24" fill="#000"/><rect x="48" width="4" height="24" fill="#000"/><rect x="54" width="2" height="24" fill="#000"/><rect x="58" width="3" height="24" fill="#000"/><rect x="63" width="2" height="24" fill="#000"/><rect x="67" width="4" height="24" fill="#000"/><rect x="73" width="1" height="24" fill="#000"/><rect x="76" width="3" height="24" fill="#000"/><rect x="81" width="2" height="24" fill="#000"/><rect x="85" width="4" height="24" fill="#000"/><rect x="91" width="2" height="24" fill="#000"/><rect x="95" width="3" height="24" fill="#000"/><rect x="100" width="1" height="24" fill="#000"/><rect x="103" width="4" height="24" fill="#000"/><rect x="109" width="2" height="24" fill="#000"/><rect x="113" width="3" height="24" fill="#000"/><rect x="118" width="2" height="24" fill="#000"/><rect x="122" width="3" height="24" fill="#000"/><rect x="127" width="2" height="24" fill="#000"/></svg>';
        $barcodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($barcodeSvg);
    @endphp

    <div class="print-container">
        {{-- Header / Kop --}}
        <table class="header-table">
            <tr>
                <td style="width: 70%; text-align: center;">
                    <div style="text-align: center;">
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
                    </div>
                </td>
                <td style="width: 30%; vertical-align: top; text-align: right;">
                    <div class="doc-box">
                        RM. 41 / Halaman 2
                    </div>
                </td>
            </tr>
        </table>

        {{-- Form Title --}}
        <div class="form-title">
            FORMULIR PENANDAAN AREA OPERASI ({{ $jenisKelaminTitle }})
        </div>

        {{-- Patient Info Metadata Table --}}
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
                <td class="meta-val">: <strong>{{ $marking->nama_pasien ?? $marking->nm_pasien ?? '-' }}</strong> ({{ $umurPasien }})</td>
            </tr>
            <tr>
                <td class="meta-label">TGL. LAHIR</td>
                <td class="meta-val">: {{ $tglLahirFormatted }}</td>
            </tr>
            <tr>
                <td class="meta-label">NO. RAWAT</td>
                <td class="meta-val" colspan="2">: {{ $marking->no_rawat }}</td>
            </tr>
            <tr>
                <td class="meta-label">PROSEDUR OPERASI</td>
                <td class="meta-val" colspan="2">: <strong>{{ $marking->prosedur_operasi ?? '-' }}</strong></td>
            </tr>
            <tr>
                <td class="meta-label">OPERATOR</td>
                <td class="meta-val" colspan="2">: dr. {{ $marking->nama_operator ?? $marking->nm_dokter ?? '-' }}</td>
            </tr>
        </table>

        {{-- 2x2 Anatomical Diagram Layout Table --}}
        <table class="diagram-main-table">
            <tr>
                {{-- Box 1 (Top Left): Full Body --}}
                <td class="diagram-box" style="width: 50%;">
                    <div class="box-title">ANATOMI BADAN</div>
                    <table class="sub-table">
                        <tr>
                            <td class="diagram-item" style="width: 50%;">
                                <div class="diagram-item-header">Badan Depan</div>
                                <table class="direction-table">
                                    <tr>
                                        <td style="text-align: left;">Kanan</td>
                                        <td style="text-align: right;">Kiri</td>
                                    </tr>
                                </table>
                                <img src="{{ $imgBodyFront }}" alt="Badan Depan" class="diagram-img img-badan">
                            </td>
                            <td class="diagram-item" style="width: 50%;">
                                <div class="diagram-item-header">Badan Belakang</div>
                                <table class="direction-table">
                                    <tr>
                                        <td style="text-align: left;">Kiri</td>
                                        <td style="text-align: right;">Kanan</td>
                                    </tr>
                                </table>
                                <img src="{{ $imgBodyBack }}" alt="Badan Belakang" class="diagram-img img-badan">
                            </td>
                        </tr>
                    </table>
                </td>

                {{-- Box 2 (Top Right): Head Views (2x2) --}}
                <td class="diagram-box" style="width: 50%;">
                    <div class="box-title">ANATOMI KEPALA</div>
                    <table class="sub-table">
                        <tr>
                            <td class="diagram-item" style="width: 50%;">
                                <div class="diagram-item-header">Kepala Profil Kiri</div>
                                <div class="direction-center">Kiri</div>
                                <img src="{{ $imgHeadLeft }}" alt="Kepala Profil Kiri" class="diagram-img img-kepala">
                            </td>
                            <td class="diagram-item" style="width: 50%;">
                                <div class="diagram-item-header">Kepala Profil Kanan</div>
                                <div class="direction-center">Kanan</div>
                                <img src="{{ $imgHeadRight }}" alt="Kepala Profil Kanan" class="diagram-img img-kepala">
                            </td>
                        </tr>
                        <tr>
                            <td class="diagram-item" style="width: 50%;">
                                <div class="diagram-item-header">Kepala Depan</div>
                                <table class="direction-table">
                                    <tr>
                                        <td style="text-align: left;">Kanan</td>
                                        <td style="text-align: right;">Kiri</td>
                                    </tr>
                                </table>
                                <img src="{{ $imgHeadFront }}" alt="Kepala Depan" class="diagram-img img-kepala">
                            </td>
                            <td class="diagram-item" style="width: 50%;">
                                <div class="diagram-item-header">Kepala Belakang</div>
                                <table class="direction-table">
                                    <tr>
                                        <td style="text-align: left;">Kiri</td>
                                        <td style="text-align: right;">Kanan</td>
                                    </tr>
                                </table>
                                <img src="{{ $imgHeadBack }}" alt="Kepala Belakang" class="diagram-img img-kepala">
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr>
                {{-- Box 3 (Bottom Left): Foot Views --}}
                <td class="diagram-box" style="width: 50%;">
                    <div class="box-title">ANATOMI KAKI</div>
                    <table class="sub-table">
                        <tr>
                            <td class="diagram-item" style="width: 50%;">
                                <div class="diagram-item-header">Plantar (posterior)</div>
                                <table class="direction-table">
                                    <tr>
                                        <td style="text-align: left;">Kiri</td>
                                        <td style="text-align: right;">Kanan</td>
                                    </tr>
                                </table>
                                <table style="width: 100%; border: none; border-collapse: collapse;">
                                    <tr>
                                        <td style="width: 50%; text-align: center; border: none; padding: 0;">
                                            <img src="{{ $imgFootLeftPlantar }}" alt="Telapak Kaki Kiri" class="diagram-img img-pair">
                                        </td>
                                        <td style="width: 50%; text-align: center; border: none; padding: 0;">
                                            <img src="{{ $imgFootRightPlantar }}" alt="Telapak Kaki Kanan" class="diagram-img img-pair">
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td class="diagram-item" style="width: 50%;">
                                <div class="diagram-item-header">Dorsal (anterior)</div>
                                <table class="direction-table">
                                    <tr>
                                        <td style="text-align: left;">Kiri</td>
                                        <td style="text-align: right;">Kanan</td>
                                    </tr>
                                </table>
                                <table style="width: 100%; border: none; border-collapse: collapse;">
                                    <tr>
                                        <td style="width: 50%; text-align: center; border: none; padding: 0;">
                                            <img src="{{ $imgFootLeftDorsal }}" alt="Punggung Kaki Kiri" class="diagram-img img-pair">
                                        </td>
                                        <td style="width: 50%; text-align: center; border: none; padding: 0;">
                                            <img src="{{ $imgFootRightDorsal }}" alt="Punggung Kaki Kanan" class="diagram-img img-pair">
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>

                {{-- Box 4 (Bottom Right): Hand Views --}}
                <td class="diagram-box" style="width: 50%;">
                    <div class="box-title">ANATOMI TANGAN</div>
                    <table class="sub-table">
                        <tr>
                            <td class="diagram-item" style="width: 50%;">
                                <div class="diagram-item-header">Palmar (anterior)</div>
                                <table class="direction-table">
                                    <tr>
                                        <td style="text-align: left;">Kiri</td>
                                        <td style="text-align: right;">Kanan</td>
                                    </tr>
                                </table>
                                <table style="width: 100%; border: none; border-collapse: collapse;">
                                    <tr>
                                        <td style="width: 50%; text-align: center; border: none; padding: 0;">
                                            <img src="{{ $imgHandLeftPalm }}" alt="Telapak Tangan Kiri" class="diagram-img img-pair">
                                        </td>
                                        <td style="width: 50%; text-align: center; border: none; padding: 0;">
                                            <img src="{{ $imgHandRightPalm }}" alt="Telapak Tangan Kanan" class="diagram-img img-pair">
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td class="diagram-item" style="width: 50%;">
                                <div class="diagram-item-header">Dorsal (posterior)</div>
                                <table class="direction-table">
                                    <tr>
                                        <td style="text-align: left;">Kiri</td>
                                        <td style="text-align: right;">Kanan</td>
                                    </tr>
                                </table>
                                <table style="width: 100%; border: none; border-collapse: collapse;">
                                    <tr>
                                        <td style="width: 50%; text-align: center; border: none; padding: 0;">
                                            <img src="{{ $imgHandLeftDorsal }}" alt="Punggung Tangan Kiri" class="diagram-img img-pair">
                                        </td>
                                        <td style="width: 50%; text-align: center; border: none; padding: 0;">
                                            <img src="{{ $imgHandRightDorsal }}" alt="Punggung Tangan Kanan" class="diagram-img img-pair">
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Declaration Statement --}}
        <div class="declaration-box">
            Saya menyatakan bahwa lokasi operasi yang telah ditetapkan pada diagram adalah <strong>benar</strong>.
        </div>

        {{-- Signature Footer --}}
        <table class="signature-table">
            <tr>
                <td style="width: 48%;"></td>
                <td style="width: 52%; text-align: center;">
                    <div>Nganjuk, {{ $tanggalPenandaan }}</div>
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
                                    <div class="tte-doc-name">{{ $marking->nama_operator ?? $marking->nm_dokter ?? '-' }}</div>
                                    <div>Kode Dokter: {{ $marking->operator_id ?? '-' }}</div>
                                    <div>Waktu TTD: {{ $tanggalPenandaan }}</div>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <div class="tte-footer-name">( {{ $marking->nama_operator ?? $marking->nm_dokter ?? '-' }} )</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
