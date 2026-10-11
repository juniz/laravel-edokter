---
target: resources/views/components/ralan/penilaian-awal-medis-i-g-d.blade.php
total_score: 15
max_score: 40
na_heuristics: 
p0_count: 1
p1_count: 1
timestamp: 2026-10-11T03-12-35Z
slug: ponents-ralan-penilaian-awal-medis-i-g-d-blade-php
---
# Design Health Score

| # | Heuristic | Score | Key Issue |
|---|-----------|:-----:|-----------|
| 1 | Visibility of System Status | 2/4 | Tidak ada indikator umur data SOAP (terbaru 5 menit lalu vs kemarin); tidak ada triage level (P1/P2/P3 atau ESI). |
| 2 | Match System / Real World | 2/4 | Tab/file bernama "Medis IGD", namun konten adalah "Keperawatan IGD"; singkatan database (`xbab`, `kbab`, `lbak`) bocor ke UI. |
| 3 | User Control and Freedom | 2/4 | Sub-card tidak dapat di-collapse; tidak ada filter cepat untuk langsung lompat ke section kritis (TTV/Nyeri). |
| 4 | Consistency and Standards | 1/4 | Melanggar konvensi warna klinis: header merah pekat untuk Risiko Jatuh dan kuning untuk Nyeri bahkan saat skor nyeri 0. |
| 5 | Error Prevention | 1/4 | Tanda vital kritis (hipotensi, hipoksia) tanpa visual alert; alergi obat tersembunyi di dalam kolom kartu biasa. |
| 6 | Recognition Rather Than Recall | 2/4 | Rentang normal fisiologis tidak ditampilkan di samping TTV; dokter harus mengingat dan menghitung risiko manual. |
| 7 | Flexibility and Efficiency | 1/4 | Tanpa shortcut keyboard, tanpa ringkasan SBAR untuk handover rawat inap, pergantian SOAP memerlukan modal. |
| 8 | Aesthetic and Minimalist Design | 1/4 | 7 kartu bersarang dengan border 4px dan header jenuh solid warna-warni memicu alert fatigue. |
| 9 | Error Recovery | 2/4 | Pemilihan record SOAP dapat diganti via modal, namun tidak ada riwayat perbandingan antar record. |
| 10 | Help and Documentation | 1/4 | Parameter screening Morse (`berjalan_a`, `berjalan_b`, `berjalan_c`) hanya berupa huruf tanpa deskripsi klinis. |
| **Total** | | **15/40** | **Poor (Major overhaul required)** |

## Design Specificity Verdict

- **LLM Assessment**: Antarmuka menderita *generic dashboard card soup*. Untuk unit gawat darurat (IGD) yang menuntut kecepatan membaca tinggi (Primary Survey: ABCDE), tata letak memperlakukan BAB/BAK dan status pendidikan wali setara dengan GCS, pupil, dan perdarahan aktif.
- **Deterministic Scan**: Terdeteksi **7 temuan** antipattern `side-tab` (`border-left-width: 4px` pada baris 45, 126, 173, 243, 294, 334, 382). Menandakan duplikasi aksen visual berlebih bersama header kartu solid.
- **Visual Overlays**: Mode browser otomatis dilewati (Blade view component terikat sesi otentikasi Livewire dan database).

## Overall Impression

Sistem terasa seperti lemari arsip administratif alih-alih instrumen klinis IGD. Penuh noise warna, tanda vital tidak terstratifikasi, dan alergi tidak menonjol.

## What's Working

1. **Reaktivitas Alpine.js**: Pergantian record SOAP instan tanpa reload browser.
2. **Kelengkapan Medis (KARS/SNARS)**: Seluruh field pengkajian nyeri PQRST, risiko jatuh, obstetri, dan psikososial lengkap.
3. **Defensive Rendering**: Null-safe Blade directives mencegah rendering crash.

## Priority Issues

- **[P0] Polusi Warna Klinis & Alert Fatigue**: Header solid Red/Yellow/Green di setiap kartu menumpulkan respon visual dokter terhadap kondisi darurat sesungguhnya.
- **[P1] Kerancuan Identitas (Medis vs Keperawatan)**: Komponen bertitel Asesmen Medis menampilkan data pengkajian keperawatan tanpa pemisahan peran yang tegas.
- **[P2] Kebutaan Triage TTV**: TTV (Tensi, Nadi, Suhu, RR, SpO2) hanya teks statis tanpa batas normal fisiologis atau peringatan otomatis (MEWS/NEWS2).
- **[P3] Card Soup & Scroll Fatigue**: Tujuh kartu bersarang setinggi 1.500px menyulitkan pencarian informasi saat resusitasi.

## Persona Red Flags

- **Alex (Dokter IGD / Power User)**: Gagal memindai primary survey dalam 3 detik; SpO2 terbenam dalam kartu; tidak ada format handover SBAR.
- **Jordan (Perawat Triage / Baru)**: Bingung antara form medis dokter dan pengkajian perawat; singkatan `berjalan_a/b/c` membingungkan.
- **Sam (Aksesibilitas / Screen Reader)**: Struktur HTML non-semantik (row/col div); kontras teks abu-abu `text-muted` pada `bg-light` di bawah standar WCAG AA.
