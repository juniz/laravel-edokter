# Task 1 Brief: Create Female Body SVGs (`body-front.svg`, `body-back.svg`) Matching Reference Image

**Plan:** `docs/superpowers/plans/2026-08-04-custom-anatomy-body-svgs.md`  
**Report Target File:** `.superpowers/sdd/2026-08-04-custom-anatomy-body-svgs/task-1-report.md`

## Requirements

1. **`public/img/penandaan-area-operasi/body-front.svg`**:
   - `viewBox="0 0 600 1200"`
   - White fill (`fill="#ffffff"`)
   - Smooth curved black stroke (`stroke="#000000"`, `stroke-width="5"`, `stroke-linecap="round"`, `stroke-linejoin="round"`)
   - Anatomical features matching reference drawing: head with eyes, nose dot, mouth line, ears, neck lines, chest with breast curves & nipples, umbilicus dot, groin/pubic V-line, flared arms with open hands, curved waist & hips, legs, 5 toes.

2. **`public/img/penandaan-area-operasi/body-back.svg`**:
   - `viewBox="0 0 600 1200"`
   - White fill (`fill="#ffffff"`)
   - Smooth curved black stroke (`stroke="#000000"`, `stroke-width="5"`, `stroke-linecap="round"`, `stroke-linejoin="round"`)
   - Anatomical features matching reference drawing: head back contour, neck lines, spine line, elbow crease lines, gluteal fold (buttocks), popliteal knee creases, heel/Achilles tendon lines.

3. **Validation**:
   - Run: `php -r 'simplexml_load_file("public/img/penandaan-area-operasi/body-front.svg") or exit(1); simplexml_load_file("public/img/penandaan-area-operasi/body-back.svg") or exit(1); echo "Female Body SVGs valid\n";'`

4. **Commit**:
   - `git add public/img/penandaan-area-operasi/body-front.svg public/img/penandaan-area-operasi/body-back.svg`
   - `git commit -m "feat(assets): update female body front and back SVGs to match reference drawing"`

---
Write status, commit hash, and test result summary to `.superpowers/sdd/2026-08-04-custom-anatomy-body-svgs/task-1-report.md`.
