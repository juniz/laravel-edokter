# Design Spec: Per-Visit Integration of Penilaian Awal Keperawatan IGD

**Date**: 2026-08-10  
**Feature**: Embed Penilaian Awal Keperawatan IGD into per-visit history cards (`pasien-tabs.blade.php`)

---

## 🎯 Goal

Move the rendering of `<x-ralan.penilaian-awal-medis-i-g-d :no-rawat="$no_rawat" />` from top-level standalone placement in `pasien-tabs.blade.php` into the per-visit card loop (`@foreach($data as $row)`), allowing multiple assessment records across different visit dates/`no_rawat` to display within their respective visit history cards.

---

## 📐 Architecture & Changes (`resources/views/livewire/ralan/pasien-tabs.blade.php`)

1. **Remove Top-Level Card Rendering**: Remove lines 154-170 in `pasien-tabs.blade.php` that query `$noRawatIGD` and render `<x-ralan.penilaian-awal-medis-i-g-d>` above all visit cards.
2. **Embed in Per-Visit Loop (`@foreach($data as $row)`)**:
   - Inside `@foreach($data as $row)` (around line 1094), query `$penilaianIGDVisit = $this->getPenilaianAwalKeperawatanIGD($no_rawat);`.
   - If `$penilaianIGDVisit` exists, render `<x-ralan.penilaian-awal-medis-i-g-d :no-rawat="$no_rawat" />` inside the card body for that `$no_rawat` visit.

---

## 🧪 Verification Plan

1. Verify top standalone card for IGD is removed.
2. Verify visit history cards with matching `no_rawat` render their corresponding Penilaian Awal Keperawatan IGD assessment card inside the visit body.
3. Verify visits without IGD assessment records render normally without errors.
