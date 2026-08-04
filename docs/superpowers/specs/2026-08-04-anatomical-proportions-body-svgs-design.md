# 7.5 Head Standard Anatomical Proportions Body SVG Redesign

**Date**: 2026-08-04  
**Module**: `penandaan-area-operasi` (Medical Record Status Lokalis & Surgical Site Marking)  
**Target Files**:
- `public/img/penandaan-area-operasi/body-front.svg` (Female Standard Anatomical Anterior View)
- `public/img/penandaan-area-operasi/body-back.svg` (Female Standard Anatomical Posterior View)
- `public/img/penandaan-area-operasi/body-front-male.svg` (Male Standard Anatomical Anterior View)
- `public/img/penandaan-area-operasi/body-back-male.svg` (Male Standard Anatomical Posterior View)

---

## 1. Goal & Proportional Standard

Redesign the 4 full-body SVG diagrams into exact 7.5-head classical medical anatomical proportions (Rasio Anatomi Standar 1:7.5) with standard medical anatomical posture (Sikap Anatomi Standar: arms hanging straight beside torso, palms facing forward, thumbs pointing outward).

---

## 2. Mathematical Proportions & Coordinates (`viewBox="0 0 600 1200"`)

- **Total Canvas Height**: 1200 px.
- **Head Length ($1H$)**: 160 px ($1200 / 7.5 = 160$ px).
  - Head Top to Chin: $Y = 40 \to 200$. Head width centered at $X = 300$.
- **Neck**: $Y = 200 \to 250$.
- **Shoulder / Clavicle Line**: $Y = 250$. Width: Female ~230 px ($X = 185 \to 415$), Male ~260 px ($X = 170 \to 430$).
- **Chest / Nipple Line ($2H$)**: $Y = 360$.
- **Waist / Navel Line ($3.25H$)**: $Y = 520$. Elbow joint level = $Y = 520$.
- **Groin / Crotch ($3.75H$ / 50% Midpoint)**: $Y = 600$ (exact vertical 50% midpoint of 1200 px height).
- **Hand Fingertips**: Reach $Y = 700$ (mid-thigh level).
- **Knee Joint / Patella ($5.625H$ / 75% height from top)**: $Y = 900$.
- **Ankle / Feet Soles**: $Y = 1160$.

---

## 3. Stroke & Color Specifications

- **Outer Body Contour**: Pure black (`#000000`), `stroke-width="5"`, `stroke-linecap="round"`, `stroke-linejoin="round"`.
- **Interior Anatomical Landmarks**: Pure black (`#000000`), fine line (`stroke-width="2.5"`), `stroke-linecap="round"`, `stroke-linejoin="round"`.
- **Fill**: White (`fill="#ffffff"`).
- **Pin Marker Safety**: Zero red color hex values (`#000000` strokes only) for 100% pin detector compatibility.

---

## 4. Verification Plan

1. **XML Syntax Check**: Validate all 4 SVG files using PHP `simplexml_load_file()`.
2. **Pin Marker Safety Check**: Ensure zero red color hex matches.
