<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Penandaan Area Operasi - {{ $marking->no_rawat }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 6mm 10mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #000;
            line-height: 1.2;
            background: #fff;
        }

        .print-container {
            width: 100%;
            max-width: 190mm;
            margin: 0 auto;
        }

        /* Header / Kop */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .kop-left {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 4px;
        }

        .hospital-logo {
            max-width: 55px;
            max-height: 55px;
            height: auto;
            display: block;
        }

        .kop-text {
            font-size: 10px;
            font-weight: bold;
            line-height: 1.25;
            text-align: center;
        }

        .kop-text .hospital-name {
            font-size: 11px;
            text-decoration: underline;
        }

        .doc-box {
            border: 1.5px solid #000;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            float: right;
            white-space: nowrap;
        }

        /* Form Title */
        .form-title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            text-decoration: underline;
            margin: 6px 0 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Metadata Table */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin-bottom: 8px;
            font-size: 10px;
        }

        .meta-table td {
            border: 1px solid #000;
            padding: 3px 6px;
            vertical-align: middle;
        }

        .meta-label {
            font-weight: bold;
            width: 130px;
            background-color: #f9f9f9;
        }

        .meta-val {
            font-weight: normal;
        }

        .barcode-cell {
            text-align: center;
            vertical-align: middle;
            padding: 4px;
            background-color: #fff;
        }

        .barcode-lines {
            height: 26px;
            width: 130px;
            margin: 0 auto;
            background: repeating-linear-gradient(
                90deg,
                #000 0px, #000 2px,
                #fff 2px, #fff 4px,
                #000 4px, #000 7px,
                #fff 7px, #fff 8px,
                #000 8px, #000 11px,
                #fff 11px, #fff 13px,
                #000 13px, #000 15px,
                #fff 15px, #fff 18px,
                #000 18px, #000 21px,
                #fff 21px, #fff 22px,
                #000 22px, #000 25px
            );
        }

        .barcode-text {
            font-family: 'Courier New', Courier, monospace;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        /* 2x3 Combined Diagram Layout Grid */
        .diagram-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: auto auto;
            gap: 6px;
            margin-bottom: 8px;
        }

        .diagram-box {
            border: 1px solid #000;
            padding: 4px;
            background: #fff;
            display: flex;
            flex-direction: column;
        }

        .box-title {
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            background-color: #eee;
            border-bottom: 1px solid #000;
            padding: 2px 4px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .sub-grid-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px;
            flex: 1;
        }

        .sub-grid-2x2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 4px;
            flex: 1;
        }

        .diagram-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            border: 1px solid #ddd;
            padding: 2px;
            background: #fafafa;
        }

        .diagram-item-header {
            font-size: 9px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 1px;
            color: #111;
        }

        .direction-bar {
            display: flex;
            justify-content: space-between;
            width: 100%;
            padding: 0 4px;
            font-size: 8px;
            font-weight: bold;
            color: #444;
            border-bottom: 1px dashed #ccc;
            margin-bottom: 2px;
        }

        .direction-center {
            text-align: center;
            width: 100%;
            font-size: 8px;
            font-weight: bold;
            color: #444;
            border-bottom: 1px dashed #ccc;
            margin-bottom: 2px;
        }

        .diagram-img {
            max-width: 100%;
            max-height: 155px;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
        }

        .box-fullbody .diagram-img {
            max-height: 310px;
        }

        .box-head .diagram-img {
            max-height: 135px;
        }

        .box-feet .diagram-img,
        .box-hands .diagram-img {
            max-height: 125px;
        }

        .pair-container {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: 2px;
            width: 100%;
            overflow: hidden;
        }

        .pair-container .diagram-img {
            flex: 1 1 0;
            min-width: 0;
            max-width: 48%;
            height: auto;
            object-fit: contain;
        }

        /* Declaration Section */
        .declaration-box {
            border: 1px solid #000;
            padding: 6px 10px;
            text-align: center;
            font-size: 10px;
            margin-bottom: 10px;
            background-color: #fcfcfc;
        }

        /* Signature Footer */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .signature-table td {
            vertical-align: top;
            font-size: 10px;
        }

        .tte-container {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin: 6px 0;
            padding: 4px 8px;
            border: 1px dashed #666;
            border-radius: 4px;
            background-color: #fafafa;
            text-align: left;
        }

        .tte-qr {
            width: 60px;
            height: 60px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .tte-qr svg {
            width: 60px !important;
            height: 60px !important;
            display: block;
        }

        .tte-meta {
            font-size: 8.5px;
            line-height: 1.35;
            color: #222;
        }

        .tte-title {
            font-size: 8px;
            color: #555;
            font-style: italic;
        }

        .tte-doc-name {
            font-weight: bold;
            color: #000;
        }

        .tte-footer-name {
            font-weight: bold;
            margin-top: 4px;
        }

        .doc-code {
            font-size: 8px;
            font-weight: bold;
            color: #555;
            text-align: left;
            margin-top: 4px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    @php
        $getImg = function ($key, $defaultSvg) use ($images, $jenisKelaminTitle) {
            if (!empty($images[$key]) && is_string($images[$key]) && str_starts_with($images[$key], 'data:image/png;base64,')) {
                return $images[$key];
            }
            return asset('img/penandaan-area-operasi/' . $defaultSvg);
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
    @endphp

    <div class="print-container">
        {{-- Header / Kop --}}
        <table class="header-table">
            <tr>
                <td style="width: 70%;">
                    <div class="kop-left">
                        @if(!empty($setting->logo_base64))
                            <img src="{{ $setting->logo_base64 }}" class="hospital-logo" alt="Logo">
                        @elseif(file_exists(public_path('img/logo/logo-kop.png')))
                            <img src="{{ asset('img/logo/logo-kop.png') }}" class="hospital-logo" alt="Logo">
                        @elseif(file_exists(public_path('img/logo/logo.png')))
                            <img src="{{ asset('img/logo/logo.png') }}" class="hospital-logo" alt="Logo">
                        @endif
                        <div class="kop-text">
                            <div>{{ $setting->header_title_1 ?? 'POLRI DAERAH JAWA TIMUR' }}</div>
                            <div>{{ $setting->header_title_2 ?? 'BIDANG KEDOKTERAN DAN KESEHATAN' }}</div>
                            <div class="hospital-name">{{ $setting->hospital_name ?? 'RUMAH SAKIT BHAYANGKARA TK. III NGANJUK' }}</div>
                            <!-- <div>{{ $setting->location ?? 'NGANJUK' }}</div> -->
                        </div>
                    </div>
                </td>
                <td style="width: 30%; vertical-align: top;">
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

        {{-- Patient Info Metadata Grid --}}
        <table class="meta-table">
            <tr>
                <td class="meta-label">NO. RM</td>
                <td class="meta-val" style="width: 35%;">: <strong>{{ $noRm }}</strong></td>
                <td rowspan="3" class="barcode-cell" style="width: 35%;">
                    <div class="barcode-lines"></div>
                    <div class="barcode-text">*{{ $noRm }}*</div>
                </td>
            </tr>
            <tr>
                <td class="meta-label">NAMA PASIEN</td>
                <td class="meta-val">: <strong>{{ $marking->nama_pasien }}</strong> ({{ $umurPasien }})</td>
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
                <td class="meta-val" colspan="2">: <strong>{{ $marking->prosedur_operasi }}</strong></td>
            </tr>
            <tr>
                <td class="meta-label">OPERATOR</td>
                <td class="meta-val" colspan="2">: dr. {{ $marking->nama_operator }}</td>
            </tr>
        </table>

        {{-- 2x3 Combined Diagram Layout Grid --}}
        <div class="diagram-grid">
            {{-- Box 1 (Top Left): Full Body --}}
            <div class="diagram-box box-fullbody">
                <div class="box-title">ANATOMI BADAN</div>
                <div class="sub-grid-2col">
                    <div class="diagram-item">
                        <div class="diagram-item-header">Badan Depan</div>
                        <div class="direction-bar">
                            <span>Kanan</span>
                            <span>Kiri</span>
                        </div>
                        <img src="{{ $imgBodyFront }}" alt="Badan Depan" class="diagram-img">
                    </div>
                    <div class="diagram-item">
                        <div class="diagram-item-header">Badan Belakang</div>
                        <div class="direction-bar">
                            <span>Kiri</span>
                            <span>Kanan</span>
                        </div>
                        <img src="{{ $imgBodyBack }}" alt="Badan Belakang" class="diagram-img">
                    </div>
                </div>
            </div>

            {{-- Box 2 (Top Right): Head Views --}}
            <div class="diagram-box box-head">
                <div class="box-title">ANATOMI KEPALA</div>
                <div class="sub-grid-2x2">
                    <div class="diagram-item">
                        <div class="diagram-item-header">Kepala Profil Kiri</div>
                        <div class="direction-center">Kiri</div>
                        <img src="{{ $imgHeadLeft }}" alt="Kepala Profil Kiri" class="diagram-img">
                    </div>
                    <div class="diagram-item">
                        <div class="diagram-item-header">Kepala Profil Kanan</div>
                        <div class="direction-center">Kanan</div>
                        <img src="{{ $imgHeadRight }}" alt="Kepala Profil Kanan" class="diagram-img">
                    </div>
                    <div class="diagram-item">
                        <div class="diagram-item-header">Kepala Depan</div>
                        <div class="direction-bar">
                            <span>Kanan</span>
                            <span>Kiri</span>
                        </div>
                        <img src="{{ $imgHeadFront }}" alt="Kepala Depan" class="diagram-img">
                    </div>
                    <div class="diagram-item">
                        <div class="diagram-item-header">Kepala Belakang</div>
                        <div class="direction-bar">
                            <span>Kiri</span>
                            <span>Kanan</span>
                        </div>
                        <img src="{{ $imgHeadBack }}" alt="Kepala Belakang" class="diagram-img">
                    </div>
                </div>
            </div>

            {{-- Box 3 (Bottom Left): Foot Views --}}
            <div class="diagram-box box-feet">
                <div class="box-title">ANATOMI KAKI</div>
                <div class="sub-grid-2col">
                    <div class="diagram-item">
                        <div class="diagram-item-header">Plantar (posterior)</div>
                        <div class="direction-bar">
                            <span>Kiri</span>
                            <span>Kanan</span>
                        </div>
                        <div class="pair-container">
                            <img src="{{ $imgFootLeftPlantar }}" alt="Telapak Kaki Kiri" class="diagram-img">
                            <img src="{{ $imgFootRightPlantar }}" alt="Telapak Kaki Kanan" class="diagram-img">
                        </div>
                    </div>
                    <div class="diagram-item">
                        <div class="diagram-item-header">Dorsal (anterior)</div>
                        <div class="direction-bar">
                            <span>Kiri</span>
                            <span>Kanan</span>
                        </div>
                        <div class="pair-container">
                            <img src="{{ $imgFootLeftDorsal }}" alt="Punggung Kaki Kiri" class="diagram-img">
                            <img src="{{ $imgFootRightDorsal }}" alt="Punggung Kaki Kanan" class="diagram-img">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Box 4 (Bottom Right): Hand Views --}}
            <div class="diagram-box box-hands">
                <div class="box-title">ANATOMI TANGAN</div>
                <div class="sub-grid-2col">
                    <div class="diagram-item">
                        <div class="diagram-item-header">Palmar (anterior)</div>
                        <div class="direction-bar">
                            <span>Kiri</span>
                            <span>Kanan</span>
                        </div>
                        <div class="pair-container">
                            <img src="{{ $imgHandLeftPalm }}" alt="Telapak Tangan Kiri" class="diagram-img">
                            <img src="{{ $imgHandRightPalm }}" alt="Telapak Tangan Kanan" class="diagram-img">
                        </div>
                    </div>
                    <div class="diagram-item">
                        <div class="diagram-item-header">Dorsal (posterior)</div>
                        <div class="direction-bar">
                            <span>Kiri</span>
                            <span>Kanan</span>
                        </div>
                        <div class="pair-container">
                            <img src="{{ $imgHandLeftDorsal }}" alt="Punggung Tangan Kiri" class="diagram-img">
                            <img src="{{ $imgHandRightDorsal }}" alt="Punggung Tangan Kanan" class="diagram-img">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Declaration Statement --}}
        <div class="declaration-box">
            Saya menyatakan bahwa lokasi operasi yang telah ditetapkan pada diagram adalah <strong>benar</strong>.
        </div>

        {{-- Signature Footer --}}
        <table class="signature-table">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%; text-align: center;">
                    <div>Nganjuk, {{ $tanggalPenandaan }}</div>
                    <div>Dokter Operator</div>
                    <div class="tte-container">
                        <div id="qrcode" class="tte-qr"></div>
                        <div class="tte-meta">
                            <div class="tte-title">Ditandatangani secara elektronik:</div>
                            <div class="tte-doc-name">dr. {{ $marking->nama_operator }}</div>
                            <div>Kode Dokter: {{ $marking->operator_id }}</div>
                            <div>Waktu TTD: {{ $tanggalPenandaan }}</div>
                        </div>
                    </div>
                    <div class="tte-footer-name">( dr. {{ $marking->nama_operator }} )</div>
                </td>
            </tr>
        </table>

        {{-- Document Code --}}
        <div class="doc-code">
            VI/2018/Komite RM
        </div>
    </div>

    {{-- QRCode Generator & Auto Print Script --}}
    <script src="{{ asset('js/qrcode.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var qrUrl = "{{ route('penandaan-area-operasi.verifikasi', ['no_rawat' => str_replace('/', '-', $marking->no_rawat)]) }}";
            var qrElement = document.getElementById('qrcode');
            if (qrElement && typeof QRCode !== 'undefined') {
                new QRCode(qrElement, {
                    text: qrUrl,
                    width: 60,
                    height: 60,
                    correctLevel: QRCode.CorrectLevel.M
                });
            }
        });

        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>
</html>
