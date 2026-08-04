# Medical Record Status Lokalis Body SVG Redesign

**Date**: 2026-08-04  
**Module**: `penandaan-area-operasi` (Surgical Site Marking / Medical Record Status Lokalis)  
**Target Files**:
- `public/img/penandaan-area-operasi/body-front.svg` (Female Status Lokalis Anterior View)
- `public/img/penandaan-area-operasi/body-back.svg` (Female Status Lokalis Posterior View)
- `public/img/penandaan-area-operasi/body-front-male.svg` (Male Status Lokalis Anterior View)
- `public/img/penandaan-area-operasi/body-back-male.svg` (Male Status Lokalis Posterior View)

---

## 1. Goal & Intent

Redesign the 4 full-body diagram SVG assets (`body-front.svg`, `body-back.svg`, `body-front-male.svg`, `body-back-male.svg`) into clean, professional minimalist medical status lokalis diagrams. The new SVGs strip away decorative facial elements (eyes/mouth/hair) and emphasize clinical anatomical region landmarks (clavicles, sternum, thorax, abdominal regions, pelvis, patellas, spine, scapulae, gluteal fold, popliteal fossae) so doctors can accurately plot physical examination findings (e.g. trauma, lesions, tumors, surgical site marks).

---

## 2. Visual & Stroke Specifications

### 2.1 Aesthetic Characteristics
- **Clean Clinical Head Outline**: Smooth cranial/head contour without decorative eyes, mouth, or facial features, providing an uncluttered background for head/facial injury marking.
- **Outer Body Contour**: Pure black (`#000000`), `stroke-width="5"`, `stroke-linecap="round"`, `stroke-linejoin="round"`.
- **Interior Anatomical Region Lines**: Pure black (`#000000`), fine line width (`stroke-width="2.5"`), `stroke-linecap="round"`, `stroke-linejoin="round"`.
- **Background & Fill**: White (`fill="#ffffff"`).
- **Dimensions**: `viewBox="0 0 600 1200"` for all 4 body SVGs.

### 2.2 Anatomical Features per Asset

1. **`body-front.svg` (Female Status Lokalis Anterior)**:
   - Clean head/neck contour, clavicle lines, sternum, breast outlines, navel, abdominal region guides, iliac crest, groin/inguinal crease, patellas, ankles, feet.
2. **`body-back.svg` (Female Status Lokalis Posterior)**:
   - Head back contour, nuchal line, scapulae, spine line, lumbar curve, gluteal fold, popliteal knee creases, calves, heels.
3. **`body-front-male.svg` (Male Status Lokalis Anterior)**:
   - Broad shoulder slope, clean head/neck contour, clavicles, sternum/pectoral chest lines, navel, abdominal region guides, iliac crest, groin, patellas, shins, feet.
4. **`body-back-male.svg` (Male Status Lokalis Posterior)**:
   - Broad upper back, head back contour, scapulae, spine line, lumbar curve, gluteal fold, popliteal knee creases, calves, heels.

### 2.3 Pin Detector Safety
All lines use `#000000` (Red channel = 0) on `#ffffff` canvas, ensuring 100% compatibility with HTML5 Canvas `isPinPixel` pin detector (`R > 150`, `G < 115`, `B < 125`).

---

## 3. Verification Plan

1. **XML Syntax Check**: Validate all 4 SVG files using PHP `simplexml_load_file()`.
2. **Pin Marker Color Safety Check**: Ensure no red channel hex values exist in any SVG asset.
