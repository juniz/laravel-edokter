# Odontogram Print Labels Removal Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove quadrant header labels (`KANAN`, `KIRI`, `CHART ODONTOGRAM`) and section labels (`Gigi Permanen`, `Gigi Desidui`) from `resources/views/prints/odontogram.blade.php`.

**Architecture:** Modify `resources/views/prints/odontogram.blade.php` to remove `<div class="quadrant-header">` and section label badges (`section-badge`).

**Tech Stack:** Blade, HTML/CSS.

## Global Constraints

- Retain tooth diagram grid numbers (18-11, 21-28, 55-51, 61-65, 85-81, 71-75, 48-41, 31-38) and PNG condition overlays.
- Retain legend bar, declaration box, and TTE QR code signature footer.

---

### Task 1: Remove Labels in `resources/views/prints/odontogram.blade.php`

**Files:**
- Modify: `resources/views/prints/odontogram.blade.php`

- [ ] **Step 1: Remove `quadrant-header` and `section-badge` elements**

In `resources/views/prints/odontogram.blade.php`:
Remove:
- `<div class="quadrant-header">...</div>`
- `<div class="section-label-container"><span class="section-badge badge-info-custom">Gigi Permanen - Maxilla (Atas)</span></div>`
- `<tr><td colspan="16" class="text-center" style="padding: 1px 0;"><span class="section-badge badge-secondary-custom">Gigi Desidui (Anak) - Atas</span></td></tr>`
- `<tr><td colspan="16" class="text-center" style="padding: 1px 0;"><span class="section-badge badge-secondary-custom">Gigi Desidui (Anak) - Bawah</span></td></tr>`
- `<div class="section-label-container" style="margin-top: 2px;"><span class="section-badge badge-info-custom">Gigi Permanen - Mandibula (Bawah)</span></div>`

- [ ] **Step 2: Commit Task 1**

```bash
git add resources/views/prints/odontogram.blade.php
git commit -m "feat: remove Kanan, Kiri, Chart Odontogram, Gigi Permanen and Gigi Desidui labels from printout"
```
