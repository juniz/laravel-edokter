# Unobstructed Arm Clearance & Anatomical Feet Body SVG Redesign

**Date**: 2026-08-04  
**Module**: `penandaan-area-operasi` (Medical Record Status Lokalis & Surgical Site Marking)  
**Target Files**:
- `public/img/penandaan-area-operasi/body-front.svg` (Female Unobstructed Anterior View)
- `public/img/penandaan-area-operasi/body-back.svg` (Female Unobstructed Posterior View)
- `public/img/penandaan-area-operasi/body-front-male.svg` (Male Unobstructed Anterior View)
- `public/img/penandaan-area-operasi/body-back-male.svg` (Male Unobstructed Posterior View)

---

## 1. Goal & Key Corrections

1. **Unobstructed Arms & Hands (Zero Overlap)**:
   - Abduct arms ~20° outward from the shoulder joint.
   - Maintain a 50–70px open gap between forearms/wrists/hands and hips/thighs so no body lines overlap or obscure the hands, wrists, or torso sides.
2. **Anatomical Feet & Lower Leg Contours**:
   - Replace truncated stump feet with complete anatomical feet drawings.
   - **Anterior Views**: Include ankle joint malleoli, instep arch, hallux (big toe) and 4 toes.
   - **Posterior Views**: Include achilles tendon lines, calcaneus heels, and plantar foot profiles.

---

## 2. Technical Vector Layout (`viewBox="0 0 600 1200"`)

- **Canvas Size**: `600 x 1200` px.
- **Head & Torso Axis**: Centered at $X = 300$.
- **Arm Abduction & Clearance Coordinates**:
  - Shoulder joint: $Y = 250$, $X = 185 / 415$ (female) or $X = 170 / 430$ (male).
  - Elbow joint: $Y = 520$, $X = 120 / 480$ (leaving ~50px open gap from waist $X = 220 / 380$).
  - Wrist joint: $Y = 620$, $X = 90 / 510$ (leaving ~70px open gap from hips $X = 210 / 390$).
  - Fingertips: $Y = 710$, $X = 70 / 530$.
- **Lower Leg & Feet Coordinates**:
  - Knee joint / Patella: $Y = 900$.
  - Ankle joint / Malleoli: $Y = 1080$, $X = 255 / 345$.
  - Instep / Arch & Feet Soles: $Y = 1090 \to 1160$.

---

## 3. Stroke & Color Specifications

- **Outer Body Contour**: Pure black (`#000000`), `stroke-width="5"`, `stroke-linecap="round"`, `stroke-linejoin="round"`.
- **Interior Region Lines**: Pure black (`#000000`), fine line (`stroke-width="2.5"`), `stroke-linecap="round"`, `stroke-linejoin="round"`.
- **Fill**: White (`fill="#ffffff"`).
- **Pin Marker Safety**: Zero red color hex values (`#000000` strokes only) for 100% pin detector compatibility.

---

## 4. Verification Plan

1. **XML Syntax Check**: Validate all 4 SVG files using PHP `simplexml_load_file()`.
2. **Pin Marker Safety Check**: Ensure zero red color hex matches in any SVG asset.
