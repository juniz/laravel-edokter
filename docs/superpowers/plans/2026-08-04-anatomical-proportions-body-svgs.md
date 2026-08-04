# 7.5 Head Standard Anatomical Proportions Body SVG Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Redesign the 4 body diagram SVG assets (`body-front.svg`, `body-back.svg`, `body-front-male.svg`, `body-back-male.svg`) into exact 7.5-head standard medical anatomical proportions with standard medical anatomical posture (arms hanging straight beside torso, palms facing forward, thumbs pointing outward).

**Architecture:** Update 4 body SVG assets in `public/img/penandaan-area-operasi/` with `viewBox="0 0 600 1200"`, `stroke-width="5"` for outer body contours and `stroke-width="2.5"` for interior anatomical landmarks, filled with `#ffffff`, using mathematically precise 7.5-head height ratios (crotch at $Y = 600$, knees at $Y = 900$, feet at $Y = 1160$).

**Tech Stack:** SVG (Scalable Vector Graphics), PHP (SimpleXML).

## Global Constraints
- Target SVG paths:
  - `public/img/penandaan-area-operasi/body-front.svg` (Female standard 7.5H anterior)
  - `public/img/penandaan-area-operasi/body-back.svg` (Female standard 7.5H posterior)
  - `public/img/penandaan-area-operasi/body-front-male.svg` (Male standard 7.5H anterior)
  - `public/img/penandaan-area-operasi/body-back-male.svg` (Male standard 7.5H posterior)
- `viewBox="0 0 600 1200"`
- Outer Body Contour: `stroke="#000000"`, `stroke-width="5"`, `stroke-linecap="round"`, `stroke-linejoin="round"`
- Interior Landmark Lines: `stroke="#000000"`, `stroke-width="2.5"`, `stroke-linecap="round"`, `stroke-linejoin="round"`
- Fill: `fill="#ffffff"`

---

### Task 1: Redesign Female Body SVGs (`body-front.svg`, `body-back.svg`) to 7.5 Head Anatomical Standard

**Files:**
- Modify: `public/img/penandaan-area-operasi/body-front.svg`
- Modify: `public/img/penandaan-area-operasi/body-back.svg`

**Interfaces:**
- Consumes: `docs/superpowers/specs/2026-08-04-anatomical-proportions-body-svgs-design.md`
- Produces: 7.5-head proportional female body SVGs.

- [ ] **Step 1: Write `body-front.svg` (Female 7.5H Anterior View)**

Write `public/img/penandaan-area-operasi/body-front.svg` with `viewBox="0 0 600 1200"` following 7.5-head height scale: head $Y = 40 \to 200$, shoulders at $Y = 250$ (width 230 px), chest $Y = 360$, waist & elbows $Y = 520$, crotch at $Y = 600$ (50% midpoint), fingertips at $Y = 700$, knees at $Y = 900$, feet soles at $Y = 1160$. Arms hanging straight alongside torso with palms facing forward and thumbs pointing outward.

- [ ] **Step 2: Write `body-back.svg` (Female 7.5H Posterior View)**

Write `public/img/penandaan-area-operasi/body-back.svg` with `viewBox="0 0 600 1200"` following matching 7.5-head height scale: head back $Y = 40 \to 200$, shoulders $Y = 250$, spine line, scapulae, lumbar curve $Y = 520$, gluteal fold $Y = 600$, posterior elbow creases, popliteal knee creases at $Y = 900$, heels at $Y = 1160$.

- [ ] **Step 3: Validate XML syntax**

Run: `php -r 'simplexml_load_file("public/img/penandaan-area-operasi/body-front.svg") or exit(1); simplexml_load_file("public/img/penandaan-area-operasi/body-back.svg") or exit(1); echo "Female 7.5H Body SVGs valid\n";'`
Expected: `Female 7.5H Body SVGs valid`

- [ ] **Step 4: Commit**

```bash
git add public/img/penandaan-area-operasi/body-front.svg public/img/penandaan-area-operasi/body-back.svg
git commit -m "feat(assets): redesign female body front and back SVGs to 7.5-head medical anatomical proportion standard"
```

---

### Task 2: Redesign Male Body SVGs (`body-front-male.svg`, `body-back-male.svg`) to 7.5 Head Anatomical Standard

**Files:**
- Modify: `public/img/penandaan-area-operasi/body-front-male.svg`
- Modify: `public/img/penandaan-area-operasi/body-back-male.svg`

**Interfaces:**
- Consumes: Design spec requirements for male 7.5-head anatomical body views.
- Produces: 7.5-head proportional male body SVGs.

- [ ] **Step 1: Write `body-front-male.svg` (Male 7.5H Anterior View)**

Write `public/img/penandaan-area-operasi/body-front-male.svg` with `viewBox="0 0 600 1200"` following 7.5-head scale: head $Y = 40 \to 200$, broad male shoulders at $Y = 250$ (width 260 px), chest pectorals $Y = 360$, waist & elbows $Y = 520$, crotch at $Y = 600$ (50% midpoint), fingertips at $Y = 700$, knees at $Y = 900$, feet soles at $Y = 1160$. Arms hanging straight alongside torso with palms facing forward and thumbs pointing outward.

- [ ] **Step 2: Write `body-back-male.svg` (Male 7.5H Posterior View)**

Write `public/img/penandaan-area-operasi/body-back-male.svg` with `viewBox="0 0 600 1200"` following matching 7.5-head scale: head back $Y = 40 \to 200$, broad upper back $Y = 250$, spine line, scapulae, lumbar curve $Y = 520$, gluteal fold $Y = 600$, posterior elbow creases, popliteal knee creases at $Y = 900$, heels at $Y = 1160$.

- [ ] **Step 3: Validate XML syntax**

Run: `php -r 'simplexml_load_file("public/img/penandaan-area-operasi/body-front-male.svg") or exit(1); simplexml_load_file("public/img/penandaan-area-operasi/body-back-male.svg") or exit(1); echo "Male 7.5H Body SVGs valid\n";'`
Expected: `Male 7.5H Body SVGs valid`

- [ ] **Step 4: Commit**

```bash
git add public/img/penandaan-area-operasi/body-front-male.svg public/img/penandaan-area-operasi/body-back-male.svg
git commit -m "feat(assets): redesign male body front and back SVGs to 7.5-head medical anatomical proportion standard"
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
