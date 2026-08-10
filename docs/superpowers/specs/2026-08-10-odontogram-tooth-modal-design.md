# Design Spec: Tooth Click Input Modal for Odontogram

**Date**: 2026-08-10  
**Feature**: Tooth Click Input Modal (`odontogram.blade.php`)

---

## 🎯 Goal

When any tooth cell in the Odontogram grid chart is clicked, launch a clean Bootstrap modal (`#modalInputOdontogram`) that allows the user to immediately enter or update examination findings for that specific tooth, with quick condition selector pills, notes input, and automatic modal dismissal upon saving.

---

## 📐 Architecture & Components

### 1. Trigger & Modal Structure (`resources/views/livewire/ralan/odontogram.blade.php`)
- Clicking any tooth cell calls `openToothModal('gg_XX', 'Gigi XX')`.
- JS function `openToothModal` sets `$gigi` property in Livewire, updates modal header title, and opens modal `$('#modalInputOdontogram').modal('show')`.
- Modal view component contains:
  - Header: Title `<i class="fas fa-tooth"></i> Input Pemeriksaan <span id="modalToothTitle">Gigi XX</span>`
  - Body:
    - Quick Condition Selector Pills (`Tanggal`, `Karies`, `Akar`, `Tumpat`, `Fraktur Mahkota`)
    - Select dropdown fallback for `penyakit`
    - Textarea for `catatan`
  - Footer: Cancel button & Submit button `Simpan Pemeriksaan`

### 2. Livewire & Event Auto-close
- Upon saving via `wire:submit.prevent="save"`, Livewire emits browser event `odontogram:saved`.
- JS event listener listens for `odontogram:saved` and triggers `$('#modalInputOdontogram').modal('hide')` to smoothly close the modal.

---

## 🧪 Verification Plan

1. Verify clicking any tooth cell opens `#modalInputOdontogram`.
2. Verify modal header displays the selected tooth number.
3. Verify selecting a condition pill or option and clicking Simpan saves data into `pemeriksaan_odontogram`.
4. Verify modal automatically closes upon successful save and stats/chart refresh seamlessly.
