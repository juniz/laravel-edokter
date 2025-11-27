# E-DOKTER RSB NGANJUK

Adalah aplikasi yang digunakan Dokkter untuk mengisi pemeriksaan Pasien.

---

## Menjalankan dengan FrankenPHP

FrankenPHP adalah web server PHP modern berbasis Caddy. Repo ini sudah disiapkan untuk development dan production.

### Prasyarat

- Docker & Docker Compose terinstal
- Port 8080 kosong (untuk server) dan 5173 bila menggunakan Vite

### Konfigurasi yang ditambahkan

- `Dockerfile.frankenphp`: image FrankenPHP (base) dan stage `dev`
- `Caddyfile.frankenphp`: konfigurasi Caddy/FrankenPHP untuk Laravel
- `docker-compose.dev.yml`: komposisi dev dengan volume bind
- `docker-compose.prod.yml`: komposisi prod untuk deploy

### Environment

Pastikan `.env` berisi konfigurasi database/redis sesuai kebutuhan. Beberapa variabel penting:

- `APP_ENV`, `APP_DEBUG`, `APP_URL`
- `DB_*` bila menggunakan DB di container terpisah

> Catatan: File `docker-compose.yml` bawaan Sail tetap ada, tapi Anda bisa memakai file compose khusus FrankenPHP berikut.

---

## Development

1. Build dan jalankan

```bash
docker compose -f docker-compose.dev.yml up --build
```

2. Akses aplikasi

- http://localhost:8080

3. Artisan di dalam container

```bash
docker exec -it edokter-frankenphp-dev php artisan migrate
```

4. Vite (opsional)

- Jika menggunakan Vite, jalankan di host atau container terpisah. Port 5173 sudah di-expose.

5. Hentikan

```bash
docker compose -f docker-compose.dev.yml down
```

---

## Production

1. Build image production (stage base sudah mengaktifkan optimasi)

```bash
docker compose -f docker-compose.prod.yml build
```

2. Jalankan

```bash
docker compose -f docker-compose.prod.yml up -d
```

3. Set variabel environment (opsional override)

- Sesuaikan `APP_URL`, `APP_ENV=production`, `APP_DEBUG=false` pada compose atau gunakan file env compose.

4. Migrasi & cache

```bash
docker exec -it edokter-frankenphp php artisan migrate --force
docker exec -it edokter-frankenphp php artisan config:cache route:cache view:cache
```

5. Log

- Log tersedia di stdout/stderr container.

6. Stop & remove

```bash
docker compose -f docker-compose.prod.yml down
```

---

## Catatan Tambahan

- Tambah service `mysql`/`redis` pada compose jika ingin mengelola dependensi di stack yang sama.
- Sesuaikan `Caddyfile.frankenphp` jika ingin TLS/HTTP/2/3 di production (letakkan domain dan sertifikat/tls otomatis Caddy).
