# Desain Penandaan Area Operasi: Lingkaran Merah Solid Tanpa Angka

## Ringkasan
Menghilangkan angka pada penanda canvas anatomi di modul Penandaan Area Operasi, menyisakan lingkaran merah solid dengan border merah tua, serta memperbarui label instruksi pengguna.

## Konteks & Masalah
Saat ini, interaksi klik/tap pada diagram anatomi menambahkan penanda lingkaran merah berisi nomor urut (`1`, `2`, dst.). Kebutuhan klinis hanya membutuhkan lingkaran merah penanda lokasi tanpa nomor urut.

## Ruang Lingkup
- File target: `resources/views/livewire/component/penandaan-area-operasi.blade.php`.
- Perubahan UI teks petunjuk.
- Modifikasi fungsi rendering canvas `addPin()`.
- Pembersihan variabel counter dan fungsi sinkronisasi nomor urut yang tidak lagi dipakai.

## Desain Teknis

### 1. Template Blade UI
Mengubah teks petunjuk di baris 31-34:
- Semula: `<span>Ketuk bagian tubuh untuk menambahkan penanda merah bernomor.</span>`
- Menjadi: `<span>Ketuk bagian tubuh untuk menambahkan penanda lingkaran merah.</span>`

### 2. Canvas Rendering (`addPin`)
Pada fungsi `addPin(state, event)`:
- Menghapus konfigurasi font canvas (`font`, `textAlign`, `textBaseline`).
- Menghapus pemanggilan `state.context.fillText(...)`.
- Mempertahankan penggambaran lingkaran solid:
  - Fill: `#c6283d` (merah)
  - Stroke: `#8f1728` (merah tua penegas batas)
  - Radius: `Math.max(16, Math.min(state.canvas.width, state.canvas.height) * .025)`
  - Border line width: `Math.max(3, radius * .16)`

### 3. Pembersihan State & Counter
Variabel dan pemanggilan yang dihapus karena tidak lagi memiliki angka:
- Deklarasi `var nextPinNumber = 1;`
- Pemanggilan `nextPinNumber += 1;` di `addPin()`
- Fungsi `refreshNextPinNumber()`
- Pemanggilan `refreshNextPinNumber()` pada:
  - `loadSavedImages()`
  - Handler tombol `resetActiveButton`
  - Handler tombol `resetAllButton` / fungsi `resetAll()`

### 4. Kompatibilitas Sistem yang Ada
- **Deteksi Pin untuk Tombol Reset (`countRedPins`)**:
  Fungsi ini membaca piksel merah RGB (R > 150, G < 115, B < 125, A > 180) dengan ukuran komponen >= 80 piksel. Lingkaran merah solid tanpa lubang teks putih tetap terdeteksi secara valid sehingga tombol "Reset Diagram Ini" dan "Reset Semua" berfungsi normal.
- **Penyimpanan Gambar**:
  Gambar diekspor via `toDataURL('image/png')` dan disimpan ke model Livewire tanpa perubahan struktur controller maupun basis data.
- **Cetak & Riwayat**:
  Hasil gambar base64 tetap kompatibel dengan tampilan riwayat dan cetak PDF/HTML.

## Rencana Verifikasi
1. Buka formulir penandaan area operasi.
2. Teks petunjuk terbaca: "Ketuk bagian tubuh untuk menambahkan penanda lingkaran merah."
3. Klik beberapa titik pada diagram anatomi:
   - Tergambar lingkaran merah solid tanpa angka.
   - Tombol "Reset Diagram Ini" dan "Reset Semua" aktif setelah titik ditambahkan.
4. Klik "Reset Diagram Ini": lingkaran pada diagram aktif terhapus, tombol reset terbarui statusnya.
5. Simpan / simpan riwayat untuk memastikan proses export base64 tidak menghasilkan error JS/Livewire.
