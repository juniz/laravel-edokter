# Odontogram Print Simplification Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove summary statistic cards and history table from `resources/views/prints/odontogram.blade.php`, leaving a clean A4 layout containing Kop header, patient info table with barcode, 1:1 tooth chart grid diagram, legend bar, declaration box, and TTE QR code signature footer.

**Architecture:** Modify `resources/views/prints/odontogram.blade.php` to strip `<table class="stats-table">` and `<div class="card card-outline card-secondary">` / history table block.

**Tech Stack:** Blade, HTML/CSS.

## Global Constraints

- Retain Kop header, patient metadata table with barcode, tooth grid diagram, legend bar, declaration box, and TTE QR code signature footer.

---

### Task 1: Remove Statistic Cards & History Table in `resources/views/prints/odontogram.blade.php`

**Files:**
- Modify: `resources/views/prints/odontogram.blade.php`

- [ ] **Step 1: Remove `stats-table` HTML markup and CSS**

In `resources/views/prints/odontogram.blade.php`:
Remove `<table class="stats-table">...</table>` and unused `.stats-table` CSS rules.

- [ ] **Step 2: Remove History Table section**

In `resources/views/prints/odontogram.blade.php`:
Remove the Riwayat Pemeriksaan Odontogram table section and associated unused styles if any.

- [ ] **Step 3: Commit Task 1**

```bash
git add resources/views/prints/odontogram.blade.php
git commit -m "feat: simplify Odontogram printout by removing statistic cards and history table"
```
