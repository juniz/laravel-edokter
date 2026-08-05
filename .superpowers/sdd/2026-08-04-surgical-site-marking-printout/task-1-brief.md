# Task 1 Brief: Update `PenandaanAreaOperasiController.php`

**Plan:** `docs/superpowers/plans/2026-08-04-surgical-site-marking-printout.md`  
**Report Target File:** `.superpowers/sdd/2026-08-04-surgical-site-marking-printout/task-1-report.md`

## Requirements

1. Update `app/Http/Controllers/PenandaanAreaOperasiController.php`:
   - Support `no_rawat` from request (e.g. `$request->input('no_rawat')` or route param).
   - Fetch `penandaan_area_operasi` joined with `reg_periksa`, `pasien`, `dokter`.
   - Query `setting` table for hospital information (`nama_instansi`, `alamat_instansi`, `kabupaten`, `propinsi`, `kontak`, `email`, `logo`). Fallback defaults:
     - Header Title 1: `POLRI DAERAH JAWA TIMUR`
     - Header Title 2: `BIDANG KEDOKTERAN DAN KESEHATAN`
     - Hospital Name: `RUMAH SAKIT BHAYANGKARA TK. III NGANJUK`
     - Location: `NGANJUK`
   - Calculate patient age from `tgl_lahir` or `reg_periksa` (e.g. `66 Th`).
   - Determine gender title: `$jenisKelaminTitle = ($pasien->jk === 'L') ? 'LAKI-LAKI' : 'PEREMPUAN'`.
   - Decode `gambar_penandaan_base64` into `$images` array.
   - Pass `$marking`, `$images`, `$setting`, `$umurPasien`, `$jenisKelaminTitle`, `$tanggalPenandaan` to view `prints.penandaan-area-operasi`.

2. Validate PHP syntax:
   `php -l app/Http/Controllers/PenandaanAreaOperasiController.php`

3. Commit:
   - `git add app/Http/Controllers/PenandaanAreaOperasiController.php`
   - `git commit -m "feat(penandaan-area-operasi): update controller to provide full printout data model"`

---
Write status, commit hash, and test result summary to `.superpowers/sdd/2026-08-04-surgical-site-marking-printout/task-1-report.md`.
