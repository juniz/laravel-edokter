# Penilaian Pre Induksi Module Design Spec

**Date**: 2026-08-12  
**Module**: `penilaian-pre-induksi` (Pre-Induction Assessment Form)  
**Target Files**:
- `app/Http/Livewire/Component/PenilaianPreInduksi.php` (New Livewire Component)
- `resources/views/livewire/component/penilaian-pre-induksi.blade.php` (New Blade View)
- `resources/views/ralan/pemeriksaan-ralan.blade.php` (Updated Ralan View)
- `resources/views/ranap/pemeriksaan-ranap.blade.php` (Updated Ranap View)

---

## 1. Requirement & Database Schema

The component manages pre-induction medical assessments for Outpatient (Ralan) and Inpatient (Ranap) medical records using existing table `penilaian_pre_induksi`:

```sql
CREATE TABLE `penilaian_pre_induksi` (
  `no_rawat` varchar(17) NOT NULL,
  `tanggal` datetime NOT NULL,
  `kd_dokter` varchar(20) NOT NULL,
  `tensi` varchar(8) DEFAULT NULL,
  `nadi` varchar(5) DEFAULT NULL,
  `rr` varchar(5) DEFAULT NULL,
  `suhu` varchar(5) DEFAULT NULL,
  `ekg` varchar(50) DEFAULT NULL,
  `lain_lain` varchar(50) DEFAULT NULL,
  `asesmen` enum('Sesuai Asesmen Pre Sedasi/Anestesi','Tidak Sesuai Asesmen Pre Sedasi/Anestesi') DEFAULT NULL,
  `perencanaan` varchar(300) DEFAULT NULL,
  `infus_perifier` varchar(300) DEFAULT NULL,
  `cvc` varchar(70) DEFAULT NULL,
  `posisi` enum('Supine','Lithotomi','Lateral','Prone','Perlindungan Mata','Kanan','Kiri','Lain-lain') DEFAULT NULL,
  `premedikasi` enum('Oral','IM','IV') DEFAULT NULL,
  `premedikasi_keterangan` varchar(50) DEFAULT NULL,
  `induksi` enum('Intravena','Inhalasi') DEFAULT NULL,
  `induksi_keterangan` varchar(70) DEFAULT NULL,
  `face_mask_no` varchar(20) DEFAULT NULL,
  `nasopharing_no` varchar(20) DEFAULT NULL,
  `ett_no` varchar(20) DEFAULT NULL,
  `ett_jenis` varchar(20) DEFAULT NULL,
  `ett_viksasi` varchar(25) DEFAULT NULL,
  `lma_no` varchar(20) DEFAULT NULL,
  `lma_jenis` varchar(20) DEFAULT NULL,
  `tracheostomi` varchar(60) DEFAULT NULL,
  `bronchoscopi_fiberoptik` varchar(60) DEFAULT NULL,
  `glidescopi` varchar(60) DEFAULT NULL,
  `lain_lain_tatalaksana` varchar(100) DEFAULT NULL,
  `intubasi_sesudah_tidur` enum('Ya','Tidak') DEFAULT NULL,
  `intubasi_oral` enum('Ya','Tidak') DEFAULT NULL,
  `intubasi_tracheostomi` enum('Ya','Tidak') DEFAULT NULL,
  `intubasi_keterangan` varchar(200) DEFAULT NULL,
  `sulit_ventilasi` varchar(100) DEFAULT NULL,
  `sulit_intubasi` varchar(100) DEFAULT NULL,
  `ventilasi` varchar(100) DEFAULT NULL,
  `teknik_regional_jenis` varchar(100) DEFAULT NULL,
  `teknik_regional_lokasi` varchar(40) DEFAULT NULL,
  `teknik_regional_jenis_jarum` varchar(30) DEFAULT NULL,
  `teknik_regional_kateter` enum('Ya','Tidak') DEFAULT NULL,
  `teknik_regional_kateter_viksasi` varchar(40) DEFAULT NULL,
  `teknik_regional_obat_obatan` varchar(400) DEFAULT NULL,
  `teknik_regional_komplikasi` varchar(200) DEFAULT NULL,
  `teknik_regional_hasil` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`no_rawat`,`tanggal`) USING BTREE,
  KEY `kd_dokter` (`kd_dokter`) USING BTREE,
  CONSTRAINT `penilaian_pre_induksi_ibfk_1` FOREIGN KEY (`no_rawat`) REFERENCES `reg_periksa` (`no_rawat`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `penilaian_pre_induksi_ibfk_2` FOREIGN KEY (`kd_dokter`) REFERENCES `dokter` (`kd_dokter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;
```

---

## 2. Component Architecture & State

### 2.1 Inputs & Initial Values
- `no_rawat`: Passed from parent page view.
- `no_rm`: Passed from parent page view (optional/contextual).
- `tanggal`: Auto-set to current datetime `Carbon::now()->format('Y-m-d H:i:s')` (displayed as `Y-m-d\TH:i` for `<input type="datetime-local">`).
- `kd_dokter`: Auto-set to logged-in doctor session `session()->get('username')`.

### 2.2 Vital Signs Auto-Fill
- When creating a new record (`modeEdit = false`), automatically query `pemeriksaan_ralan` or `pemeriksaan_ranap` for the latest vital signs (`tensi`, `nadi`, `rr` -> `respirasi`, `suhu`).

### 2.3 Form Sections
1. **Informasi Waktu & Dokter**: `tanggal` (datetime-local), `kd_dokter` (display doctor name or hidden).
2. **Tanda Vital & Penunjang**: `tensi`, `nadi`, `rr`, `suhu`, `ekg`, `lain_lain`.
3. **Evaluasi & Perencanaan**: `asesmen`, `perencanaan`.
4. **Akses Vaskuler & Posisi**: `infus_perifier`, `cvc`, `posisi`.
5. **Premedikasi & Induksi**: `premedikasi`, `premedikasi_keterangan`, `induksi`, `induksi_keterangan`.
6. **Tatalaksana Jalan Napas & Intubasi**: `face_mask_no`, `nasopharing_no`, `ett_no`, `ett_jenis`, `ett_viksasi`, `lma_no`, `lma_jenis`, `tracheostomi`, `bronchoscopi_fiberoptik`, `glidescopi`, `lain_lain_tatalaksana`, `intubasi_sesudah_tidur`, `intubasi_oral`, `intubasi_tracheostomi`, `intubasi_keterangan`, `sulit_ventilasi`, `sulit_intubasi`, `ventilasi`.
7. **Teknik Regional**: `teknik_regional_jenis`, `teknik_regional_lokasi`, `teknik_regional_jenis_jarum`, `teknik_regional_kateter`, `teknik_regional_kateter_viksasi`, `teknik_regional_obat_obatan`, `teknik_regional_komplikasi`, `teknik_regional_hasil`.

### 2.4 CRUD Operations
- `simpan()`: Validates and inserts new record into `penilaian_pre_induksi` table.
- `edit($tanggal)`: Loads existing record by primary key `(no_rawat, tanggal)` into state and switches `modeEdit = true`.
- `update()`: Validates and updates record in `penilaian_pre_induksi` table where `no_rawat` and `tanggal` match.
- `hapus($tanggal)`: Confirms and deletes record by composite key `(no_rawat, tanggal)`.
- `batal()`: Resets form fields and sets `modeEdit = false`.

---

## 3. Views & Layout Integration

1. **Livewire Component Blade** (`resources/views/livewire/component/penilaian-pre-induksi.blade.php`):
   - Structured Bootstrap 4 form matching AdminLTE theme.
   - History table at bottom listing previous assessments for the same `no_rawat`, with action buttons for Edit and Delete.

2. **Ralan Integration** (`resources/views/ralan/pemeriksaan-ralan.blade.php`):
   - Positioned in right column under pre-anestesi assessment as an AdminLTE card:
     ```html
     <x-adminlte-card title="Penilaian Pre Induksi" icon="fas fa-syringe" theme="info" maximizable collapsible="collapsed">
         <livewire:component.penilaian-pre-induksi :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" :key="'penilaian-pre-induksi-ralan-'.request()->get('no_rawat')" />
     </x-adminlte-card>
     ```

3. **Ranap Integration** (`resources/views/ranap/pemeriksaan-ranap.blade.php`):
   - Positioned in right column under pre-anestesi assessment as an AdminLTE card:
     ```html
     <x-adminlte-card title="Penilaian Pre Induksi" icon="fas fa-syringe" theme="info" maximizable collapsible="collapsed">
         <livewire:component.penilaian-pre-induksi :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" :key="'penilaian-pre-induksi-ranap-'.request()->get('no_rawat')" />
     </x-adminlte-card>
     ```

---

## 4. Verification Plan

1. **Syntax & Class Verification**: Ensure component class `App\Http\Livewire\Component\PenilaianPreInduksi` and view render cleanly without errors.
2. **Form Interaction**: Test inputting pre-induction fields, verifying auto-fill for vital signs, saving to DB, loading data into edit mode, updating, and deleting.
3. **View Integration**: Verify that card renders collapsible in both Ralan (`/ralan/pemeriksaan-ralan`) and Ranap (`/ranap/pemeriksaan-ranap`).
