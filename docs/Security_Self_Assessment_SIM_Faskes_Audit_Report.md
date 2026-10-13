---
document_type: audit_report
title: Security Self-Assessment SIM Faskes - Audit Report
form_code: SPPTI-SEC-FORM-001
version: "1.1"
classification: Rahasia
language: id
owner: BPJS Kesehatan
assessment_date: "13/10/2026"
application_name: SIM RS e-Dokter
status_recommendation: Tidak Layak (Perlu Remediasi)
---

# SECURITY SELF-ASSESSMENT SIM FASKES
## LAPORAN AUDIT KEAMANAN SISTEM INFORMASI MANAJEMEN FASILITAS KESEHATAN

**Kode Dokumen:** `SPPTI-SEC-FORM-001`  
**Versi Formulir:** 1.1  
**Klasifikasi:** Rahasia  
**Status Evaluasi Mandiri:** **TIDAK LAYAK (15 dari 15 Item Memerlukan Remediasi)**

---

## 1. Informasi Umum

| Field | Nilai | Keterangan |
|---|---|---|
| **PIC Security Assessment** | Tim Integrasi Sistem SIM RS e-Dokter | PIC Integrasi Sistem Faskes & Keamanan Aplikasi |
| **Nama SIM Faskes** | SIM RS e-Dokter (`http://localhost:8080`) | Sistem Informasi Manajemen RS / Poliklinik Rawat Jalan & Inap |
| **Versi / Build** | Build 2026.10.13-rel (Git Commit HEAD: `47220f2`) | Berbasis rilis kernel Laravel 9.52.16 |
| **Platform** | Laravel 9.19 (v9.52.16), PHP 8.3, FrankenPHP / Caddy Server, MariaDB/MySQL Khanza | Containerized Docker Environment |
| **Tanggal Pengujian** | 13/10/2026 | Format: DD/MM/YYYY |
| **Distribusi Dokumen** | Rumah Sakit SIM RS e-Dokter; BPJS Kesehatan KC Surakarta | Distribusi Resmi Evaluasi Pra-Bridging |

---

## 2. Tujuan

*Security Self-Assessment* ini dilakukan dalam rangka verifikasi kesiapan dan kepatuhan standar keamanan informasi implementasi integrasi sistem (*Bridging*) antara SIM Faskes (SIM RS e-Dokter) dengan Web Service milik BPJS Kesehatan sesuai dengan pedoman dokumen `SPPTI-SEC-FORM-001` versi 1.1.

---

## 3. Ruang Lingkup dan Metode Pengujian

### 3.1 Ruang Lingkup Pengujian
Pengujian dilakukan secara menyeluruh terhadap seluruh komponen kode sumber dan infrastruktur pendukung:
1. **Aplikasi Web (Frontend & Controller Logic):** Seluruh modul routing (`routes/web.php`), modul autentikasi (`app/Http/Controllers/LoginController.php`), modul konsultasi medis (`app/Http/Controllers/KonsultasiMedikController.php`), komponen livewire/blade views (`resources/views/`), serta middleware otorisasi (`app/Http/Middleware/`).
2. **Infrastruktur & Server Deployment (Backend):** Konfigurasi runtime container (`Dockerfile.frankenphp`, `Caddyfile.frankenphp`, `docker-compose.yml`, `docker-compose.prod.yml`), konfigurasi SSL/TLS reverse proxy, eksposur port jaringan inbound/outbound, serta versi dependensi paket third-party (`composer.lock`, `package-lock.json`).
3. **Data & Kredensial:** Alur penanganan password akun pada tabel `user` schema Khanza (`AES_ENCRYPT` / `AES_DECRYPT`), penanganan session pengguna, dan aksesibilitas endpoint rekam medis.

### 3.2 Metode Pengujian
Pengujian mengadopsi metodologi:
- **OWASP Web Application Penetration Testing Methodology (OWASP Top 10 Web Application Risks)**: Fokus pada Injection (A03), Broken Access Control (A01), Cryptographic Failures (A02), Identification and Authentication Failures (A07), serta Vulnerable and Outdated Components (A06).
- **Static Application Security Testing (SAST)**: Audit statis terhadap sintaks query mentah, unescaped Blade directives, dan kontrol alur controller.
- **Automated Dependency Vulnerability Scanning**: Audit database CVE dependensi Composer dan NPM.
- **Configuration & Infrastructure Review**: Verifikasi direktif TLS Caddyfile dan pemetaan port Docker Compose.

---

## 4. Tools yang Digunakan

| Tool | Versi / Publisher | Pemanfaatan |
|---|---|---|
| **Ripgrep / Git Grep SAST** | Ripgrep 14.1.0 / Git SAST Inspector | Pemindaian pola kode rawan (SQLi `whereRaw`, XSS `{!! !!}`, session auth bypass). |
| **NPM Audit CLI** | NPM 10.x / GitHub Security Advisory Database | Pemindaian kerentanan pustaka dependensi frontend JavaScript/Node.js. |
| **Composer Audit CLI** | Composer 2.7.x / Packagist Advisory Database | Pemindaian kelemahan keamanan paket PHP dan status lifecycle framework Laravel. |
| **PHP CLI & Artisan** | PHP 8.3 CLI / Laravel Artisan | Inspeksi middleware pipeline, route list mapping, dan environment runtime. |
| **Docker Compose Inspector** | Docker Compose Engine v2.x | Verifikasi konfigurasi port forwarding, environment exposure, dan base image container. |

---

## 5. Hasil Pengujian

### Ringkasan Eksekutif Hasil Evaluasi

| Kategori Pengujian | Total Kontrol | Status PASS | Status NOT PASS | Kepatuhan (%) |
|---|:---:|:---:|:---:|:---:|
| **5.1 Frontend & Aplikasi (FE-01 s/d FE-10)** | 10 | 0 | 10 | 0% |
| **5.2 Backend & Server (BE-01 s/d BE-05)** | 5 | 0 | 5 | 0% |
| **TOTAL** | **15** | **0** | **15** | **0%** |

---

### 5.1 Frontend: Aspek Minimal yang Harus Dipenuhi SIM Faskes

| ID | Aspek Pengujian | Kategori | Eviden Teknis & Analisis Celah Keamanan | Checklist Rekomendasi Faskes |
|:---:|---|---|---|:---:|
| **FE-01** | Memastikan tidak ada celah XSS (*cross site scripting*) pada setiap form inputan. | Input validation | **Lokasi Temuan:**<br>- [`master-template-ekg.blade.php:34`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/resources/views/master-template-ekg.blade.php#L34):<br>```blade\n<td>{!! $item->template !!}</td>\n```<br>- [`echo.blade.php:150`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/resources/views/prints/echo.blade.php#L150):<br>```blade\n{!! $isi !!}\n```<br>**Analisis Celah:**<br>Penggunaan unescaped raw echo syntax `{!! !!}` pada template output data tanpa melalui HTML purifier/sanitizer membuka celah Stored Cross-Site Scripting (XSS). Jika pengguna menginput payload script seperti `<script>alert(document.cookie)</script>` pada field template/keterangan medis, script akan tereksekusi di browser user lain.<br>Selain itu, [`SanitizeInputMiddleware.php`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/app/Http/Middleware/SanitizeInputMiddleware.php) belum didaftarkan pada `$middlewareGroups['web']` di [`app/Http/Kernel.php`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/app/Http/Kernel.php). | [ ] PASS<br>**[X] NOT PASS** |
| **FE-02** | Memastikan tidak ada celah SQL Injection pada setiap form inputan. | Input validation | **Lokasi Temuan:**<br>- [`LoginController.php:32-36`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/app/Http/Controllers/LoginController.php#L32-L36):<br>```php\n$cek = DB::table('user')\n    ->join("dokter", "dokter.kd_dokter", "=", DB::Raw("AES_DECRYPT(id_user, 'nur')"))\n    ->whereRaw("id_user = AES_ENCRYPT('{$request->username}', 'nur')")\n    ->selectRaw("AES_DECRYPT(id_user, 'nur') as id_user, AES_DECRYPT(password, 'windi') as password, dokter.kd_sps")\n    ->first();\n```<br>**Analisis Celah:**<br>Parameter `$request->username` diinterpolasi langsung ke dalam query string `whereRaw` tanpa menggunakan parameter binding PDO (`?`). Penyerang dapat menyisipkan kutip satu (`'`) dan payload SQL Injection (misal: `' OR '1'='1' -- `) untuk memanipulasi logika autentikasi database Khanza secara langsung. | [ ] PASS<br>**[X] NOT PASS** |
| **FE-03** | Terdapat mekanisme untuk membatasi upaya login berkali-kali (mis. CAPTCHA, *delay time*) dan berfungsi baik: tidak dapat di-bypass, dibaca OCR, atau di-reuse. | Autentikasi | **Lokasi Temuan:**<br>- [`LoginController.php:29-48`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/app/Http/Controllers/LoginController.php#L29-L48)<br>- [`routes/web.php:28-31`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/routes/web.php#L28-L31)<br>**Analisis Celah:**<br>Rute `POST /customlogin` dan method `customLogin()` tidak menerapkan proteksi rate limiting bawaan Laravel (`Illuminate\Support\Facades\RateLimiter`), tidak menggunakan middleware `throttle:login`, serta tidak memiliki validasi tantangan interaktif seperti CAPTCHA. Penyerang dapat menjalankan automated brute-force dan credential stuffing attack dengan ribuan request per detik tanpa pembatasan waktu tunda (*delay time*). | [ ] PASS<br>**[X] NOT PASS** |
| **FE-04** | Lock out login temporer setelah 3 kali salah percobaan login. | Autentikasi | **Lokasi Temuan:**<br>- [`LoginController.php:37-48`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/app/Http/Controllers/LoginController.php#L37-L48)<br>**Analisis Celah:**<br>Sistem sama sekali tidak menyimpan jejak percobaan gagal login per akun maupun per IP (zero attempt counter). Tidak ada fungsi penguncian akun temporer (*temporary lockout*) setelah 3 kali salah percobaan login. Pengguna/bot dapat mencoba kombinasi kredensial tanpa batas tanpa pernah diblokir sementara. | [ ] PASS<br>**[X] NOT PASS** |
| **FE-05** | Memunculkan pesan *warning* "Gagal Login". | Autentikasi | **Lokasi Temuan:**<br>- [`LoginController.php:43,47`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/app/Http/Controllers/LoginController.php#L43-L47):<br>```php\nreturn back()->withErrors(['message' => 'Password salah']);\n...\nreturn back()->withErrors(['message' => 'User tidak ditemukan']);\n```<br>- [`resources/views/auth/login.blade.php:25-27`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/resources/views/auth/login.blade.php#L25-L27)<br>**Analisis Celah:**<br>Sistem tidak memunculkan pesan peringatan baku "Gagal Login" melainkan pesan teknis deskriptif yang membocorkan status validasi internal pengguna. | [ ] PASS<br>**[X] NOT PASS** |
| **FE-06** | Pesan *warning* "Gagal Login" tidak boleh membedakan antara username salah dan password salah. | Autentikasi | **Lokasi Temuan:**<br>- [`LoginController.php:43,47`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/app/Http/Controllers/LoginController.php#L43-L47):<br>```php\nif ($cek) {\n    if ($cek->password == $request->password) {\n        // Login sukses\n    } else {\n        return back()->withErrors(['message' => 'Password salah']); // PELANGGARAN\n    }\n}\nreturn back()->withErrors(['message' => 'User tidak ditemukan']); // PELANGGARAN\n```<br>**Analisis Celah:**<br>Sistem membedakan secara eksplisit respon error antara keberadaan username (`Password salah`) dan ketiadaan akun (`User tidak ditemukan`). Hal ini melanggar prinsip *Anti-User Enumeration* OWASP, karena penyerang dapat menyusun daftar NIP/ID dokter yang valid pada fasilitas kesehatan hanya dengan mengamati perbedaan pesan error. | [ ] PASS<br>**[X] NOT PASS** |
| **FE-07** | Kompleksitas password minimal: panjang ≥ 8 karakter, memuat huruf besar, huruf kecil, angka, dan karakter khusus (mis. `*`, `#`, `$`, `!`, `&`). | Autentikasi | **Lokasi Temuan:**<br>- [`Auth/RegisterController.php:55`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/app/Http/Controllers/Auth/RegisterController.php#L55):<br>```php\n'password' => ['required', 'string', 'min:8', 'confirmed'],\n```<br>- [`LoginController.php:60`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/app/Http/Controllers/LoginController.php#L60)<br>**Analisis Celah:**<br>Aturan validasi password yang ada hanya membatasi panjang minimal 8 karakter (`min:8`) tanpa aturan penegakan komposisi karakter: huruf besar (*mixed case*), huruf kecil, angka, dan karakter khusus simbol. Di samping itu, sistem menyimpan dan mencocokkan password Khanza secara plain-text terenkripsi AES dua arah (`AES_DECRYPT(password, 'windi')`) tanpa kebijakan validasi kompleksitas kredensial saat pengguna membuat/mengganti password. | [ ] PASS<br>**[X] NOT PASS** |
| **FE-08** | Menu pada SIM Faskes hanya dapat diakses sesuai peruntukan. Mis. menu admin tidak dapat diakses petugas walaupun URL menu admin dimasukkan langsung. | Otorisasi | **Lokasi Temuan:**<br>- [`LoginAuth.php:17-22`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/app/Http/Middleware/LoginAuth.php#L17-L22):<br>```php\npublic function handle(Request $request, Closure $next) {\n    if($request->session()->has('username') && $request->session()->has('password')) {\n        return $next($request);\n    }\n    return redirect()->route('login');\n}\n```<br>- [`routes/web.php:207`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/routes/web.php#L207)<br>**Analisis Celah:**<br>Seluruh rute internal hanya dilindungi middleware tunggal `loginauth` yang hanya mengecek eksistensi session `username` dan `password`. Tidak ada middleware Role-Based Access Control (RBAC) atau pengecekan otorisasi per-menu (`hasRole`/`can`). Pengguna dengan peran apa pun (mis. dokter spesialis/petugas) dapat mengakses URL menu modul lain secara langsung melalui browser URL bar jika mengetahui path rutenya. | [ ] PASS<br>**[X] NOT PASS** |
| **FE-09** | Memastikan tidak ada celah *broken access control*: non-user tidak dapat mengakses data milik user, dan user A tidak dapat mengakses data user B. | Otorisasi | **Lokasi Temuan:**<br>- [`KonsultasiMedikController.php:15-20, 32-48`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/app/Http/Controllers/KonsultasiMedikController.php#L15-L20):<br>```php\npublic function jawaban($no_permintaan) {\n    $konsultasi = KonsultasiMedik::with('dokter')->find($no_permintaan);\n    $jawaban = JawabanKonsultasiMedik::find($no_permintaan);\n    return view('konsultasi.jawab', compact('konsultasi', 'jawaban'));\n}\n```<br>- [`routes/web.php:283-285`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/routes/web.php#L283-L285)<br>**Analisis Celah:**<br>Terjadi kerentanan kritis *Insecure Direct Object Reference* (IDOR / Broken Access Control). Endpoint `/konsultasi/jawaban/{no_permintaan}` mengambil dan mengizinkan modifikasi jawaban konsultasi medis hanya berdasarkan parameter ID dari URL tanpa memeriksa apakah dokter yang sedang login (`session('username')`) berhak atas konsultasi tersebut (`kd_dokter` atau `kd_dokter_dikonsuli`). User Dokter A dapat mengintip dan mengubah rekam konsultasi Dokter B cukup dengan mengganti nomor permintaan di URL. | [ ] PASS<br>**[X] NOT PASS** |
| **FE-10** | Tidak menggunakan *library* yang vulnerable, mis. Bootstrap, jQuery, dan sebagainya. | Komponen | **Lokasi Temuan & Hasil Audit:**<br>Hasil eksekusi `npm audit`: **18 vulnerabilities (1 Critical, 12 High, 5 Moderate)**:<br>1. **Webpack (Critical - GHSA-hc6q-2mpp-qw7j, GHSA-4vvj-4cpr-p986):** Cross-realm object access & DOM Clobbering XSS.<br>2. **Axios (High - GHSA-fvcv-3m26-pcqx, GHSA-62hf-57xw-28j9):** Unrestricted Cloud Metadata Exfiltration via Header Injection Chain, unbounded recursion DoS, ReDoS cookie injection.<br>3. **Lodash (High - GHSA-r5fr-rjxr-66jc):** Code Injection via `_.template` imports key names & Prototype Pollution.<br>4. **Braces, Browserslist, Nanoid, PostCSS, Rollup (High):** ReDoS, stack-exhaustion DoS, dan Prototype Pollution.<br>Hasil audit Composer:<br>- [`composer.json:22`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/composer.json#L22) menggunakan `laravel/framework: ^9.19` (terpasang `v9.52.16`) yang sudah mencapai End-of-Life (EOL) sejak Februari 2024. | [ ] PASS<br>**[X] NOT PASS** |

---

### 5.2 Backend: Aspek Minimal yang Harus Dipenuhi Server SIM Faskes

| ID | Aspek Pengujian | Kategori | Eviden Teknis & Analisis Konfigurasi Server | Checklist Rekomendasi Faskes |
|:---:|---|---|---|:---:|
| **BE-01** | Server terinstall antivirus. | Hardening server | **Lokasi Temuan:**<br>- [`Dockerfile.frankenphp:10-24`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/Dockerfile.frankenphp#L10-L24)<br>**Analisis Konfigurasi:**<br>Image kontainer runtime Linux Debian Bookworm (`ghcr.io/dunglas/frankenphp:1-php8.3-bookworm`) tidak memiliki instalasi endpoint protection / anti-malware (seperti ClamAV atau OSSEC agent). Pada sisi host operating system saat ini belum terdapat dokumentasi verifikasi agen antivirus real-time tersertifikasi aktif untuk monitoring file dan upload aplikasi medis. | [ ] PASS<br>**[X] NOT PASS** |
| **BE-02** | Firewall diaktifkan dan hanya menerima port HTTPS untuk inbound. | Jaringan | **Lokasi Temuan:**<br>- [`docker-compose.prod.yml:10-11`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/docker-compose.prod.yml#L10-L11):<br>```yaml\nports:\n  - "8080:8080"\n```<br>- [`docker-compose.yml:11-14, 28-30`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/docker-compose.yml#L11-L14):<br>```yaml\nports:\n  - '${APP_PORT:-80}:80'\n  - '${VITE_PORT:-5173}:${VITE_PORT:-5173}'\n  - '${FORWARD_REDIS_PORT:-6379}:6379'\n```<br>**Analisis Konfigurasi:**<br>Konfigurasi deployment mengekspos port HTTP tidak terenkripsi (port 8080 dan port 80), serta port service internal Redis (6379) dan Vite dev server (5173). Tidak terdapat pembatasan firewall ingress host (UFW/iptables) yang secara eksklusif hanya mengizinkan port inbound HTTPS (port 443). Port plain-text HTTP masih terbuka untuk lalu lintas jaringan publik. | [ ] PASS<br>**[X] NOT PASS** |
| **BE-03** | Versi TLS minimal 1.2. | Enkripsi | **Lokasi Temuan:**<br>- [`Caddyfile.frankenphp:1-6`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/Caddyfile.frankenphp#L1-L6):<br>```caddyfile\n{\n    # Global options\n    auto_https disable_certs\n}\n\n:8080 {\n    root * /app/public\n    ...\n```<br>**Analisis Konfigurasi:**<br>Konfigurasi web server Caddy mendisable penerbitan sertifikat SSL/TLS (`auto_https disable_certs`) dan beroperasi pada skema plain HTTP di port `:8080`. Tidak terdapat deklarasi direktif TLS protokol eksplisit (`protocols tls1.2 tls1.3`) untuk menolak koneksi SSLv3, TLS 1.0, dan TLS 1.1 yang telah dinyatakan *deprecated* dan *insecure*. | [ ] PASS<br>**[X] NOT PASS** |
| **BE-04** | Versi web server yang digunakan (Apache, Nginx, IIS) tidak memiliki celah keamanan. | Komponen | **Lokasi Temuan:**<br>- [`Dockerfile.frankenphp:10`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/Dockerfile.frankenphp#L10):<br>```dockerfile\nFROM ghcr.io/dunglas/frankenphp:1-php8.3-bookworm AS base\n```<br>**Analisis Konfigurasi:**<br>Web server menggunakan FrankenPHP (Caddy embedded engine) berbasis unpinned floating tag (`1-php8.3-bookworm`) tanpa pinning SHA256 digest atau versi minor/patch Caddy spesifik. Server belum dilengkapi konfigurasi security response headers (HSTS, CSP, X-Frame-Options, X-Content-Type-Options) untuk mitigasi clickjacking dan MIME-sniffing. | [ ] PASS<br>**[X] NOT PASS** |
| **BE-05** | Versi framework/bahasa pemrograman yang digunakan tidak memiliki celah keamanan. | Komponen | **Lokasi Temuan:**<br>- [`composer.json:22`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/composer.json#L22)<br>- [`composer.lock`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/composer.lock) (Line 3804-3806):<br>```json\n"name": "laravel/framework",\n"version": "v9.52.16"\n```<br>**Analisis Konfigurasi:**<br>Framework yang digunakan adalah Laravel versi 9 (`v9.52.16`). Siklus hidup (*Lifecycle*) Laravel 9 resmi dinyatakan **End-of-Life (EOL) per 14 Februari 2024**. Framework ini tidak lagi mendapatkan patch keamanan berkala dari Laravel security team, sehingga setiap kerentanan *zero-day* atau celah baru pada core framework Laravel 9 tidak akan diperbaiki secara otomatis. | [ ] PASS<br>**[X] NOT PASS** |

---

## 6. Kesimpulan

**Status Kelayakan Integrasi Sistem:**  
[ ] **Layak**  
[X] **Tidak Layak (Rekomendasi Faskes: TIDAK LAYAK sampai Remediasi Selesai)**

### Catatan Evaluasi Tim Keamanan Faskes:
1. Berdasarkan hasil audit mandiri (*Self-Assessment*) yang dilakukan pada tanggal **13 Oktober 2026**, aplikasi SIM RS e-Dokter **belum memenuhi persyaratan minimal keamanan informasi** yang disyaratkan oleh BPJS Kesehatan dalam formulir `SPPTI-SEC-FORM-001` versi 1.1.
2. Dari total **15 parameter uji**, tercatat **0 item PASS** dan **15 item NOT PASS** (10 kontrol Frontend dan 5 kontrol Backend).
3. Terdapat temuan berisiko **Kritis (Critical)** dan **Tinggi (High)** yang berpotensi membahayakan kerahasiaan (*confidentiality*), integritas (*integrity*), dan ketersediaan (*availability*) data bridging pasien:
   - **FE-02 (SQL Injection):** Interpolasi string mentah input username login pada fungsi enkripsi database Khanza.
   - **FE-03 & FE-04 (Brute Force & Lockout):** Ketiadaan rate limiting dan ketiadaan sistem temporary lockout 3x salah percobaan login.
   - **FE-06 (User Enumeration):** Pesan kesalahan login membedakan keberadaan username dan password.
   - **FE-08 & FE-09 (Broken Access Control & IDOR):** Tidak adanya validasi kepemilikan record pada data konsultasi medis dan ketiadaan RBAC pada rute internal.
   - **FE-10 (Vulnerable Libraries):** Ketergantungan pada pustaka Webpack (Critical) dan Axios (High) yang memiliki celah keamanan aktif.
   - **BE-02 & BE-03 (Firewall & TLS):** Penggunaan port plain HTTP 8080 tanpa penegakan TLS minimal 1.2 pada reverse proxy Caddy.
   - **BE-05 (Framework EOL):** Penggunaan Laravel 9 yang telah berstatus End-of-Life sejak Februari 2024.
4. **Rekomendasi Tindak Lanjut:** Faskes segera melaksanakan perbaikan bertahap mengacu pada dokumen **Security Remediation Plan** (Prioritas P0 s/d P3) dan mengajukan audit ulang (*re-assessment*) setelah seluruh 15 kontrol dinyatakan berstatus `PASS`.

---

## 7. Pengesahan

### Disusun oleh:
| No. | Nama | Jabatan | Tanda Tangan | Tanggal |
|:---:|---|---|:---:|:---:|
| 1 | **Tim Integrasi Sistem SIM RS e-Dokter** | PIC Integrasi Sistem Faskes (RS/FKTP) | *(Draft Elektronik - Audit Terverifikasi)* | 13/10/2026 |

### Diperiksa oleh:
| No. | Nama | Jabatan | Tanda Tangan | Tanggal |
|:---:|---|---|:---:|:---:|
| 1 | *(Menunggu Penunjukan PIC BPJS)* | PIC Integrasi Sistem (BPJS Kesehatan) | ______________________ | ____/____/2026 |

### Disetujui oleh:
| No. | Nama | Jabatan | Tanda Tangan | Tanggal |
|:---:|---|---|:---:|:---:|
| 1 | *(Menunggu Disposisi Pimpinan Unit Kerja)* | Pimpinan Unit Kerja minimal setingkat Kepala Cabang | ______________________ | ____/____/2026 |

---

**BPJS Kesehatan Republik Indonesia**  
Kantor Pusat: Jl. Letjen Suprapto Kav.20 No.14, Cempaka Putih, Jakarta Pusat
