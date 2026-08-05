# Task 3 Brief: Integrate Patient Gender Auto-Detection in Component & View

**Plan:** `docs/superpowers/plans/2026-08-04-custom-anatomy-body-svgs.md`  
**Report Target File:** `.superpowers/sdd/2026-08-04-custom-anatomy-body-svgs/task-3-report.md`

## Requirements

1. **`app/Http/Livewire/Component/PenandaanAreaOperasi.php`**:
   - Declare public property `$jenisKelamin = 'P';`
   - In `mount()`, query `pasien.jk` from `reg_periksa` joined with `pasien`:
     ```php
     $pasien = DB::table('reg_periksa')
         ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
         ->where('reg_periksa.no_rawat', $this->noRawat)
         ->select('pasien.jk')
         ->first();

     if ($pasien && in_array($pasien->jk, ['L', 'P'], true)) {
         $this->jenisKelamin = $pasien->jk;
     }
     ```

2. **`resources/views/livewire/component/penandaan-area-operasi.blade.php`**:
   - In push script section, update JS `diagrams` array to dynamically load male or female body SVGs based on `@json($jenisKelamin === 'L')`:
     ```javascript
     var isMale = @json($jenisKelamin === 'L');
     var diagrams = [
         { key: 'body_front', label: 'Badan depan', asset: isMale ? '/img/penandaan-area-operasi/body-front-male.svg' : '/img/penandaan-area-operasi/body-front.svg' },
         { key: 'body_back', label: 'Badan belakang', asset: isMale ? '/img/penandaan-area-operasi/body-back-male.svg' : '/img/penandaan-area-operasi/body-back.svg' },
         { key: 'head_left_profile', label: 'Kepala profil kiri', asset: '/img/penandaan-area-operasi/head-left-profile.svg' },
         { key: 'head_right_profile', label: 'Kepala profil kanan', asset: '/img/penandaan-area-operasi/head-right-profile.svg' },
         { key: 'head_front', label: 'Kepala depan', asset: '/img/penandaan-area-operasi/head-front.svg' },
         { key: 'head_back', label: 'Kepala belakang', asset: '/img/penandaan-area-operasi/head-back.svg' },
         { key: 'hand_left_palm', label: 'Telapak tangan kiri', asset: '/img/penandaan-area-operasi/hand-left-palm.svg' },
         { key: 'hand_right_palm', label: 'Telapak tangan kanan', asset: '/img/penandaan-area-operasi/hand-right-palm.svg' },
         { key: 'hand_left_dorsal', label: 'Punggung tangan kiri', asset: '/img/penandaan-area-operasi/hand-left-dorsal.svg' },
         { key: 'hand_right_dorsal', label: 'Punggung tangan kanan', asset: '/img/penandaan-area-operasi/hand-right-dorsal.svg' },
         { key: 'foot_left_plantar', label: 'Telapak kaki kiri', asset: '/img/penandaan-area-operasi/foot-left-plantar.svg' },
         { key: 'foot_right_plantar', label: 'Telapak kaki kanan', asset: '/img/penandaan-area-operasi/foot-right-plantar.svg' },
         { key: 'foot_left_dorsal', label: 'Punggung kaki kiri', asset: '/img/penandaan-area-operasi/foot-left-dorsal.svg' },
         { key: 'foot_right_dorsal', label: 'Punggung kaki kanan', asset: '/img/penandaan-area-operasi/foot-right-dorsal.svg' }
     ];
     ```

3. **Validation**:
   - Run: `php -l app/Http/Livewire/Component/PenandaanAreaOperasi.php`

4. **Commit**:
   - `git add app/Http/Livewire/Component/PenandaanAreaOperasi.php resources/views/livewire/component/penandaan-area-operasi.blade.php`
   - `git commit -m "feat(penandaan-area-operasi): auto-detect patient gender and dynamically load male/female body SVGs"`

---
Write status, commit hash, and test result summary to `.superpowers/sdd/2026-08-04-custom-anatomy-body-svgs/task-3-report.md`.
