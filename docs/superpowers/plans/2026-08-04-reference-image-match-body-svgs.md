# 1:1 Reference Image Match Body SVG Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Redesign the 4 body diagram SVG assets (`body-front.svg`, `body-back.svg`, `body-front-male.svg`, `body-back-male.svg`) into 1:1 vector path representations matching the user's reference drawing (facial features, neck dots, chest curves, navel, groin V-line, arm stance, and foot shape).

**Architecture:** Update 4 body SVG assets in `public/img/penandaan-area-operasi/` with `viewBox="0 0 600 1200"`, `stroke-width="5"` outer body contours and detailed internal features (eyes, pupils, eyebrows, nose, mouth, ears, nipples, navel, groin V-line, fingers, toes), filled with `#ffffff`.

**Tech Stack:** SVG (Scalable Vector Graphics), PHP (SimpleXML).

## Global Constraints
- Target SVG paths:
  - `public/img/penandaan-area-operasi/body-front.svg` (Female 1:1 reference anterior)
  - `public/img/penandaan-area-operasi/body-back.svg` (Female 1:1 reference posterior)
  - `public/img/penandaan-area-operasi/body-front-male.svg` (Male 1:1 reference anterior)
  - `public/img/penandaan-area-operasi/body-back-male.svg` (Male 1:1 reference posterior)
- `viewBox="0 0 600 1200"`
- Contour & Landmark Stroke: `stroke="#000000"`, `stroke-width="5"`, `stroke-linecap="round"`, `stroke-linejoin="round"`
- Fill: `fill="#ffffff"`

---

### Task 1: Redesign Female Body SVGs (`body-front.svg`, `body-back.svg`) 1:1 to Reference Drawing

**Files:**
- Modify: `public/img/penandaan-area-operasi/body-front.svg`
- Modify: `public/img/penandaan-area-operasi/body-back.svg`

**Interfaces:**
- Consumes: `docs/superpowers/specs/2026-08-04-reference-image-match-body-svgs-design.md`
- Produces: 1:1 reference-matched female body SVGs.

- [ ] **Step 1: Write `body-front.svg` (Female 1:1 Reference Anterior View)**

Write `public/img/penandaan-area-operasi/body-front.svg` with `viewBox="0 0 600 1200"` featuring cranial head outline, ears, eyebrows, almond eyes with pupils, nose dash, mouth line, neck lines, suprasternal neck dots, underbust breast curves & nipples, navel dot, groin V-line, flared arms with open 5-finger hands, thighs, knee creases, ankles, and 5 toes pointing forward.

- [ ] **Step 2: Write `body-back.svg` (Female 1:1 Reference Posterior View)**

Write `public/img/penandaan-area-operasi/body-back.svg` with `viewBox="0 0 600 1200"` featuring matching head back contour, ears, nape of neck, spine line, scapulae lines, elbow creases, gluteal fold & buttocks cleft, popliteal knee creases, heels & Achilles lines.

- [ ] **Step 3: Validate XML syntax**

Run: `php -r 'simplexml_load_file("public/img/penandaan-area-operasi/body-front.svg") or exit(1); simplexml_load_file("public/img/penandaan-area-operasi/body-back.svg") or exit(1); echo "Female 1:1 Reference Body SVGs valid\n";'`
Expected: `Female 1:1 Reference Body SVGs valid`

- [ ] **Step 4: Commit**

```bash
git add public/img/penandaan-area-operasi/body-front.svg public/img/penandaan-area-operasi/body-back.svg
git commit -m "feat(assets): redesign female body front and back SVGs to 1:1 match reference drawing"
```

---

### Task 2: Redesign Male Body SVGs (`body-front-male.svg`, `body-back-male.svg`) in 1:1 Reference Style

**Files:**
- Modify: `public/img/penandaan-area-operasi/body-front-male.svg`
- Modify: `public/img/penandaan-area-operasi/body-back-male.svg`

**Interfaces:**
- Consumes: Design spec requirements for male 1:1 reference body views.
- Produces: 1:1 reference-style male body SVGs.

- [ ] **Step 1: Write `body-front-male.svg` (Male 1:1 Reference Anterior View)**

Write `public/img/penandaan-area-operasi/body-front-male.svg` with `viewBox="0 0 600 1200"` featuring broad male shoulders, cranial head outline, ears, eyebrows, almond eyes with pupils, nose dash, mouth line, neck lines, neck dots, pectoral chest lines & nipples, navel dot, groin V-line, flared arms with open 5-finger hands, thighs, knees, ankles, 5 toes.

- [ ] **Step 2: Write `body-back-male.svg` (Male 1:1 Reference Posterior View)**

Write `public/img/penandaan-area-operasi/body-back-male.svg` with `viewBox="0 0 600 1200"` featuring broad male upper back, head back contour, ears, nape of neck, spine line, scapulae, elbow creases, gluteal fold, popliteal knee creases, heels & Achilles lines.

- [ ] **Step 3: Validate XML syntax**

Run: `php -r 'simplexml_load_file("public/img/penandaan-area-operasi/body-front-male.svg") or exit(1); simplexml_load_file("public/img/penandaan-area-operasi/body-back-male.svg") or exit(1); echo "Male 1:1 Reference Body SVGs valid\n";'`
Expected: `Male 1:1 Reference Body SVGs valid`

- [ ] **Step 4: Commit**

```bash
git add public/img/penandaan-area-operasi/body-front-male.svg public/img/penandaan-area-operasi/body-back-male.svg
git commit -m "feat(assets): redesign male body front and back SVGs in 1:1 reference drawing style"
```

---

### Task 3: Integration Verification & Pin Safety Checks

**Files:**
- Verify: `public/img/penandaan-area-operasi/*.svg` (all 16 SVGs)

**Interfaces:**
- Consumes: All 16 SVG files in target folder.
- Produces: Final verification status.

- [ ] **Step 1: Run comprehensive PHP XML parsing & pin safety validation**

Run: `php -r '$files=glob("public/img/penandaan-area-operasi/*.svg"); foreach($files as $f){ simplexml_load_file($f) or exit(1); if(preg_match("/#(?:c6283d|ff0000|e53e3e)/i", file_get_contents($f))){ exit(1); } } echo "All 16 SVGs valid and pin-safe\n";'`
Expected: `All 16 SVGs valid and pin-safe`

- [ ] **Step 2: Commit & Status Check**

```bash
git status
```
