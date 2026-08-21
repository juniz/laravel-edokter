# Fitur Multi-Record dan Hapus Data Penandaan Area Operasi Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambahkan kemampuan input lebih dari satu data penandaan area operasi untuk pasien/`no_rawat` yang sama, opsi hapus data penandaan dengan dialog konfirmasi, serta tabel riwayat penandaan dengan aksi edit, hapus, cetak, dan unduh PDF per data.

**Architecture:** 
1. Migrasi database untuk menghapus indeks unik pada `no_rawat` di tabel `penandaan_area_operasi` dan memanfaatkan kolom `id` (primary key auto-increment).
2. Komponen Livewire `PenandaanAreaOperasi` diperbarui untuk mendukung state multi-record (`$data`, `$selectedId`, `$modeEdit`, `$tanggalPenandaan`), method `simpan()` (insert), `edit($id)`, `update()`, `confirmHapus($id)`, `hapus()`, dan `batalEdit()`.
3. Service `PenandaanAreaOperasiService` dan Controller `PenandaanAreaOperasiController` diperbarui untuk mendukung parameter `$id` spesifik pada pembuatan PDF, pencetakan, pengunggahan berkas digital, dan verifikasi dokumen QR/HMAC.
4. Tampilan Blade `penandaan-area-operasi.blade.php` ditambahkan input tanggal penandaan, tombol Simpan/Ubah/Batal dinamis, serta tabel Riwayat Penandaan Area Operasi dengan tombol aksi (Edit, Hapus, Cetak, Download PDF).

**Tech Stack:** PHP 8.1+, Laravel 9, Livewire 2.x, MySQL/MariaDB, LivewireAlert (SweetAlert2), DomPDF, JavaScript Canvas API.

## Global Constraints
- Laravel 9 compatible (PHP 8.1+ syntax).
- Existing canvas diagram keys (14 anatomical view keys) must remain unchanged.
- Backward compatibility: Cetak / Download PDF tanpa parameter `id` harus tetap berfungsi dengan mengambil data penandaan terbaru untuk `no_rawat` tersebut.
- Konfirmasi penghapusan menggunakan `LivewireAlert` (`$this->confirm(...)` dan listener event).
- Semua test PHPUnit harus dijalankan dan dipastikan hijau (PASS).

---

### Task 1: Database Migration untuk Mendukung Multi-Record per `no_rawat`

**Files:**
- Create: `database/migrations/2026_08_21_000000_remove_unique_from_penandaan_area_operasi.php`
- Test: `tests/Feature/PenandaanAreaOperasiMigrationTest.php`

**Interfaces:**
- Table: `penandaan_area_operasi`
- Indexes: Drop unique index on `no_rawat`, add index `penandaan_area_operasi_no_rawat_index`.

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/PenandaanAreaOperasiMigrationTest.php
<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PenandaanAreaOperasiMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->requireTestingEnvironment();
    }

    public function test_it_allows_multiple_records_for_the_same_no_rawat(): void
    {
        $noRawat = '2026/08/21/000001';

        $id1 = DB::table('penandaan_area_operasi')->insertGetId([
            'no_rawat' => $noRawat,
            'no_rm' => '123456',
            'nama_pasien' => 'PASIEN MULTI TEST',
            'tgl_lahir' => '1990-01-01',
            'prosedur_operasi' => 'Prosedur Operasi 1',
            'operator_id' => 'D001',
            'nama_operator' => 'dr. Bedah Satu',
            'tanggal_penandaan' => '2026-08-21 08:00:00',
            'gambar_penandaan_base64' => json_encode(['body_front' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==']),
            'format_gambar' => 'image/png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id2 = DB::table('penandaan_area_operasi')->insertGetId([
            'no_rawat' => $noRawat,
            'no_rm' => '123456',
            'nama_pasien' => 'PASIEN MULTI TEST',
            'tgl_lahir' => '1990-01-01',
            'prosedur_operasi' => 'Prosedur Operasi 2',
            'operator_id' => 'D001',
            'nama_operator' => 'dr. Bedah Satu',
            'tanggal_penandaan' => '2026-08-21 14:00:00',
            'gambar_penandaan_base64' => json_encode(['body_back' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==']),
            'format_gambar' => 'image/png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertNotEquals($id1, $id2);
        $records = DB::table('penandaan_area_operasi')->where('no_rawat', $noRawat)->get();
        $this->assertCount(2, $records);
    }
}
```

- [ ] **Step 2: Run test to verify it fails if unique constraint is enforced**

Run: `vendor/bin/phpunit tests/Feature/PenandaanAreaOperasiMigrationTest.php`

- [ ] **Step 3: Write migration**

```php
// database/migrations/2026_08_21_000000_remove_unique_from_penandaan_area_operasi.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penandaan_area_operasi', function (Blueprint $table) {
            // Drop unique constraint if exists
            $table->dropUnique('penandaan_area_operasi_no_rawat_unique');
            $table->index('no_rawat', 'penandaan_area_operasi_no_rawat_index');
        });
    }

    public function down(): void
    {
        Schema::table('penandaan_area_operasi', function (Blueprint $table) {
            $table->dropIndex('penandaan_area_operasi_no_rawat_index');
            $table->unique('no_rawat', 'penandaan_area_operasi_no_rawat_unique');
        });
    }
};
```

- [ ] **Step 4: Run test to verify it passes**

Run: `vendor/bin/phpunit tests/Feature/PenandaanAreaOperasiMigrationTest.php`
Expected: PASS (1 test, 2 assertions)

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_08_21_000000_remove_unique_from_penandaan_area_operasi.php tests/Feature/PenandaanAreaOperasiMigrationTest.php
git commit -m "feat(database): remove unique constraint on no_rawat in penandaan_area_operasi"
```

---

### Task 2: Update Service dan Controller untuk Penanganan ID-Based (PDF, Cetak, Upload, Verifikasi)

**Files:**
- Modify: `app/Services/PenandaanAreaOperasiService.php`
- Modify: `app/Http/Controllers/PenandaanAreaOperasiController.php`
- Modify: `tests/Feature/PenandaanAreaOperasiServiceTest.php`

**Interfaces:**
- `PenandaanAreaOperasiService::generatePdf(string $noRawat, ?int $id = null): string`
- `PenandaanAreaOperasiService::uploadPdf(string $noRawat, string $pdfBinary, ?int $id = null): array`
- `PenandaanAreaOperasiService::generateAndUploadPdf(string $noRawat, ?int $id = null): array`
- `PenandaanAreaOperasiController::generateToken(string $noRawat, ?int $id = null): string`
- `PenandaanAreaOperasiController::cetak(Request $request)` (supports `id` param)
- `PenandaanAreaOperasiController::downloadPdf(Request $request)` (supports `id` param)
- `PenandaanAreaOperasiController::verifikasi(Request $request, ?string $token = null)`

- [ ] **Step 1: Write test cases in `PenandaanAreaOperasiServiceTest.php`**

Test that:
- `generatePdf` with `$id` fetches the specific record.
- `generatePdf` without `$id` fetches the latest record.
- `generateToken` produces consistent verification token for `$noRawat` and `$id`.
- `verifikasi` resolves token correctly.

- [ ] **Step 2: Run test to verify it fails with old method signature/logic**

Run: `vendor/bin/phpunit tests/Feature/PenandaanAreaOperasiServiceTest.php`

- [ ] **Step 3: Update `PenandaanAreaOperasiService.php` and `PenandaanAreaOperasiController.php`**

In `app/Services/PenandaanAreaOperasiService.php`:
- Update `generatePdf(string $noRawat, ?int $id = null)`:
  - If `$id` is provided, add `->where('penandaan_area_operasi.id', $id)`.
  - Else, add `->orderByDesc('penandaan_area_operasi.tanggal_penandaan')->orderByDesc('penandaan_area_operasi.id')`.
  - Update token generation call to pass `$marking->id`: `PenandaanAreaOperasiController::generateToken($marking->no_rawat, $marking->id)`.
- Update `uploadPdf(string $noRawat, string $pdfBinary, ?int $id = null)`:
  - If `$id` is given, suffix filename: `PENANDAAN_OPERASI_{sanitizedNoRawat}_{id}_{timestamp}`.
  - Delete old digital record for the same specific marking id / prefix.
- Update `generateAndUploadPdf(string $noRawat, ?int $id = null)`:
  - Pass `$id` to `generatePdf` and `uploadPdf`.

In `app/Http/Controllers/PenandaanAreaOperasiController.php`:
- Update `generateToken(string $noRawat, ?int $id = null): string`:
  - Normalized string: `str_replace('-', '/', $noRawat) . ($id ? '#' . $id : '')`.
- Update `cetak(Request $request)`:
  - Extract `$id = $request->input('id') ? (int) $request->input('id') : null`.
  - Query with `where('penandaan_area_operasi.id', $id)` if `$id` present, or latest.
- Update `downloadPdf(Request $request)`:
  - Extract `$id = $request->input('id') ? (int) $request->input('id') : null`.
  - Pass `$id` to `$service->generatePdf($noRawat, $id)`.
- Update `verifikasi(Request $request, ?string $token = null)`:
  - Search all records and match `generateToken($item->no_rawat, $item->id) === $param` or `generateToken($item->no_rawat) === $param`.

- [ ] **Step 4: Run test to verify it passes**

Run: `vendor/bin/phpunit tests/Feature/PenandaanAreaOperasiServiceTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Services/PenandaanAreaOperasiService.php app/Http/Controllers/PenandaanAreaOperasiController.php tests/Feature/PenandaanAreaOperasiServiceTest.php
git commit -m "feat(penandaan): support id-based pdf generation, printing, uploading, and verification"
```

---

### Task 3: Update Komponen Livewire `PenandaanAreaOperasi` untuk Multi-Record, Edit, Hapus, dan Reset

**Files:**
- Modify: `app/Http/Livewire/Component/PenandaanAreaOperasi.php`
- Create: `tests/Feature/PenandaanAreaOperasiMultiRecordTest.php`

**Interfaces:**
- Public Properties:
  - `$noRawat`, `$noRm`, `$jenisKelamin`, `$prosedurOperasi`, `$gambarPenandaanBase64`, `$savedGambarPenandaanBase64`
  - `$data = []` (List of markings)
  - `$selectedId = null` (ID being edited, or null)
  - `$modeEdit = false`
  - `$tanggalPenandaan` (datetime string format `Y-m-d\TH:i`)
  - `$selectedIdHapus = null`
- Public Methods:
  - `mount(string $noRawat, string $noRm): void`
  - `getData(): void`
  - `simpan(): void`
  - `edit(int $id): void`
  - `update(): void`
  - `confirmHapus(int $id): void`
  - `hapus(): void`
  - `batalEdit(): void`

- [ ] **Step 1: Write the failing tests in `tests/Feature/PenandaanAreaOperasiMultiRecordTest.php`**

```php
// tests/Feature/PenandaanAreaOperasiMultiRecordTest.php
<?php

namespace Tests\Feature;

use App\Http\Livewire\Component\PenandaanAreaOperasi;
use App\Services\PenandaanAreaOperasiService;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class PenandaanAreaOperasiMultiRecordTest extends TestCase
{
    private const PNG_BASE64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();
        $this->requireTestingEnvironment();
        // Setup doctor, patient, reg_periksa fixture tables
        $this->createFixtureTable('dokter', function ($table) {
            $table->string('kd_dokter')->primary();
            $table->string('nm_dokter');
        });
        $this->createFixtureTable('pasien', function ($table) {
            $table->string('no_rkm_medis')->primary();
            $table->string('nm_pasien');
            $table->date('tgl_lahir')->nullable();
            $table->enum('jk', ['L', 'P'])->default('L');
        });
        $this->createFixtureTable('reg_periksa', function ($table) {
            $table->string('no_rawat')->primary();
            $table->string('no_rkm_medis');
            $table->string('umurdaftar')->default('30');
            $table->string('sttsumur')->default('Th');
            $table->date('tgl_registrasi')->nullable();
        });
        $this->createFixtureTable('berkas_digital_perawatan', function ($table) {
            $table->string('no_rawat');
            $table->string('kode');
            $table->string('lokasi_file');
        });

        DB::table('dokter')->insert(['kd_dokter' => 'D001', 'nm_dokter' => 'dr. Bedah']);
        DB::table('pasien')->insert(['no_rkm_medis' => '000001', 'nm_pasien' => 'PASIEN TEST', 'jk' => 'L', 'tgl_lahir' => '1995-05-15']);
        DB::table('reg_periksa')->insert(['no_rawat' => '2026/08/21/000001', 'no_rkm_medis' => '000001']);
    }

    public function test_can_save_multiple_markings_for_same_no_rawat(): void
    {
        $mockService = \Mockery::mock(PenandaanAreaOperasiService::class);
        $mockService->shouldReceive('generateAndUploadPdf')->andReturn(['success' => true, 'is_replaced' => false]);
        $this->app->instance(PenandaanAreaOperasiService::class, $mockService);

        Livewire::withQueryParams(['username' => 'D001'])
            ->test(PenandaanAreaOperasi::class, ['noRawat' => '2026/08/21/000001', 'noRm' => '000001'])
            ->set('prosedurOperasi', 'Apendektomi Laparoskopi')
            ->set('gambarPenandaanBase64', ['body_front' => self::PNG_BASE64])
            ->call('simpan')
            ->assertHasNoErrors();

        Livewire::withQueryParams(['username' => 'D001'])
            ->test(PenandaanAreaOperasi::class, ['noRawat' => '2026/08/21/000001', 'noRm' => '000001'])
            ->set('prosedurOperasi', 'Herniotomi Inguinal')
            ->set('gambarPenandaanBase64', ['body_back' => self::PNG_BASE64])
            ->call('simpan')
            ->assertHasNoErrors();

        $count = DB::table('penandaan_area_operasi')->where('no_rawat', '2026/08/21/000001')->count();
        $this->assertEquals(2, $count);
    }

    public function test_can_edit_and_update_specific_marking(): void
    {
        $id = DB::table('penandaan_area_operasi')->insertGetId([
            'no_rawat' => '2026/08/21/000001',
            'no_rm' => '000001',
            'nama_pasien' => 'PASIEN TEST',
            'prosedur_operasi' => 'Prosedur Awal',
            'operator_id' => 'D001',
            'nama_operator' => 'dr. Bedah',
            'tanggal_penandaan' => '2026-08-21 09:00:00',
            'gambar_penandaan_base64' => json_encode(['body_front' => self::PNG_BASE64]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mockService = \Mockery::mock(PenandaanAreaOperasiService::class);
        $mockService->shouldReceive('generateAndUploadPdf')->with('2026/08/21/000001', $id)->andReturn(['success' => true]);
        $this->app->instance(PenandaanAreaOperasiService::class, $mockService);

        Livewire::withQueryParams(['username' => 'D001'])
            ->test(PenandaanAreaOperasi::class, ['noRawat' => '2026/08/21/000001', 'noRm' => '000001'])
            ->call('edit', $id)
            ->assertSet('modeEdit', true)
            ->assertSet('selectedId', $id)
            ->assertSet('prosedurOperasi', 'Prosedur Awal')
            ->set('prosedurOperasi', 'Prosedur Diperbarui')
            ->set('gambarPenandaanBase64', ['body_front' => self::PNG_BASE64])
            ->call('update')
            ->assertHasNoErrors()
            ->assertSet('modeEdit', false)
            ->assertSet('selectedId', null);

        $this->assertDatabaseHas('penandaan_area_operasi', [
            'id' => $id,
            'prosedur_operasi' => 'Prosedur Diperbarui',
        ]);
    }

    public function test_can_delete_marking(): void
    {
        $id = DB::table('penandaan_area_operasi')->insertGetId([
            'no_rawat' => '2026/08/21/000001',
            'no_rm' => '000001',
            'nama_pasien' => 'PASIEN TEST',
            'prosedur_operasi' => 'Prosedur Dihapus',
            'operator_id' => 'D001',
            'nama_operator' => 'dr. Bedah',
            'tanggal_penandaan' => '2026-08-21 09:00:00',
            'gambar_penandaan_base64' => json_encode(['body_front' => self::PNG_BASE64]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Livewire::withQueryParams(['username' => 'D001'])
            ->test(PenandaanAreaOperasi::class, ['noRawat' => '2026/08/21/000001', 'noRm' => '000001'])
            ->call('confirmHapus', $id)
            ->call('hapus');

        $this->assertDatabaseMissing('penandaan_area_operasi', ['id' => $id]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/phpunit tests/Feature/PenandaanAreaOperasiMultiRecordTest.php`

- [ ] **Step 3: Implement component methods in `app/Http/Livewire/Component/PenandaanAreaOperasi.php`**

```php
<?php

namespace App\Http\Livewire\Component;

use App\Services\PenandaanAreaOperasiService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class PenandaanAreaOperasi extends Component
{
    use LivewireAlert;

    private const DIAGRAM_KEYS = [
        'body_front', 'body_back', 'head_left_profile', 'head_right_profile',
        'head_front', 'head_back', 'hand_left_palm', 'hand_right_palm',
        'hand_left_dorsal', 'hand_right_dorsal', 'foot_left_plantar',
        'foot_right_plantar', 'foot_left_dorsal', 'foot_right_dorsal',
    ];

    public $noRawat;
    public $noRm;
    public $jenisKelamin = 'P';
    public $tanggalPenandaan;
    public $prosedurOperasi = '';
    public $gambarPenandaanBase64 = [];
    public $savedGambarPenandaanBase64 = [];

    public $data = [];
    public $selectedId = null;
    public $selectedIdHapus = null;
    public $modeEdit = false;

    protected $listeners = [
        'hapusPenandaanAreaOperasi' => 'hapus',
    ];

    public function mount(string $noRawat, string $noRm): void
    {
        $this->noRawat = $noRawat;
        $this->noRm = $noRm;
        $this->tanggalPenandaan = Carbon::now()->format('Y-m-d\TH:i');

        $pasien = DB::table('reg_periksa')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->where('reg_periksa.no_rawat', $this->noRawat)
            ->select('pasien.jk')
            ->first();

        if ($pasien && in_array($pasien->jk, ['L', 'P'], true)) {
            $this->jenisKelamin = $pasien->jk;
        }

        $this->getData();
    }

    public function getData(): void
    {
        if (empty($this->noRawat)) {
            $this->data = [];
            return;
        }

        $this->data = DB::table('penandaan_area_operasi')
            ->leftJoin('dokter', 'penandaan_area_operasi.operator_id', '=', 'dokter.kd_dokter')
            ->where('penandaan_area_operasi.no_rawat', $this->noRawat)
            ->select('penandaan_area_operasi.*', 'dokter.nm_dokter')
            ->orderBy('penandaan_area_operasi.tanggal_penandaan', 'desc')
            ->orderBy('penandaan_area_operasi.id', 'desc')
            ->get();
    }

    public function simpan(): void
    {
        $this->validate([
            'tanggalPenandaan' => ['required'],
            'prosedurOperasi' => ['required', 'string'],
            'gambarPenandaanBase64' => ['required', 'array', 'min:1', function ($attribute, $payload, $fail) {
                foreach ($payload as $key => $image) {
                    $decodedImage = is_string($image)
                        ? base64_decode(substr($image, 22), true)
                        : false;

                    if (! in_array($key, self::DIAGRAM_KEYS, true)
                        || ! is_string($image)
                        || ! str_starts_with($image, 'data:image/png;base64,')
                        || $decodedImage === false
                        || $decodedImage === '') {
                        $fail('The diagram payload must use canonical keys and valid PNG Base64 data URLs.');
                    }
                }
            }],
        ]);

        $operatorId = session('username');
        if (! is_string($operatorId) || trim($operatorId) === '') {
            $this->addError('operator', 'An authenticated operator is required.');
            $this->alert('error', 'Operator tidak terotentikasi. Silakan login kembali.');
            return;
        }

        try {
            $visit = DB::table('reg_periksa')
                ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
                ->where('reg_periksa.no_rawat', $this->noRawat)
                ->select('reg_periksa.no_rkm_medis', 'pasien.nm_pasien', 'pasien.tgl_lahir')
                ->first();
            $dokter = DB::table('dokter')->where('kd_dokter', $operatorId)->first();
            $namaPasien = $visit->nm_pasien ?? '';
            $tglLahir = $visit->tgl_lahir ?? null;
            $namaOperator = $dokter->nm_dokter ?? $operatorId;
            $noRm = $visit->no_rkm_medis ?? $this->noRm;

            $formattedTanggal = ! empty($this->tanggalPenandaan)
                ? Carbon::parse($this->tanggalPenandaan)->format('Y-m-d H:i:s')
                : Carbon::now()->format('Y-m-d H:i:s');

            $insertedId = DB::table('penandaan_area_operasi')->insertGetId([
                'no_rawat' => $this->noRawat,
                'no_rm' => $noRm,
                'nama_pasien' => $namaPasien,
                'tgl_lahir' => $tglLahir,
                'prosedur_operasi' => $this->prosedurOperasi,
                'operator_id' => $operatorId,
                'nama_operator' => $namaOperator,
                'tanggal_penandaan' => $formattedTanggal,
                'gambar_penandaan_base64' => json_encode($this->gambarPenandaanBase64, JSON_THROW_ON_ERROR),
                'format_gambar' => 'image/png',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $uploadResult = app(PenandaanAreaOperasiService::class)->generateAndUploadPdf($this->noRawat, $insertedId);

            $this->batalEdit();
            $this->getData();

            if (! empty($uploadResult['success'])) {
                $this->alert('success', 'Penandaan area operasi berhasil disimpan dan diunggah ke berkas digital.');
            } else {
                $errorMessage = $uploadResult['message'] ?? 'Error upload';
                Log::warning('Penandaan area operasi automatic upload failed: ' . $errorMessage, [
                    'no_rawat' => $this->noRawat,
                    'id' => $insertedId,
                ]);
                $this->alert('warning', 'Penandaan area operasi berhasil disimpan, namun gagal diunggah otomatis: ' . $errorMessage);
            }
        } catch (\Throwable $e) {
            Log::error('Gagal menyimpan penandaan area operasi: ' . $e->getMessage(), [
                'no_rawat' => $this->noRawat,
                'exception' => $e,
            ]);
            $this->alert('error', 'Gagal menyimpan penandaan area operasi: ' . $e->getMessage());
        }
    }

    public function edit(int $id): void
    {
        $marking = DB::table('penandaan_area_operasi')
            ->where('no_rawat', $this->noRawat)
            ->where('id', $id)
            ->first();

        if (! $marking) {
            $this->alert('warning', 'Data penandaan tidak ditemukan.');
            return;
        }

        $userDokter = session('username');
        if (! empty($marking->operator_id) && $marking->operator_id !== $userDokter) {
            $this->alert('warning', 'Anda tidak memiliki hak akses untuk mengubah data penandaan dokter lain.');
            return;
        }

        $this->selectedId = $marking->id;
        $this->modeEdit = true;
        $this->prosedurOperasi = $marking->prosedur_operasi;
        $this->tanggalPenandaan = Carbon::parse($marking->tanggal_penandaan)->format('Y-m-d\TH:i');

        try {
            $this->savedGambarPenandaanBase64 = json_decode($marking->gambar_penandaan_base64, true, 512, JSON_THROW_ON_ERROR) ?? [];
        } catch (\Throwable $e) {
            $this->savedGambarPenandaanBase64 = [];
        }

        $this->gambarPenandaanBase64 = $this->savedGambarPenandaanBase64;
        $this->emit('loadPenandaanCanvases', $this->savedGambarPenandaanBase64);
    }

    public function update(): void
    {
        if (empty($this->selectedId)) {
            $this->alert('warning', 'Data yang ingin diubah tidak valid.');
            return;
        }

        $this->validate([
            'tanggalPenandaan' => ['required'],
            'prosedurOperasi' => ['required', 'string'],
            'gambarPenandaanBase64' => ['required', 'array', 'min:1', function ($attribute, $payload, $fail) {
                foreach ($payload as $key => $image) {
                    $decodedImage = is_string($image)
                        ? base64_decode(substr($image, 22), true)
                        : false;

                    if (! in_array($key, self::DIAGRAM_KEYS, true)
                        || ! is_string($image)
                        || ! str_starts_with($image, 'data:image/png;base64,')
                        || $decodedImage === false
                        || $decodedImage === '') {
                        $fail('The diagram payload must use canonical keys and valid PNG Base64 data URLs.');
                    }
                }
            }],
        ]);

        try {
            $formattedTanggal = ! empty($this->tanggalPenandaan)
                ? Carbon::parse($this->tanggalPenandaan)->format('Y-m-d H:i:s')
                : Carbon::now()->format('Y-m-d H:i:s');

            DB::table('penandaan_area_operasi')
                ->where('id', $this->selectedId)
                ->where('no_rawat', $this->noRawat)
                ->update([
                    'prosedur_operasi' => $this->prosedurOperasi,
                    'tanggal_penandaan' => $formattedTanggal,
                    'gambar_penandaan_base64' => json_encode($this->gambarPenandaanBase64, JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ]);

            $uploadResult = app(PenandaanAreaOperasiService::class)->generateAndUploadPdf($this->noRawat, $this->selectedId);

            $this->batalEdit();
            $this->getData();

            if (! empty($uploadResult['success'])) {
                $this->alert('success', 'Penandaan area operasi berhasil diperbarui dan berkas digital disinkronkan.');
            } else {
                $this->alert('warning', 'Penandaan area operasi berhasil diperbarui.');
            }
        } catch (\Throwable $e) {
            Log::error('Gagal memperbarui penandaan area operasi: ' . $e->getMessage(), [
                'no_rawat' => $this->noRawat,
                'id' => $this->selectedId,
                'exception' => $e,
            ]);
            $this->alert('error', 'Gagal memperbarui penandaan area operasi: ' . $e->getMessage());
        }
    }

    public function confirmHapus(int $id): void
    {
        $marking = DB::table('penandaan_area_operasi')
            ->where('no_rawat', $this->noRawat)
            ->where('id', $id)
            ->first();

        if (! $marking) {
            $this->alert('warning', 'Data tidak ditemukan');
            return;
        }

        $userDokter = session('username');
        if (! empty($marking->operator_id) && $marking->operator_id !== $userDokter) {
            $this->alert('warning', 'Anda tidak memiliki akses untuk menghapus data penandaan dokter lain');
            return;
        }

        $this->selectedIdHapus = $id;
        $this->confirm('Apakah Anda yakin ingin menghapus data penandaan ini?', [
            'onConfirmed' => 'hapusPenandaanAreaOperasi',
            'cancelButtonText' => 'Batal',
            'confirmButtonText' => 'Ya, Hapus',
        ]);
    }

    public function hapus(): void
    {
        if (! $this->selectedIdHapus) {
            return;
        }

        try {
            $marking = DB::table('penandaan_area_operasi')
                ->where('id', $this->selectedIdHapus)
                ->where('no_rawat', $this->noRawat)
                ->first();

            if ($marking) {
                DB::table('penandaan_area_operasi')
                    ->where('id', $this->selectedIdHapus)
                    ->delete();

                if ($this->selectedId === $this->selectedIdHapus) {
                    $this->batalEdit();
                }

                $this->getData();
                $this->alert('success', 'Data penandaan area operasi berhasil dihapus');
            }
        } catch (\Throwable $e) {
            Log::error('Gagal menghapus penandaan area operasi: ' . $e->getMessage(), [
                'id' => $this->selectedIdHapus,
                'exception' => $e,
            ]);
            $this->alert('error', 'Gagal menghapus penandaan: ' . $e->getMessage());
        } finally {
            $this->selectedIdHapus = null;
        }
    }

    public function batalEdit(): void
    {
        $this->reset([
            'prosedurOperasi',
            'selectedId',
            'modeEdit',
            'gambarPenandaanBase64',
            'savedGambarPenandaanBase64',
        ]);
        $this->tanggalPenandaan = Carbon::now()->format('Y-m-d\TH:i');
        $this->resetErrorBag();
        $this->emit('resetAllCanvases');
    }

    public function render()
    {
        return view('livewire.component.penandaan-area-operasi');
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `vendor/bin/phpunit tests/Feature/PenandaanAreaOperasiMultiRecordTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Http/Livewire/Component/PenandaanAreaOperasi.php tests/Feature/PenandaanAreaOperasiMultiRecordTest.php
git commit -m "feat(penandaan): implement multi-record, edit, delete, and cancel logic in Livewire component"
```

---

### Task 4: Update Blade View dengan Tabel Riwayat, Aksi Per Baris, Input Waktu, dan Event Reset

**Files:**
- Modify: `resources/views/livewire/component/penandaan-area-operasi.blade.php`
- Create: `tests/Feature/PenandaanAreaOperasiViewTest.php`

- [ ] **Step 1: Write test verifying view rendering**

Test that view renders:
- `#tanggal-penandaan` datetime-local input
- Riwayat Penandaan Area Operasi card and table
- Action buttons (Edit, Hapus, Cetak, Download PDF) for each row
- Ubah Penandaan and Batal buttons when in `$modeEdit`

- [ ] **Step 2: Run test to verify it fails**

Run: `vendor/bin/phpunit tests/Feature/PenandaanAreaOperasiViewTest.php`

- [ ] **Step 3: Update `resources/views/livewire/component/penandaan-area-operasi.blade.php`**

- Add datetime-local field:
```blade
<div class="form-group">
    <label for="tanggal-penandaan"><i class="fas fa-clock mr-1 text-primary"></i> Tanggal & Jam Penandaan</label>
    <input
        type="datetime-local"
        wire:model.defer="tanggalPenandaan"
        id="tanggal-penandaan"
        class="form-control @error('tanggalPenandaan') is-invalid @enderror"
        required
    >
    @error('tanggalPenandaan')
        <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
    @enderror
</div>
```
- In `.penandaan-actions`, add conditional buttons:
  - If `$modeEdit`: Show "Ubah Penandaan" button (`id="ubah-penandaan"`) and "Batal" button (`wire:click="batalEdit"`).
  - If not `$modeEdit`: Show "Simpan" button (`id="simpan-penandaan"`).
- In JavaScript canvas manager:
  - Add listener `Livewire.on('resetAllCanvases', ...)` to clear all canvases back to blank diagrams and clear marked points array.
- In bottom section, add the History Table:
```blade
<div class="card card-outline card-info mt-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-history mr-1"></i> Riwayat Penandaan Area Operasi</h3>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead class="thead-dark">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th style="width: 170px;">Tanggal Penandaan</th>
                    <th>Prosedur Operasi</th>
                    <th style="width: 200px;">Dokter Operator</th>
                    <th style="width: 180px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal_penandaan)->format('d-m-Y H:i') }}</td>
                        <td>{{ $item->prosedur_operasi }}</td>
                        <td>{{ $item->nm_dokter ?? $item->nama_operator }}</td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button type="button" wire:click="edit({{ $item->id }})" class="btn btn-warning" title="Edit Penandaan">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" wire:click="confirmHapus({{ $item->id }})" class="btn btn-danger" title="Hapus Penandaan">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <a href="{{ route('penandaan-area-operasi.cetak', ['no_rawat' => $noRawat, 'id' => $item->id]) }}" target="_blank" class="btn btn-info" title="Cetak Form">
                                    <i class="fas fa-print"></i>
                                </a>
                                <a href="{{ route('penandaan-area-operasi.download-pdf', ['no_rawat' => $noRawat, 'id' => $item->id]) }}" target="_blank" class="btn btn-secondary" title="Download PDF">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">Belum ada riwayat penandaan area operasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
```

- [ ] **Step 4: Run test to verify it passes**

Run: `vendor/bin/phpunit tests/Feature/PenandaanAreaOperasiViewTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add resources/views/livewire/component/penandaan-area-operasi.blade.php tests/Feature/PenandaanAreaOperasiViewTest.php
git commit -m "feat(penandaan): add history table, row actions, and edit mode switching in blade view"
```

---

### Task 5: End-to-End Suite & Regression Verification

**Files:**
- Modify: `tests/Feature/PenandaanAreaOperasiTest.php`
- Run all test suites: `vendor/bin/phpunit` on all Penandaan tests.

- [ ] **Step 1: Update fixture setup in `tests/Feature/PenandaanAreaOperasiTest.php` to include `berkas_digital_perawatan` and `setting`**
- [ ] **Step 2: Run all tests to verify everything passes**

Run:
```bash
vendor/bin/phpunit tests/Feature/PenandaanAreaOperasiTest.php
vendor/bin/phpunit tests/Feature/PenandaanAreaOperasiAutoUploadTest.php
vendor/bin/phpunit tests/Feature/PenandaanAreaOperasiServiceTest.php
vendor/bin/phpunit tests/Feature/PenandaanAreaOperasiMultiRecordTest.php
vendor/bin/phpunit tests/Feature/PenandaanAreaOperasiViewTest.php
vendor/bin/phpunit tests/Feature/PenandaanAreaOperasiMigrationTest.php
```
Expected: All tests pass 100%.

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/PenandaanAreaOperasiTest.php
git commit -m "test(penandaan): update test fixtures and verify entire penandaan test suite"
```
