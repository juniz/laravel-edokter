# Anatomy SVG Assets Design for Surgical Site Marking Module

**Date**: 2026-08-04  
**Module**: `penandaan-area-operasi` (Surgical Site Marking)  
**Target Path**: `public/img/penandaan-area-operasi/`

---

## 1. Goal & Overview

Replace the existing basic/simplified stick-figure SVG diagrams in the e-Dokter surgical site marking component (`penandaan-area-operasi`) with 14 high-quality, anatomically accurate vector SVG assets. The new assets feature modern minimalist medical line art with clear anatomical landmarks, ensuring professional medical charting visuals while remaining 100% compatible with the component's HTML5 canvas interactive pin-marking system.

---

## 2. Technical Specifications & Visual Hierarchy

### 2.1 Colors & Styling
- **Canvas Background**: Solid white (`#ffffff`).
- **Main Body Contour (Primary Outline)**: Pure black (`#000000`), `stroke-width="6"`, `stroke-linecap="round"`, `stroke-linejoin="round"`.
- **Anatomical Landmarks (Internal Contours/Landmarks)**: Pure black (`#000000`), `stroke-width="3"`, `stroke-linecap="round"`, `stroke-linejoin="round"`.
- **Fills**: Inner body area filled with `#ffffff` to prevent transparency issues when drawn onto HTML5 canvas context.

### 2.2 Dimensions & Aspect Ratios
- **Full Body Views** (`body-front.svg`, `body-back.svg`): `viewBox="0 0 600 1200"`
- **Head & Extremity Views** (Head, Hand, Foot SVGs): `viewBox="0 0 800 900"`

### 2.3 Pin Detector Compatibility
The component uses an automated red pin pixel counting algorithm (`isPinPixel`: `R > 150`, `G < 115`, `B < 125`, `A > 180`). Because all SVG lines strictly use `#000000` (Red channel = 0), there is zero risk of false-positive pin counting.

---

## 3. Asset Breakdown (14 SVG Files)

### 3.1 Body Views
1. **`public/img/penandaan-area-operasi/body-front.svg`**
   - **View**: Anterior full body.
   - **Landmarks**: Head, neck, clavicles, sternum, ribcage contours, umbilicus, iliac crest, groin/inguinal fold, thighs, patellas, shins, feet, arms, elbows, forearms, hands.
2. **`public/img/penandaan-area-operasi/body-back.svg`**
   - **View**: Posterior full body.
   - **Landmarks**: Occiput, neck, scapulae (shoulder blades), spinal column contour, lumbar curve, gluteal fold, popliteal fossae (back of knees), calves, heels.

### 3.2 Head Views
3. **`public/img/penandaan-area-operasi/head-front.svg`**
   - **View**: Anterior head and neck.
   - **Landmarks**: Cranial vault, forehead, eyebrows, eyes/eyelids, nose bridge & nostrils, lips, chin, jawline, ears (pinna), neck, clavicle base.
4. **`public/img/penandaan-area-operasi/head-back.svg`**
   - **View**: Posterior head and neck.
   - **Landmarks**: Occipital vault, posterior ears, nuchal line, neck, trapezius slope.
5. **`public/img/penandaan-area-operasi/head-left-profile.svg`**
   - **View**: Lateral left profile.
   - **Landmarks**: Forehead, nose, lips, chin, mandible/jaw angle, left ear, eye profile, neck contour.
6. **`public/img/penandaan-area-operasi/head-right-profile.svg`**
   - **View**: Lateral right profile.
   - **Landmarks**: Forehead, nose, lips, chin, mandible/jaw angle, right ear, eye profile, neck contour.

### 3.3 Hand Views
7. **`public/img/penandaan-area-operasi/hand-left-palm.svg`**
   - **View**: Left hand palmar surface.
   - **Landmarks**: Wrist crease, thenar & hypothenar eminences, main palmar flexion creases (life, head, heart lines), 5 digits, interphalangeal creases.
8. **`public/img/penandaan-area-operasi/hand-right-palm.svg`**
   - **View**: Right hand palmar surface.
   - **Landmarks**: Wrist crease, thenar & hypothenar eminences, main palmar flexion creases, 5 digits, interphalangeal creases.
9. **`public/img/penandaan-area-operasi/hand-left-dorsal.svg`**
   - **View**: Left hand dorsal surface.
   - **Landmarks**: Wrist, metacarpals, extensor tendon contours, knuckles (MCP joints), fingernails on 5 digits.
10. **`public/img/penandaan-area-operasi/hand-right-dorsal.svg`**
    - **View**: Right hand dorsal surface.
    - **Landmarks**: Wrist, metacarpals, extensor tendon contours, knuckles (MCP joints), fingernails on 5 digits.

### 3.4 Foot Views
11. **`public/img/penandaan-area-operasi/foot-left-plantar.svg`**
    - **View**: Left foot plantar surface (sole).
    - **Landmarks**: Calcaneal heel contour, medial arch curve, plantar flexion creases, ball of foot, hallux (big toe) and 4 toes with toe pads.
12. **`public/img/penandaan-area-operasi/foot-right-plantar.svg`**
    - **View**: Right foot plantar surface (sole).
    - **Landmarks**: Calcaneal heel contour, medial arch curve, plantar flexion creases, ball of foot, hallux and 4 toes with toe pads.
13. **`public/img/penandaan-area-operasi/foot-left-dorsal.svg`**
    - **View**: Left foot dorsal surface (top).
    - **Landmarks**: Ankle joint, medial & lateral malleoli (ankle bones), extensor tendons, 5 toes with toenails.
14. **`public/img/penandaan-area-operasi/foot-right-dorsal.svg`**
    - **View**: Right foot dorsal surface (top).
    - **Landmarks**: Ankle joint, medial & lateral malleoli (ankle bones), extensor tendons, 5 toes with toenails.

---

## 4. Verification & Testing Strategy

1. **SVG Structure & Syntax Validation**: Ensure all 14 SVG files are valid XML/SVG with clean `<svg>`, `<rect>`, and `<g>` tag wrappers.
2. **Asset Loading Verification**: Verify all 14 paths resolve correctly without 404 or load errors in `penandaan-area-operasi.blade.php`.
3. **Canvas Pin Interaction Test**: Ensure user clicks place red numbered pins accurately over the new anatomical diagrams and pin counter correctly registers pins without counting background lines.
