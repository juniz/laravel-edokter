# Task 2 Brief: Create Male Body SVGs (`body-front-male.svg`, `body-back-male.svg`)

**Plan:** `docs/superpowers/plans/2026-08-04-custom-anatomy-body-svgs.md`  
**Report Target File:** `.superpowers/sdd/2026-08-04-custom-anatomy-body-svgs/task-2-report.md`

## Requirements

1. **`public/img/penandaan-area-operasi/body-front-male.svg`**:
   - `viewBox="0 0 600 1200"`
   - White fill (`fill="#ffffff"`)
   - Black stroke (`stroke="#000000"`, `stroke-width="5"`, `stroke-linecap="round"`, `stroke-linejoin="round"`)
   - Matching line art style: broader male shoulders, pectoral chest line (without female breasts), umbilicus dot, waist, groin V-crease, straight legs, arms, head & facial features.

2. **`public/img/penandaan-area-operasi/body-back-male.svg`**:
   - `viewBox="0 0 600 1200"`
   - White fill (`fill="#ffffff"`)
   - Black stroke (`stroke="#000000"`, `stroke-width="5"`, `stroke-linecap="round"`, `stroke-linejoin="round"`)
   - Matching line art style: broad upper back slope, neck, spine line, scapulae lines, elbow creases, gluteal fold, popliteal knee creases, heel lines.

3. **Validation**:
   - Run: `php -r 'simplexml_load_file("public/img/penandaan-area-operasi/body-front-male.svg") or exit(1); simplexml_load_file("public/img/penandaan-area-operasi/body-back-male.svg") or exit(1); echo "Male Body SVGs valid\n";'`

4. **Commit**:
   - `git add public/img/penandaan-area-operasi/body-front-male.svg public/img/penandaan-area-operasi/body-back-male.svg`
   - `git commit -m "feat(assets): add male body front and back SVGs in matching line art style"`

---
Write status, commit hash, and test result summary to `.superpowers/sdd/2026-08-04-custom-anatomy-body-svgs/task-2-report.md`.
