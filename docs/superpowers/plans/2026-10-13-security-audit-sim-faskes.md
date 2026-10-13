# Security Code Audit & Remediation Plan SIM Faskes Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menjalankan audit menyeluruh terhadap 15 kontrol keamanan bridging BPJS Kesehatan (`SPPTI-SEC-FORM-001`), menghasilkan dokumen laporan audit terisi lengkap beserta bukti eviden teknis, serta menyusun panduan rencana perbaikan (remediation plan) kode.

**Architecture:** Evaluasi statis dan runtime berbasis repository terhadap layer Frontend/Aplikasi (FE-01 s/d FE-10) dan Backend/Infrastruktur (BE-01 s/d BE-05). Hasil evaluasi dituangkan ke dokumen laporan audit formal dan dokumen rencana remediasi patch bertahap (P0 s/d P3).

**Tech Stack:** Laravel 9.19, PHP 8.2/8.3, FrankenPHP / Caddy, MariaDB/MySQL, Composer, NPM, Bash/CLI audit tools.

## Global Constraints

- Standar kepatuhan merujuk langsung ke dokumen template `docs/Template_Security_Self_Assessment_SIM_Faskes.md` (SPPTI-SEC-FORM-001 v1.1).
- Template asli `docs/Template_Security_Self_Assessment_SIM_Faskes.md` tidak boleh diubah/ditimpa.
- Output laporan audit wajib disimpan di `docs/Security_Self_Assessment_SIM_Faskes_Audit_Report.md`.
- Setiap eviden item `FE-*` harus menyertakan lokasi file, baris kode riil, atau hasil eksekusi terminal.
- Rencana perbaikan harus mempertahankan kompatibilitas schema database Khanza (termasuk fungsi enkripsi `AES_ENCRYPT` dan `AES_DECRYPT`).

---

### Task 1: Audit Frontend & Application Controls (FE-01 s/d FE-06)

**Files:**
- Read: `app/Http/Controllers/LoginController.php`
- Read: `routes/web.php`
- Read: `resources/views/auth/login.blade.php`
- Read: `app/Http/Kernel.php`

**Interfaces:**
- Consumes: Codebase routing, authentication controller, request validation logic.
- Produces: Data evaluasi status dan eviden untuk FE-01 (XSS), FE-02 (SQL Injection), FE-03 (Brute Force Rate Limit), FE-04 (Lockout 3x), FE-05 (Warning Login), FE-06 (Pesan Generic).

- [ ] **Step 1: Audit FE-01 (XSS)**
Run inspection pada Blade view dan request parameter:
```bash
grep -rn '{!!' resources/views/ | grep -v 'csrf_field' | head -n 30
```
Catat temuan penggunaan unescaped `{!! !!}` dan penanganan input form.

- [ ] **Step 2: Audit FE-02 (SQL Injection)**
Inspeksi raw queries dan interpolasi variabel di `LoginController.php` dan controllers utama:
```bash
grep -rn 'whereRaw\|selectRaw\|DB::raw' app/Http/Controllers/LoginController.php
grep -rn '\$request->' app/Http/Controllers/ | grep 'Raw(' | head -n 30
```
Verifikasi keberadaan interpolasi string mentah seperti `whereRaw("id_user = AES_ENCRYPT('{$request->username}', 'nur')")`. Status: `NOT PASS`.

- [ ] **Step 3: Audit FE-03 & FE-04 (Rate Limiting & Lockout 3x)**
Inspeksi middleware throttle dan controller login:
```bash
grep -rn 'throttle\|RateLimiter' app/Http/Controllers/LoginController.php app/Http/Kernel.php routes/web.php
```
Verifikasi apakah ada pembatasan 3x percobaan login dan mekanisme lockout sementara. Status: `NOT PASS`.

- [ ] **Step 4: Audit FE-05 & FE-06 (Warning Login & Generic Error)**
Inspeksi respon gagal login pada `LoginController::customLogin`:
```bash
sed -n '37,49p' app/Http/Controllers/LoginController.php
```
Evaluasi pesan `Password salah` vs `User tidak ditemukan`. Verifikasi pelanggaran FE-06 (membocorkan status user). Status: `NOT PASS`.

- [ ] **Step 5: Kompilasi ringkasan eviden FE-01 s/d FE-06**
Simpan catatan baris kode dan temuan untuk dimasukkan ke laporan audit.

---

### Task 2: Audit Frontend Credentials, Access Control, and Dependencies (FE-07 s/d FE-10)

**Files:**
- Read: `app/Http/Controllers/`
- Read: `routes/web.php`
- Read: `app/Http/Middleware/`
- Read: `composer.json`
- Read: `package.json`

**Interfaces:**
- Consumes: User management controllers, role middleware, composer/npm manifests.
- Produces: Data evaluasi status dan eviden untuk FE-07 (Password Complexity), FE-08 (Menu Access Control), FE-09 (Broken Access Control / IDOR), FE-10 (Vulnerable Libraries).

- [ ] **Step 1: Audit FE-07 (Password Complexity)**
Inspeksi validasi password pada controller registrasi/update password:
```bash
grep -rn 'Rules\\Password\|min:8' app/Http/Controllers/
```
Periksa apakah ada enforcement regex / password rule untuk huruf besar, huruf kecil, angka, dan karakter khusus (`*#$!&`).

- [ ] **Step 2: Audit FE-08 (Menu Role Access Control)**
Inspeksi definisi rute di `routes/web.php` dan middleware perlindungan URL:
```bash
grep -rn "Route::middleware" routes/web.php
grep -rn "loginauth" routes/web.php | head -n 30
```
Periksa apakah rute membedakan peran dokter vs admin atau hanya memeriksa status login semata.

- [ ] **Step 3: Audit FE-09 (Broken Access Control / IDOR)**
Inspeksi controller penanganan data rekam medis/konsultasi dengan ID dinamis:
```bash
grep -rn 'jawaban(' app/Http/Controllers/KonsultasiMedikController.php -A 20
```
Verifikasi apakah ada otorisasi kepemilikan (`kd_dokter` session vs target data) saat mengakses data pasien/permintaan.

- [ ] **Step 4: Audit FE-10 (Vulnerable Libraries)**
Jalankan audit dependensi via composer dan npm:
```bash
composer audit --format=plain || true
npm audit || true
```
Catat advisory security terdeteksi pada library vendor frontend/backend.

- [ ] **Step 5: Kompilasi ringkasan eviden FE-07 s/d FE-10**
Simpan catatan audit library dan kontrol akses.

---

### Task 3: Audit Backend & Infrastructure Controls (BE-01 s/d BE-05)

**Files:**
- Read: `Dockerfile.frankenphp`
- Read: `Caddyfile.frankenphp`
- Read: `docker-compose.yml`
- Read: `docker-compose.prod.yml`
- Read: `composer.json`

**Interfaces:**
- Consumes: Containerization config, web server config, runtime environment declarations.
- Produces: Data evaluasi status dan eviden untuk BE-01 (Antivirus), BE-02 (Firewall/HTTPS Port), BE-03 (TLS 1.2+), BE-04 (Web Server), BE-05 (Framework & Runtime).

- [ ] **Step 1: Audit BE-01 (Antivirus)**
Dokumentasikan standar kepatuhan antivirus server host production (ClamAV / EDR endpoint security pada host OS).

- [ ] **Step 2: Audit BE-02 (Firewall & Port Inbound)**
Inspeksi pemetaan port pada file docker-compose:
```bash
grep -rn 'ports:' docker-compose*.yml -A 4
```
Verifikasi apakah port selain 443 (misal port 80 HTTP biasa atau 3306 MySQL) diekspos ke publik atau dibatasi reverse proxy.

- [ ] **Step 3: Audit BE-03 (Versi TLS Minimal 1.2)**
Inspeksi konfigurasi TLS pada Caddy/FrankenPHP:
```bash
cat Caddyfile.frankenphp
```
Evaluasi pengaturan TLS Caddy (secara default Caddy v2 menegakkan TLS 1.2 & 1.3, namun butuh eksplisit direktif jika ingin auditable).

- [ ] **Step 4: Audit BE-04 & BE-05 (Web Server & Framework Version)**
Inspeksi versi base image di `Dockerfile.frankenphp` dan versi framework di `composer.json`:
```bash
grep 'FROM' Dockerfile.frankenphp
grep '"laravel/framework"' composer.json
php -v
```
Verifikasi status keamanan versi FrankenPHP, PHP 8.x, dan Laravel 9.19.

---

### Task 4: Generate Official Audit Report Document

**Files:**
- Create: `docs/Security_Self_Assessment_SIM_Faskes_Audit_Report.md`

**Interfaces:**
- Consumes: Temuan dan eviden riil dari Task 1, Task 2, dan Task 3.
- Produces: Dokumen laporan resmi `SPPTI-SEC-FORM-001` v1.1 terisi lengkap.

- [ ] **Step 1: Buat file laporan audit lengkap**
Tulis file `docs/Security_Self_Assessment_SIM_Faskes_Audit_Report.md` mencakup:
  - Header Informasi Umum Faskes & Platform
  - Rekapitulasi Status PASS / NOT PASS (Executive Summary)
  - Tabel 5.1 Frontend (FE-01 s/d FE-10) lengkap dengan eviden baris kode riil
  - Tabel 5.2 Backend (BE-01 s/d BE-05) lengkap dengan eviden konfigurasi
  - Kesimpulan Kelayakan Integrasi (Status: Tidak Layak sampai item NOT PASS dimitigasi)

- [ ] **Step 2: Verifikasi format dan kelengkapan dokumen laporan**
Periksa bahwa tidak ada placeholder `[DIISI]` yang tertinggal di bagian pengisian Faskes.

- [ ] **Step 3: Commit dokumen laporan audit ke git**
```bash
git add docs/Security_Self_Assessment_SIM_Faskes_Audit_Report.md
git commit -m "docs: add completed security self-assessment audit report"
```

---

### Task 5: Compile Technical Remediation Plan & Patch Specification

**Files:**
- Create: `docs/superpowers/specs/2026-10-13-security-remediation-patch-guide.md`

**Interfaces:**
- Consumes: Daftar item `NOT PASS` dari laporan audit (FE-02, FE-03, FE-04, FE-05, FE-06, FE-07, FE-08, FE-09, FE-10, BE-02, BE-03).
- Produces: Panduan patch teknis siap pakai lengkap dengan diff kode dan instruksi testing.

- [ ] **Step 1: Susun panduan remediasi P0 (SQL Injection & Login Message)**
Tuliskan patch perbaikan di `LoginController.php`:
  - Parameter binding untuk `AES_ENCRYPT(?, 'nur')`
  - Pesan error generik `Username atau password salah`

- [ ] **Step 2: Susun panduan remediasi P1 (Rate Limiting & Lockout 3x & Password Rule)**
Tuliskan patch perbaikan di `LoginController.php`:
  - Integrasi `RateLimiter::tooManyAttempts` & `RateLimiter::hit`
  - Pesan lockout 3x gagal login
  - Implementasi Password validation rule

- [ ] **Step 3: Susun panduan remediasi P2 & P3 (Access Control, Dependensi & TLS)**
Tuliskan panduan patch untuk middleware otorisasi, pengetatan Caddyfile TLS, dan perbaikan dependensi.

- [ ] **Step 4: Commit panduan rencana remediasi ke git**
```bash
git add docs/superpowers/specs/2026-10-13-security-remediation-patch-guide.md
git commit -m "docs: add technical security remediation patch guide"
```
