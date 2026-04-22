# Al-Kutub Monorepo

Proyek ini berisi 2 aplikasi yang saling terhubung:

- `al-kutub/`: backend web + API (Laravel 8)
- `AlKutub/`: aplikasi Android (Kotlin + Jetpack Compose)

## Arsitektur Singkat

- Backend Laravel menyediakan:
  - autentikasi (Sanctum), role user/admin, 2FA
  - API katalog kitab, bookmark, history, rating, komentar, notifikasi
  - admin dashboard dan manajemen data
- Android consume API `api/v1` untuk:
  - login/register, home, search, detail kitab, PDF viewer
  - bookmark, history, reading notes, notifikasi, pengaturan akun

## Quick Start

## 1) Jalankan Backend Laravel

```bash
cd al-kutub
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

Default API base URL lokal: `http://127.0.0.1:8000/api/v1/`

## 2) Jalankan Android

```bash
cd AlKutub
./gradlew assembleDebug
```

Untuk emulator Android, backend lokal menggunakan `http://10.0.2.2:8000/`.

## Akun Demo (Contoh)

Silakan sesuaikan dengan data seeder lokal Anda:

- Admin: `admin` / `password`
- User: `user` / `password`

## Alur Demo 7-10 Menit

1. Login sebagai user di Android.
2. Search kitab, buka detail, beri rating/komentar.
3. Download dan buka PDF, tunjukkan progress/history tersimpan.
4. Buka bookmark + reading notes.
5. Buka notifikasi dan mark as read (unread count turun).
6. Login admin di web, tambah kitab, lihat dashboard dan notifikasi broadcast.

## Checklist Smoke Test

- Auth user/admin berhasil.
- Endpoint admin API terlindungi role admin.
- Notifikasi unread count akurat setelah mark as read.
- Search + suggestion + history berjalan.
- Fitur utama Android (katalog, detail, PDF, bookmark, history) tetap normal.
