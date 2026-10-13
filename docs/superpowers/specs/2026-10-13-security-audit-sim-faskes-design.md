# Spesifikasi Desain: Security Code Audit & Remediation Plan SIM Faskes

- **Dokumen Referensi:** [Template_Security_Self_Assessment_SIM_Faskes.md](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/docs/Template_Security_Self_Assessment_SIM_Faskes.md) (SPPTI-SEC-FORM-001 v1.1)
- **Target Output Laporan:** [Security_Self_Assessment_SIM_Faskes_Audit_Report.md](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/docs/Security_Self_Assessment_SIM_Faskes_Audit_Report.md)
- **Target Aplikasi:** SIM RS e-Dokter (Laravel 9.19, PHP 8.3/8.0, FrankenPHP / Caddy, MariaDB/MySQL Khanza)

---

## 1. Tujuan & Ruang Lingkup

Melakukan audit keamanan kode sumber SIM Faskes secara komprehensif terhadap 15 parameter penilaian keamanan standar bridging BPJS Kesehatan (10 parameter Frontend/Aplikasi `FE-01` s/d `FE-10` dan 5 parameter Backend/Server `BE-01` s/d `BE-05`), menghasilkan laporan penilaian diri yang terisi lengkap dengan bukti evidensi teknis riil, serta menyusun rencana perbaikan (remediation plan) terstruktur.

---

## 2. Metodologi Audit

Kombinasi **Static Code Analysis (SAST)**, **Contextual Architecture Review**, dan **Automated Dependency Auditing**:
1. **Pemeriksaan Input Validation & Query:** Analisis penggunaan raw SQL (`whereRaw`, `selectRaw`, `DB::raw`) dan escaping Blade (`{!! !!}`) untuk identifikasi celah SQLi dan XSS.
2. **Pemeriksaan Autentikasi & Sesi:** Analisis alur login di `LoginController`, rate limiting, lockout count, format pesan login, serta kompleksitas password.
3. **Pemeriksaan Otorisasi & Kontrol Akses:** Analisis middleware route (`loginauth`, `sudahlogin`), verifikasi hak akses URL langsung, dan mitigasi IDOR pada endpoint rekam medis.
4. **Pemeriksaan Dependensi & Konfigurasi Server:** Scan kelemahan paket melalui `composer audit` & `npm audit`, serta verifikasi konfigurasi Docker dan TLS Caddy.

---

## 3. Matriks Aspek Pengujian & Kriteria Evaluasi

### 3.1 Frontend / Aplikasi
| ID | Aspek Pengujian | Kategori | Lokasi Target Pemeriksaan | Kriteria PASS |
|---|---|---|---|---|
| **FE-01** | Celah XSS pada form input | Input validation | Views Blade, Form Request, controller inputs | Sanitasi input dan output escaping aktif (`{{ }}`) |
| **FE-02** | Celah SQL Injection pada form input | Input validation | `LoginController.php`, query builder | Semua parameter menggunakan binding PDO / Eloquent, zero string interpolation |
| **FE-03** | Pembatasan upaya login berulang | Autentikasi | `LoginController::customLogin`, Middleware | Rate limiting (Laravel RateLimiter / CAPTCHA / delay) aktif |
| **FE-04** | Lockout sementara setelah 3x salah login | Autentikasi | `LoginController::customLogin` | Sistem mengunci akun/IP sementara setelah 3 percobaan gagal berturut-turut |
| **FE-05** | Pesan peringatan "Gagal Login" | Autentikasi | `LoginController::customLogin` | Menampilkan pesan peringatan kegagalan login yang jelas |
| **FE-06** | Pesan generic (tidak bedakan username/password) | Autentikasi | `LoginController::customLogin` | Pesan tunggal tidak membocorkan keberadaan username |
| **FE-07** | Kompleksitas password (>= 8 char, upper, lower, angka, simbol) | Autentikasi | Validation rules user / password reset | Enforce minimal 8 karakter dengan kombinasi lengkap |
| **FE-08** | Akses menu sesuai role (URL direct blocking) | Otorisasi | `routes/web.php`, Middleware role/permission | Route dilindungi middleware spesifik per peran pengguna |
| **FE-09** | Pencegahan Broken Access Control / IDOR | Otorisasi | Controller data pasien & rekam medis | Validasi kepemilikan data sebelum mengembalikan response |
| **FE-10** | Bebas library vulnerable | Komponen | `composer.json`, `package.json` | Tidak ditemukan CVE berisiko tinggi tanpa mitigasi |

### 3.2 Backend / Server
| ID | Aspek Pengujian | Kategori | Lokasi Target Pemeriksaan | Kriteria PASS |
|---|---|---|---|---|
| **BE-01** | Antivirus terinstal di server | Hardening | Dokumentasi server host / environment | Antivirus aktif pada host server |
| **BE-02** | Firewall aktif, hanya port HTTPS inbound | Jaringan | `docker-compose*.yml`, Caddyfile | Port terbuka hanya 443 (HTTPS) untuk akses publik |
| **BE-03** | Versi TLS minimal 1.2 | Enkripsi | Caddyfile / FrankenPHP reverse proxy | Konfigurasi TLS menolak versi < 1.2 |
| **BE-04** | Web server bebas celah keamanan | Komponen | Dockerfile base image (FrankenPHP/Caddy) | Versi web server up-to-date & bebas CVE aktif |
| **BE-05** | Framework & runtime bebas celah keamanan | Komponen | PHP version, Laravel framework 9.x | Runtime didukung dengan patch security terkini |

---

## 4. Struktur Output Dokumen Laporan

File laporan disimpan di `docs/Security_Self_Assessment_SIM_Faskes_Audit_Report.md` dengan isi:
1. **Header Metadata:** Kode form `SPPTI-SEC-FORM-001`, versi 1.1, data Faskes dan platform.
2. **Ringkasan Eksekutif & Skor:** Jumlah item PASS vs NOT PASS, rekomendasi status kelayakan.
3. **Tabel Pengujian Frontend (FE-01 s/d FE-10):** Status dan eviden teknis (nomor baris kode, snippet, hasil audit CLI).
4. **Tabel Pengujian Backend (BE-01 s/d BE-05):** Status dan eviden arsitektur server/Docker.
5. **Rencana Aksi Perbaikan (Remediation Plan):** Prioritas patch P0 (Kritis) hingga P3 (Pemeliharaan).

---

## 5. Rencana Remediasi Teknis (Remediation Plan)

### Fase P0: Perbaikan Kritis (Auth & SQL Injection)
* **P0.1 - SQLi Fix:** Refactor query login di `app/Http/Controllers/LoginController.php`:
  ```php
  // Ganti string interpolation menjadi parameter binding
  ->whereRaw("id_user = AES_ENCRYPT(?, 'nur')", [$request->username])
  ```
* **P0.2 - Error Message Sanitization:** Samakan respon pesan gagal login menjadi `Username atau password salah` untuk menepis user enumeration.

### Fase P1: Proteksi Brute Force & Kebijakan Kredensial
* **P1.1 - Login Lockout & Throttle:** Pasang `RateLimiter::tooManyAttempts` pada `customLogin`:
  * Kunci login berdasarkan `Str::transliterate(Str::lower($request->input('username')).'|'.$request->ip())`.
  * Batasi 3 percobaan gagal. Jika terlewati, kirim error lockout temporer (misal 5 menit).
* **P1.2 - Password Policy:** Terapkan aturan `Password::min(8)->letters()->mixedCase()->numbers()->symbols()` di fitur pengelolaan password.

### Fase P2: Otorisasi & Pencegahan IDOR
* **P2.1 - URL & Menu Authorization:** Audit rute di `routes/web.php`, pastikan setiap rute sensitif tidak hanya memeriksa `loginauth` tapi juga mencocokkan hak akses peran.
* **P2.2 - Resource Scoping:** Pastikan controller dengan parameter ID (misal konsultasi, rekam medis) memvalidasi kepemilikan dokter/unit layanan aktif.

### Fase P3: Dependensi & Hardening Server
* **P3.1 - Library Remediation:** Jalankan `composer update --dry-run` / selektif patch untuk dependensi dengan advisory security.
* **P3.2 - Reverse Proxy & TLS:** Pastikan file konfigurasi Caddy membatasi `protocols tls1.2 tls1.3`.
