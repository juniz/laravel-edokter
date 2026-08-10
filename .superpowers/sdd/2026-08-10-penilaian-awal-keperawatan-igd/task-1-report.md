# Task 1 Report: Add getPenilaianAwalKeperawatanIGD query and update PenilaianAwalMedisIGD component class

## Status
DONE

## Summary of Changes
1. **`app/Http/Livewire/Ralan/PasienTabs.php`**
   - Added method `getPenilaianAwalKeperawatanIGD($noRawat)` which performs a left join between `penilaian_awal_keperawatan_igd` and `petugas` on `nip` to retrieve the initial nursing assessment record along with the nurse's name (`nama_petugas`).

2. **`app/View/Components/Ralan/PenilaianAwalMedisIGD.php`**
   - Added `$noRawat` property and constructor parameter.
   - Updated `render()` method to pass `'data' => $this->getPenilaianIGD($this->noRawat)` to the component view `components.ralan.penilaian-awal-medis-i-g-d`.
   - Added `getPenilaianIGD($noRawat)` method querying `penilaian_awal_keperawatan_igd` with left join to `petugas`.

## Verification
- Ran PHP syntax check:
  `php -l app/Http/Livewire/Ralan/PasienTabs.php app/View/Components/Ralan/PenilaianAwalMedisIGD.php` -> No syntax errors detected.
- Committed changes with message: `feat: add getPenilaianAwalKeperawatanIGD query and update PenilaianAwalMedisIGD component class`.
