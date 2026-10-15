---
document_type: form_template
title: Security Self-Assessment SIM Faskes
form_code: SPPTI-SEC-FORM-001
version: "1.1"
classification: Rahasia
language: id
owner: BPJS Kesehatan
purpose: Syarat implementasi integrasi sistem (bridging) antara SIM Faskes dan Web Service BPJS Kesehatan
---

# SECURITY SELF-ASSESSMENT SIM FASKES

> **Catatan konversi:** Header asli dokumen sumber memuat dua blok metadata: (1) "Kebijakan Keamanan Informasi TI", No. Dok `SPKTI-SEC-001`, versi 2.0, label *Penting*; (2) form ini, kode `SPPTI-SEC-FORM-001`, versi 1.1, label *Rahasia*. Blok pertama kemungkinan sisa header dokumen lain. Metadata form ini yang dipakai di atas.

## Cara Membaca Dokumen Ini (untuk AI Agent)

- Dokumen ini adalah **template form**. Teks `[DIISI: ...]` adalah **placeholder** yang harus diisi oleh pengguna/Faskes.
- Bagian bertanda `[DIISI OLEH PIC BPJS KESEHATAN]` **tidak boleh diisi oleh pihak Faskes**.
- Setiap item pengujian punya ID unik (`FE-xx` untuk frontend, `BE-xx` untuk backend). Gunakan ID ini saat merujuk item.
- Nilai status yang valid per item hanya: `PASS`, `NOT PASS`, atau kosong (belum dinilai).
- Item **wajib minimal**: semua item `FE-*` dan `BE-*` harus terpenuhi agar Faskes dinyatakan layak integrasi.

---

## 1. Informasi Umum

| Field | Nilai | Petunjuk Pengisian |
|---|---|---|
| PIC Security Assessment | `[DIISI: nama PIC]` | Nama PIC Integrasi Sistem Faskes atau pihak yang ditunjuk Faskes. |
| Nama SIM Faskes | `[DIISI: nama SIM, URL]` | Nama SIM Faskes (tanpa versi) dan alamat aplikasi (khusus web). Contoh: `SIM RS Sehat Selalu, www.layanan.rssehatselalu.com` |
| Versi | `[DIISI: versi]` | Format angka, mis. `1.0.12`. Jika tidak tersedia, ganti dengan tanggal *build/compile/release*. |
| Platform | `[DIISI: teknologi]` | Nama teknologi (*Application Server, Programming Language, Framework*) jika diketahui. Contoh: Apache, IIS, PHP, ASP.NET, Java, Android, CodeIgniter. |
| Tanggal Pengujian | `[DIISI: DD/MM/YYYY]` | Format `DD/MM/YYYY`. |
| Distribusi Dokumen | Rumah Sakit `[DIISI: nama Faskes]`; BPJS Kesehatan KC Surakarta | Isi nama Faskes. |

---

## 2. Tujuan

*Security Self-Assessment* ini dilakukan dalam rangka implementasi integrasi sistem (*Bridging*) antara SIM Faskes dengan Web Service milik BPJS Kesehatan.

---

## 3. Ruang Lingkup dan Metode Pengujian

**Ruang lingkup** — `[DIISI]`, mencakup antara lain:

- Apakah yang diuji hanya aplikasi saja, atau termasuk infrastruktur (khusus aplikasi web atau client-server).
- Alamat web yang digunakan (jika ada).
- Jaringan internal/eksternal (khusus aplikasi web atau client-server).
- User yang digunakan (jika ada).
- Batasan lain, mis. jika hanya menguji fungsi/menu tertentu, harus dituliskan.

**Metode pengujian** — `[DIISI]`, bebas. Jika mengacu pada metodologi tertentu, tuliskan. Contoh: *OWASP Web Application Penetration Testing Methodology*.

---

## 4. Tools yang Digunakan

| Tool | Pemanfaatan |
|---|---|
| `[DIISI: nama publisher + versi jika tersedia]` | `[DIISI: tujuan penggunaan]` |

Contoh Tool: IBM Security AppScan Standard 9.0.3.3 / BurpSuite / Wappalyzer
Contoh Pemanfaatan: digunakan untuk *scanning vulnerability* terhadap aplikasi web.

---

## 5. Hasil Pengujian

### Format Pengisian Per Item

| Kolom | Diisi oleh | Isi |
|---|---|---|
| Aspek Pengujian | (sudah terisi) | Kriteria yang harus dipenuhi. |
| Eviden | Faskes | Screenshot hasil setiap tahap/proses; atau konfirmasi hasil scanning; atau temuan manual; atau URL referensi yang menjelaskan celah keamanan yang ditemukan. |
| Checklist | **PIC BPJS Kesehatan** | `PASS` atau `NOT PASS`. |

### 5.1 Frontend: Aspek Minimal yang Harus Dipenuhi SIM Faskes

| ID | Aspek Pengujian | Kategori | Eviden | Checklist `[DIISI OLEH PIC BPJS KESEHATAN]` |
|---|---|---|---|---|
| FE-01 | Memastikan tidak ada celah XSS (*cross site scripting*) pada setiap form inputan. | Input validation | `[DIISI]` | ☐ PASS ☐ NOT PASS |
| FE-02 | Memastikan tidak ada celah SQL Injection pada setiap form inputan. | Input validation | `[DIISI]` | ☐ PASS ☐ NOT PASS |
| FE-03 | Terdapat mekanisme untuk membatasi upaya login berkali-kali (mis. CAPTCHA, *delay time*) dan berfungsi baik: tidak dapat di-bypass, dibaca OCR, atau di-reuse. | Autentikasi | `[DIISI]` | ☐ PASS ☐ NOT PASS |
| FE-04 | Lock out login temporer setelah 3 kali salah percobaan login. | Autentikasi | `[DIISI]` | ☐ PASS ☐ NOT PASS |
| FE-05 | Memunculkan pesan *warning* "Gagal Login". | Autentikasi | `[DIISI]` | ☐ PASS ☐ NOT PASS |
| FE-06 | Pesan *warning* "Gagal Login" tidak boleh membedakan antara username salah dan password salah. | Autentikasi | `[DIISI]` | ☐ PASS ☐ NOT PASS |
| FE-07 | Kompleksitas password minimal: panjang ≥ 8 karakter, memuat huruf besar, huruf kecil, angka, dan karakter khusus (mis. `*`, `#`, `$`, `!`, `&`). | Autentikasi | `[DIISI]` | ☐ PASS ☐ NOT PASS |
| FE-08 | Menu pada SIM Faskes hanya dapat diakses sesuai peruntukan. Mis. menu admin tidak dapat diakses petugas walaupun URL menu admin dimasukkan langsung. | Otorisasi | `[DIISI]` | ☐ PASS ☐ NOT PASS |
| FE-09 | Memastikan tidak ada celah *broken access control*: non-user tidak dapat mengakses data milik user, dan user A tidak dapat mengakses data user B. | Otorisasi | `[DIISI]` | ☐ PASS ☐ NOT PASS |
| FE-10 | Tidak menggunakan *library* yang vulnerable, mis. Bootstrap, jQuery, dan sebagainya. | Komponen | `[DIISI]` | ☐ PASS ☐ NOT PASS |

### 5.2 Backend: Aspek Minimal yang Harus Dipenuhi Server SIM Faskes

| ID | Aspek Pengujian | Kategori | Eviden | Checklist `[DIISI OLEH PIC BPJS KESEHATAN]` |
|---|---|---|---|---|
| BE-01 | Server terinstall antivirus. | Hardening server | `[DIISI]` | ☐ PASS ☐ NOT PASS |
| BE-02 | Firewall diaktifkan dan hanya menerima port HTTPS untuk inbound. | Jaringan | `[DIISI]` | ☐ PASS ☐ NOT PASS |
| BE-03 | Versi TLS minimal 1.2. | Enkripsi | `[DIISI]` | ☐ PASS ☐ NOT PASS |
| BE-04 | Versi web server yang digunakan (Apache, Nginx, IIS) tidak memiliki celah keamanan. | Komponen | `[DIISI]` | ☐ PASS ☐ NOT PASS |
| BE-05 | Versi framework/bahasa pemrograman yang digunakan tidak memiliki celah keamanan. | Komponen | `[DIISI]` | ☐ PASS ☐ NOT PASS |

> Kolom "Kategori" ditambahkan saat konversi untuk memudahkan pengelompokan; tidak ada di dokumen asli.

---

## 6. Kesimpulan `[DIISI OLEH PIC BPJS KESEHATAN]`

Kesimpulan memuat informasi apakah Faskes **Layak** atau **Tidak Layak** untuk melakukan Integrasi Sistem.

- Status: ☐ Layak ☐ Tidak Layak
- Catatan: `[DIISI]`

---

## 7. Pengesahan

**Disusun oleh**

| No. | Nama | Jabatan | Tanda Tangan | Tanggal |
|---|---|---|---|---|
| | `[DIISI]` | PIC Integrasi Sistem Faskes (RS/FKTP) | | |

**Diperiksa oleh**

| No. | Nama | Jabatan | Tanda Tangan | Tanggal |
|---|---|---|---|---|
| 1 | `[DIISI]` | PIC Integrasi Sistem (BPJS Kesehatan) | | |

**Disetujui oleh**

| No. | Nama | Jabatan | Tanda Tangan | Tanggal |
|---|---|---|---|---|
| 1 | `[DIISI]` | Pimpinan Unit Kerja minimal setingkat Kepala Cabang | | |

---

**BPJS Kesehatan Republik Indonesia**
Kantor Pusat: Jl. Letjen Suprapto Kav.20 No.14, Cempaka Putih
