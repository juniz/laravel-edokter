<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pemeriksaan Odontogram - {{ $regData->no_rawat ?? '-' }}</title>
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


        /* Odontogram Chart Section */
        .odontogram-wrapper {
            border: 1px solid #000;
            padding: 6px;
            margin-bottom: 8px;
            background: #fff;
        }

        .quadrant-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
            padding: 0 4px;
        }

        .quadrant-badge {
            background-color: #007bff;
            color: #fff;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8.5px;
            font-weight: bold;
        }

        .chart-title-text {
            font-size: 10px;
            font-weight: bold;
            color: #333;
        }

        .section-label-container {
            text-align: center;
            margin: 2px 0;
        }

        .section-badge {
            display: inline-block;
            padding: 1px 8px;
            font-size: 8.5px;
            border-radius: 4px;
            font-weight: bold;
        }

        .badge-info-custom {
            background-color: #17a2b8;
            color: #fff;
        }

        .badge-secondary-custom {
            background-color: #6c757d;
            color: #fff;
        }

        .table-odontogram-container {
            width: 100%;
            overflow-x: auto;
            margin: 2px 0;
        }

        .table-odontogram-grid {
            margin: 0 auto;
            border-collapse: separate;
            border-spacing: 2px;
            text-align: center;
        }

        .tooth-number-row td {
            font-size: 9px;
            font-weight: bold;
            color: #555;
            padding: 1px 0;
        }

        .tooth-cell {
            width: 26px;
            height: 26px;
            min-width: 26px;
            min-height: 26px;
            position: relative;
            border-radius: 3px;
            vertical-align: middle;
            background-color: #fff;
        }

        .gigi_posterior {
            background-image: url("{{ asset('images/posterior.png') }}");
            background-repeat: no-repeat;
            background-size: 100% 100%;
        }

        .gigi_anterior {
            background-image: url("{{ asset('images/anterior.png') }}");
            background-repeat: no-repeat;
            background-size: 100% 100%;
        }

        .tooth-img {
            max-width: 100%;
            max-height: 100%;
            display: block;
            margin: 0 auto;
            pointer-events: none;
        }

        .spacer-row {
            height: 6px;
        }

        /* Legend Bar */
        .legend-bar {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-top: 6px;
            padding-top: 4px;
            border-top: 1px solid #ddd;
            font-size: 8.5px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .legend-icon {
            height: 14px;
            width: auto;
        }


        /* Declaration Box */
        .declaration-box {
            border: 1px solid #000;
            padding: 5px 8px;
            text-align: center;
            font-size: 9.5px;
            margin-bottom: 8px;
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
            margin: 4px 0;
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

        .tte-qr svg, .tte-qr img {
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
        $listOdonto = collect($listPemeriksaan ?? []);

        $getTooth = function($key) use ($listOdonto) {
            return $listOdonto->where('gg_xx', $key)->first();
        };

        $tglLahirRaw = $regData->pasien_tgl_lahir ?? null;
        $tglLahirFormatted = $tglLahirRaw ? \Carbon\Carbon::parse($tglLahirRaw)->format('d-m-Y') : '-';
        $noRm = $regData->no_rkm_medis ?? '-';
        $noRawat = $regData->no_rawat ?? '-';
    @endphp

    <div class="print-container">
        {{-- Header / Kop --}}
        <table class="header-table">
            <tr>
                <td style="width: 100%;">
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
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Form Title --}}
        <div class="form-title">
            FORMULIR PEMERIKSAAN ODONTOGRAM ({{ $jenisKelaminTitle }})
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
                <td class="meta-val">: <strong>{{ $regData->nm_pasien ?? '-' }}</strong> ({{ $umurPasien }})</td>
            </tr>
            <tr>
                <td class="meta-label">TGL. LAHIR</td>
                <td class="meta-val">: {{ $tglLahirFormatted }}</td>
            </tr>
            <tr>
                <td class="meta-label">NO. RAWAT</td>
                <td class="meta-val" colspan="2">: {{ $noRawat }}</td>
            </tr>
            <tr>
                <td class="meta-label">DOKTER / PETUGAS</td>
                <td class="meta-val" colspan="2">: {{ $namaDokterPetugas }}</td>
            </tr>
        </table>


        {{-- Odontogram Chart Section --}}
        <div class="odontogram-wrapper">


            <div class="table-odontogram-container">
                <table class="table-odontogram-grid">
                    {{-- Numbers row Gigi Permanen Atas --}}
                    <tr class="tooth-number-row">
                        @foreach([18,17,16,15,14,13,12,11, 21,22,23,24,25,26,27,28] as $num)
                            <td>{{ $num % 10 }}</td>
                        @endforeach
                    </tr>
                    {{-- Teeth row Gigi Permanen Atas --}}
                    <tr>
                        @foreach(['gg_18','gg_17','gg_16','gg_15','gg_14'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_posterior" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ asset('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        @foreach(['gg_13','gg_12','gg_11','gg_21','gg_22','gg_23'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_anterior" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ asset('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        @foreach(['gg_24','gg_25','gg_26','gg_27','gg_28'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_posterior" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ asset('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                    </tr>

                    {{-- Spacer --}}
                    <tr class="spacer-row"></tr>



                    {{-- Teeth row Gigi Desidui Atas --}}
                    <tr>
                        <td colspan="3" style="border: 0;"></td>
                        @foreach(['gg_55','gg_54'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_posterior" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ asset('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        @foreach(['gg_53','gg_52','gg_51','gg_61','gg_62','gg_63'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_anterior" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ asset('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        @foreach(['gg_64','gg_65'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_posterior" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ asset('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        <td colspan="3" style="border: 0;"></td>
                    </tr>

                    {{-- Numbers row Gigi Desidui Atas & Bawah --}}
                    <tr class="tooth-number-row">
                        <td colspan="3" style="border: 0;"></td>
                        @foreach([55,54,53,52,51, 61,62,63,64,65] as $num)
                            <td>{{ $num % 10 }}</td>
                        @endforeach
                        <td colspan="3" style="border: 0;"></td>
                    </tr>

                    {{-- Teeth row Gigi Desidui Bawah --}}
                    <tr>
                        <td colspan="3" style="border: 0;"></td>
                        @foreach(['gg_85','gg_84'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_posterior" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ asset('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        @foreach(['gg_83','gg_82','gg_81','gg_71','gg_72','gg_73'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_anterior" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ asset('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        @foreach(['gg_74','gg_75'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_posterior" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ asset('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        <td colspan="3" style="border: 0;"></td>
                    </tr>



                    {{-- Spacer --}}
                    <tr class="spacer-row"></tr>

                    {{-- Teeth row Gigi Permanen Bawah --}}
                    <tr>
                        @foreach(['gg_48','gg_47','gg_46','gg_45','gg_44'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_posterior" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ asset('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        @foreach(['gg_43','gg_42','gg_41','gg_31','gg_32','gg_33'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_anterior" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ asset('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                        @foreach(['gg_34','gg_35','gg_36','gg_37','gg_38'] as $k)
                            @php $t = $getTooth($k); $num = str_replace('gg_', '', $k); @endphp
                            <td class="tooth-cell gigi_posterior" title="Gigi {{ $num }} {{ $t ? '- '.$t->value : '' }}">
                                @if($t)<img src="{{ asset('images/'.$t->value.'.png') }}" alt="{{ $t->value }}" class="tooth-img">@endif
                            </td>
                        @endforeach
                    </tr>
                    {{-- Numbers row Gigi Permanen Bawah --}}
                    <tr class="tooth-number-row">
                        @foreach([48,47,46,45,44,43,42,41, 31,32,33,34,35,36,37,38] as $num)
                            <td>{{ $num % 10 }}</td>
                        @endforeach
                    </tr>
                </table>
            </div>



            {{-- Legend Bar --}}
            <div class="legend-bar">
                <div class="legend-item">
                    <img src="{{ asset('images/Tanggal.png') }}" class="legend-icon" alt="Tanggal">
                    <span>Tanggal (Hilang)</span>
                </div>
                <div class="legend-item">
                    <img src="{{ asset('images/Karies.png') }}" class="legend-icon" alt="Karies">
                    <span>Karies</span>
                </div>
                <div class="legend-item">
                    <img src="{{ asset('images/Akar.png') }}" class="legend-icon" alt="Sisa Akar">
                    <span>Sisa Akar</span>
                </div>
                <div class="legend-item">
                    <img src="{{ asset('images/Tumpat.png') }}" class="legend-icon" alt="Tumpatan">
                    <span>Tumpatan</span>
                </div>
                <div class="legend-item">
                    <img src="{{ asset('images/Fraktur Mahkota.png') }}" class="legend-icon" alt="Fraktur Mahkota">
                    <span>Fraktur Mahkota</span>
                </div>
            </div>
        </div>


        {{-- Declaration Box --}}
        <div class="declaration-box">
            Saya menyatakan bahwa pemeriksaan odontogram ini telah dilakukan dan dicatat dengan benar.
        </div>

        {{-- Signature Footer --}}
        <table class="signature-table">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%; text-align: center;">
                    <div>Nganjuk, {{ $tanggalPemeriksaan }}</div>
                    <div>Dokter / Petugas</div>
                    <div class="tte-container">
                        <div id="qrcode" class="tte-qr"></div>
                        <div class="tte-meta">
                            <div class="tte-title">Ditandatangani secara elektronik:</div>
                            <div class="tte-doc-name">{{ $namaDokterPetugas }}</div>
                            <div>Kode / NIP: {{ $kodeDokterPetugas }}</div>
                            <div>Waktu TTD: {{ $tanggalPemeriksaan }}</div>
                        </div>
                    </div>
                    <div class="tte-footer-name">( {{ $namaDokterPetugas }} )</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- QRCode Generator & Auto Print Script --}}
    <script src="{{ asset('js/qrcode.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var qrUrl = "{{ route('odontogram.cetak', ['no_rawat' => $noRawat, 'token' => $verificationToken ?? '']) }}";
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
