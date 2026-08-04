# Custom Anatomy Body SVG Assets & Gender Auto-Detection Design

**Date**: 2026-08-04  
**Module**: `penandaan-area-operasi` (Surgical Site Marking)  
**Target Paths**:
- `public/img/penandaan-area-operasi/body-front.svg` (Female / Default Anterior Body)
- `public/img/penandaan-area-operasi/body-back.svg` (Female / Default Posterior Body)
- `public/img/penandaan-area-operasi/body-front-male.svg` (Male Anterior Body)
- `public/img/penandaan-area-operasi/body-back-male.svg` (Male Posterior Body)
- `app/Http/Livewire/Component/PenandaanAreaOperasi.php`
- `resources/views/livewire/component/penandaan-area-operasi.blade.php`

---

## 1. Executive Summary & Intent

Replace the body SVG assets in `penandaan-area-operasi` with new vector paths drawn strictly based on the user's provided reference illustration. Furthermore, introduce automatic patient gender detection (`pasien.jk` = `'L'` for Laki-laki / Male vs `'P'` for Perempuan / Female) in the Livewire component so that male patients load male body diagrams (`body-front-male.svg` / `body-back-male.svg`) and female patients load female body diagrams (`body-front.svg` / `body-back.svg`).

---

## 2. Visual Style & SVG Specifications

### 2.1 Aesthetic Characteristics (Matching Reference Image)
- **Line Style**: Smooth curved black outline stroke (`stroke="#000000"`, `stroke-width="5"`, `stroke-linecap="round"`, `stroke-linejoin="round"`), white fill (`fill="#ffffff"`).
- **Dimensions**: `viewBox="0 0 600 1200"` for all 4 body SVGs.
- **Female Front View (`body-front.svg`)**:
  - Head outline with facial features (eyes with pupils, nose dot, mouth line, ears).
  - Curved neck and shoulder slope.
  - Chest contour with breasts and nipples.
  - Umbilicus (navel dot) and pubic/groin V-crease.
  - Flared arms in anatomical position (palms facing forward).
  - Curving waist & hips, legs tapering to ankles and 5 toes.
- **Female Back View (`body-back.svg`)**:
  - Head back contour and neck.
  - Spine line.
  - Elbow creases.
  - Gluteal fold (buttocks curve).
  - Popliteal creases (back of knees) and heel/Achilles tendon lines.
- **Male Front View (`body-front-male.svg`)**:
  - Broad shoulder slope, chest pectoral outline without breast curves.
  - Umbilicus, waist, groin V-crease, straight thigh and knee contours.
- **Male Back View (`body-back-male.svg`)**:
  - Broad upper back/scapula contours, spine line, gluteal fold, popliteal creases, heels.

### 2.2 Pin Counter Compatibility
All strokes use `#000000` (Red channel = 0) on `#ffffff` canvas, maintaining 100% compatibility with `isPinPixel` marker detector (`R > 150`, `G < 115`, `B < 125`).

---

## 3. Component & Livewire Integration

### 3.1 Livewire Controller (`PenandaanAreaOperasi.php`)
- Query `$jenisKelamin` from `pasien.jk` by joining `reg_periksa` and `pasien`:
  ```php
  $visit = DB::table('reg_periksa')
      ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
      ->where('reg_periksa.no_rawat', $this->noRawat)
      ->select('pasien.jk')
      ->first();
  $this->jenisKelamin = $visit->jk ?? 'P';
  ```

### 3.2 Blade Template & Javascript (`penandaan-area-operasi.blade.php`)
- Dynamically pass `$jenisKelamin` to JavaScript `diagrams` asset array:
  ```javascript
  var isMale = @json($jenisKelamin === 'L');
  var diagrams = [
      { key: 'body_front', label: 'Badan depan', asset: isMale ? '/img/penandaan-area-operasi/body-front-male.svg' : '/img/penandaan-area-operasi/body-front.svg' },
      { key: 'body_back', label: 'Badan belakang', asset: isMale ? '/img/penandaan-area-operasi/body-back-male.svg' : '/img/penandaan-area-operasi/body-back.svg' },
      // ... head, hand, foot diagrams remain unchanged
  ];
  ```

---

## 4. Verification Plan

1. **XML Syntax Validation**: Validate all 4 body SVG files using PHP `simplexml_load_file()`.
2. **Pin Marker Color Safety**: Confirm zero red channel hex matches in any SVG file.
3. **Livewire Component Syntax & Data Flow Test**: Verify PHP code compiles cleanly and passes `$jenisKelamin` property to view.
