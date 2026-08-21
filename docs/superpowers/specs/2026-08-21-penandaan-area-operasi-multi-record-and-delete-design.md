# Desain Fitur Multi-Record dan Hapus Data pada Penandaan Area Operasi

## Ringkasan Eksekutif
Modul Penandaan Area Operasi sebelumnya hanya mendukung satu rekaman per `no_rawat` (menggunakan `updateOrInsert` dan constraint `unique` pada kolom `no_rawat`). Fitur baru ini memungkinkan:
1. Perekaman lebih dari satu data penandaan area operasi untuk pasien yang sama (`no_rawat`), misalnya untuk beberapa prosedur bedah terpisah atau waktu tindakan yang berbeda.
2. Penambahan opsi hapus data penandaan dengan dialog konfirmasi aman.
3. Tabel riwayat penandaan di bawah form dengan kemampuan Edit, Hapus, Cetak, dan Download PDF untuk setiap rekaman.

---

## 1. Skema Database & Migrasi
- **File Migrasi**: `database/migrations/2026_08_21_000000_remove_unique_from_penandaan_area_operasi.php`
- **Perubahan**:
  - Menghapus indeks `unique` pada kolom `no_rawat` pada tabel `penandaan_area_operasi`.
  - Menambahkan indeks non-unik pada kolom `no_rawat` untuk menjaga performa pencarian.
  - Memanfaatkan kolom `id` (auto-increment primary key) sebagai identifier unik setiap baris penandaan.

---

## 2. Komponen Livewire (`App\Http\Livewire\Component\PenandaanAreaOperasi`)
- **Properti State**:
  - `$data`: Koleksi riwayat seluruh data penandaan untuk pasien/`no_rawat` aktif (diurutkan `tanggal_penandaan DESC, id DESC`).
  - `$selectedId`: `null` untuk mode input baru; terisi integer `id` saat sedang mengedit rekaman yang dipilih.
  - `$modeEdit`: boolean (`true` saat dalam mode edit, `false` saat input baru).
  - `$tanggalPenandaan`: Input string datetime (format `Y-m-d\TH:i`), default ke `now()`.
  - `$prosedurOperasi`: Teks deskripsi prosedur operasi.
  - `$gambarPenandaanBase64`: Array pasangan canvas key => base64 PNG.
- **Metode Komponen**:
  - `mount(string $noRawat, string $noRm)`: Inisialisasi properti, memuat data riwayat via `getData()`. Form awal tetap kosong siap input.
  - `getData()`: Query data penandaan dari database berdasarkan `no_rawat` beserta join ke tabel `dokter` untuk mendapatkan nama dokter operator.
  - `simpan()`:
    - Validasi `$prosedurOperasi`, `$tanggalPenandaan`, dan array `$gambarPenandaanBase64`.
    - Melakukan `DB::table('penandaan_area_operasi')->insertGetId(...)`.
    - Menjalankan `PenandaanAreaOperasiService::generateAndUploadPdf($this->noRawat, $insertedId)`.
    - Mereset form & canvas ke keadaan awal dan memuat ulang riwayat via `getData()`.
    - Menampilkan notifikasi sukses.
  - `edit(int $id)`:
    - Mengambil record penandaan berdasarkan `$id` dan `$noRawat`.
    - Memvalidasi hak akses (operator ID sesuai session dokter).
    - Mengisi `$selectedId`, `$modeEdit = true`, `$prosedurOperasi`, `$tanggalPenandaan`.
    - Mengirim event browser `loadPenandaanCanvases` dengan payload gambar untuk me-render ulang titik/coretan canvas.
  - `update()`:
    - Memvalidasi form dan canvas.
    - Memperbarui baris pada `penandaan_area_operasi` sesuai `$selectedId`.
    - Menjalankan `PenandaanAreaOperasiService::generateAndUploadPdf($this->noRawat, $this->selectedId)`.
    - Mereset form, mereset status edit, memuat ulang data riwayat, dan menampilkan notifikasi sukses.
  - `confirmHapus(int $id)`:
    - Memvalidasi record dan hak akses pengguna.
    - Menampilkan konfirmasi pop-up LivewireAlert.
  - `hapus()`:
    - Menghapus record dari tabel `penandaan_area_operasi` berdasarkan `$selectedIdHapus`.
    - Menghapus atau membersihkan berkas digital terkait jika ada.
    - Memuat ulang `getData()` dan menampilkan notifikasi sukses.
  - `batalEdit()`:
    - Mengembalikan `$modeEdit = false`, `$selectedId = null`.
    - Mengosongkan `$prosedurOperasi` dan mereset waktu ke waktu sekarang.
    - Mengirim event browser `resetAllCanvases` untuk membersihkan canvas diagram.

---

## 3. Tampilan Antarmuka (`resources/views/livewire/component/penandaan-area-operasi.blade.php`)
- **Bagian Form (Atas)**:
  - Input Tanggal & Jam Penandaan (`wire:model.defer="tanggalPenandaan"`).
  - Input Prosedur Operasi (`wire:model.defer="prosedurOperasi"`).
  - Area Canvas 14 Diagram Anatomi Tubuh.
  - Tombol aksi form:
    - Jika `$modeEdit == false`: Tombol **Simpan**.
    - Jika `$modeEdit == true`: Tombol **Ubah Penandaan** dan **Batal (Input Baru)**.
    - Tombol utilitas canvas: **Reset Diagram Ini** dan **Reset Semua**.
- **Bagian Riwayat (Bawah)**:
  - Card Box: "Riwayat Penandaan Area Operasi".
  - Tabel:
    - Kolom `#`, `Tanggal & Jam`, `Prosedur Operasi`, `Dokter Operator`, `Aksi`.
    - Tombol Aksi per baris:
      - ✏️ **Edit**: Panggil `wire:click="edit({{ $item->id }})"`.
      - 🗑️ **Hapus**: Panggil `wire:click="confirmHapus({{ $item->id }})"`.
      - 🖨️ **Cetak**: Link ke `route('penandaan-area-operasi.cetak', ['no_rawat' => $noRawat, 'id' => $item->id])`.
      - 📄 **Download PDF**: Link ke `route('penandaan-area-operasi.download-pdf', ['no_rawat' => $noRawat, 'id' => $item->id])`.

---

## 4. Service, Controller & Verifikasi Dokumen
- **Service (`App\Services\PenandaanAreaOperasiService`)**:
  - `generatePdf(string $noRawat, ?int $id = null)`: Memuat record spesifik berdasarkan `$id` (atau record terbaru jika `$id` kosong).
  - `uploadPdf(string $noRawat, string $pdfBinary, ?int $id = null)`: Menghasilkan berkas digital bernama `PENANDAAN_OPERASI_{sanitizedNoRawat}_{id}_{timestamp}`.
  - `generateAndUploadPdf(string $noRawat, ?int $id = null)`.
- **Controller (`App\Http\Controllers\PenandaanAreaOperasiController`)**:
  - `generateToken(string $noRawat, ?int $id = null)`: Menghasilkan token HMAC SHA-256 berbasis `$noRawat` dan `$id`.
  - `cetak(Request $request)`: Menerima parameter `no_rawat` dan opsional `id`.
  - `downloadPdf(Request $request)`: Menerima parameter `no_rawat` dan opsional `id`.
  - `verifikasi(Request $request, ?string $token = null)`: Mencocokkan token verifikasi dokumen untuk menampilkan rincian penandaan yang valid.

---

## 5. Rencana Pengujian
- Test migrasi database: tabel dapat menampung multi-record untuk `no_rawat` yang sama.
- Test Feature Livewire:
  - Menyimpan penandaan pertama dan kedua pada satu pasien (`no_rawat` yang sama) berhasil.
  - Mengubah data penandaan tertentu (`update`) memperbarui baris yang benar tanpa mengubah baris lainnya.
  - Menghapus data penandaan (`hapus`) menghapus baris yang dipilih dari database.
- Test PDF generation & download dengan parameter `id`.
