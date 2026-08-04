# Desain: Penandaan Area Operasi

## Tujuan dan ruang lingkup

Digitalisasi formulir **Penandaan Area Operasi (Perempuan)** ke dalam aplikasi Laravel yang ada. Modul tidak menambah menu atau halaman mandiri. Form tersedia sebagai kartu `Penandaan Area Operasi` pada halaman pemeriksaan Rawat Jalan (Ralan) dan Rawat Inap (Ranap), dan selalu terikat ke kunjungan pasien yang sedang dibuka.

Versi pertama hanya mencakup diagram perempuan pada formulir sumber. Tidak ada tanda tangan pasien/keluarga ataupun gambar tanda tangan operator. Acknowledgement operator berupa nama operator dan waktu simpan.

## Integrasi halaman

Satu komponen Livewire yang dapat digunakan ulang menerima `noRawat` dan `noRm` dari halaman aktif.

- `resources/views/ralan/pemeriksaan-ralan.blade.php`
- `resources/views/ranap/pemeriksaan-ranap.blade.php`

Komponen ditempatkan dalam kartu AdminLTE `Penandaan Area Operasi`, berdekatan dengan kartu `Laporan Operasi`. Tidak ada pengaturan akses berbasis peran untuk modul ini.

Kartu memuat satu data penandaan untuk satu `no_rawat`. Bila sudah pernah disimpan, data dimuat kembali dan tetap dapat diedit; simpan berikutnya memperbarui data untuk kunjungan tersebut.

## Antarmuka dan pengalaman mobile

Rancangan mengikuti tampilan formulir kertas pada desktop dan cetak. Pada perangkat mobile, tiap diagram muncul sebagai kanvas besar yang ditukar dengan tab atau tombol sebelumnya/berikutnya agar target sentuh cukup besar.

Form berisi:

- Identitas pasien yang diisi otomatis dari kunjungan: nomor rekam medis, nama, tanggal lahir, dan nomor rawat.
- `Prosedur operasi` sebagai teks bebas dan wajib diisi.
- Nama operator dari pengguna/sesi aktif dan waktu simpan.
- Diagram anatomi perempuan: badan depan/belakang, empat tampilan kepala, empat tampilan tangan, dan empat tampilan kaki.
- Tombol simpan serta pratinjau/cetak.

Pengguna mengetuk diagram untuk menambahkan pin merah bernomor. Pin adalah satu-satunya mode penandaan. Nomor pin ditampilkan dalam gambar hasil dan penandaan dapat diubah dengan memuat ulang gambar ke kanvas lalu menyimpan ulang.

## Penyimpanan data

Tabel baru: `penandaan_area_operasi`.

| Kolom | Tipe | Aturan |
|---|---|---|
| `id` | bigint | primary key |
| `no_rawat` | varchar | wajib, unik; identitas satu data per kunjungan |
| `no_rm` | varchar | snapshot wajib dari kunjungan |
| `nama_pasien` | varchar | snapshot wajib dari kunjungan |
| `tgl_lahir` | date | snapshot pasien, boleh null bila sumber tidak tersedia |
| `prosedur_operasi` | text | wajib, teks bebas |
| `operator_id` | varchar | identitas pengguna/sesi, boleh null bila tidak tersedia |
| `nama_operator` | varchar | wajib, snapshot operator saat simpan |
| `tanggal_penandaan` | datetime | wajib, waktu simpan terakhir |
| `gambar_penandaan_base64` | longtext | wajib; JSON diagram ke PNG data-URL Base64 |
| `format_gambar` | varchar | `image/png` |
| `created_at`, `updated_at` | timestamp | audit dasar |

`gambar_penandaan_base64` adalah JSON yang portable antaraplikasi. Setiap key menggunakan nama tampilan stabil, misalnya `body_front`, `body_back`, `head_left_profile`, `head_right_profile`, `head_front`, `head_back`, `hand_left_palm`, `hand_right_palm`, `hand_left_dorsal`, `hand_right_dorsal`, `foot_left_plantar`, `foot_right_plantar`, `foot_left_dorsal`, dan `foot_right_dorsal`.

Nilai setiap key adalah PNG data-URL lengkap, misalnya `data:image/png;base64,...`. Gambar mencakup diagram latar dan pin, sehingga aplikasi lain cukup menampilkan Base64 tanpa mengetahui koordinat atau kode kanvas. Tabel titik/koordinat terpisah tidak digunakan.

## Alur simpan dan cetak

1. Komponen menerima kunjungan aktif dan memuat snapshot pasien.
2. Komponen memuat gambar Base64 yang ada atau diagram template kosong.
3. Pengguna mengisi prosedur, memberi pin pada satu atau lebih diagram, lalu menyimpan.
4. Browser mengekspor tiap kanvas ke PNG Base64; komponen memvalidasi dan menyimpan JSON bersama metadata operator.
5. Pratinjau/cetak merender metadata dan semua diagram tersimpan ke format formulir lengkap.

Sebelum ekspor, kanvas dibatasi ke resolusi cetak yang memadai agar ukuran payload tetap layak untuk Livewire dan database. Penyimpanan memakai `LONGTEXT` untuk mengakomodasi beberapa PNG Base64.

## Validasi dan kegagalan

- Tolak simpan bila `no_rawat` tidak valid, prosedur kosong, JSON tidak valid, atau nilai gambar bukan data-URL PNG Base64.
- Tampilkan error di kartu tanpa menghapus state kanvas yang belum tersimpan.
- Bila salah satu gambar tersimpan tidak dapat dibaca, tampilkan template kosong hanya untuk diagram tersebut dan biarkan diagram lain tetap tersedia.
- Kegagalan ekspor/penyimpanan tidak boleh mengganti data Base64 lama di database.

## Pengujian

- Buat data baru dari halaman pemeriksaan Ralan dan Ranap.
- Pastikan data dan gambar yang sama dimuat kembali untuk `no_rawat` yang sama dan dapat diedit.
- Pastikan data antar-`no_rawat` terisolasi.
- Uji prosedur kosong, payload gambar tidak valid, dan satu diagram Base64 rusak.
- Uji responsivitas penandaan pada layar mobile dan susunan semua diagram pada desktop/cetak.
- Pastikan data Base64 dapat didekode sebagai PNG tanpa bergantung pada JavaScript kanvas aplikasi ini.

## Di luar ruang lingkup

- Formulir/diagram laki-laki.
- Menu terpisah, daftar global, pencarian, dan akses berbasis peran.
- Tanda tangan pasien/keluarga dan penyimpanan gambar tanda tangan operator.
- Sinkronisasi otomatis ke aplikasi lain; desain hanya menyediakan payload Base64 portabel untuk konsumsi aplikasi lain.
