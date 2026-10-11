# Tabel Riwayat Penilaian Awal Medis IGD di Modal Form Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menampilkan tabel riwayat input asesmen medis IGD pasien (`penilaian_medis_igd`) di bagian bawah modal form dokter `App\Http\Livewire\Component\AwalIgd\Form` lengkap dengan aksi Edit dan Salin dari riwayat lama.

**Architecture:** Tambahkan property `$dataRiwayat` array dan method `loadRiwayat()`, `editRiwayat()`, `salinDariRiwayat()` di Livewire component, sinkronisasi saat rawat di-update, simpan, atau hapus, serta render card tabel riwayat di bawah form pada Blade view.

**Tech Stack:** Laravel 9, Livewire 2, AdminLTE 3 / Bootstrap 4, FontAwesome, PHPUnit.

## Global Constraints
- PHP 8.x compatible dengan Laravel 9.
- `$dataRiwayat` wajib berupa array murni PHP (`.toArray()`) untuk mencegah serialization crash Livewire 2.
- Menggunakan LivewireAlert untuk notifikasi sukses/gagal.
- Kompatibel dengan alur input dan hapus yang ada tanpa merusak form dokter.

---

### Task 1: Backend Update di Component Class `Form.php` & Unit Test

**Files:**
- Modify: `app/Http/Livewire/Component/AwalIgd/Form.php`
- Modify: `tests/Unit/AwalIgdFormTest.php`

**Interfaces:**
- Produces:
  - `public $dataRiwayat = []`
  - `public function loadRiwayat(): void`
  - `public function editRiwayat(string $noRawat): void`
  - `public function salinDariRiwayat(string $noRawat): void`

- [ ] **Step 1: Write the failing test**

Tambahkan test di `tests/Unit/AwalIgdFormTest.php`:
```php
    public function test_component_has_data_riwayat_property_and_methods()
    {
        $component = new Form();
        $this->assertTrue(property_exists($component, 'dataRiwayat'));
        $this->assertTrue(method_exists($component, 'loadRiwayat'));
        $this->assertTrue(method_exists($component, 'editRiwayat'));
        $this->assertTrue(method_exists($component, 'salinDariRiwayat'));
    }
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=AwalIgdFormTest`
Expected: FAIL (method or property missing).

- [ ] **Step 3: Implement minimal code in Form.php**

1. Tambah property:
```php
public $dataRiwayat = [];
```
2. In `resetInput()`:
```php
$this->dataRiwayat = [];
```
3. Implement `loadRiwayat()`:
```php
public function loadRiwayat()
{
    if (!$this->no_rawat) {
        $this->dataRiwayat = [];
        return;
    }

    $noRm = DB::table('reg_periksa')->where('no_rawat', $this->no_rawat)->value('no_rkm_medis');
    if (!$noRm) {
        $this->dataRiwayat = [];
        return;
    }

    $this->dataRiwayat = DB::table('penilaian_medis_igd')
        ->join('reg_periksa', 'penilaian_medis_igd.no_rawat', '=', 'reg_periksa.no_rawat')
        ->leftJoin('dokter', 'penilaian_medis_igd.kd_dokter', '=', 'dokter.kd_dokter')
        ->where('reg_periksa.no_rkm_medis', $noRm)
        ->select('penilaian_medis_igd.*', 'dokter.nm_dokter', 'reg_periksa.tgl_registrasi')
        ->orderByDesc('penilaian_medis_igd.tanggal')
        ->get()
        ->map(fn($item) => (array) $item)
        ->toArray();
}
```
4. Panggil `$this->loadRiwayat()` di:
   - `updatedNoRawat()` (setelah load form)
   - `simpan()` (setelah upsert)
   - `hapus()` (setelah delete)
5. Implement `editRiwayat($noRawat)`:
```php
public function editRiwayat($noRawat)
{
    $this->no_rawat = $noRawat;
    $this->updatedNoRawat();
    $this->alert('info', 'Data penilaian medis IGD dimuat ke form');
}
```
6. Implement `salinDariRiwayat($noRawat)`:
```php
public function salinDariRiwayat($noRawat)
{
    $data = DB::table('penilaian_medis_igd')->where('no_rawat', $noRawat)->first();
    if (!$data) {
        $this->alert('warning', 'Data riwayat tidak ditemukan');
        return;
    }

    if (!empty($data->keluhan_utama)) $this->keluhan_utama = $data->keluhan_utama;
    if (!empty($data->rps)) $this->rps = $data->rps;
    if (!empty($data->rpk)) $this->rpk = $data->rpk;
    if (!empty($data->rpd)) $this->rpd = $data->rpd;
    if (!empty($data->rpo)) $this->rpo = $data->rpo;
    if (!empty($data->alergi)) $this->alergi = $data->alergi;
    if (!empty($data->keadaan)) $this->keadaan_umum = $data->keadaan;
    if (!empty($data->kesadaran)) $this->kesadaran = $data->kesadaran;
    if (!empty($data->gcs)) $this->gcs = $data->gcs;
    if (!empty($data->tb)) $this->tb = $data->tb;
    if (!empty($data->bb)) $this->bb = $data->bb;
    if (!empty($data->td)) $this->td = $data->td;
    if (!empty($data->nadi)) $this->nadi = $data->nadi;
    if (!empty($data->rr)) $this->rr = $data->rr;
    if (!empty($data->suhu)) $this->suhu = $data->suhu;
    if (!empty($data->spo)) $this->spo2 = $data->spo;
    if (!empty($data->kepala)) $this->kepala = $data->kepala;
    if (!empty($data->mata)) $this->mata = $data->mata;
    if (!empty($data->thoraks)) $this->thoraks = $data->thoraks;
    if (!empty($data->abdomen)) $this->abdomen = $data->abdomen;
    if (!empty($data->gigi)) $this->gigi = $data->gigi;
    if (!empty($data->genital)) $this->genital = $data->genital;
    if (!empty($data->leher)) $this->leher = $data->leher;
    if (!empty($data->ekstremitas)) $this->ekstremitas = $data->ekstremitas;
    if (!empty($data->ket_fisik)) $this->ket_fisik = $data->ket_fisik;
    if (!empty($data->ket_lokalis)) $this->ket_lokalis = $data->ket_lokalis;
    if (!empty($data->ekg)) $this->ekg = $data->ekg;
    if (!empty($data->rad)) $this->radiologi = $data->rad;
    if (!empty($data->lab)) $this->laborat = $data->lab;
    if (!empty($data->diagnosis)) $this->diagnosis = $data->diagnosis;
    if (!empty($data->tata)) $this->tatalaksana = $data->tata;

    $this->alert('success', 'Data berhasil disalin dari riwayat kunjungan lampau');
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=AwalIgdFormTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Livewire/Component/AwalIgd/Form.php tests/Unit/AwalIgdFormTest.php
git commit -m "feat: add dataRiwayat property and history management methods in AwalIgd Form"
```

---

### Task 2: Implementasi Tabel Riwayat di Blade View

**Files:**
- Modify: `resources/views/livewire/component/awal-igd/form.blade.php`

**Interfaces:**
- Consumes: `$dataRiwayat`, `wire:click="editRiwayat"`, `wire:click="salinDariRiwayat"` from `Form` component.

- [ ] **Step 1: Tambahkan markup tabel di bawah form**

Tepat setelah `</form>` dan sebelum modal selector SOAP:
```blade
    <!-- Tabel Riwayat Penilaian Medis IGD Pasien -->
    <div class="card card-outline card-info mt-4 shadow-sm">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <h6 class="card-title text-sm mb-0">
                <i class="fas fa-history text-info mr-1"></i> <strong>Riwayat Penilaian Awal Medis IGD Pasien</strong>
            </h6>
            <span class="badge badge-info">{{ count($dataRiwayat) }} Catatan</span>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover table-striped mb-0 text-sm">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th style="width: 170px;">Waktu & No. Rawat</th>
                        <th style="width: 180px;">Dokter Pemeriksa</th>
                        <th>Keluhan & TTV</th>
                        <th>Diagnosis & Tatalaksana</th>
                        <th class="text-center" style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataRiwayat as $index => $row)
                    @php
                        $isCurrentVisit = (data_get($row, 'no_rawat') === $no_rawat);
                        $tglRow = data_get($row, 'tanggal');
                    @endphp
                    <tr class="{{ $isCurrentVisit ? 'table-info' : '' }}">
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ !empty($tglRow) ? date('d/m/Y H:i', strtotime($tglRow)) : '-' }}</strong>
                            <small class="text-muted d-block">{{ data_get($row, 'no_rawat') }}</small>
                            @if($isCurrentVisit)
                                <span class="badge badge-success">Kunjungan Ini</span>
                            @else
                                <span class="badge badge-secondary">Kunjungan Lalu</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ data_get($row, 'nm_dokter') ?? data_get($row, 'kd_dokter') ?? '-' }}</strong>
                            <div class="text-xs text-muted">{{ data_get($row, 'anamnesis') ?? '-' }}</div>
                        </td>
                        <td>
                            <div class="font-weight-bold mb-1">{{ data_get($row, 'keluhan_utama') ?: '-' }}</div>
                            <div class="text-xs text-muted">
                                TD: {{ data_get($row, 'td') ?: '-' }} | N: {{ data_get($row, 'nadi') ?: '-' }} | S: {{ data_get($row, 'suhu') ?: '-' }} | RR: {{ data_get($row, 'rr') ?: '-' }} | SpO2: {{ data_get($row, 'spo') ?: '-' }}
                            </div>
                        </td>
                        <td>
                            <div><strong>Diagnosis:</strong> {{ data_get($row, 'diagnosis') ?: '-' }}</div>
                            <div class="text-xs text-muted"><strong>Tatalaksana:</strong> {{ \Illuminate\Support\Str::limit(data_get($row, 'tata') ?: '-', 80) }}</div>
                        </td>
                        <td class="text-center align-middle">
                            @if($isCurrentVisit)
                                <button type="button" class="btn btn-xs btn-warning mb-1" wire:click="editRiwayat('{{ data_get($row, 'no_rawat') }}')" title="Muat ke Form">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </button>
                            @else
                                <button type="button" class="btn btn-xs btn-outline-primary" wire:click="salinDariRiwayat('{{ data_get($row, 'no_rawat') }}')" title="Salin Data Riwayat ke Form">
                                    <i class="fas fa-copy mr-1"></i> Salin
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            <i class="fas fa-info-circle mr-1"></i> Belum ada riwayat penilaian medis IGD untuk pasien ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
```

- [ ] **Step 2: Clear view cache**

Run: `php artisan view:clear`
Expected: `Compiled views cleared!`

- [ ] **Step 3: Test render view**

Run:
```bash
php artisan tinker --execute="echo app(\Illuminate\View\Factory::class)->make('livewire.component.awal-igd.form', ['dataRiwayat' => [], 'dataPerawat' => null])->render();"
```
Expected: Output HTML normal, no errors.

- [ ] **Step 4: Commit**

```bash
git add resources/views/livewire/component/awal-igd/form.blade.php
git commit -m "feat: add patient assessment history table below form in awal igd modal"
```

---

### Task 3: Verifikasi Menyeluruh & Testing

**Files:**
- Test execution & verification

- [ ] **Step 1: Jalankan PHPUnit test**

Run: `php artisan test --filter=AwalIgdFormTest`
Expected: PASS

- [ ] **Step 2: Test simulasi Livewire dengan no_rawat riil di Tinker**

Run:
```bash
php artisan tinker --execute="
\$c = new \App\Http\Livewire\Component\AwalIgd\Form();
\$c->no_rawat = '2026/10/11/000027';
\$c->updatedNoRawat();
echo 'Jumlah riwayat: ' . count(\$c->dataRiwayat) . PHP_EOL;
echo 'No Rawat pertama: ' . (\$c->dataRiwayat[0]['no_rawat'] ?? 'kosong') . PHP_EOL;
"
```
Expected: `Jumlah riwayat: >= 1`, data riwayat terbaca rapi.

- [ ] **Step 3: Cek git status**

Run: `git status -s`
Expected: Bersih.

---
