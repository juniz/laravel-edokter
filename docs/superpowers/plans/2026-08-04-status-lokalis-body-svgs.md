# Medical Record Status Lokalis Body SVG Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Redesign the 4 body diagram SVG assets (`body-front.svg`, `body-back.svg`, `body-front-male.svg`, `body-back-male.svg`) into clean, professional minimalist medical status lokalis diagrams with fine interior region lines and clean head outlines.

**Architecture:** Update 4 body SVG assets in `public/img/penandaan-area-operasi/` with `viewBox="0 0 600 1200"`, `stroke-width="5"` for outer contours and `stroke-width="2.5"` for internal anatomical region landmarks, filled with `#ffffff`.

**Tech Stack:** SVG (Scalable Vector Graphics), PHP (SimpleXML).

## Global Constraints
- Target SVG paths:
  - `public/img/penandaan-area-operasi/body-front.svg` (Female status lokalis anterior)
  - `public/img/penandaan-area-operasi/body-back.svg` (Female status lokalis posterior)
  - `public/img/penandaan-area-operasi/body-front-male.svg` (Male status lokalis anterior)
  - `public/img/penandaan-area-operasi/body-back-male.svg` (Male status lokalis posterior)
- `viewBox="0 0 600 1200"`
- Outer Body Contour: `stroke="#000000"`, `stroke-width="5"`, `stroke-linecap="round"`, `stroke-linejoin="round"`
- Interior Region Lines: `stroke="#000000"`, `stroke-width="2.5"`, `stroke-linecap="round"`, `stroke-linejoin="round"`
- Fill: `fill="#ffffff"`

---

### Task 1: Redesign Female Status Lokalis Body SVGs (`body-front.svg`, `body-back.svg`)

**Files:**
- Modify: `public/img/penandaan-area-operasi/body-front.svg`
- Modify: `public/img/penandaan-area-operasi/body-back.svg`

**Interfaces:**
- Consumes: `docs/superpowers/specs/2026-08-04-status-lokalis-body-svgs-design.md`
- Produces: Female status lokalis body SVGs.

- [ ] **Step 1: Write `body-front.svg` (Female Status Lokalis Anterior View)**

Write `public/img/penandaan-area-operasi/body-front.svg` with `viewBox="0 0 600 1200"` featuring clean head/neck contour without facial features, outer contour stroke width 5, fine interior stroke width 2.5 for clavicles, sternum, breast outlines, navel, abdominal regions, iliac crest, groin/inguinal crease, patellas, ankles, feet.

- [ ] **Step 2: Write `body-back.svg` (Female Status Lokalis Posterior View)**

Write `public/img/penandaan-area-operasi/body-back.svg` with `viewBox="0 0 600 1200"` featuring clean head back/neck contour, outer contour stroke width 5, fine interior stroke width 2.5 for nuchal line, scapulae, spine line, lumbar curve, gluteal fold, popliteal knee creases, calves, heels.

- [ ] **Step 3: Validate XML syntax**

Run: `php -r 'simplexml_load_file("public/img/penandaan-area-operasi/body-front.svg") or exit(1); simplexml_load_file("public/img/penandaan-area-operasi/body-back.svg") or exit(1); echo "Female Status Lokalis SVGs valid\n";'`
Expected: `Female Status Lokalis SVGs valid`

- [ ] **Step 4: Commit**

```bash
git add public/img/penandaan-area-operasi/body-front.svg public/img/penandaan-area-operasi/body-back.svg
git commit -m "feat(assets): redesign female body front and back SVGs for medical record status lokalis"
```

---

### Task 2: Redesign Male Status Lokalis Body SVGs (`body-front-male.svg`, `body-back-male.svg`)

**Files:**
- Modify: `public/img/penandaan-area-operasi/body-front-male.svg`
- Modify: `public/img/penandaan-area-operasi/body-back-male.svg`

**Interfaces:**
- Consumes: Design spec requirements for male status lokalis body views.
- Produces: Male status lokalis body SVGs.

- [ ] **Step 1: Write `body-front-male.svg` (Male Status Lokalis Anterior View)**

Write `public/img/penandaan-area-operasi/body-front-male.svg` with `viewBox="0 0 600 1200"` featuring broad male shoulders, clean head/neck contour without facial features, outer contour stroke width 5, fine interior stroke width 2.5 for clavicles, sternum/pectoral lines, navel, abdominal regions, iliac crest, groin, patellas, shins, feet.

- [ ] **Step 2: Write `body-back-male.svg` (Male Status Lokalis Posterior View)**

Write `public/img/penandaan-area-operasi/body-back-male.svg` with `viewBox="0 0 600 1200"` featuring broad upper back, clean head back contour, outer contour stroke width 5, fine interior stroke width 2.5 for scapulae, spine line, lumbar curve, gluteal fold, popliteal knee creases, calves, heels.

- [ ] **Step 3: Validate XML syntax**

Run: `php -r 'simplexml_load_file("public/img/penandaan-area-operasi/body-front-male.svg") or exit(1); simplexml_load_file("public/img/penandaan-area-operasi/body-back-male.svg") or exit(1); echo "Male Status Lokalis SVGs valid\n";'`
Expected: `Male Status Lokalis SVGs valid`

- [ ] **Step 4: Commit**

```bash
git add public/img/penandaan-area-operasi/body-front-male.svg public/img/penandaan-area-operasi/body-back-male.svg
git commit -m "feat(assets): redesign male body front and back SVGs for medical record status lokalis"
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
