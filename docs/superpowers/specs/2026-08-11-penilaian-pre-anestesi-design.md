# Penilaian Pre Anestesi Module Design Spec

**Date**: 2026-08-11  
**Module**: `penilaian-pre-anestesi` (Pre-Anesthesia Assessment)  
**Target Files**:
- `app/Http/Livewire/Component/PenilaianPreAnestesi.php` (New Livewire Component)
- `resources/views/livewire/component/penilaian-pre-anestesi.blade.php` (New Blade View)
- `resources/views/ralan/pemeriksaan-ralan.blade.php` (Updated Ralan Page)
- `resources/views/ranap/pemeriksaan-ranap.blade.php` (Updated Ranap Page)

---

## 1. Requirement & Database Schema

The component manages pre-anesthesia medical assessments for outpatient (Ralan) and inpatient (Ranap) medical records using table `penilaian_pre_anestesi`:

```sql
CREATE TABLE `penilaian_pre_anestesi` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  `tanggal_operasi` datetime DEFAULT NULL,
  `diagnosa` varchar(100) DEFAULT NULL,
  `rencana_tindakan` varchar(100) DEFAULT NULL,
  `tb` varchar(5) NOT NULL DEFAULT '',
  `bb` varchar(5) NOT NULL DEFAULT '',
  `td` varchar(8) NOT NULL DEFAULT '',
  `io2` varchar(5) NOT NULL,
  `nadi` varchar(5) NOT NULL DEFAULT '',
  `pernapasan` varchar(5) NOT NULL,
  `suhu` varchar(5) NOT NULL DEFAULT '',
  `fisik_cardiovasculer` varchar(100) DEFAULT NULL,
  `fisik_paru` varchar(100) DEFAULT NULL,
  `fisik_abdomen` varchar(100) DEFAULT NULL,
  `fisik_extrimitas` varchar(100) DEFAULT NULL,
  `fisik_endokrin` varchar(100) DEFAULT NULL,
  `fisik_ginjal` varchar(100) DEFAULT NULL,
  `fisik_obatobatan` varchar(100) DEFAULT NULL,
  `fisik_laborat` varchar(100) DEFAULT NULL,
  `fisik_penunjang` varchar(100) DEFAULT NULL,
  `riwayat_penyakit_alergiobat` varchar(50) DEFAULT NULL,
  `riwayat_penyakit_alergilainnya` varchar(50) DEFAULT NULL,
  `riwayat_penyakit_terapi` varchar(100) DEFAULT NULL,
  `riwayat_kebiasaan_merokok` enum('Tidak','Ya') NOT NULL,
  `riwayat_kebiasaan_ket_merokok` varchar(5) NOT NULL,
  `riwayat_kebiasaan_alkohol` enum('Tidak','Ya') NOT NULL,
  `riwayat_kebiasaan_ket_alkohol` varchar(5) NOT NULL,
  `riwayat_kebiasaan_obat` enum('-','Obat Obatan','Vitamin','Jamu Jamuan') NOT NULL,
  `riwayat_kebiasaan_ket_obat` varchar(100) NOT NULL,
  `riwayat_medis_cardiovasculer` varchar(100) DEFAULT NULL,
  `riwayat_medis_respiratory` varchar(100) DEFAULT NULL,
  `riwayat_medis_endocrine` varchar(100) DEFAULT NULL,
  `riwayat_medis_lainnya` varchar(100) DEFAULT NULL,
  `asa` enum('1','2','3','4','5','E') DEFAULT NULL,
  `puasa` datetime DEFAULT NULL,
  `rencana_anestesi` enum('GA','RA Spinal','RA Epidural','RA Combined','Blok Syaraf') DEFAULT NULL,
  `rencana_perawatan` varchar(40) DEFAULT NULL,
  `catatan_khusus` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`no_rawat`,`tanggal`) USING BTREE,
  KEY `kd_dokter` (`kd_dokter`) USING BTREE,
  CONSTRAINT `penilaian_pre_anestesi_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `penilaian_pre_anestesi_ibfk_2` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
```

---

## 2. Component Architecture & Auto-Fill Logic

### 2.1 Livewire Component (`App\Http\Livewire\Component\PenilaianPreAnestesi`)
- **Properties**:
  - `$no_rawat`, `$no_rm`, `$kd_dokter`
  - Form Fields:
    - `$tanggal_operasi`, `$diagnosa`, `$rencana_tindakan`
    - Vital signs: `$tb`, `$bb`, `$td`, `$io2`, `$nadi`, `$pernapasan`, `$suhu`
    - Physical exam: `$fisik_cardiovasculer`, `$fisik_paru`, `$fisik_abdomen`, `$fisik_extrimitas`, `$fisik_endokrin`, `$fisik_ginjal`, `$fisik_obatobatan`, `$fisik_laborat`, `$fisik_penunjang`
    - Medical history & habits: `$riwayat_penyakit_alergiobat`, `$riwayat_penyakit_alergilainnya`, `$riwayat_penyakit_terapi`, `$riwayat_kebiasaan_merokok` (default `'Tidak'`), `$riwayat_kebiasaan_ket_merokok`, `$riwayat_kebiasaan_alkohol` (default `'Tidak'`), `$riwayat_kebiasaan_ket_alkohol`, `$riwayat_kebiasaan_obat` (default `'-'`), `$riwayat_kebiasaan_ket_obat`
    - Medical conditions: `$riwayat_medis_cardiovasculer`, `$riwayat_medis_respiratory`, `$riwayat_medis_endocrine`, `$riwayat_medis_lainnya`
    - Assessment & Plan: `$asa`, `$puasa`, `$rencana_anestesi`, `$rencana_perawatan`, `$catatan_khusus`
  - Component state: `$data`, `$modeEdit` (bool), `$tanggalEdit` (string/datetime)
- **Traits**: `LivewireAlert`
- **Auto-Fill Logic**:
  - When initializing/resetting inputs, fetch latest record from `pemeriksaan_ralan` or `pemeriksaan_ranap` for `$no_rawat`.
  - Populate initial values for `$tb`, `$bb`, `$td`, `$nadi`, `$pernapasan`, `$suhu`, `$io2` (SpO2/saturasi) if available.
- **Methods**:
  - `mount($noRawat, $noRm = null)`: Set parameters, doctor ID (`session('username')`), load data and autofill vital signs.
  - `getData()`: Query `penilaian_pre_anestesi` leftJoin `dokter` where `no_rawat` = `$no_rawat` ordered by `tanggal desc`.
  - `simpan()`: Validate inputs and insert into DB.
  - `edit($tanggal)`: Load record into properties and set `$modeEdit = true`.
  - `update()`: Update record by `no_rawat` and `tanggal`.
  - `confirmHapus($tanggal)` & `hapus()`: Confirm and delete record if authorized.
  - `resetInput()`: Clear state, reset defaults, re-autofill vital signs if not in edit mode.

### 2.2 Blade View (`resources/views/livewire/component/penilaian-pre-anestesi.blade.php`)
- Form grouped into clean sections (Header & Tanggal Operasi, Vital Signs, Physical Exam, History & Habits, Medical Conditions, Anesthesia Plan).
- History table displaying past assessments with doctor name, assessment date, operation date, ASA, anesthesia plan, and action buttons (Edit/Delete).

### 2.3 Views Integration
- Embed AdminLTE card in `resources/views/ralan/pemeriksaan-ralan.blade.php`:
  ```blade
  <x-adminlte-card title="Penilaian Pre Anestesi" icon="fas fa-syringe" theme="info" maximizable collapsible="collapsed">
      <livewire:component.penilaian-pre-anestesi :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" :key="'penilaian-pre-anestesi-ralan-'.request()->get('no_rawat')" />
  </x-adminlte-card>
  ```
- Embed AdminLTE card in `resources/views/ranap/pemeriksaan-ranap.blade.php`:
  ```blade
  <x-adminlte-card title="Penilaian Pre Anestesi" icon="fas fa-syringe" theme="info" maximizable collapsible="collapsed">
      <livewire:component.penilaian-pre-anestesi :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" :key="'penilaian-pre-anestesi-ranap-'.request()->get('no_rawat')" />
  </x-adminlte-card>
  ```

---

## 3. Validation & Authorization Rules

- **Validation Rules**:
  - `tanggal_operasi`, `puasa`: `nullable|date`
  - `diagnosa`, `rencana_tindakan`: `nullable|string|max:100`
  - `tb`, `bb`, `io2`, `nadi`, `pernapasan`, `suhu`: `nullable|string|max:5`
  - `td`: `nullable|string|max:8`
  - Physical exam fields (`fisik_*`): `nullable|string|max:100`
  - `riwayat_penyakit_alergiobat`, `riwayat_penyakit_alergilainnya`: `nullable|string|max:50`
  - `riwayat_penyakit_terapi`: `nullable|string|max:100`
  - `riwayat_kebiasaan_merokok`: `required|in:Tidak,Ya`
  - `riwayat_kebiasaan_ket_merokok`, `riwayat_kebiasaan_ket_alkohol`: `nullable|string|max:5`
  - `riwayat_kebiasaan_alkohol`: `required|in:Tidak,Ya`
  - `riwayat_kebiasaan_obat`: `required|in:-,Obat Obatan,Vitamin,Jamu Jamuan`
  - `riwayat_kebiasaan_ket_obat`: `nullable|string|max:100`
  - Medical history fields (`riwayat_medis_*`): `nullable|string|max:100`
  - `asa`: `nullable|in:1,2,3,4,5,E`
  - `rencana_anestesi`: `nullable|in:GA,RA Spinal,RA Epidural,RA Combined,Blok Syaraf`
  - `rencana_perawatan`: `nullable|string|max:40`
  - `catatan_khusus`: `nullable|string|max:100`
- **Access Control**:
  - Edit/Delete restricted to `$record->kd_dokter == session('username')`. Unauthorized actions display alert *"Anda tidak memiliki akses untuk mengubah/menghapus data ini"*.

---

## 4. Verification Plan

1. **Syntax Validation**: `php -l app/Http/Livewire/Component/PenilaianPreAnestesi.php`
2. **View Cache Test**: `php artisan view:cache`
3. **Git Status & Commit**: Clean workspace and verify commit.
