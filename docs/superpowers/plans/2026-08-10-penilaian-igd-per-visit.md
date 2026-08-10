# Penilaian Awal Keperawatan IGD Per-Visit Integration Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Move the rendering of Penilaian Awal Keperawatan IGD from top-level standalone placement in `pasien-tabs.blade.php` into the per-visit card loop (`@foreach($data as $row)`), allowing multiple assessment records across different visit dates/`no_rawat` to display within their respective visit history cards.

**Architecture:** Modify `resources/views/livewire/ralan/pasien-tabs.blade.php` to remove top `$noRawatIGD` render block (lines 154-170) and add per-visit check `$penilaianIGDVisit = $this->getPenilaianAwalKeperawatanIGD($no_rawat);` inside `@foreach($data as $row)` card body.

**Tech Stack:** Laravel, Livewire, Blade.

## Global Constraints

- Retain component `<x-ralan.penilaian-awal-medis-i-g-d :no-rawat="$no_rawat" />` with all sections (`theme="info"`).

---

### Task 1: Update `resources/views/livewire/ralan/pasien-tabs.blade.php` for Per-Visit Integration

**Files:**
- Modify: `resources/views/livewire/ralan/pasien-tabs.blade.php`

- [ ] **Step 1: Remove top standalone card check and render**

Remove lines 154-170 in `pasien-tabs.blade.php`:
```blade
- // Ambil data Penilaian Awal Keperawatan IGD dari kunjungan yang memiliki data
- $noRawatIGD = null;
- foreach($data as $row) {
-     $no_rawat_temp = is_array($row) ? ($row['no_rawat'] ?? null) : ($row->no_rawat ?? null);
-     if ($no_rawat_temp) {
-         $checkIGD = $this->getPenilaianAwalKeperawatanIGD($no_rawat_temp);
-         if ($checkIGD) {
-             $noRawatIGD = $no_rawat_temp;
-             break;
-         }
-     }
- }
- @endphp
- 
- @if($noRawatIGD)
-     <x-ralan.penilaian-awal-medis-i-g-d :no-rawat="$noRawatIGD" />
- @endif
```

- [ ] **Step 2: Add per-visit check and render inside `@foreach($data as $row)`**

Inside `@foreach($data as $row)` (around line 1094), query `$penilaianIGDVisit = $this->getPenilaianAwalKeperawatanIGD($no_rawat);` and render `<x-ralan.penilaian-awal-medis-i-g-d :no-rawat="$no_rawat" />` at top of card body if present:
```blade
@php
$penilaianIGDVisit = $this->getPenilaianAwalKeperawatanIGD($no_rawat);
@endphp
@if($penilaianIGDVisit)
    <x-ralan.penilaian-awal-medis-i-g-d :no-rawat="$no_rawat" />
@endif
```

- [ ] **Step 3: Commit Task 1**

```bash
git add resources/views/livewire/ralan/pasien-tabs.blade.php
git commit -m "feat: embed Penilaian Awal Keperawatan IGD into per-visit history cards"
```
