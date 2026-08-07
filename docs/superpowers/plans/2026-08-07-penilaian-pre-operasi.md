# Penilaian Pre Operasi Module Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Create a new Livewire component `PenilaianPreOperasi` to manage pre-operative clinical assessments (`penilaian_pre_operasi` table) and integrate it into Ralan and Ranap examination pages.

**Architecture:**
- Component: `app/Http/Livewire/Component/PenilaianPreOperasi.php`
- View: `resources/views/livewire/component/penilaian-pre-operasi.blade.php`
- Integration:
  - `resources/views/ralan/pemeriksaan-ralan.blade.php`
  - `resources/views/ranap/pemeriksaan-ranap.blade.php`

---

### Task 1: Create Livewire Component & View for Penilaian Pre Operasi

**Files:**
- Create: `app/Http/Livewire/Component/PenilaianPreOperasi.php`
- Create: `resources/views/livewire/component/penilaian-pre-operasi.blade.php`

**Interfaces:**
- Consumes: `$noRawat`, `$noRm`
- Produces: Form & History Table for `penilaian_pre_operasi`

- [ ] **Step 1: Write `app/Http/Livewire/Component/PenilaianPreOperasi.php`**

Create Livewire class extending `Livewire\Component` with `LivewireAlert`:
- Public properties: `$no_rawat`, `$no_rm`, `$kd_dokter`, `$ringkasan_klinik`, `$pemeriksaan_fisik`, `$pemeriksaan_diagnostik`, `$diagnosa_pre_operasi`, `$rencana_tindakan_bedah`, `$hal_hal_yang_perludi_persiapkan`, `$terapi_pre_operasi`, `$data = []`, `$modeEdit = false`, `$tanggalEdit`.
- Methods: `mount`, `hydrate`, `getData`, `simpan`, `edit`, `confirmHapus`, `hapus`, `resetInput`.
- Validate max 500 characters per field.
- Check doctor permission (`$record->kd_dokter == session('username')`) on edit/delete.

- [ ] **Step 2: Write `resources/views/livewire/component/penilaian-pre-operasi.blade.php`**

Create responsive AdminLTE/Bootstrap form view:
- 7 Textarea inputs (max 500 chars counter/help text).
- "Simpan" & "Batal" buttons.
- History table listing assessments with Date, Doctor Name, 7 Fields preview, Edit & Hapus action buttons.

- [ ] **Step 3: Validate PHP syntax**

Run: `php -l app/Http/Livewire/Component/PenilaianPreOperasi.php`

- [ ] **Step 4: Commit**

```bash
git add app/Http/Livewire/Component/PenilaianPreOperasi.php resources/views/livewire/component/penilaian-pre-operasi.blade.php
git commit -m "feat(penilaian-pre-operasi): create Livewire component and blade view"
```

---

### Task 2: Integrate Penilaian Pre Operasi into Ralan and Ranap Views

**Files:**
- Modify: `resources/views/ralan/pemeriksaan-ralan.blade.php`
- Modify: `resources/views/ranap/pemeriksaan-ranap.blade.php`

**Interfaces:**
- Consumes: `$no_rawat`, `$no_rm` query parameters.
- Produces: Collapsible AdminLTE cards in Ralan & Ranap pages.

- [ ] **Step 1: Update `resources/views/ralan/pemeriksaan-ralan.blade.php`**

Add collapsible card before or after Penandaan Area Operasi:
```html
<x-adminlte-card title="Penilaian Pre Operasi" icon="fas fa-notes-medical" theme="info" maximizable collapsible="collapsed">
    <livewire:component.penilaian-pre-operasi :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" />
</x-adminlte-card>
```

- [ ] **Step 2: Update `resources/views/ranap/pemeriksaan-ranap.blade.php`**

Add collapsible card before or after Penandaan Area Operasi:
```html
<x-adminlte-card title="Penilaian Pre Operasi" icon="fas fa-notes-medical" theme="info" maximizable collapsible="collapsed">
    <livewire:component.penilaian-pre-operasi :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" />
</x-adminlte-card>
```

- [ ] **Step 3: Commit**

```bash
git add resources/views/ralan/pemeriksaan-ralan.blade.php resources/views/ranap/pemeriksaan-ranap.blade.php
git commit -m "feat(penilaian-pre-operasi): embed component in ralan and ranap examination views"
```

---

### Task 3: Integration Verification & Syntax Checks

**Files:**
- Verify: `app/Http/Livewire/Component/PenilaianPreOperasi.php`
- Verify: `resources/views/livewire/component/penilaian-pre-operasi.blade.php`
- Verify: `resources/views/ralan/pemeriksaan-ralan.blade.php`
- Verify: `resources/views/ranap/pemeriksaan-ranap.blade.php`

- [ ] **Step 1: Test Blade view cache**

Run: `php artisan view:cache`

- [ ] **Step 2: Verify git status**

Run: `git status`
Expected: Clean working tree
