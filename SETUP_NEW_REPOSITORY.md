# Panduan Setup Repository GitHub Baru

Repository ini sudah diputus dari repository asal (`yogijowo/laravel12-react-starterkit`). Ikuti langkah-langkah berikut untuk membuat repository GitHub baru.

## Langkah 1: Buat Repository Baru di GitHub

1. Buka [GitHub](https://github.com) dan login ke akun Anda
2. Klik tombol **"New"** atau **"+"** di pojok kanan atas
3. Pilih **"New repository"**
4. Isi informasi repository:
   - **Repository name**: `abahost` (atau nama lain yang Anda inginkan)
   - **Description**: Deskripsi proyek Anda (opsional)
   - **Visibility**: Pilih **Public** atau **Private**
   - **JANGAN** centang "Initialize this repository with a README"
   - **JANGAN** pilih "Add .gitignore" atau "Choose a license"
5. Klik **"Create repository"**

## Langkah 2: Hubungkan Repository Lokal ke GitHub Baru

Setelah repository GitHub dibuat, Anda akan mendapatkan URL repository. Gunakan salah satu metode berikut:

### Metode A: Menggunakan HTTPS (Recommended)

```bash
# Tambahkan remote origin baru (ganti YOUR_USERNAME dengan username GitHub Anda)
git remote add origin https://github.com/YOUR_USERNAME/abahost.git

# Verifikasi remote sudah ditambahkan
git remote -v

# Push semua branch ke repository baru
git push -u origin main
```

### Metode B: Menggunakan SSH

```bash
# Tambahkan remote origin baru (ganti YOUR_USERNAME dengan username GitHub Anda)
git remote add origin git@github.com:YOUR_USERNAME/abahost.git

# Verifikasi remote sudah ditambahkan
git remote -v

# Push semua branch ke repository baru
git push -u origin main
```

## Langkah 3: Verifikasi

Setelah push berhasil, buka repository di GitHub dan pastikan semua file sudah ter-upload.

## Catatan Penting

- ✅ Repository lokal sudah diputus dari repository asal
- ✅ Semua commit history lokal akan tetap ada
- ✅ Anda bisa mulai bekerja dengan repository baru yang independen
- ⚠️ Pastikan Anda sudah membuat repository di GitHub sebelum menjalankan `git remote add origin`

## Troubleshooting

### Jika terjadi error "remote origin already exists"
```bash
# Hapus remote yang ada terlebih dahulu
git remote remove origin

# Tambahkan remote baru
git remote add origin https://github.com/YOUR_USERNAME/abahost.git
```

### Jika terjadi error saat push
```bash
# Pastikan branch main sudah di-set sebagai default
git branch -M main

# Push dengan force (hati-hati, hanya jika diperlukan)
git push -u origin main --force
```

### Jika ingin menghapus semua history dan mulai fresh
```bash
# Hapus folder .git
rm -rf .git

# Inisialisasi git baru
git init
git add .
git commit -m "Initial commit"

# Tambahkan remote baru
git remote add origin https://github.com/YOUR_USERNAME/abahost.git
git push -u origin main
```

