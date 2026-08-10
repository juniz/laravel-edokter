# Design Spec: Odontogram Redesign & Interactive Animations

**Date**: 2026-08-10  
**Feature**: Redesign and interactive animations for Dental Examination (`odontogram.blade.php` & `Odontogram.php`)

---

## 🎯 Goal

Transform the current dental examination view (`odontogram.blade.php`) into a modern, highly interactive, and visually aesthetic Odontogram dashboard. Features include direct click selection on tooth cells, quick condition pills, CSS micro-animations (hover scaling, pulse highlights, ripple click feedback), tooltips, quadrant badges, condition counters, and enhanced history table.

---

## 📐 Key Design Features

### 1. Interactive Tooth Grid Chart
- **Quadrant Dividers**: Clear visual labels for Permanen (Dewasa) & Desidui (Anak), split into Kanan Atas (Q1/Q5), Kiri Atas (Q2/Q6), Kanan Bawah (Q4/Q8), Kiri Bawah (Q3/Q7).
- **Direct Click Selection**: Clicking any tooth cell triggers a JS event that updates Livewire property `$gigi` / select2 value, adds `.tooth-selected` class with a glowing pulse ring (`box-shadow` animation), and auto-scrolls/focuses the input form.
- **Hover Micro-Animations**: Tooth cells expand slightly on hover (`transform: scale(1.18)`), show a subtle drop-shadow glow, and display tooltips with tooth name and current status.
- **Condition Badges**: Tooth cells with existing records show colored status indicator dots or overlay badges (Hilang = Red, Karies = Orange, Sisa Akar = Dark Red, Tumpatan = Blue, Fraktur = Purple).

### 2. Summary Statistics Bar
- Quick counter badges at top of chart:
  - 🦷 Total Examined Teeth
  - 🔴 Karies / Cavity Count
  - ✖️ Hilang / Missing Count
  - 🛠️ Tumpatan / Filling Count
  - 🪵 Sisa Akar Count

### 3. Interactive Condition Quick Selectors (Pills)
- In addition to standard selects, interactive condition pills are rendered:
  - `Tanggal` (Hilang) - Badge Red
  - `Karies` - Badge Danger/Orange
  - `Akar` (Sisa Akar) - Badge Warning/Dark
  - `Tumpat` (Tumpatan) - Badge Primary/Blue
  - `Fraktur Mahkota` - Badge Purple/Secondary
- Clicking a pill automatically sets the `$penyakit` Livewire model property with a smooth active state animation.

### 4. Interactive History Table
- Styled with modern AdminLTE/Bootstrap table, condition badges, search/filter, and instant confirmation modal for deletion.

---

## 🧪 Verification Plan

1. Verify clicking any tooth cell in the chart updates `$gigi` and highlights the tooth cell visually.
2. Verify clicking any condition pill sets `$penyakit`.
3. Verify saving an assessment updates the tooth icon, counters, and history table without page reload.
4. Verify smooth CSS animations without performance lag or layout shifts.
