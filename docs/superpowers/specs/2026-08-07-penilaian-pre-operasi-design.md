# Penilaian Pre Operasi Module Design Spec

**Date**: 2026-08-07  
**Module**: `penilaian-pre-operasi` (Pre-Operative Assessment)  
**Target Files**:
- `app/Http/Livewire/Component/PenilaianPreOperasi.php` (New Livewire Component)
- `resources/views/livewire/component/penilaian-pre-operasi.blade.php` (New Blade View)
- `resources/views/ralan/pemeriksaan-ralan.blade.php` (Updated Ralan Page)
- `resources/views/ranap/pemeriksaan-ranap.blade.php` (Updated Ranap Page)

---

## 1. Requirement & Database Schema

The component manages pre-operative clinical assessments for outpatient (Ralan) and inpatient (Ranap) medical records using table `penilaian_pre_operasi`:

```sql
CREATE TABLE `penilaian_pre_operasi` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  `ringkasan_klinik` varchar(500) DEFAULT NULL,
  `pemeriksaan_fisik` varchar(500) DEFAULT NULL,
  `pemeriksaan_diagnostik` varchar(500) DEFAULT NULL,
  `diagnosa_pre_operasi` varchar(500) DEFAULT NULL,
  `rencana_tindakan_bedah` varchar(500) DEFAULT NULL,
  `hal_hal_yang_perludi_persiapkan` varchar(500) DEFAULT NULL,
  `terapi_pre_operasi` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`no_rawat`,`tanggal`),
  KEY `kd_dokter` (`kd_dokter`)
);
```

---

## 2. Component Architecture & UI Specification

### 2.1 Livewire Component (`App\Http\Livewire\Component\PenilaianPreOperasi`)
- **Properties**:
  - `$no_rawat`, `$no_rm`, `$kd_dokter`
  - `$ringkasan_klinik`, `$pemeriksaan_fisik`, `$pemeriksaan_diagnostik`, `$diagnosa_pre_operasi`, `$rencana_tindakan_bedah`, `$hal_hal_yang_perludi_persiapkan`, `$terapi_pre_operasi`
  - `$data` (array of recorded assessments)
  - `$modeEdit` (boolean, default `false`)
  - `$tanggalEdit` (string/datetime identifier for edit/delete)
- **Traits**: `LivewireAlert`
- **Methods**:
  - `mount($noRawat, $noRm = null)`: Initializes properties and sets doctor ID from `session('username')`.
  - `getData()`: Fetches existing assessments for `$no_rawat` ordered by `tanggal desc` joined with `dokter.nm_dokter`.
  - `simpan()`: Validates inputs (max 500 chars) and performs DB insert or update.
  - `edit($tanggal)`: Loads record for editing if authorized.
  - `confirmHapus($tanggal)`: Shows confirm alert.
  - `hapus()`: Deletes record if authorized.
  - `resetInput()`: Clears form state and resets edit mode.

### 2.2 Views Integration
- Add collapsible AdminLTE card to `resources/views/ralan/pemeriksaan-ralan.blade.php`:
  ```html
  <x-adminlte-card title="Penilaian Pre Operasi" icon="fas fa-notes-medical" theme="info" maximizable collapsible="collapsed">
      <livewire:component.penilaian-pre-operasi :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" />
  </x-adminlte-card>
  ```
- Add collapsible AdminLTE card to `resources/views/ranap/pemeriksaan-ranap.blade.php`:
  ```html
  <x-adminlte-card title="Penilaian Pre Operasi" icon="fas fa-notes-medical" theme="info" maximizable collapsible="collapsed">
      <livewire:component.penilaian-pre-operasi :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" />
  </x-adminlte-card>
  ```

---

## 3. Authorization & Validation

- **Validation Rules**:
  - All 7 text fields: `nullable|string|max:500`
- **Access Control**:
  - Edit & Delete operations verify `$record->kd_dokter == session('username')`. Unauthorized attempts show a warning alert: *"Anda tidak memiliki akses untuk mengubah/menghapus data ini"*.

---

## 4. Verification Plan

1. **Syntax Check**: `php -l app/Http/Livewire/Component/PenilaianPreOperasi.php`
2. **View Cache Test**: `php artisan view:cache`
3. **Git Status Check**: Ensure working tree is clean.
