# Technical Security Remediation Plan & Patch Specification
## SIM RS e-Dokter — Bridging BPJS Kesehatan (`SPPTI-SEC-FORM-001` v1.1)

- **Target File:** `docs/superpowers/specs/2026-10-13-security-remediation-patch-guide.md`
- **Dokumen Audit Terkait:** [Security_Self_Assessment_SIM_Faskes_Audit_Report.md](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/docs/Security_Self_Assessment_SIM_Faskes_Audit_Report.md)
- **Status Baseline:** 15 Kontrol NOT PASS (0% Kepatuhan)
- **Target Kepatuhan:** 15 Kontrol PASS (100% Kepatuhan Standar BPJS Kesehatan)
- **Tanggal Penyusunan:** 13 Oktober 2026

---

## 1. Ikhtisar Eksekutif & Roadmap Remediasi

Audit keamanan mandiri (`SPPTI-SEC-FORM-001` v1.1) menemukan 15 kerentanan pada layer aplikasi (Frontend FE-01 s/d FE-10) dan server (Backend BE-01 s/d BE-05). Dokumen ini memberikan spesifikasi patch kode konkret, diff siap pakai, konfigurasi infrastruktur, dan tata cara verifikasi pengujian per fase.

```
+-----------------------------------------------------------------------------------+
|                           ROADMAP REMEDIASI KEAMANAN                              |
+-----------------------------------------------------------------------------------+
| FASE P0 (Kritis - 24 Jam)  : FE-01 (XSS), FE-02 (SQLi), FE-05, FE-06 (Anti-Enum) |
| FASE P1 (Tinggi - 3 Hari)   : FE-03 (Rate Limit), FE-04 (Lockout 3x), FE-07 (PWD) |
| FASE P2 (Menengah - 1 Minggu): FE-08 (RBAC Menu), FE-09 (IDOR / Ownership)       |
| FASE P3 (Infra - 2 Minggu)  : FE-10 (Deps), BE-01 s/d BE-05 (TLS, Port, Caddy)   |
+-----------------------------------------------------------------------------------+
```

---

## 2. Fase P0: Perbaikan Kritis (SQLi, Anti-Enumeration & Sanitasi XSS)

### 2.1 [FE-02] SQL Injection Remediation di `LoginController.php`

#### Analisis Akar Masalah
Pada [`LoginController.php`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/app/Http/Controllers/LoginController.php#L34), interpolasi variabel mentah `'{$request->username}'` digunakan di dalam klausa `whereRaw()`. Penyerang dapat menyuntikkan karakter quote `'` dan sintaks SQL (misal `' OR '1'='1' -- `) untuk membobol autentikasi atau mengeksekusi ekspresi tak diinginkan.

#### Spesifikasi Patch
Ganti interpolasi string menjadi prepared statement dengan parameter binding PDO (`?`). Pertahankan enkripsi Khanza `AES_ENCRYPT(?, 'nur')` dengan menyalurkan `$request->username` sebagai array argumen binding kedua pada `whereRaw()`.

#### Code Diff Patch
```diff
--- a/app/Http/Controllers/LoginController.php
+++ b/app/Http/Controllers/LoginController.php
@@ -31,7 +31,7 @@ class LoginController extends Controller
         $this->validateLogin($request);
         $cek = DB::table('user')
             ->join("dokter", "dokter.kd_dokter", "=", DB::Raw("AES_DECRYPT(id_user, 'nur')"))
-            ->whereRaw("id_user = AES_ENCRYPT('{$request->username}', 'nur')")
+            ->whereRaw("id_user = AES_ENCRYPT(?, 'nur')", [$request->username])
             ->selectRaw("AES_DECRYPT(id_user, 'nur') as id_user, AES_DECRYPT(password, 'windi') as password, dokter.kd_sps")
             ->first();
```

#### Verifikasi & Uji Penetrasi
1. Kirim payload uji SQLi pada parameter `username`:
   ```bash
   curl -i -X POST http://localhost:8080/customlogin \
     -H "Content-Type: application/x-www-form-urlencoded" \
     -d "username=' OR '1'='1' -- &password=test&poli=INT"
   ```
2. **Kriteria Lulus:** Query tidak memicu sintaks error database atau bypass login; request gagal divalidasi dan ditolak.

---

### 2.2 [FE-05 & FE-06] Generic Login Error Message (Anti-User Enumeration)

#### Analisis Akar Masalah
[`LoginController.php`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/app/Http/Controllers/LoginController.php#L43-L47) membedakan respon error antara `Password salah` saat username ada dan `User tidak ditemukan` saat username tidak terdaftar. Perilaku ini memungkinkan penyerang memetakan daftar username/NIP dokter yang valid (User Enumeration).

#### Spesifikasi Patch
1. Ubah seluruh respon gagal login menjadi pesan baku generik: `Username atau password salah`.
2. Jangan bedakan jalur penolakan apakah kegagalan terjadi di pencarian akun atau verifikasi kecocokan password.

#### Code Diff Patch
```diff
--- a/app/Http/Controllers/LoginController.php
+++ b/app/Http/Controllers/LoginController.php
@@ -36,15 +36,11 @@ class LoginController extends Controller
             ->first();
         if ($cek) {
             if ($cek->password == $request->password) {
                 session(['username' => $cek->id_user, 'password' => $cek->password, 'kd_poli' => $request->poli, 'kd_sps' => $cek->kd_sps]);
                 return redirect()->intended('home')
                     ->withSuccess('Signed in');
-            } else {
-                return back()->withErrors(['message' => 'Password salah']);
             }
         }
 
-        return back()->withErrors(['message' => 'User tidak ditemukan']);
+        return back()->withErrors(['message' => 'Username atau password salah']);
     }
```

#### Verifikasi & Pengujian
1. Request 1: Kirim username sembarang yang tidak ada di database (`fake_user_999`).
2. Request 2: Kirim username dokter valid dengan password sengaja salah.
3. **Kriteria Lulus:** Kedua request menghasilkan status HTTP yang sama dan body respon yang memuat string error persis sama: `Username atau password salah`.

---

### 2.3 [FE-01] Sanitasi XSS & Registrasi Middleware

#### Analisis Akar Masalah
[`SanitizeInputMiddleware.php`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/app/Http/Middleware/SanitizeInputMiddleware.php) telah dibuat namun belum didaftarkan pada grup `web` di [`app/Http/Kernel.php`](file:///Users/hardiko/Documents/Developer/LARAVEL/laravel9-edokter/app/Http/Kernel.php). Selain itu, sintaks unescaped `{!! !!}` masih ditemukan pada template rendering seperti `master-template-ekg.blade.php:34` dan `echo.blade.php:150`.

#### Spesifikasi Patch
1. Daftarkan `SanitizeInputMiddleware` ke pipeline middleware grup `web` di `Kernel.php`.
2. Ganti unescaped `{!! !!}` dengan auto-escaping `{{ }}` pada teks biasa, atau gunakan helper purifier (`clean($item->template)` via `mews/purifier`) jika field HTML kaya (rich text) dibutuhkan dokter.

#### Code Diff Patch
```diff
--- a/app/Http/Kernel.php
+++ b/app/Http/Kernel.php
@@ -38,6 +38,7 @@ class Kernel extends HttpKernel
             \App\Http\Middleware\VerifyCsrfToken::class,
             \Illuminate\Routing\Middleware\SubstituteBindings::class,
+            \App\Http\Middleware\SanitizeInputMiddleware::class,
         ],
```
```diff
--- a/resources/views/master-template-ekg.blade.php
+++ b/resources/views/master-template-ekg.blade.php
@@ -31,7 +31,7 @@
-                        <td>{!! $item->template !!}</td>
+                        <td>{{ $item->template }}</td>
```

---

## 3. Fase P1: Brute Force Protection, Lockout 3x & Password Policy

### 3.1 [FE-03 & FE-04] Rate Limiting & Lockout Sementara 3x Gagal di `LoginController`

#### Analisis Akar Masalah
Endpoint login tidak memiliki pelindung frekuensi percobaan. Penyerang dapat melancarkan jutaan tebakan password otomatis. Standar BPJS Kesehatan mensyaratkan:
- Batas maksimal 3 kali salah percobaan login berturut-turut.
- Penguncian login sementara (*temporary lockout*) minimal 5 menit (300 detik) jika batas terlewati.
- Perhitungan waktu jeda (*countdown lock time*) yang jelas.

#### Spesifikasi Implementasi
1. Manfaatkan facade `Illuminate\Support\Facades\RateLimiter`.
2. Tentukan throttle key unik berbasis kombinasi username yang dinormalisasi dan IP address request:
   ```php
   $throttleKey = Str::transliterate(Str::lower($request->input('username')) . '|' . $request->ip());
   ```
3. Di awal method `customLogin()`: periksa apakah `$throttleKey` telah melebihi 3 percobaan via `RateLimiter::tooManyAttempts($throttleKey, 3)`.
4. Jika ya, hitung sisa detik lockout menggunakan `RateLimiter::availableIn($throttleKey)` dan tolak login dengan pesan:
   `Terlalu banyak percobaan login gagal. Akun dikunci sementara selama :seconds detik.`
5. Jika verifikasi kredensial gagal: catat kegagalan dengan masa penalti 300 detik menggunakan `RateLimiter::hit($throttleKey, 300)`.
6. Jika login berhasil: reset riwayat kegagalan menggunakan `RateLimiter::clear($throttleKey)`.

#### Full Code Diff Patch di `LoginController.php`
```diff
--- a/app/Http/Controllers/LoginController.php
+++ b/app/Http/Controllers/LoginController.php
@@ -5,8 +5,10 @@ namespace App\Http\Controllers;
 use Illuminate\Support\Facades\DB;
 use Illuminate\Http\Request;
 use Illuminate\Encryption\Encrypter;
+use Illuminate\Support\Facades\RateLimiter;
+use Illuminate\Support\Str;
 
 class LoginController extends Controller
 {
@@ -29,18 +31,35 @@ class LoginController extends Controller
     public function customLogin(Request $request)
     {
         $this->validateLogin($request);
+
+        $throttleKey = Str::transliterate(Str::lower($request->input('username')) . '|' . $request->ip());
+
+        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
+            $seconds = RateLimiter::availableIn($throttleKey);
+            return back()->withErrors([
+                'message' => "Terlalu banyak percobaan login gagal. Akses dikunci sementara selama {$seconds} detik."
+            ])->withInput($request->except('password'));
+        }
+
         $cek = DB::table('user')
             ->join("dokter", "dokter.kd_dokter", "=", DB::Raw("AES_DECRYPT(id_user, 'nur')"))
-            ->whereRaw("id_user = AES_ENCRYPT('{$request->username}', 'nur')")
+            ->whereRaw("id_user = AES_ENCRYPT(?, 'nur')", [$request->username])
             ->selectRaw("AES_DECRYPT(id_user, 'nur') as id_user, AES_DECRYPT(password, 'windi') as password, dokter.kd_sps")
             ->first();
+
         if ($cek) {
             if ($cek->password == $request->password) {
+                RateLimiter::clear($throttleKey);
                 session(['username' => $cek->id_user, 'password' => $cek->password, 'kd_poli' => $request->poli, 'kd_sps' => $cek->kd_sps]);
                 return redirect()->intended('home')
                     ->withSuccess('Signed in');
             }
         }
 
-        return back()->withErrors(['message' => 'User tidak ditemukan']);
+        RateLimiter::hit($throttleKey, 300);
+        $attemptsLeft = RateLimiter::remaining($throttleKey, 3);
+
+        return back()->withErrors([
+            'message' => "Username atau password salah. Sisa percobaan: {$attemptsLeft} kali."
+        ])->withInput($request->except('password'));
     }
```

#### Verifikasi & Uji Otomasi
Gunakan loop bash sederhana untuk menguji penguncian pada percobaan ke-4:
```bash
for i in {1..4}; do
  echo "Percobaan ke-$i:"
  curl -s -i -X POST http://localhost:8080/customlogin \
    -d "username=D001&password=salah&poli=INT" | grep -E "Username atau password salah|Akses dikunci sementara"
done
```
**Kriteria Lulus:**
- Percobaan 1, 2, 3: Mendapatkan respon gagal login disertai informasi sisa kuota percobaan.
- Percobaan 4: Langsung diblokir dengan respon `Akses dikunci sementara selama 300 detik` tanpa melakukan eksekusi query ke database.

---

### 3.2 [FE-07] Password Policy & Enkripsi Kredensial

#### Analisis Akar Masalah
Validasi password pada registrasi/update hanya membatasi `min:8` tanpa memeriksa komposisi karakter khusus, huruf besar, dan angka. Di samping itu, schema Khanza menggunakan enkripsi `AES_ENCRYPT(password, 'windi')`.

#### Spesifikasi Implementasi
1. Terapkan rule bawaan Laravel `Illuminate\Validation\Rules\Password` pada form ganti password / registrasi:
   ```php
   'password' => [
       'required',
       'string',
       Password::min(8)
           ->letters()
           ->mixedCase()
           ->numbers()
           ->symbols(),
       'confirmed'
   ]
   ```
2. Buat service helper enkripsi kompatibel Khanza `KhanzaPasswordHelper.php` untuk memastikan kompatibilitas integritas data tabel `user`:
   ```php
   namespace App\Helpers;
   use Illuminate\Support\Facades\DB;

   class KhanzaPasswordHelper
   {
       public static function encryptPassword(string $password): string
       {
           $res = DB::selectOne("SELECT AES_ENCRYPT(?, 'windi') as enc", [$password]);
           return $res->enc;
       }

       public static function verifyPassword(string $inputPassword, string $encryptedDbPassword): bool
       {
           $res = DB::selectOne("SELECT AES_DECRYPT(?, 'windi') as dec", [$encryptedDbPassword]);
           return hash_equals((string)$res->dec, $inputPassword);
       }
   }
   ```

---

## 4. Fase P2: Broken Access Control & IDOR Remediation

### 4.1 [FE-08] Menu Role-Based Access Control (RBAC) & Route Protection

#### Analisis Akar Masalah
Rute internal hanya dilindungi middleware `loginauth` yang hanya memeriksa eksistensi `session('username')`. Selain itu, rute publik seperti `/persetujuan-penolakan-tindakan` dan `/odontogram/cetak` sama sekali tidak dilindungi autentikasi maupun otorisasi.

#### Spesifikasi Implementasi
1. Buat middleware `CheckUserRole.php`:
   ```bash
   php artisan make:middleware CheckUserRole
   ```
2. Isi logika middleware `app/Http/Middleware/CheckUserRole.php`:
   ```php
   namespace App\Http\Middleware;

   use Closure;
   use Illuminate\Http\Request;

   class CheckUserRole
   {
       public function handle(Request $request, Closure $next, ...$roles)
       {
           if (!$request->session()->has('username')) {
               return redirect()->route('login');
           }

           $userRole = $request->session()->get('user_role', 'dokter');
           if (!in_array($userRole, $roles)) {
               abort(403, 'Akses Ditolak: Anda tidak memiliki hak akses ke menu ini.');
           }

           return $next($request);
       }
   }
   ```
3. Daftarkan di `$routeMiddleware` pada `app/Http/Kernel.php`:
   ```php
   'role' => \App\Http\Middleware\CheckUserRole::class,
   ```
4. Pasang proteksi pada rute-rute tak bertuan di `routes/web.php`:
   ```diff
   --- a/routes/web.php
   +++ b/routes/web.php
   @@ -254,12 +254,14 @@ Route::get('/penilaian-pre-induksi/download-pdf', [PenilaianPreInduksiController
    Route::get('/persetujuan-penolakan-tindakan', [
        App\Http\Controllers\PersetujuanPenolakanTindakan::class,
        'index',
   -])->name('persetujuan-penolakan-tindakan');
   +])->middleware(['loginauth', 'role:dokter,admin'])->name('persetujuan-penolakan-tindakan');
    
    Route::post('/persetujuan-penolakan-tindakan', [
        App\Http\Controllers\PersetujuanPenolakanTindakan::class,
        'simpan',
   -])->name('persetujuan-penolakan-tindaka.store');
   +])->middleware(['loginauth', 'role:dokter,admin'])->name('persetujuan-penolakan-tindaka.store');
   
   -Route::get('/odontogram/cetak', [OdontogramPrintController::class, 'cetak'])->name('odontogram.cetak');
   +Route::get('/odontogram/cetak', [OdontogramPrintController::class, 'cetak'])
   +    ->middleware(['loginauth', 'role:dokter,admin,petugas'])
   +    ->name('odontogram.cetak');
   ```

---

### 4.2 [FE-09] Resource Ownership Check (Anti-IDOR) pada Clinical Controllers

#### Analisis Akar Masalah
Controller klinis seperti `KonsultasiMedikController::jawaban` mengambil data rekam medis hanya berdasarkan parameter ID dari URL (`$no_permintaan`) tanpa memverifikasi apakah akun dokter yang sedang aktif dalam sesi (`session('username')`) merupakan dokter pengirim atau dokter yang dituju.

#### Spesifikasi Implementasi
1. Terapkan pattern pemeriksaan kepemilikan data (*Data Ownership Verification*).
2. Periksa apakah dokter saat ini berhak atas data tersebut (`kd_dokter == session('username')` atau `kd_dokter_dikonsuli == session('username')`).
3. Tolak request dengan HTTP `403 Forbidden` jika pengguna mencoba mengakses ID milik dokter/pasien lain.

#### Code Diff Patch di `KonsultasiMedikController.php`
```diff
--- a/app/Http/Controllers/KonsultasiMedikController.php
+++ b/app/Http/Controllers/KonsultasiMedikController.php
@@ -14,8 +14,19 @@ class KonsultasiMedikController extends Controller
     public function jawaban($no_permintaan)
     {
         $konsultasi = KonsultasiMedik::with('dokter')->find($no_permintaan);
+        if (!$konsultasi) {
+            abort(404, 'Data konsultasi tidak ditemukan.');
+        }
+
+        $currentDoctor = session('username');
+        if ($konsultasi->kd_dokter !== $currentDoctor && $konsultasi->kd_dokter_dikonsuli !== $currentDoctor) {
+            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang atas data konsultasi ini.');
+        }
+
         $jawaban = JawabanKonsultasiMedik::find($no_permintaan);
         return view('konsultasi.jawab', compact('konsultasi', 'jawaban'));
     }
```

#### Pengujian Anti-IDOR
1. Login sebagai Dokter A (NIP: `D001`).
2. Buka URL `/konsultasi/jawaban/{no_permintaan_milik_dokter_B}`.
3. **Kriteria Lulus:** Sistem menampilkan layar `403 Forbidden - Akses Ditolak`, bukan formulir konsultasi milik Dokter B.

---

## 5. Fase P3: Dependensi & Infrastruktur Server (BE-01 s/d BE-05)

### 5.1 [FE-10] Perbaikan Dependensi Pustaka Frontend & Backend

#### Analisis Akar Masalah
Audit mendeteksi 18 vulnerabilitas paket NPM (termasuk Webpack Critical, Axios High, Lodash High) dan Laravel 9 EOL.

#### Langkah Remediasi
1. Jalankan perbaikan otomatis dependensi NPM:
   ```bash
   npm audit fix
   ```
2. Jika paket Webpack dan Vite konflik versi, perbarui secara selektif pada `package.json`:
   ```bash
   npm install --save-dev axios@^1.7.4 lodash@^4.17.21
   ```
3. Lakukan pengujian build frontend untuk memastikan asset pipeline tetap stabil:
   ```bash
   npm run build
   ```

---

### 5.2 [BE-01] Standarisasi Antivirus & Pipeline Pemindaian File

#### Analisis Akar Masalah
Server belum memiliki dokumentasi endpoint antivirus dan kontainer belum memindai upload dokumen medis.

#### Langkah Remediasi
1. **Host OS Server:** Pasang dan aktifkan ClamAV daemon pada host Linux:
   ```bash
   sudo apt-get update && sudo apt-get install -y clamav clamav-daemon
   sudo systemctl enable --now clamav-daemon
   sudo freshclam
   ```
2. **Cron Auto-Scan Harian:** Buat cron job inspeksi folder upload berkas medis:
   ```bash
   0 2 * * * /usr/bin/clamscan -r -i /var/www/laravel9-edokter/storage/app/public >> /var/log/clamav-edokter.log
   ```

---

### 5.3 [BE-02] Hardening Firewall & Docker Port Binding

#### Analisis Akar Masalah
Port 8080 diekspos langsung ke interface publik (`0.0.0.0:8080`), sehingga penyerang dapat mem-bypass reverse proxy SSL/TLS.

#### Langkah Remediasi
1. Ubah binding port pada `docker-compose.prod.yml` agar hanya mendengarkan pada interface lokal (`127.0.0.1`):
```diff
--- a/docker-compose.prod.yml
+++ b/docker-compose.prod.yml
@@ -8,7 +8,7 @@ services:
     image: edokter/frankenphp:latest
     container_name: edokter-frankenphp
     ports:
-      - "8080:8080"
+      - "127.0.0.1:8080:8080"
```
2. Aktifkan firewall UFW pada host Linux, tutup semua port selain HTTPS 443 dan SSH berizin:
   ```bash
   sudo ufw default deny incoming
   sudo ufw default allow outgoing
   sudo ufw allow 22/tcp comment 'SSH restricted'
   sudo ufw allow 443/tcp comment 'HTTPS Production BPJS Bridging'
   sudo ufw enable
   ```

---

### 5.4 [BE-03 & BE-04] Pengetatan TLS 1.2+ & Security Response Headers di Caddy

#### Analisis Akar Masalah
Caddyfile beroperasi pada port HTTP polos `:8080` dengan `auto_https disable_certs` tanpa deklarasi TLS protokol serta ketiadaan security response headers (HSTS, X-Frame-Options, CSP).

#### Spesifikasi Implementasi
1. Terapkan protokol TLS minimal TLS 1.2 (`protocols tls1.2 tls1.3`).
2. Pasang Security Headers standar industri:
   - `Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"`
   - `X-Frame-Options "SAMEORIGIN"`
   - `X-Content-Type-Options "nosniff"`
   - `X-XSS-Protection "1; mode=block"`
   - `Referrer-Policy "strict-origin-when-cross-origin"`
3. Pin image FrankenPHP di `Dockerfile.frankenphp` ke versi tag rilis stabil (bukan unpinned floating tag).

#### Patch Konfigurasi `Caddyfile.frankenphp`
```diff
--- a/Caddyfile.frankenphp
+++ b/Caddyfile.frankenphp
@@ -1,13 +1,24 @@
 {
-	# Global options
-	auto_https disable_certs
+	# Force modern TLS
+	servers {
+		protocols tls1.2 tls1.3
+	}
 }
 
-:8080 {
+simrs.faskes.id:443 {
 	root * /app/public
 	encode zstd gzip
 	php_server
 
+	# Security Response Headers
+	header {
+		Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
+		X-Frame-Options "SAMEORIGIN"
+		X-Content-Type-Options "nosniff"
+		X-XSS-Protection "1; mode=block"
+		Referrer-Policy "strict-origin-when-cross-origin"
+	}
+
 	# Static assets caching
 	@static {
```

#### Pin Image di `Dockerfile.frankenphp`
```diff
--- a/Dockerfile.frankenphp
+++ b/Dockerfile.frankenphp
@@ -7,4 +7,4 @@ COPY composer.json composer.lock ./
 RUN composer install --no-interaction --no-scripts --no-progress --prefer-dist --ignore-platform-reqs
 
-# -------- Base FrankenPHP image --------
-FROM ghcr.io/dunglas/frankenphp:1-php8.3-bookworm AS base
+# Pin spesifik release version untuk kepatuhan BE-04
+FROM ghcr.io/dunglas/frankenphp:1.4.4-php8.3-bookworm AS base
```

---

### 5.5 [BE-05] Roadmap Upgrade Siklus Hidup Framework Laravel 9 -> Laravel 10/11

#### Analisis Akar Masalah
Laravel 9 mencapai End-of-Life (EOL) pada Februari 2024. Walaupun aplikasi saat ini berjalan stabil di PHP 8.3, peningkatan versi framework diperlukan untuk kepatuhan audit BPJS jangka panjang.

#### Roadmap Tahapan Upgrade
1. **Tahap 1 (Bulan 1): Persiapan Test Suite**
   - Buat automated unit & feature tests untuk rute-rute utama poliklinik dan IGD.
   - Verifikasi kompatibilitas library third-party (`maatwebsite/excel`, `livewire/livewire`).
2. **Tahap 2 (Bulan 2): Migrasi Laravel 10**
   - Update `composer.json`: `"laravel/framework": "^10.0"`.
   - Update type hint return types pada controller dan model sesuai standar Laravel 10.
   - Jalankan `composer update` dan test suite lokal.
3. **Tahap 3 (Bulan 3): Migrasi Laravel 11 LTS**
   - Bersihkan konfigurasi kernel lama mengadopsi struktur `bootstrap/app.php` Laravel 11.
   - Deployment bertahap ke server staging faskes sebelum promosi ke production.

---

## 6. Checklist Verifikasi Pasca-Remediasi

Gunakan tabel checklist ini untuk memastikan seluruh 15 kontrol telah berstatus **PASS** sebelum mengajukan audit ulang ke BPJS Kesehatan:

| ID Kontrol | Aspek | Metode Verifikasi | Ekspektasi Hasil | Status Verifikasi |
|:---:|---|---|---|:---:|
| **FE-01** | XSS Protection | Input payload `<script>` pada form EKG/Echo | Teks ter-encode / sanitasi aman | [ ] PASS |
| **FE-02** | SQL Injection | Input `' OR '1'='1' -- ` pada username login | Gagal login tanpa SQL error | [ ] PASS |
| **FE-03** | Brute Force Protection | Automated curl 5x berturut-turut | Request 4+ tertolak rate limiter | [ ] PASS |
| **FE-04** | Temporary Lockout 3x | 3x salah login berturut-turut | Lockout 300 detik aktif | [ ] PASS |
| **FE-05** | Warning Login Gagal | Salah login | Pesan warning muncul jelas | [ ] PASS |
| **FE-06** | Generic Login Error | Tes akun ada vs akun fiktif | Pesan persis sama (Anti-Enum) | [ ] PASS |
| **FE-07** | Password Complexity | Validasi password tanpa simbol/angka | Form menolak kredensial lemah | [ ] PASS |
| **FE-08** | Menu RBAC | Buka URL admin dari session dokter | HTTP 403 Forbidden | [ ] PASS |
| **FE-09** | Anti-IDOR | Akses konsultasi dokter lain via ID URL | HTTP 403 Forbidden | [ ] PASS |
| **FE-10** | Library Vulnerability | `npm audit` & `composer audit` | Zero Critical/High CVEs | [ ] PASS |
| **BE-01** | Antivirus Host | Status `systemctl status clamav-daemon` | Active (running) | [ ] PASS |
| **BE-02** | Firewall Inbound | Port scan nmap dari luar host | Hanya port 443 terbuka | [ ] PASS |
| **BE-03** | TLS Minimal 1.2 | `testssl.sh` / `curl --tlsv1.1` | Koneksi ditolak jika < TLS 1.2 | [ ] PASS |
| **BE-04** | Web Server Hardening | Header inspect via `curl -I https://...` | HSTS, X-Frame-Options terpasang | [ ] PASS |
| **BE-05** | Framework Support | Cek milestone upgrade ke L10/L11 | Jadwal maintenance disetujui | [ ] PASS |

---

## 7. Rencana Rollback (Contingency Procedure)

Jika terjadi kendala saat penerapan patch di server produksi:
1. **Git Rollback:** `git revert <commit-hash> --no-edit && git push origin main`
2. **Container Rollback:**
   ```bash
   docker-compose -f docker-compose.prod.yml down
   docker tag edokter/frankenphp:backup-pre-remediation edokter/frankenphp:latest
   docker-compose -f docker-compose.prod.yml up -d
   ```
3. **Database Check:** Pastikan struktur data Khanza tidak terdampak karena seluruh patch hanya beroperasi pada layer kode aplikasi Laravel dan server reverse proxy.
