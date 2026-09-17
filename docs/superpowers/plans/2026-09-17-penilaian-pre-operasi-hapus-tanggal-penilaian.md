# Sembunyikan Baris Tanggal Penilaian pada Print Penilaian Pre Operasi Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Sembunyikan baris "TANGGAL PENILAIAN" pada formulir print PDF penilaian pre operasi dengan komentar Blade, menjaga tanggal pada blok tanda tangan TTE.

**Architecture:** Modifikasi template DomPDF Blade `resources/views/prints/penilaian-pre-operasi-pdf.blade.php` dengan membungkus baris tabel metadata `TANGGAL PENILAIAN` menggunakan komentar Blade `{{-- ... --}}`.

**Tech Stack:** Laravel 9, Blade, DomPDF, PHPUnit.

## Global Constraints
- Target template: `resources/views/prints/penilaian-pre-operasi-pdf.blade.php`
- Blok tanggal tanda tangan (`Nganjuk, ...` dan `Waktu TTD: ...`) tetap aktif menggunakan `$tanggalAss`.
- Tidak ada perubahan skema database atau logic controller/Livewire.
- Seluruh automated feature tests harus lolos 100%.

---

### Task 1: Sembunyikan Baris TANGGAL PENILAIAN pada Template PDF & Verifikasi

**Files:**
- Modify: `resources/views/prints/penilaian-pre-operasi-pdf.blade.php:296-300`
- Modify: `tests/Feature/PenilaianPreOperasiAutoUploadTest.php`

**Interfaces:**
- Consumes: `PenilaianPreOperasiService::generatePdf(string $noRawat, ?string $tanggal = null)`
- Produces: Output PDF tanpa label text `TANGGAL PENILAIAN` pada tabel metadata identitas.

- [ ] **Step 1: Write assertion in test suite ensuring TANGGAL PENILAIAN is absent from view render**

Tambahkan method test di `tests/Feature/PenilaianPreOperasiAutoUploadTest.php`:
```php
    public function test_pdf_template_omits_tanggal_penilaian_row_but_keeps_signature_timestamp(): void
    {
        $view = view('prints.penilaian-pre-operasi-pdf', [
            'penilaian' => DB::table('penilaian_pre_operasi')->where('no_rawat', '2026/08/19/000001')->first(),
            'setting' => (object) [
                'nama_instansi' => 'RS BHAYANGKARA',
                'alamat_instansi' => 'Jl. Langsep',
                'kabupaten' => 'NGANJUK',
                'propinsi' => 'JATIM',
                'kontak' => '123',
                'email' => 'rs@test.com',
                'logo_base64' => '',
            ],
            'namaPasien' => 'Tn. Pasien Uji',
            'noRm' => '000001',
            'umur' => '36 Th',
            'tglLahirFormatted' => '02-01-1990',
            'jkFormatted' => 'Laki-Laki',
            'namaDokter' => 'Dr. Operator Bedah',
            'kdDokter' => 'D001',
            'tanggalPenilaian' => '19 Agustus 2026 08:30',
            'qrBase64' => '',
            'barcodeBase64' => '',
        ])->render();

        $this->assertStringNotContainsString('TANGGAL PENILAIAN', $view);
        $this->assertStringContainsString('Waktu TTD: 19 Agustus 2026 08:30', $view);
    }
```

- [ ] **Step 2: Run test to verify it fails before template modification**

Run: `./vendor/bin/phpunit --filter test_pdf_template_omits_tanggal_penilaian_row_but_keeps_signature_timestamp tests/Feature/PenilaianPreOperasiAutoUploadTest.php`
Expected: FAIL (view still contains `TANGGAL PENILAIAN`).

- [ ] **Step 3: Modify template to comment out TANGGAL PENILAIAN row**

Di `resources/views/prints/penilaian-pre-operasi-pdf.blade.php`, bungkus baris:
```blade
            {{-- 
            <tr>
                <td class="meta-label">TANGGAL PENILAIAN</td>
                <td class="meta-val" colspan="2">: {{ $tanggalAss }}</td>
            </tr>
            --}}
```

- [ ] **Step 4: Validate syntax and run test to verify it passes**

Run:
```bash
php -l resources/views/prints/penilaian-pre-operasi-pdf.blade.php
./vendor/bin/phpunit --filter test_pdf_template_omits_tanggal_penilaian_row_but_keeps_signature_timestamp tests/Feature/PenilaianPreOperasiAutoUploadTest.php
```
Expected: PASS.

- [ ] **Step 5: Run full test suite for regression check**

Run:
```bash
./vendor/bin/phpunit tests/Feature/PenilaianPreOperasiAutoUploadTest.php tests/Feature/PenilaianPreOperasiControllerTest.php
```
Expected: 14 tests, all PASS.

- [ ] **Step 6: Commit changes**

```bash
git add resources/views/prints/penilaian-pre-operasi-pdf.blade.php tests/Feature/PenilaianPreOperasiAutoUploadTest.php
git commit -m "feat(penilaian-pre-operasi): hide tanggal penilaian row in pdf print"
```

---

## Verification Plan
- Unit/Feature test:
  `./vendor/bin/phpunit tests/Feature/PenilaianPreOperasiAutoUploadTest.php tests/Feature/PenilaianPreOperasiControllerTest.php`
- Assert `TANGGAL PENILAIAN` is absent from rendered view output.
- Assert signature block still contains valid date and time.
