# Design Spec: Sembunyikan Baris Tanggal Penilaian pada Print PDF Penilaian Pre Operasi

## 1. Overview
Pengguna meminta untuk menghapus/menyembunyikan baris "TANGGAL PENILAIAN" pada hasil cetak PDF formulir penilaian pre operasi. Sesuai keputusan pengguna (Pendekatan 2), baris metadata `TANGGAL PENILAIAN` disembunyikan menggunakan komentar Blade (`{{-- ... --}}`) agar tidak tampil saat dokumen di-render oleh DomPDF.

## 2. Scope & Target Files
- **Target File**: `resources/views/prints/penilaian-pre-operasi-pdf.blade.php`
- **Scope**:
  - Menyembunyikan baris `<tr>` yang memuat label `TANGGAL PENILAIAN` pada tabel metadata identitas pasien (`.meta-table`).
  - Mempertahankan variabel `$tanggalAss` dan penanggalan pada blok tanda tangan dokter di bagian bawah (`Nganjuk, ...` dan `Waktu TTD: ...`).
  - Tidak mengubah skema database, controller, service, atau form Livewire.

## 3. Technical Changes

### Template PDF (`resources/views/prints/penilaian-pre-operasi-pdf.blade.php`)
Ubah baris 296-299:
```blade
            <tr>
                <td class="meta-label">NO. RAWAT</td>
                <td class="meta-val" colspan="2">: {{ $penilaian->no_rawat ?? '-' }}</td>
            </tr>
            {{-- 
            <tr>
                <td class="meta-label">TANGGAL PENILAIAN</td>
                <td class="meta-val" colspan="2">: {{ $tanggalAss }}</td>
            </tr>
            --}}
            <tr>
                <td class="meta-label">DOKTER OPERATOR</td>
                <td class="meta-val" colspan="2">: {{ $namaDokter }}</td>
            </tr>
```

## 4. Verification & Testing Plan
1. **Sintaks Blade / PHP**:
   - Jalankan `php -l resources/views/prints/penilaian-pre-operasi-pdf.blade.php`.
2. **Automated Feature Tests**:
   - Jalankan `./vendor/bin/phpunit tests/Feature/PenilaianPreOperasiAutoUploadTest.php`.
   - Jalankan `./vendor/bin/phpunit tests/Feature/PenilaianPreOperasiControllerTest.php`.
   - Pastikan seluruh test case (7/7 dan 6/6) tetap passed 100%.
3. **Manual / Rendering Check**:
   - Pastikan baris "TANGGAL PENILAIAN" tidak lagi muncul pada dokumen PDF pre operasi yang dihasilkan.
