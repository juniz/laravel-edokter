# Unobstructed Arm Clearance & Anatomical Feet Body SVG Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Redesign the 4 body diagram SVG assets (`body-front.svg`, `body-back.svg`, `body-front-male.svg`, `body-back-male.svg`) to abduct the arms ~20° outward from the torso (creating a 50–70px open gap with zero line overlap) and render complete anatomical feet with ankles, insteps, and toes.

**Architecture:** Update 4 body SVG assets in `public/img/penandaan-area-operasi/` with `viewBox="0 0 600 1200"`, `stroke-width="5"` for outer body contours and `stroke-width="2.5"` for interior anatomical landmarks, filled with `#ffffff`, using wide arm abduction layout and anatomical foot/ankle vectors.

**Tech Stack:** SVG (Scalable Vector Graphics), PHP (SimpleXML).

## Global Constraints
- Target SVG paths:
  - `public/img/penandaan-area-operasi/body-front.svg` (Female clear arm/feet anterior)
  - `public/img/penandaan-area-operasi/body-back.svg` (Female clear arm/feet posterior)
  - `public/img/penandaan-area-operasi/body-front-male.svg` (Male clear arm/feet anterior)
  - `public/img/penandaan-area-operasi/body-back-male.svg` (Male clear arm/feet posterior)
- `viewBox="0 0 600 1200"`
- Outer Body Contour: `stroke="#000000"`, `stroke-width="5"`, `stroke-linecap="round"`, `stroke-linejoin="round"`
- Interior Landmark Lines: `stroke="#000000"`, `stroke-width="2.5"`, `stroke-linecap="round"`, `stroke-linejoin="round"`
- Fill: `fill="#ffffff"`

---

### Task 1: Redesign Female Body SVGs (`body-front.svg`, `body-back.svg`) with 20° Arm Clearance & Full Feet

**Files:**
- Modify: `public/img/penandaan-area-operasi/body-front.svg`
- Modify: `public/img/penandaan-area-operasi/body-back.svg`

**Interfaces:**
- Consumes: `docs/superpowers/specs/2026-08-04-arm-clearance-feet-body-svgs-design.md`
- Produces: Female body SVGs with unobstructed arms and anatomical feet.

- [ ] **Step 1: Write `body-front.svg` (Female Clear Arm & Feet Anterior View)**

Write `public/img/penandaan-area-operasi/body-front.svg` with `viewBox="0 0 600 1200"` featuring arms abducted ~20° (elbows $X = 120 / 480$, wrists $X = 90 / 510$, hands $X = 70 / 530$, leaving ~50–70px open gap from hips/waist with zero overlap), and anatomical feet (ankle malleoli at $Y = 1080$, instep arch $Y = 1090 \to 1150$, 5 toes at $Y = 1150 \to 1160$).

- [ ] **Step 2: Write `body-back.svg` (Female Clear Arm & Feet Posterior View)**

Write `public/img/penandaan-area-operasi/body-back.svg` with `viewBox="0 0 600 1200"` featuring matching ~20° arm abduction and posterior feet (Achilles tendon lines, calcaneus heels $Y = 1140 \to 1160$, plantigrade foot contour).

- [ ] **Step 3: Validate XML syntax**

Run: `php -r 'simplexml_load_file("public/img/penandaan-area-operasi/body-front.svg") or exit(1); simplexml_load_file("public/img/penandaan-area-operasi/body-back.svg") or exit(1); echo "Female Clear Arm & Feet SVGs valid\n";'`
Expected: `Female Clear Arm & Feet SVGs valid`

- [ ] **Step 4: Commit**

```bash
git add public/img/penandaan-area-operasi/body-front.svg public/img/penandaan-area-operasi/body-back.svg
git commit -m "feat(assets): redesign female body SVGs with unobstructed arm clearance and anatomical feet"
```

---

### Task 2: Redesign Male Body SVGs (`body-front-male.svg`, `body-back-male.svg`) with 20° Arm Clearance & Full Feet

**Files:**
- Modify: `public/img/penandaan-area-operasi/body-front-male.svg`
- Modify: `public/img/penandaan-area-operasi/body-back-male.svg`

**Interfaces:**
- Consumes: Design spec requirements for male clear arm & feet body views.
- Produces: Male body SVGs with unobstructed arms and anatomical feet.

- [ ] **Step 1: Write `body-front-male.svg` (Male Clear Arm & Feet Anterior View)**

Write `public/img/penandaan-area-operasi/body-front-male.svg` with `viewBox="0 0 600 1200"` featuring broad male shoulders, arms abducted ~20° (elbows $X = 115 / 485$, wrists $X = 85 / 515$, hands $X = 65 / 535$, leaving ~60–80px open gap from hips/waist with zero overlap), and anatomical feet (ankle malleoli at $Y = 1080$, instep arch $Y = 1090 \to 1150$, 5 toes at $Y = 1150 \to 1160$).

- [ ] **Step 2: Write `body-back-male.svg` (Male Clear Arm & Feet Posterior View)**

Write `public/img/penandaan-area-operasi/body-back-male.svg` with `viewBox="0 0 600 1200"` featuring broad male upper back, matching ~20° arm abduction, and posterior feet (Achilles tendon lines, calcaneus heels $Y = 1140 \to 1160$, plantigrade foot contour).

- [ ] **Step 3: Validate XML syntax**

Run: `php -r 'simplexml_load_file("public/img/penandaan-area-operasi/body-front-male.svg") or exit(1); simplexml_load_file("public/img/penandaan-area-operasi/body-back-male.svg") or exit(1); echo "Male Clear Arm & Feet SVGs valid\n";'`
Expected: `Male Clear Arm & Feet SVGs valid`

- [ ] **Step 4: Commit**

```bash
git add public/img/penandaan-area-operasi/body-front-male.svg public/img/penandaan-area-operasi/body-back-male.svg
git commit -m "feat(assets): redesign male body SVGs with unobstructed arm clearance and anatomical feet"
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
