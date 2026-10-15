# Penandaan Area Operasi: Lingkaran Merah Solid Tanpa Angka Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menghilangkan teks angka pada penanda diagram anatomi di formulir penandaan area operasi sehingga hanya menyisakan lingkaran merah solid dengan border penegas, serta memperbarui petunjuk antarmuka pengguna.

**Architecture:** Modifikasi file Blade Livewire `penandaan-area-operasi.blade.php` pada blok teks petunjuk instruksi dan skrip canvas HTML5 2D. Pada skrip canvas, hapus rendering font/fillText putih dan hapus sisa variabel counter penomoran pin, dengan tetap mempertahankan cluster piksel merah untuk deteksi reset.

**Tech Stack:** Laravel 9, Livewire 2, HTML5 Canvas 2D API, Blade, PHPUnit.

## Global Constraints
- Target file: `resources/views/livewire/component/penandaan-area-operasi.blade.php`
- Pertahankan kompatibilitas deteksi tombol reset `countRedPins()` dengan warna fill `#c6283d` dan stroke `#8f1728`.
- Tidak ada perubahan skema database atau kontrak API controller/Livewire.
- Ikuti standar commit proyek (Conventional Commits).

---

### Task 1: Update Teks Petunjuk dan Tambah Test Assertion

**Files:**
- Modify: `tests/Feature/PenandaanAreaOperasiViewTest.php:100-110`
- Modify: `resources/views/livewire/component/penandaan-area-operasi.blade.php:31-35`

**Interfaces:**
- Produces: Petunjuk UI bertuliskan "Ketuk bagian tubuh untuk menambahkan penanda lingkaran merah."

- [ ] **Step 1: Write the failing test**

Tambahkan method test baru di `tests/Feature/PenandaanAreaOperasiViewTest.php`:

```php
    public function test_view_renders_correct_instructions_for_red_circle_markers(): void
    {
        Livewire::withQueryParams(['username' => 'D001'])
            ->test(PenandaanAreaOperasi::class, ['noRawat' => '2026/08/21/000001', 'noRm' => '000001'])
            ->assertSee('Ketuk bagian tubuh untuk menambahkan penanda lingkaran merah.')
            ->assertDontSee('Ketuk bagian tubuh untuk menambahkan penanda merah bernomor.');
    }
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/PenandaanAreaOperasiViewTest.php --filter=test_view_renders_correct_instructions_for_red_circle_markers`
Expected: FAIL (karena teks lama masih bertuliskan "bernomor").

- [ ] **Step 3: Write minimal implementation**

Ubah baris 33 di `resources/views/livewire/component/penandaan-area-operasi.blade.php`:

```blade
    <div class="penandaan-instructions" aria-label="Petunjuk penandaan">
        <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
        <span>Ketuk bagian tubuh untuk menambahkan penanda lingkaran merah.</span>
    </div>
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/PenandaanAreaOperasiViewTest.php --filter=test_view_renders_correct_instructions_for_red_circle_markers`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add tests/Feature/PenandaanAreaOperasiViewTest.php resources/views/livewire/component/penandaan-area-operasi.blade.php
git commit -m "feat(penandaan): update instruction text for unnumbered circle markers"
```

---

### Task 2: Modifikasi Skrip Canvas untuk Menghilangkan Angka dan Bersihkan Dead Code

**Files:**
- Modify: `resources/views/livewire/component/penandaan-area-operasi.blade.php:400,547-591,693,726,758`

**Interfaces:**
- Consumes: User click / pointerup event pada elemen `<canvas data-diagram="...">`.
- Produces: Lingkaran merah solid `#c6283d` dengan border `#8f1728` tanpa teks angka putih.

- [ ] **Step 1: Modifikasi fungsi `addPin` dan hapus penomoran pada canvas script**

Di `resources/views/livewire/component/penandaan-area-operasi.blade.php`:
1. Hapus baris deklarasi:
   `var nextPinNumber = 1;` (sekitar baris 400).
2. Hapus fungsi `refreshNextPinNumber()` (sekitar baris 547-553).
3. Ubah fungsi `addPin(state, event)` menjadi hanya menggambar lingkaran merah solid:

```javascript
        function addPin(state, event) {
            if (!state.ready || event.button > 0) {
                return;
            }

            var rect = state.canvas.getBoundingClientRect();

            if (!rect.width || !rect.height) {
                return;
            }

            var scaleX = state.canvas.width / rect.width;
            var scaleY = state.canvas.height / rect.height;
            var x = (event.clientX - rect.left) * scaleX;
            var y = (event.clientY - rect.top) * scaleY;
            var radius = Math.max(16, Math.min(state.canvas.width, state.canvas.height) * .025);

            state.context.save();
            state.context.beginPath();
            state.context.arc(x, y, radius, 0, Math.PI * 2);
            state.context.fillStyle = '#c6283d';
            state.context.fill();
            state.context.lineWidth = Math.max(3, radius * .16);
            state.context.strokeStyle = '#8f1728';
            state.context.stroke();
            state.context.restore();

            savedPinCounts[state.key] = (savedPinCounts[state.key] || 0) + 1;
            updateResetButtonsState();
        }
```

4. Hapus pemanggilan `refreshNextPinNumber();` di `loadSavedImages()` (sekitar baris 693).
5. Hapus `nextPinNumber = 1;` di fungsi `resetAll()` (sekitar baris 726).
6. Hapus pemanggilan `refreshNextPinNumber();` di event listener `resetActiveButton` (sekitar baris 758).

- [ ] **Step 2: Jalankan validasi sintaks PHP dan pengujian unit**

Run: `php -l resources/views/livewire/component/penandaan-area-operasi.blade.php`
Expected: `No syntax errors detected in resources/views/livewire/component/penandaan-area-operasi.blade.php`

Run: `php artisan test tests/Feature/PenandaanAreaOperasiViewTest.php`
Expected: PASS (Semua 6 test lulus).

- [ ] **Step 3: Jalankan seluruh test suite penandaan area operasi**

Run: `php artisan test --filter=PenandaanAreaOperasi`
Expected: PASS (Semua test penandaan area operasi lulus tanpa regresi).

- [ ] **Step 4: Commit**

```bash
git add resources/views/livewire/component/penandaan-area-operasi.blade.php
git commit -m "feat(penandaan): remove number rendering from canvas circle markers"
```
