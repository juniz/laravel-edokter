# Timestamped Diagnostik Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menyertakan format tanggal dan jam pemeriksaan (`d/m/Y H:i`) pada hasil Lab dan Radiologi yang dimasukkan ke field Pemeriksaan Diagnostik.

**Architecture:** Memperbarui method `importDiagnostikLab` dan `importDiagnostikRadiologi` di `PenilaianPreOperasi.php`.

**Tech Stack:** Laravel 9, Livewire, Carbon.

## Global Constraints
- Tetap mematuhi batas maksimal 500 karakter per field (`mb_substr`).
- Menggunakan Carbon untuk formatting `d/m/Y H:i`.

---

### Task 1: Update Livewire Component Methods di `PenilaianPreOperasi.php`

**Files:**
- Modify: `app/Http/Livewire/Component/PenilaianPreOperasi.php:415-475`

- [ ] **Step 1: Update `importDiagnostikLab($index = null)`**

Tambahkan formatting tanggal & jam (`d/m/Y H:i`):
- Untuk single item: `[{tgl} {jam}] {Pemeriksaan}: {nilai} {satuan}`
- Untuk semua item: `Lab ({tgl} {jam}):\n- {Pemeriksaan}: {nilai} {satuan}`

- [ ] **Step 2: Update `importDiagnostikRadiologi($index = null)`**

Tambahkan formatting tanggal & jam (`d/m/Y H:i`):
- Untuk single item: `[{tgl} {jam}] Radiologi: {hasil}`
- Untuk semua item: `Radiologi ({tgl} {jam}):\n{hasil}`

---

### Task 2: Verifikasi Kode

- [ ] **Step 1: Jalankan PHP linting check**

Run: `php -l app/Http/Livewire/Component/PenilaianPreOperasi.php`
Expected: `No syntax errors detected`
