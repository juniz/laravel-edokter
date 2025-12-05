# PDF Proxy untuk Mengatasi Masalah CORS

## Deskripsi

File `pdf-proxy.php` adalah server-side proxy yang digunakan untuk mengatasi masalah CORS saat mengakses file PDF dari domain yang berbeda.

## Instalasi

1. **Upload file `pdf-proxy.php` ke server PDF**

   - Lokasi: `https://simrs.rsbhayangkaranganjuk.com/pdf-proxy.php`
   - Pastikan file memiliki permission yang benar (644 atau 755)

2. **Konfigurasi**
   - Buka file `pdf-proxy.php`
   - Edit bagian konfigurasi di bagian atas file:

```php
// Konfigurasi
$allowedOrigins = [
    'http://localhost:8000',
    'http://localhost',
    'https://yourdomain.com', // Ganti dengan domain aplikasi Anda
    // Tambahkan domain lain yang diizinkan
];

// Base path - gunakan document root untuk absolute path (disarankan)
$basePath = $_SERVER['DOCUMENT_ROOT'] . '/webapps/berkasrawat/';

// Atau gunakan relative path jika absolute path tidak bekerja
// $basePath = '/webapps/berkasrawat/';

$maxFileSize = 50 * 1024 * 1024; // 50MB max file size
```

**Catatan Penting:**

- Jika menggunakan `$_SERVER['DOCUMENT_ROOT']`, pastikan path benar-benar ada
- Jika file masih tidak ditemukan, aktifkan debug mode (lihat troubleshooting)
- Setelah menemukan path yang benar, pastikan `$debugMode = false;` untuk keamanan

3. **Sesuaikan konfigurasi:**
   - `$allowedOrigins`: Tambahkan domain aplikasi Laravel Anda
   - `$basePath`: Pastikan sesuai dengan path file PDF di server
   - `$maxFileSize`: Sesuaikan dengan kebutuhan (default 50MB)

## Penggunaan

### Format URL

```
https://simrs.rsbhayangkaranganjuk.com/pdf-proxy.php?file=path/to/file.pdf
```

### Contoh

Jika file PDF berada di: `/webapps/berkasrawat/2024/01/laporan.pdf`

Maka URL proxy-nya adalah:

```
https://simrs.rsbhayangkaranganjuk.com/pdf-proxy.php?file=2024/01/laporan.pdf
```

## Fitur

1. **CORS Support**

   - Mengatur header CORS dengan benar
   - Mendukung preflight OPTIONS request
   - Mengizinkan origin yang dikonfigurasi

2. **Security**

   - Validasi file extension (hanya PDF)
   - Proteksi terhadap path traversal attacks
   - Validasi file size
   - Validasi bahwa file benar-benar PDF

3. **Performance**

   - Support Range requests untuk streaming
   - Cache headers untuk optimasi
   - Partial content support untuk PDF.js

4. **Error Handling**
   - Error messages yang jelas
   - HTTP status codes yang sesuai
   - JSON error responses

## Testing

### Test dengan cURL

```bash
# Test basic request
curl -I "https://simrs.rsbhayangkaranganjuk.com/pdf-proxy.php?file=test.pdf"

# Test dengan origin header
curl -H "Origin: http://localhost:8000" \
     -I "https://simrs.rsbhayangkaranganjuk.com/pdf-proxy.php?file=test.pdf"

# Test OPTIONS request (preflight)
curl -X OPTIONS \
     -H "Origin: http://localhost:8000" \
     -H "Access-Control-Request-Method: GET" \
     -I "https://simrs.rsbhayangkaranganjuk.com/pdf-proxy.php?file=test.pdf"
```

### Test di Browser

Buka di browser:

```
https://simrs.rsbhayangkaranganjuk.com/pdf-proxy.php?file=test.pdf
```

Seharusnya PDF langsung terdownload atau terbuka di browser.

## Troubleshooting

### Error 400: Parameter file diperlukan

- Pastikan parameter `file` ada di URL
- Format: `?file=path/to/file.pdf`

### Error 404: File tidak ditemukan

- Pastikan path file benar
- Pastikan `$basePath` di konfigurasi sesuai
- Pastikan file benar-benar ada di server
- **Aktifkan debug mode** untuk melihat detail path:
  1. Buka `pdf-proxy.php`
  2. Set `$debugMode = true;` (baris ~118)
  3. Coba akses file lagi
  4. Lihat response JSON yang berisi informasi detail tentang path yang dicari
  5. Bandingkan dengan struktur direktori aktual di server
  6. Sesuaikan `$basePath` sesuai dengan hasil debug
  7. **PENTING**: Set `$debugMode = false;` setelah selesai debugging untuk keamanan

### Error 400: File type tidak diizinkan

- Pastikan file extension adalah `.pdf`
- File harus memiliki extension PDF

### Error 413: File terlalu besar

- File melebihi `$maxFileSize`
- Sesuaikan `$maxFileSize` di konfigurasi jika perlu

### CORS masih error

- Pastikan domain aplikasi ada di `$allowedOrigins`
- Pastikan server mengizinkan request dari domain tersebut
- Cek browser console untuk detail error

## Keamanan

1. **Path Traversal Protection**

   - Menghapus `../` dari path
   - Validasi path sebelum digunakan

2. **File Type Validation**

   - Hanya mengizinkan file PDF
   - Validasi MIME type dan file header

3. **Size Limitation**

   - Membatasi ukuran file yang bisa diakses
   - Mencegah abuse

4. **Origin Validation**
   - Hanya mengizinkan origin yang dikonfigurasi
   - Mencegah unauthorized access

## Catatan Penting

- File ini harus ditempatkan di server PDF (bukan di server Laravel)
- Pastikan PHP di server mendukung fungsi yang digunakan
- Pastikan server memiliki akses read ke file PDF
- Disarankan untuk menggunakan HTTPS untuk keamanan

## Support

Jika ada masalah, periksa:

1. PHP error logs
2. Server access logs
3. Browser console untuk CORS errors
4. Network tab di browser developer tools
