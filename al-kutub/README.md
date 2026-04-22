# Al-Kutub Backend (Laravel 8)

Backend untuk aplikasi Al-Kutub (web admin + REST API untuk Android).

## Fitur Utama

- Auth Sanctum + role user/admin
- Verifikasi email wajib sebelum login (web + API)
- Forgot password end-to-end (request API/web, reset via link web)
- 2FA (setup, enable/disable, backup codes, login verification)
- API kitab (list, detail, search, related, download)
- Bookmark, history, rating, komentar, reading notes
- Notifikasi + unread/read tracking per user
- Dashboard analytics admin

## Setup Lokal

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

Server lokal default: `http://127.0.0.1:8000`

## API Base URL

- Versioned API: `/api/v1/...`
- Legacy API masih tersedia untuk backward compatibility.

## Endpoint Penting

- Auth: `POST /api/v1/login`, `POST /api/v1/register`
- Email verification: `POST /api/v1/email/verification/resend`, `POST /api/v1/email/verification/status`
- Forgot password: `POST /api/v1/password/forgot`
- Kitab: `GET /api/v1/kitab`, `GET /api/v1/kitab/search`
- Notifikasi: `GET /api/v1/notifications`, `GET /api/v1/notifications/unread-count`, `POST /api/v1/notifications/{id}/read`, `POST /api/v1/notifications/read-all`
- Admin API (protected): `POST /api/v1/admin/kitab/store`, `GET /api/v1/admin/kitab/stats`

## Flow Auth Baru

1. Register membuat akun `unverified` dan kirim email verifikasi.
2. Login akun unverified ditolak token-nya dan return status `requires_email_verification=true`.
3. User verifikasi melalui link email signed (`/email/verify/{id}/{hash}`).
4. Setelah verifikasi, user login normal (dan jika aktif 2FA akan lanjut ke verifikasi 2FA).

## Test

```bash
php artisan test
```

## Catatan Keamanan

- Route debug seperti `/phpinfo` dan route test dashboard hanya aktif di environment `local`.
- Endpoint admin API dilindungi `auth:sanctum` + role `admin`.
