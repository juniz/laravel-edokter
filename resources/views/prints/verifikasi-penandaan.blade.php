<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Digital Penandaan Area Operasi - {{ $marking->no_rawat }}</title>
    <style>
        :root {
            --primary: #1b365d;
            --success: #10b981;
            --success-bg: #ecfdf5;
            --success-text: #065f46;
            --dark: #1e293b;
            --light: #f8fafc;
            --border: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f1f5f9;
            color: #334155;
            line-height: 1.6;
            padding: 24px 16px;
        }

        .container {
            max-width: 680px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .header {
            background: linear-gradient(135deg, #1b365d 0%, #0f172a 100%);
            color: #ffffff;
            padding: 28px 24px;
            text-align: center;
        }

        .hospital-logo {
            max-width: 65px;
            max-height: 65px;
            margin-bottom: 12px;
            height: auto;
        }

        .header-sub {
            font-size: 11px;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            opacity: 0.85;
            font-weight: 600;
        }

        .hospital-name {
            font-size: 18px;
            font-weight: 700;
            margin: 4px 0 2px;
            color: #ffffff;
            letter-spacing: 0.5px;
        }

        .header-title {
            font-size: 13px;
            opacity: 0.9;
        }

        .status-card {
            background-color: var(--success-bg);
            border-left: 5px solid var(--success);
            padding: 16px 20px;
            margin: 24px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .status-icon {
            width: 44px;
            height: 44px;
            background-color: var(--success);
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: bold;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
        }

        .status-text h3 {
            color: var(--success-text);
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .status-text p {
            color: #047857;
            font-size: 12px;
        }

        .content {
            padding: 0 24px 28px;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--primary);
            border-bottom: 2px solid var(--border);
            padding-bottom: 8px;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .info-table tr {
            border-bottom: 1px solid #f1f5f9;
        }

        .info-table tr:last-child {
            border-bottom: none;
        }

        .info-table td {
            padding: 12px 8px;
            font-size: 13px;
            vertical-align: top;
        }

        .info-label {
            width: 38%;
            color: #64748b;
            font-weight: 600;
        }

        .info-val {
            width: 62%;
            color: #1e293b;
            font-weight: 600;
        }

        .badge-tag {
            display: inline-block;
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 4px;
            background-color: #e0f2fe;
            color: #0369a1;
        }

        .footer-note {
            background-color: #f8fafc;
            border-radius: 10px;
            padding: 16px;
            border: 1px dashed #cbd5e1;
            font-size: 12px;
            color: #64748b;
            text-align: center;
            line-height: 1.5;
        }

        .footer-note strong {
            color: #334155;
        }

        @media (max-width: 576px) {
            body {
                padding: 12px 8px;
            }
            .container {
                border-radius: 12px;
            }
            .header {
                padding: 20px 16px;
            }
            .hospital-name {
                font-size: 16px;
            }
            .status-card {
                margin: 16px;
                padding: 12px 14px;
                gap: 12px;
            }
            .content {
                padding: 0 16px 20px;
            }
            .info-table td {
                padding: 10px 4px;
                font-size: 12px;
            }
            .info-label {
                width: 42%;
            }
        }
    </style>
</head>
<body>

    @php
        $tglLahirRaw = $marking->pasien_tgl_lahir ?? $marking->tgl_lahir ?? null;
        $tglLahirFormatted = $tglLahirRaw ? \Carbon\Carbon::parse($tglLahirRaw)->format('d-m-Y') : '-';
        $noRm = $marking->no_rkm_medis ?? $marking->no_rm ?? '-';
    @endphp

    <div class="container">
        {{-- Header --}}
        <div class="header">
            @if(!empty($setting->logo_base64))
                <img src="{{ $setting->logo_base64 }}" class="hospital-logo" alt="Logo Instansi">
            @elseif(file_exists(public_path('img/logo/logo-kop.png')))
                <img src="{{ asset('img/logo/logo-kop.png') }}" class="hospital-logo" alt="Logo Instansi">
            @elseif(file_exists(public_path('img/logo/logo.png')))
                <img src="{{ asset('img/logo/logo.png') }}" class="hospital-logo" alt="Logo Instansi">
            @endif
            <div class="header-sub">{{ $setting->header_title_1 ?? 'POLRI DAERAH JAWA TIMUR' }}</div>
            <div class="header-sub">{{ $setting->header_title_2 ?? 'BIDANG KEDOKTERAN DAN KESEHATAN' }}</div>
            <div class="hospital-name">{{ $setting->hospital_name ?? 'RUMAH SAKIT BHAYANGKARA TK. III NGANJUK' }}</div>
            <div class="header-title">Verifikasi Keabsahan Dokumen Medis Elektronik (TTE)</div>
        </div>

        {{-- Verification Status Card --}}
        <div class="status-card">
            <div class="status-icon">✓</div>
            <div class="status-text">
                <h3>Dokumen Terverifikasi Sah secara Digital (TTE)</h3>
                <p>Formulir Penandaan Area Operasi ini tercatat resmi di SIMRS dan ditandatangani secara elektronik oleh Dokter Operator.</p>
            </div>
        </div>

        {{-- Main Details --}}
        <div class="content">
            <div class="section-title">Detail Rekam Medis & Penandaan</div>
            
            <table class="info-table">
                <tr>
                    <td class="info-label">Jenis Dokumen</td>
                    <td class="info-val"><span class="badge-tag">Formulir Penandaan Area Operasi</span></td>
                </tr>
                <tr>
                    <td class="info-label">No. Rawat</td>
                    <td class="info-val">{{ $marking->no_rawat }}</td>
                </tr>
                <tr>
                    <td class="info-label">No. Rekam Medis</td>
                    <td class="info-val">{{ $noRm }}</td>
                </tr>
                <tr>
                    <td class="info-label">Nama Pasien</td>
                    <td class="info-val">{{ $marking->nama_pasien }}</td>
                </tr>
                <tr>
                    <td class="info-label">Jenis Kelamin / Umur</td>
                    <td class="info-val">{{ $jenisKelaminTitle }} / {{ $umurPasien }}</td>
                </tr>
                <tr>
                    <td class="info-label">Tanggal Lahir Pasien</td>
                    <td class="info-val">{{ $tglLahirFormatted }}</td>
                </tr>
                <tr>
                    <td class="info-label">Prosedur Operasi</td>
                    <td class="info-val">{{ $marking->prosedur_operasi }}</td>
                </tr>
                <tr>
                    <td class="info-label">Dokter Operator</td>
                    <td class="info-val">dr. {{ $marking->nama_operator }}</td>
                </tr>
                <tr>
                    <td class="info-label">Kode Dokter / Operator</td>
                    <td class="info-val">{{ $marking->operator_id }}</td>
                </tr>
                <tr>
                    <td class="info-label">Waktu Penandaan / TTE</td>
                    <td class="info-val">{{ $tanggalPenandaan }}</td>
                </tr>
                <tr>
                    <td class="info-label">Fasilitas Kesehatan</td>
                    <td class="info-val">{{ $setting->hospital_name }}</td>
                </tr>
            </table>

            <div class="footer-note">
                <strong>Catatan Keabsahan:</strong> Halaman verifikasi ini dipublikasikan secara langsung oleh Sistem Informasi Manajemen Rumah Sakit (SIMRS) {{ $setting->hospital_name }} untuk menjamin otentisitas dokumen medis elektronik.
            </div>
        </div>
    </div>

</body>
</html>
