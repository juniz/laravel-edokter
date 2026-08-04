# Custom Reference Image Body SVGs & Gender Detection Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace body diagram SVG assets with custom vector paths matching the user's reference drawing (female & male body views) and implement automatic patient gender detection in the Livewire component so male patients load male body SVGs and female patients load female body SVGs.

**Architecture:** Create 4 body SVG assets in `public/img/penandaan-area-operasi/` with `viewBox="0 0 600 1200"` matching the reference image line art style. Update `PenandaanAreaOperasi.php` to fetch `pasien.jk` and pass `$jenisKelamin` to `penandaan-area-operasi.blade.php`, which dynamically updates JS `diagrams` asset URLs.

**Tech Stack:** SVG, PHP (Laravel Livewire), Blade, JavaScript, HTML5 Canvas.

## Global Constraints
- Target SVG paths:
  - `public/img/penandaan-area-operasi/body-front.svg` (Female anterior)
  - `public/img/penandaan-area-operasi/body-back.svg` (Female posterior)
  - `public/img/penandaan-area-operasi/body-front-male.svg` (Male anterior)
  - `public/img/penandaan-area-operasi/body-back-male.svg` (Male posterior)
- `viewBox="0 0 600 1200"`
- Stroke: `stroke="#000000"`, `stroke-width="5"`, `stroke-linecap="round"`, `stroke-linejoin="round"`
- Fill: `fill="#ffffff"`

---

### Task 1: Create Female Body SVGs (`body-front.svg`, `body-back.svg`) Matching Reference Image

**Files:**
- Modify: `public/img/penandaan-area-operasi/body-front.svg`
- Modify: `public/img/penandaan-area-operasi/body-back.svg`

**Interfaces:**
- Consumes: `docs/superpowers/specs/2026-08-04-custom-anatomy-body-svgs-design.md`
- Produces: Female body anterior & posterior SVGs based on reference drawing.

- [ ] **Step 1: Write `body-front.svg` (Female Anterior View)**

Write `public/img/penandaan-area-operasi/body-front.svg` with `viewBox="0 0 600 1200"` featuring clean smooth curves matching the reference drawing: head with facial features (eyes, nose dot, mouth line, ears), neck lines, chest with breast curves and nipples, umbilicus dot, groin/pubic V-line, arms in flared anatomical position, hips, thighs, knees, shins, feet.

- [ ] **Step 2: Write `body-back.svg` (Female Posterior View)**

Write `public/img/penandaan-area-operasi/body-back.svg` with `viewBox="0 0 600 1200"` featuring matching reference drawing: head back, neck, spine line, elbow crease lines, buttocks/gluteal fold curves, popliteal knee creases, heels.

- [ ] **Step 3: Validate XML syntax**

Run: `php -r 'simplexml_load_file("public/img/penandaan-area-operasi/body-front.svg") or exit(1); simplexml_load_file("public/img/penandaan-area-operasi/body-back.svg") or exit(1); echo "Female Body SVGs valid\n";'`
Expected: `Female Body SVGs valid`

- [ ] **Step 4: Commit**

```bash
git add public/img/penandaan-area-operasi/body-front.svg public/img/penandaan-area-operasi/body-back.svg
git commit -m "feat(assets): update female body front and back SVGs to match reference drawing"
```

---

### Task 2: Create Male Body SVGs (`body-front-male.svg`, `body-back-male.svg`)

**Files:**
- Create: `public/img/penandaan-area-operasi/body-front-male.svg`
- Create: `public/img/penandaan-area-operasi/body-back-male.svg`

**Interfaces:**
- Consumes: Design spec requirements for male body views.
- Produces: Male body anterior & posterior SVGs.

- [ ] **Step 1: Write `body-front-male.svg` (Male Anterior View)**

Write `public/img/penandaan-area-operasi/body-front-male.svg` with `viewBox="0 0 600 1200"` featuring broader male shoulders, pectoral chest outline, navel, waist, groin V-line, straight legs, arms, head & facial features in matching line art style.

- [ ] **Step 2: Write `body-back-male.svg` (Male Posterior View)**

Write `public/img/penandaan-area-operasi/body-back-male.svg` with `viewBox="0 0 600 1200"` featuring male upper back slope, spine line, gluteal fold, popliteal creases, heels.

- [ ] **Step 3: Validate XML syntax**

Run: `php -r 'simplexml_load_file("public/img/penandaan-area-operasi/body-front-male.svg") or exit(1); simplexml_load_file("public/img/penandaan-area-operasi/body-back-male.svg") or exit(1); echo "Male Body SVGs valid\n";'`
Expected: `Male Body SVGs valid`

- [ ] **Step 4: Commit**

```bash
git add public/img/penandaan-area-operasi/body-front-male.svg public/img/penandaan-area-operasi/body-back-male.svg
git commit -m "feat(assets): add male body front and back SVGs in matching line art style"
```

---

### Task 3: Integrate Patient Gender Auto-Detection in Component & View

**Files:**
- Modify: `app/Http/Livewire/Component/PenandaanAreaOperasi.php`
- Modify: `resources/views/livewire/component/penandaan-area-operasi.blade.php`

**Interfaces:**
- Consumes: DB query for `pasien.jk` from `reg_periksa` and `pasien`.
- Produces: Dynamic `$jenisKelamin` binding in Livewire PHP and dynamic SVG asset loading in Blade JavaScript.

- [ ] **Step 1: Update Livewire PHP Class `PenandaanAreaOperasi.php`**

Add public property `$jenisKelamin = 'P';` and populate it in `mount()` by querying `pasien.jk`:
```php
public $jenisKelamin = 'P';

public function mount(string $noRawat, string $noRm): void
{
    $this->noRawat = $noRawat;
    $this->noRm = $noRm;

    $pasien = DB::table('reg_periksa')
        ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
        ->where('reg_periksa.no_rawat', $this->noRawat)
        ->select('pasien.jk')
        ->first();

    if ($pasien && in_array($pasien->jk, ['L', 'P'], true)) {
        $this->jenisKelamin = $pasien->jk;
    }
    // ...
}
```

- [ ] **Step 2: Update Blade Template `penandaan-area-operasi.blade.php`**

Update JavaScript `diagrams` array in `penandaan-area-operasi.blade.php` to dynamically select male or female body SVG paths based on `@json($jenisKelamin === 'L')`:
```javascript
var isMale = @json($jenisKelamin === 'L');
var diagrams = [
    { key: 'body_front', label: 'Badan depan', asset: isMale ? '/img/penandaan-area-operasi/body-front-male.svg' : '/img/penandaan-area-operasi/body-front.svg' },
    { key: 'body_back', label: 'Badan belakang', asset: isMale ? '/img/penandaan-area-operasi/body-back-male.svg' : '/img/penandaan-area-operasi/body-back.svg' },
    // ... remaining diagrams unchanged
];
```

- [ ] **Step 3: Run PHP Lint / Code Check**

Run: `php -l app/Http/Livewire/Component/PenandaanAreaOperasi.php`
Expected: `No syntax errors detected in app/Http/Livewire/Component/PenandaanAreaOperasi.php`

- [ ] **Step 4: Commit**

```bash
git add app/Http/Livewire/Component/PenandaanAreaOperasi.php resources/views/livewire/component/penandaan-area-operasi.blade.php
git commit -m "feat(penandaan-area-operasi): auto-detect patient gender and dynamically load male/female body SVGs"
```

---

### Task 4: Integration Verification & Pin Safety Checks

**Files:**
- Verify: `public/img/penandaan-area-operasi/*.svg`
- Verify: `app/Http/Livewire/Component/PenandaanAreaOperasi.php`

**Interfaces:**
- Consumes: All 16 SVG files in target folder & Livewire component.
- Produces: Final verification status.

- [ ] **Step 1: Run comprehensive PHP XML parsing & pin safety validation**

Run: `php -r '$files=glob("public/img/penandaan-area-operasi/*.svg"); foreach($files as $f){ simplexml_load_file($f) or exit(1); if(preg_match("/#(?:c6283d|ff0000|e53e3e)/i", file_get_contents($f))){ exit(1); } } echo "All SVGs valid and pin-safe\n";'`
Expected: `All SVGs valid and pin-safe`

- [ ] **Step 2: Commit & Status Check**

```bash
git status
```
