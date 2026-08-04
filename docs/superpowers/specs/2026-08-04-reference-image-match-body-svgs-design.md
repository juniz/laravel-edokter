# 1:1 Reference Image Vector Match Body SVG Redesign

**Date**: 2026-08-04  
**Module**: `penandaan-area-operasi` (Surgical Site Marking)  
**Target Files**:
- `public/img/penandaan-area-operasi/body-front.svg` (Female 1:1 Reference Anterior)
- `public/img/penandaan-area-operasi/body-back.svg` (Female 1:1 Reference Posterior)
- `public/img/penandaan-area-operasi/body-front-male.svg` (Male 1:1 Reference Anterior)
- `public/img/penandaan-area-operasi/body-back-male.svg` (Male 1:1 Reference Posterior)

---

## 1. Goal & Intent

Create 1:1 vector SVG path representations directly matching the visual style, silhouette, facial features (eyes, eyebrows, nose, mouth, ears), chest contours, arm angles, and leg/foot stance of the user's reference drawing across all 4 body diagram SVG assets (`body-front.svg`, `body-back.svg`, `body-front-male.svg`, `body-back-male.svg`).

---

## 2. Detailed Visual Features (`viewBox="0 0 600 1200"`)

- **Canvas & Colors**: `viewBox="0 0 600 1200"`, `fill="#ffffff"`, `stroke="#000000"`, `stroke-width="5"`, `stroke-linecap="round"`, `stroke-linejoin="round"`.
- **Head & Face**:
  - Cranial head contour ($Y = 40 \to 200$, centered at $X = 300$).
  - Ears on both sides ($X = 240 / 360$).
  - Facial features: Eyebrow arches, almond eye outlines with pupils, nose dash, mouth line.
- **Neck & Chest**:
  - Neck lines, suprasternal dots at neck base.
  - Female chest: underbust curves with nipple dots (`r="4"` fill `#000000`).
  - Male chest: broad pectoral lines with nipple dots.
- **Torso & Arms**:
  - Waist curve, umbilicus (navel dot `r="4"` fill `#000000`).
  - V-shaped pubic/groin crease.
  - Flared arms extending outwards from torso with a clear gap from hips.
  - Hands: open palms with thumb extending outward, 4 fingers.
- **Legs & Feet**:
  - Thighs, knee lines, shins, ankles, 5 toes pointing forward at foot base.
  - Sample paper scribble circle at right ankle is excluded from template.

---

## 3. Verification Plan

1. **XML Syntax Check**: Validate all 4 SVG files using PHP `simplexml_load_file()`.
2. **Pin Marker Safety Check**: Ensure zero red color hex matches in any SVG asset (`#000000` strokes on `#ffffff` canvas).
