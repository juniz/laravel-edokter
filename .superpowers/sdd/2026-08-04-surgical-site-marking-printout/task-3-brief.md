# Task 3 Brief: Add "Cetak" Button in Livewire View & Route Configuration

**Plan:** `docs/superpowers/plans/2026-08-04-surgical-site-marking-printout.md`  
**Report Target File:** `.superpowers/sdd/2026-08-04-surgical-site-marking-printout/task-3-report.md`

## Requirements

1. **`resources/views/livewire/component/penandaan-area-operasi.blade.php`**:
   - Add a "Cetak" button next to "Simpan" in `.penandaan-actions`:
     ```html
     <a href="{{ route('penandaan-area-operasi.cetak', ['no_rawat' => $noRawat]) }}" target="_blank" class="btn btn-info ml-2" id="cetak-penandaan" title="Cetak Formulir Penandaan Area Operasi (A4)">
         <i class="fas fa-print mr-1" aria-hidden="true"></i>
         Cetak
     </a>
     ```

2. **`routes/web.php`**:
   - Verify that `route('penandaan-area-operasi.cetak')` is correctly registered and works with `no_rawat` query parameter or path parameter.

3. **Validation**:
   - Verify route syntax and Blade view syntax.

4. **Commit**:
   - `git add resources/views/livewire/component/penandaan-area-operasi.blade.php routes/web.php`
   - `git commit -m "feat(penandaan-area-operasi): add Cetak button linking to A4 printout route"`

---
Write status, commit hash, and test result summary to `.superpowers/sdd/2026-08-04-surgical-site-marking-printout/task-3-report.md`.
