# Design Spec: Odontogram Print Labels Removal

**Date**: 2026-08-10  
**Feature**: Remove Quadrant & Section Labels from Odontogram Print Template (`resources/views/prints/odontogram.blade.php`)

---

## 🎯 Goal

Remove quadrant direction labels (`KANAN`, `KIRI`), chart title (`CHART ODONTOGRAM`), and section labels (`Gigi Permanen - Maxilla (Atas)`, `Gigi Desidui (Anak) - Atas`, `Gigi Desidui (Anak) - Bawah`, `Gigi Permanen - Mandibula (Bawah)`) from `resources/views/prints/odontogram.blade.php`, leaving a clean tooth chart diagram grid.

---

## 📐 Modifications (`resources/views/prints/odontogram.blade.php`)

1. **Remove Header Labels**: Remove `<div class="quadrant-header">` containing `KANAN (RIGHT)`, `CHART ODONTOGRAM`, `KIRI (LEFT)`.
2. **Remove Section Badges**:
   - Remove `<span class="section-badge badge-info-custom">Gigi Permanen - Maxilla (Atas)</span>`.
   - Remove `<tr><td colspan="16"><span class="section-badge badge-secondary-custom">Gigi Desidui (Anak) - Atas</span></td></tr>`.
   - Remove `<tr><td colspan="16"><span class="section-badge badge-secondary-custom">Gigi Desidui (Anak) - Bawah</span></td></tr>`.
   - Remove `<span class="section-badge badge-info-custom">Gigi Permanen - Mandibula (Bawah)</span>`.
3. **Retain Tooth Grid Diagram & Legend Bar**:
   - Keep tooth cell grid with numbers (18-11, 21-28, 55-51, 61-65, 85-81, 71-75, 48-41, 31-38) and condition PNG overlays.
   - Keep legend bar, declaration box, and TTE QR code footer.

---

## 🧪 Verification Plan

1. Open `/odontogram/cetak?no_rawat=...` in browser.
2. Verify labels `KANAN`, `KIRI`, `CHART ODONTOGRAM`, `Gigi Permanen`, `Gigi Desidui` are removed.
3. Verify clean tooth chart diagram grid, legend bar, and TTE signature footer render without layout breakage.
