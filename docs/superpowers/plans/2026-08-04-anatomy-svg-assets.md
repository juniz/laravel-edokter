# Anatomy SVG Assets Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace existing basic stick-figure SVG diagrams with 14 high-quality, anatomically accurate vector SVG assets featuring modern minimalist line art and anatomical landmarks for the surgical site marking module (`penandaan-area-operasi`).

**Architecture:** Create 14 clean, standalone SVG vector files in `public/img/penandaan-area-operasi/` using pure black `#000000` outlines (`stroke-width="6"` for body contours, `stroke-width="3"` for internal landmarks) and `#ffffff` fills. Ensure 100% pixel-matching compatibility with the HTML5 Canvas red pin marker detector in `penandaan-area-operasi.blade.php`.

**Tech Stack:** SVG (Scalable Vector Graphics), HTML5 Canvas, Laravel Livewire, Blade.

## Global Constraints
- Target directory: `public/img/penandaan-area-operasi/`
- Full Body Views (`body-front.svg`, `body-back.svg`): `viewBox="0 0 600 1200"`
- Head & Extremities Views (Head, Hand, Foot SVGs): `viewBox="0 0 800 900"`
- Primary Contours: `stroke="#000000"`, `stroke-width="6"`, `stroke-linecap="round"`, `stroke-linejoin="round"`
- Internal Anatomical Landmarks: `stroke="#000000"`, `stroke-width="3"`, `stroke-linecap="round"`, `stroke-linejoin="round"`
- Fill: `fill="#ffffff"`

---

### Task 1: Create Full Body SVGs (`body-front.svg`, `body-back.svg`)

**Files:**
- Modify: `public/img/penandaan-area-operasi/body-front.svg`
- Modify: `public/img/penandaan-area-operasi/body-back.svg`

**Interfaces:**
- Consumes: `docs/superpowers/specs/2026-08-04-anatomy-svg-assets-design.md`
- Produces: Anatomical full body anterior & posterior SVG assets for `penandaan-area-operasi.blade.php` canvas rendering.

- [ ] **Step 1: Write `body-front.svg`**

Create `public/img/penandaan-area-operasi/body-front.svg` with `viewBox="0 0 600 1200"` containing anterior body contours (head, neck, clavicles, sternum, ribcage, umbilicus, iliac crest, groin, thighs, patellas, shins, feet, arms, elbows, forearms, hands).

- [ ] **Step 2: Write `body-back.svg`**

Create `public/img/penandaan-area-operasi/body-back.svg` with `viewBox="0 0 600 1200"` containing posterior body contours (occiput, neck, scapulae, spine line, lumbar curve, gluteal fold, popliteal fossae, calves, heels).

- [ ] **Step 3: Verify SVG file validity & syntax**

Run: `python3 -c "import xml.etree.ElementTree as ET; ET.parse('public/img/penandaan-area-operasi/body-front.svg'); ET.parse('public/img/penandaan-area-operasi/body-back.svg'); print('Full body SVGs valid')"`
Expected: `Full body SVGs valid`

- [ ] **Step 4: Commit**

```bash
git add public/img/penandaan-area-operasi/body-front.svg public/img/penandaan-area-operasi/body-back.svg
git commit -m "feat(assets): add anatomical full body anterior and posterior SVGs"
```

---

### Task 2: Create Head Views SVGs (`head-front.svg`, `head-back.svg`, `head-left-profile.svg`, `head-right-profile.svg`)

**Files:**
- Modify: `public/img/penandaan-area-operasi/head-front.svg`
- Modify: `public/img/penandaan-area-operasi/head-back.svg`
- Modify: `public/img/penandaan-area-operasi/head-left-profile.svg`
- Modify: `public/img/penandaan-area-operasi/head-right-profile.svg`

**Interfaces:**
- Consumes: Specs for head anatomical views.
- Produces: 4 Head SVG assets (`viewBox="0 0 800 900"`).

- [ ] **Step 1: Write `head-front.svg`**

Create `public/img/penandaan-area-operasi/head-front.svg` with cranial dome, forehead, eyebrows, eyes/pupils, nose, nostrils, lips, chin, jawline, ears, neck, clavicle base.

- [ ] **Step 2: Write `head-back.svg`**

Create `public/img/penandaan-area-operasi/head-back.svg` with occipital vault, posterior ears, nuchal line, neck, trapezius slope.

- [ ] **Step 3: Write `head-left-profile.svg`**

Create `public/img/penandaan-area-operasi/head-left-profile.svg` with left facial profile, nose, lips, chin, left ear, eye profile, neck contour.

- [ ] **Step 4: Write `head-right-profile.svg`**

Create `public/img/penandaan-area-operasi/head-right-profile.svg` with right facial profile, nose, lips, chin, right ear, eye profile, neck contour.

- [ ] **Step 5: Verify XML validity**

Run: `python3 -c "import xml.etree.ElementTree as ET; [ET.parse(f'public/img/penandaan-area-operasi/{f}') for f in ['head-front.svg','head-back.svg','head-left-profile.svg','head-right-profile.svg']]; print('Head SVGs valid')"`
Expected: `Head SVGs valid`

- [ ] **Step 6: Commit**

```bash
git add public/img/penandaan-area-operasi/head-*.svg
git commit -m "feat(assets): add anatomical head SVG diagrams (front, back, left profile, right profile)"
```

---

### Task 3: Create Hand Views SVGs (`hand-left-palm.svg`, `hand-right-palm.svg`, `hand-left-dorsal.svg`, `hand-right-dorsal.svg`)

**Files:**
- Modify: `public/img/penandaan-area-operasi/hand-left-palm.svg`
- Modify: `public/img/penandaan-area-operasi/hand-right-palm.svg`
- Modify: `public/img/penandaan-area-operasi/hand-left-dorsal.svg`
- Modify: `public/img/penandaan-area-operasi/hand-right-dorsal.svg`

**Interfaces:**
- Consumes: Hand anatomical specs.
- Produces: 4 Hand SVG assets (`viewBox="0 0 800 900"`).

- [ ] **Step 1: Write `hand-left-palm.svg` and `hand-right-palm.svg`**

Create Palmar hand views with wrist crease, thenar & hypothenar eminences, main palmar flexion creases (life, head, heart lines), 5 digits, and interphalangeal creases.

- [ ] **Step 2: Write `hand-left-dorsal.svg` and `hand-right-dorsal.svg`**

Create Dorsal hand views with wrist line, metacarpals, extensor tendon contours, knuckles (MCP joints), and fingernails on all digits.

- [ ] **Step 3: Verify XML validity**

Run: `python3 -c "import xml.etree.ElementTree as ET; [ET.parse(f'public/img/penandaan-area-operasi/{f}') for f in ['hand-left-palm.svg','hand-right-palm.svg','hand-left-dorsal.svg','hand-right-dorsal.svg']]; print('Hand SVGs valid')"`
Expected: `Hand SVGs valid`

- [ ] **Step 4: Commit**

```bash
git add public/img/penandaan-area-operasi/hand-*.svg
git commit -m "feat(assets): add anatomical hand palmar and dorsal SVGs"
```

---

### Task 4: Create Foot Views SVGs (`foot-left-plantar.svg`, `foot-right-plantar.svg`, `foot-left-dorsal.svg`, `foot-right-dorsal.svg`)

**Files:**
- Modify: `public/img/penandaan-area-operasi/foot-left-plantar.svg`
- Modify: `public/img/penandaan-area-operasi/foot-right-plantar.svg`
- Modify: `public/img/penandaan-area-operasi/foot-left-dorsal.svg`
- Modify: `public/img/penandaan-area-operasi/foot-right-dorsal.svg`

**Interfaces:**
- Consumes: Foot anatomical specs.
- Produces: 4 Foot SVG assets (`viewBox="0 0 800 900"`).

- [ ] **Step 1: Write `foot-left-plantar.svg` and `foot-right-plantar.svg`**

Create Plantar foot views (soles) with calcaneus heel contour, medial arch curve, plantar flexion creases, ball of foot, hallux (big toe) and 4 toes with toe pads.

- [ ] **Step 2: Write `foot-left-dorsal.svg` and `foot-right-dorsal.svg`**

Create Dorsal foot views (tops) with ankle joint, medial & lateral malleoli (ankle bones), extensor tendons, 5 toes, and toenails.

- [ ] **Step 3: Verify XML validity**

Run: `python3 -c "import xml.etree.ElementTree as ET; [ET.parse(f'public/img/penandaan-area-operasi/{f}') for f in ['foot-left-plantar.svg','foot-right-plantar.svg','foot-left-dorsal.svg','foot-right-dorsal.svg']]; print('Foot SVGs valid')"`
Expected: `Foot SVGs valid`

- [ ] **Step 4: Commit**

```bash
git add public/img/penandaan-area-operasi/foot-*.svg
git commit -m "feat(assets): add anatomical foot plantar and dorsal SVGs"
```

---

### Task 5: Integration & Verification of All SVGs

**Files:**
- Verify: `public/img/penandaan-area-operasi/*.svg` (all 14 SVGs)
- Verify: `resources/views/livewire/component/penandaan-area-operasi.blade.php`

**Interfaces:**
- Consumes: All 14 created SVG files.
- Produces: Verification confirmation for the full surgical site marking component.

- [ ] **Step 1: Run comprehensive XML validation on all 14 SVGs**

Run: `python3 -c "import xml.etree.ElementTree as ET, glob; files = glob.glob('public/img/penandaan-area-operasi/*.svg'); assert len(files) == 14, f'Expected 14 SVG files, found {len(files)}'; [ET.parse(f) for f in files]; print(f'Successfully validated all {len(files)} SVGs!')"`
Expected: `Successfully validated all 14 SVGs!`

- [ ] **Step 2: Verify pin color compatibility (No Red channel false-positives)**

Run: `python3 -c "import glob, re; files = glob.glob('public/img/penandaan-area-operasi/*.svg'); [assert not re.search(r'#(?:c6283d|ff0000|e53e3e|f56565|dc2626)', open(f).read(), re.I), f'Red color found in {f}' for f in files]; print('Pin color safety check passed!')"`
Expected: `Pin color safety check passed!`

- [ ] **Step 3: Final commit & push readiness**

```bash
git status
```
