# Penandaan Area Operasi Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add an editable, mobile-friendly female operation-site marking form to the existing Ralan and Ranap examination pages, storing rendered diagram PNGs as portable Base64 JSON.

**Architecture:** A reusable Livewire component loads the active visit and persists one row per `no_rawat`. Browser-owned canvases render static anatomy backgrounds and numbered pins, then send PNG data URLs only when saving. A print controller reads the same payload and renders it without Canvas logic.

**Tech Stack:** Laravel 9, PHP 8, Livewire 2, AdminLTE/Bootstrap 4, MySQL `LONGTEXT`, Canvas API, PHPUnit 9.

## Global Constraints

- No sidebar/menu item, standalone list, global search, or role authorization.
- Mount the same component in both examination views; `no_rawat` is the sole record selector.
- Persist one editable record per `no_rawat`; its `gambar_penandaan_base64` is JSON mapping stable diagram keys to complete `data:image/png;base64,...` values.
- Save snapshots of patient and operator. Use `session('username')` as operator ID and resolve `dokter.nm_dokter`, falling back to that ID.
- Scope is the supplied female form; no male diagram, patient/family signature, or image signature.
- Only click/tap-to-add numbered pins. Never freehand ink or resizable regions.
- Exempt the Livewire Base64 property from `SanitizeInputMiddleware`; preserve exact bytes.

---

## File Structure

| Path | Responsibility |
|---|---|
| `database/migrations/2026_08_04_000000_create_penandaan_area_operasi_table.php` | One-row-per-visit schema. |
| `app/Http/Livewire/Component/PenandaanAreaOperasi.php` | Snapshot lookup, validation, upsert, browser events. |
| `resources/views/livewire/component/penandaan-area-operasi.blade.php` | Responsive form and Canvas lifecycle. |
| `public/img/penandaan-area-operasi/*.svg` | Fourteen static female anatomy backgrounds. |
| `app/Http/Controllers/PenandaanAreaOperasiController.php` | Read-only printable form lookup. |
| `resources/views/prints/penandaan-area-operasi.blade.php` | A4 printable Base64 document. |
| `routes/web.php` | Authenticated print route. |
| `resources/views/ralan/pemeriksaan-ralan.blade.php`, `resources/views/ranap/pemeriksaan-ranap.blade.php` | Component mounts. |
| `app/Http/Middleware/SanitizeInputMiddleware.php` | Base64 sanitation exception. |
| `tests/Feature/PenandaanAreaOperasiTest.php` | Persistence, validation, isolation, print tests. |
| `tests/Feature/SanitizeInputMiddlewareTest.php` | Base64 middleware regression. |

### Task 1: Add the persistence contract

**Files:**
- Create: `database/migrations/2026_08_04_000000_create_penandaan_area_operasi_table.php`
- Create: `tests/Feature/PenandaanAreaOperasiTest.php`

**Interfaces:**
- Produces table `penandaan_area_operasi`, uniquely addressable by `no_rawat`.

- [ ] **Step 1: Write the failing schema test**

```php
public function test_penandaan_table_has_required_columns_and_unique_visit(): void
{
    $this->assertTrue(Schema::hasTable('penandaan_area_operasi'));
    $columns = Schema::getColumnListing('penandaan_area_operasi');

    $this->assertContains('gambar_penandaan_base64', $columns);
    $this->assertContains('nama_operator', $columns);
    $indexes = DB::select(
        'SHOW INDEX FROM `penandaan_area_operasi` WHERE Key_name = ?',
        ['penandaan_area_operasi_no_rawat_unique']
    );

    $this->assertNotEmpty($indexes);
    $this->assertSame(0, (int) $indexes[0]->Non_unique);
}
```

- [ ] **Step 2: Verify it fails**

Run: `php artisan test --filter=PenandaanAreaOperasiTest`

Expected: FAIL because the table does not exist.

- [ ] **Step 3: Add the migration**

```php
Schema::create('penandaan_area_operasi', function (Blueprint $table) {
    $table->id();
    $table->string('no_rawat')->unique();
    $table->string('no_rm');
    $table->string('nama_pasien');
    $table->date('tgl_lahir')->nullable();
    $table->text('prosedur_operasi');
    $table->string('operator_id')->nullable();
    $table->string('nama_operator');
    $table->dateTime('tanggal_penandaan');
    $table->longText('gambar_penandaan_base64');
    $table->string('format_gambar')->default('image/png');
    $table->timestamps();
});
```

Implement `down()` with `Schema::dropIfExists('penandaan_area_operasi')`.

- [ ] **Step 4: Verify migration and test**

Run: `php artisan test --filter=PenandaanAreaOperasiTest`

Expected: the schema test passes in an environment where the normal test bootstrap has created the table. Do not run `php artisan migrate` or any migration command.

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_08_04_000000_create_penandaan_area_operasi_table.php tests/Feature/PenandaanAreaOperasiTest.php
git commit -m "feat: add operation site marking schema"
```

### Task 2: Implement safe Livewire persistence

**Files:**
- Create: `app/Http/Livewire/Component/PenandaanAreaOperasi.php`
- Modify: `app/Http/Middleware/SanitizeInputMiddleware.php`
- Modify: `tests/Feature/PenandaanAreaOperasiTest.php`
- Modify: `tests/Feature/SanitizeInputMiddlewareTest.php`

**Interfaces:**
- Consumes `mount(string $noRawat, string $noRm)`, `reg_periksa`, `pasien`, `dokter`, and `session('username')`.
- Produces `simpan(): void`, `loadPenandaanCanvases`, and an upserted visit row.

- [ ] **Step 1: Write failing component and middleware tests**

```php
$payload = ['body_front' => 'data:image/png;base64,' . base64_encode('front')];

Livewire::test(PenandaanAreaOperasi::class, [
    'noRawat' => '2026/08/04/000001', 'noRm' => '000001',
])
    ->set('prosedurOperasi', 'Appendectomy')
    ->set('gambarPenandaanBase64', $payload)
    ->call('simpan')
    ->assertHasNoErrors();

$this->assertDatabaseHas('penandaan_area_operasi', [
    'no_rawat' => '2026/08/04/000001',
    'gambar_penandaan_base64' => json_encode($payload),
]);
```

Add a `SanitizeInputMiddlewareTest` Livewire `syncInput` request named `gambarPenandaanBase64` containing `+`, `/`, and `=`; assert the exact data URL is retained.

- [ ] **Step 2: Verify failures**

Run: `php artisan test --filter='PenandaanAreaOperasiTest|SanitizeInputMiddlewareTest'`

Expected: FAIL because the component is absent and Base64 is sanitized.

- [ ] **Step 3: Implement canonical payload validation and upsert**

Define these exact keys:

```php
private const DIAGRAM_KEYS = [
    'body_front', 'body_back', 'head_left_profile', 'head_right_profile',
    'head_front', 'head_back', 'hand_left_palm', 'hand_right_palm',
    'hand_left_dorsal', 'hand_right_dorsal', 'foot_left_plantar',
    'foot_right_plantar', 'foot_left_dorsal', 'foot_right_dorsal',
];
```

Require `prosedurOperasi` to be `required|string`. Validate that the payload is non-empty, each key is in `DIAGRAM_KEYS`, each value begins with `data:image/png;base64,`, and `base64_decode(substr($image, 22), true)` is not false. Load visit data through `reg_periksa` joined to `pasien`; load the doctor name by `kd_dokter = session('username')` and fall back to that ID.

Use:

```php
DB::table('penandaan_area_operasi')->updateOrInsert(
    ['no_rawat' => $this->noRawat],
    ['no_rm' => $noRm, 'nama_pasien' => $namaPasien, 'tgl_lahir' => $tglLahir,
     'prosedur_operasi' => $this->prosedurOperasi, 'operator_id' => $operatorId,
     'nama_operator' => $namaOperator, 'tanggal_penandaan' => now(),
     'gambar_penandaan_base64' => json_encode($this->gambarPenandaanBase64, JSON_THROW_ON_ERROR),
     'format_gambar' => 'image/png', 'updated_at' => now(), 'created_at' => now()]
);
```

Emit `loadPenandaanCanvases` with decoded saved JSON during mount. Add `gambarPenandaanBase64` to the middleware `$except` array.

- [ ] **Step 4: Add reload, rejection, and isolation tests**

Assert an invalid `not-a-data-url` payload produces an error for `gambarPenandaanBase64`. Save the same visit twice and assert one row with updated procedure. Save a second visit and assert two rows with distinct payloads. Mount the first visit again and assert the emitted payload equals the original JSON.

- [ ] **Step 5: Run and commit**

Run: `php artisan test --filter='PenandaanAreaOperasiTest|SanitizeInputMiddlewareTest'`

Expected: PASS.

```bash
git add app/Http/Livewire/Component/PenandaanAreaOperasi.php app/Http/Middleware/SanitizeInputMiddleware.php tests/Feature/PenandaanAreaOperasiTest.php tests/Feature/SanitizeInputMiddlewareTest.php
git commit -m "feat: persist base64 operation site markings"
```

### Task 3: Build the mobile-first Canvas interface

**Files:**
- Create: `resources/views/livewire/component/penandaan-area-operasi.blade.php`
- Create: fourteen SVGs under `public/img/penandaan-area-operasi/`: `body-front`, `body-back`, `head-left-profile`, `head-right-profile`, `head-front`, `head-back`, `hand-left-palm`, `hand-right-palm`, `hand-left-dorsal`, `hand-right-dorsal`, `foot-left-plantar`, `foot-right-plantar`, `foot-left-dorsal`, and `foot-right-dorsal` (all `.svg`).

**Interfaces:**
- Consumes the Task 2 event and the fourteen canonical keys.
- Produces `window.PenandaanAreaOperasi.exportImages(): Record<string, string>`.

- [ ] **Step 1: Establish Canvas acceptance cases**

In the feature test class docblock, record these manual browser checks: at 375px width, a tap on `body_front` creates exactly pin 1; switching away/back preserves it; save/reload restores it; print displays the saved PNG. PHPUnit cannot execute Canvas rendering, so these checks are mandatory.

- [ ] **Step 2: Create clean static anatomy assets**

Trace the supplied female form into black-stroke/white-background SVGs, one exact view per named file. The SVGs contain only anatomy outlines; no patient metadata, pin, handwritten mark, or photo artifact. Each uses a stable viewBox and the filename maps directly from its canonical key by replacing underscores with hyphens.

- [ ] **Step 3: Implement Canvas lifecycle**

```html
<div wire:ignore id="penandaan-area-operasi-canvas-root">
  <canvas data-diagram="body_front" aria-label="Diagram badan depan"></canvas>
</div>
<button type="button" id="simpan-penandaan" class="btn btn-primary">Simpan</button>
```

Build one local diagrams array for all fourteen keys/assets. After loading each SVG, set its Canvas internal dimensions to a maximum 1200px long side, draw the background, and redraw stored PNG/pins. On `pointerup`, use `getBoundingClientRect()` and the Canvas scale to add a single red circle with the next number; do not use `click`, which duplicates touch events.

Keep canvases inside `wire:ignore`. The save handler must call `canvas.toDataURL('image/png')` for every diagram and then run `@this.set('gambarPenandaanBase64', images, false); @this.call('simpan');`. The `loadPenandaanCanvases` listener must load each saved data URL with `new Image()` and redraw it under the same key. Do not clear local canvases on an error response.

Display one active canvas with 44px minimum previous/next controls under 768px. Show the full paper-form grid at 768px or wider.

- [ ] **Step 4: Manually verify browser behavior**

Run: `php artisan serve`

Expected: in both Ralan and Ranap visits, touch produces exactly one numbered pin, save reports success, and a refresh restores every saved diagram.

- [ ] **Step 5: Commit**

```bash
git add resources/views/livewire/component/penandaan-area-operasi.blade.php public/img/penandaan-area-operasi
git commit -m "feat: add responsive operation site marking canvas"
```

### Task 4: Mount the card and deliver a printable view

**Files:**
- Modify: `resources/views/ralan/pemeriksaan-ralan.blade.php`
- Modify: `resources/views/ranap/pemeriksaan-ranap.blade.php`
- Create: `app/Http/Controllers/PenandaanAreaOperasiController.php`
- Create: `resources/views/prints/penandaan-area-operasi.blade.php`
- Modify: `routes/web.php`
- Modify: `tests/Feature/PenandaanAreaOperasiTest.php`

**Interfaces:**
- Consumes saved data and `GET /penandaan-area-operasi/cetak?no_rawat={no_rawat}`.
- Produces a card in both examination pages and a printable HTML representation.

- [ ] **Step 1: Write failing integration tests**

```php
$this->withSession(['username' => 'D001', 'password' => 'secret'])
    ->get(route('penandaan-area-operasi.cetak', ['no_rawat' => '2026/08/04/000001']))
    ->assertOk()
    ->assertSee('FORMULIR PENANDAAN AREA OPERASI')
    ->assertSee('data:image/png;base64,', false);
```

Also assert both examination views contain `livewire:component.penandaan-area-operasi` with the current `no_rawat` and `no_rm`.

- [ ] **Step 2: Verify failures**

Run: `php artisan test --filter=PenandaanAreaOperasiTest`

Expected: FAIL because the mounts and print endpoint are absent.

- [ ] **Step 3: Add the mounts and print endpoint**

Insert this card immediately before the existing `Laporan Operasi` card in both views:

```blade
<x-adminlte-card title="Penandaan Area Operasi" icon="fas fa-map-marker-alt" theme="info" maximizable collapsible="collapsed">
  <livewire:component.penandaan-area-operasi :no-rawat="request()->get('no_rawat')" :no-rm="request()->get('no_rm')" />
</x-adminlte-card>
```

Register the route with a query parameter (visit numbers can contain slashes):

```php
Route::get('/penandaan-area-operasi/cetak', [PenandaanAreaOperasiController::class, 'cetak'])
    ->middleware('loginauth')
    ->name('penandaan-area-operasi.cetak');
```

The controller validates `no_rawat` as `required|string`, calls `firstOrFail()`, decodes with `json_decode(..., true, 512, JSON_THROW_ON_ERROR)`, and returns the print view. The print view escapes text fields, renders every image as `<img src="{{ $image }}">`, and applies A4 `@media print` CSS. It must show patient snapshot, free-text procedure, operator name/timestamp, and all diagrams; it must not show either signature section.

- [ ] **Step 4: Run tests and inspect print output**

Run: `php artisan test --filter=PenandaanAreaOperasiTest && php artisan test`

Expected: all tests pass. A print preview from saved Ralan and Ranap records shows all images and no signature fields.

- [ ] **Step 5: Commit**

```bash
git add resources/views/ralan/pemeriksaan-ralan.blade.php resources/views/ranap/pemeriksaan-ranap.blade.php app/Http/Controllers/PenandaanAreaOperasiController.php resources/views/prints/penandaan-area-operasi.blade.php routes/web.php tests/Feature/PenandaanAreaOperasiTest.php
git commit -m "feat: embed and print operation site marking"
```

### Task 5: Verify the complete workflow

**Files:**
- Modify: `docs/superpowers/specs/2026-08-04-penandaan-area-operasi-design.md` only if implementation changes an approved behavior.

**Interfaces:**
- Consumes Tasks 1–4.
- Produces a verified feature while leaving user-owned `.gitignore` and visual-companion files unstaged.

- [ ] **Step 1: Run static and automated verification**

Run: `php -l app/Http/Livewire/Component/PenandaanAreaOperasi.php && php -l app/Http/Controllers/PenandaanAreaOperasiController.php && php artisan test`

Expected: no syntax errors and all tests pass.

- [ ] **Step 2: Verify cross-application portability**

Run: `php artisan tinker --execute="\$row = DB::table('penandaan_area_operasi')->first(); \$images = json_decode(\$row->gambar_penandaan_base64, true, 512, JSON_THROW_ON_ERROR); foreach (\$images as \$image) { if (!str_starts_with(\$image, 'data:image/png;base64,') || base64_decode(substr(\$image, 22), true) === false) { throw new RuntimeException('Invalid image payload'); } } echo count(\$images);"`

Expected: a positive diagram count and no exception.

- [ ] **Step 3: Complete mobile, desktop, and print acceptance**

At 375px, verify all fourteen diagrams are reachable, each touch makes one pin, controls are at least 44px, and a saved form reloads. At desktop width, verify all diagrams appear in paper-form order. In print preview, verify title, patient snapshot, procedure, operator/timestamp, and diagrams; verify signatures are absent.

- [ ] **Step 4: Review and commit intended files only**

Run: `git status --short && git diff --check`

Expected: no whitespace errors. Do not stage user-owned `.gitignore` or generated `.superpowers/` files.

If the approved design document did not change during implementation, do not create an empty verification commit. If it did change, commit only that file:

```bash
git add docs/superpowers/specs/2026-08-04-penandaan-area-operasi-design.md
git commit -m "docs: align operation site marking design"
```
