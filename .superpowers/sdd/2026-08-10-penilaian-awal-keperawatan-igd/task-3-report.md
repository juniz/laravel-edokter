# Task 3 Report: Render Top Card for Penilaian Awal Keperawatan IGD

## Work Completed
- Modified `resources/views/livewire/ralan/pasien-tabs.blade.php`.
- In `@if(count($data)>0)`, added logic to iterate over `$data` visits and invoke `$this->getPenilaianAwalKeperawatanIGD($no_rawat_temp)`.
- If data is found for a visit, store `$noRawatIGD` and render `<x-ralan.penilaian-awal-medis-i-g-d :no-rawat="$noRawatIGD" />`.
- Committed changes with message: `feat: render top card for Penilaian Awal Keperawatan IGD in pasien-tabs.blade.php`.

## Status
DONE
