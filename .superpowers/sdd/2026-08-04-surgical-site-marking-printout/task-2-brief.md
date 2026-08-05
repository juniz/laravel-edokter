# Task 2 Brief: Create A4 1:1 Print Template (`prints/penandaan-area-operasi.blade.php`)

**Plan:** `docs/superpowers/plans/2026-08-04-surgical-site-marking-printout.md`  
**Report Target File:** `.superpowers/sdd/2026-08-04-surgical-site-marking-printout/task-2-report.md`

## Requirements

1. **`resources/views/prints/penandaan-area-operasi.blade.php`**:
   - Create pixel-perfect HTML/CSS layout matching hospital reference photo 1:1.
   - `@page { size: A4 portrait; margin: 6mm 10mm; }`
   - **Header Block**:
     - Hospital Kop (Left): Logo (if available), `POLRI DAERAH JAWA TIMUR`, `BIDANG KEDOKTERAN DAN KESEHATAN`, `RUMAH SAKIT BHAYANGKARA TK. III NGANJUK`, `NGANJUK`.
     - Document Code (Right): `RM. 41 / Halaman 2` in boxed border.
   - **Form Title Banner**:
     - `FORMULIR PENANDAAN AREA OPERASI ({{ $jenisKelaminTitle }})` (Bold, centered, underlined).
   - **Patient & Procedure Metadata Grid**:
     - Left side: Barcode representation, `NO. RM`, `NAMA` (with age), `TGL. LAHIR`, `NO. RAWAT`, `PROSEDUR OPERASI`, `OPERATOR`.
   - **2x3 Combined Diagram Layout Grid**:
     - Bordered outer box matching photo.
     - **Box 1 (Top Left)**: Full Body Anterior (`Badan depan`: `Kanan` | `Kiri`) & Posterior (`Badan belakang`: `Kiri` | `Kanan`).
     - **Box 2 (Top Right)**: 2x2 Sub-grid of Head Left Profile (`Kiri`), Head Right Profile (`Kanan`), Head Front (`Kanan` | `Kiri`), Head Back (`Kiri` | `Kanan`).
     - **Box 3 (Bottom Left)**: Foot Plantar (`Plantar (posterior)`: `Kiri` | `Kanan`) & Foot Dorsal (`Dorsal (anterior)`: `Kiri` | `Kanan`).
     - **Box 4 (Bottom Right)**: Hand Palm (`Palmar (anterior)`: `Kiri` | `Kanan`) & Hand Dorsal (`Dorsal (posterior)`: `Kiri` | `Kanan`).
   - **Declaration Statement**:
     - *"Saya menyatakan bahwa lokasi operasi yang telah ditetapkan pada diagram adalah **benar***"
   - **Signatures Footer**:
     - Left: Signature line for `Pasien / Keluarga Pasien`.
     - Right: Location & Date line (`Nganjuk, {{ $tanggalPenandaan }}`), Signature line for `Dokter Operator` (`(dr. {{ $marking->nama_operator }})`).
     - Bottom Left Document Code: `VI/2018/Komite RM`.
   - **Auto-Print**:
     - `<script>window.addEventListener('load', function() { window.print(); });</script>`.

2. **Validation**:
   - Check file existence and Blade syntax.

3. **Commit**:
   - `git add resources/views/prints/penandaan-area-operasi.blade.php`
   - `git commit -m "feat(penandaan-area-operasi): create 1:1 A4 printout template matching official hospital form"`

---
Write status, commit hash, and test result summary to `.superpowers/sdd/2026-08-04-surgical-site-marking-printout/task-2-report.md`.
