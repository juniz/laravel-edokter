<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email - {{ config('app.name') }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f5;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f5f5f5;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center; border-radius: 8px 8px 0 0;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700;">Verifikasi Email Anda</h1>
                            <p style="margin: 10px 0 0; color: #ffffff; font-size: 16px; opacity: 0.9;">{{ config('app.name') }}</p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;">
                                Halo <strong>{{ $name }}</strong>,
                            </p>
                            <p style="margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;">
                                Terima kasih telah mendaftar di <strong>{{ config('app.name') }}</strong>. Untuk menyelesaikan proses pendaftaran, silakan verifikasi alamat email Anda dengan memasukkan kode verifikasi berikut:
                            </p>

                            <!-- Verification Code Box -->
                            <table role="presentation" style="width: 100%; margin: 30px 0; border-collapse: collapse;">
                                <tr>
                                    <td align="center" style="padding: 0;">
                                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; padding: 30px; text-align: center;">
                                            <p style="margin: 0 0 10px; color: #ffffff; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Kode Verifikasi</p>
                                            <div style="background-color: #ffffff; border-radius: 6px; padding: 20px; margin: 15px 0; display: inline-block;">
                                                <span style="font-size: 36px; font-weight: 700; color: #667eea; letter-spacing: 8px; font-family: 'Courier New', monospace;">{{ $code }}</span>
                                            </div>
                                            <p style="margin: 10px 0 0; color: #ffffff; font-size: 12px; opacity: 0.9;">Kode ini berlaku selama 15 menit</p>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 20px 0; color: #666666; font-size: 14px; line-height: 1.6;">
                                Masukkan kode di atas pada halaman verifikasi email untuk mengaktifkan akun Anda.
                            </p>

                            <!-- Security Notice -->
                            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 25px 0; border-radius: 4px;">
                                <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.6;">
                                    <strong>⚠️ Keamanan:</strong> Jangan bagikan kode verifikasi ini kepada siapa pun. Tim {{ config('app.name') }} tidak akan pernah meminta kode verifikasi Anda.
                                </p>
                            </div>

                            <p style="margin: 20px 0 0; color: #666666; font-size: 14px; line-height: 1.6;">
                                Jika Anda tidak melakukan pendaftaran ini, abaikan email ini.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0 0 10px; color: #666666; font-size: 14px;">
                                <strong>{{ config('app.name') }}</strong><br>
                                Layanan Hosting & Server Terpercaya
                            </p>
                            <p style="margin: 10px 0 0; color: #999999; font-size: 12px;">
                                Email ini dikirim secara otomatis, mohon tidak membalas email ini.
                            </p>
                            <p style="margin: 15px 0 0; color: #999999; font-size: 12px;">
                                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

