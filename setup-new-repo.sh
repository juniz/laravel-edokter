#!/bin/bash

# Script untuk setup repository GitHub baru
# Pastikan Anda sudah membuat repository di GitHub terlebih dahulu

echo "=========================================="
echo "Setup Repository GitHub Baru"
echo "=========================================="
echo ""

# Cek apakah remote origin sudah ada
if git remote | grep -q "^origin$"; then
    echo "⚠️  Remote 'origin' sudah ada."
    read -p "Hapus remote origin yang ada? (y/n) " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        git remote remove origin
        echo "✅ Remote origin lama sudah dihapus"
    else
        echo "❌ Dibatalkan. Silakan hapus remote origin secara manual."
        exit 1
    fi
fi

# Minta input URL repository GitHub baru
echo ""
echo "Masukkan URL repository GitHub baru Anda:"
echo "Contoh: https://github.com/USERNAME/REPO.git"
echo "   atau: git@github.com:USERNAME/REPO.git"
read -p "URL: " REPO_URL

if [ -z "$REPO_URL" ]; then
    echo "❌ URL tidak boleh kosong!"
    exit 1
fi

# Tambahkan remote origin baru
echo ""
echo "Menambahkan remote origin baru..."
git remote add origin "$REPO_URL"

# Verifikasi
echo ""
echo "✅ Remote origin sudah ditambahkan:"
git remote -v

# Tanya apakah ingin push sekarang
echo ""
read -p "Push ke repository baru sekarang? (y/n) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo ""
    echo "Mempush branch main ke origin..."
    git push -u origin main
    
    if [ $? -eq 0 ]; then
        echo ""
        echo "✅ Berhasil! Repository sudah terhubung ke GitHub baru."
        echo "   URL: $REPO_URL"
    else
        echo ""
        echo "❌ Push gagal. Pastikan:"
        echo "   1. Repository sudah dibuat di GitHub"
        echo "   2. URL repository benar"
        echo "   3. Anda memiliki akses ke repository tersebut"
    fi
else
    echo ""
    echo "ℹ️  Remote sudah ditambahkan. Push manual dengan:"
    echo "   git push -u origin main"
fi

echo ""
echo "=========================================="
echo "Selesai!"
echo "=========================================="

