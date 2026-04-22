# Batch Lanjutan: E2E Reader + DB Migration Readiness

Dokumen ini menutup 2 item prioritas:
1. E2E flow utama Android (reader/progress/marker)
2. Readiness migrasi backend Laravel untuk deploy
3. Regression runner batch 1/5/8 (quality gate + observability + build checks)

## 1) Android E2E Reader Smoke

Jalankan:

```bash
cd /home/amiir/AndroidStudioProjects
./scripts/run-android-reader-e2e.sh --collect-logs
```

Output:
- Report template manual: `docs/reports/android_reader_e2e_*.md`
- Log ringkas: `docs/reports/android_reader_e2e_*.log`

Skenario yang wajib lulus:
1. Login berhasil.
2. Buka kitab -> PDF tidak stuck loading.
3. Lanjut baca menuju halaman terakhir.
4. Marker halaman add/jump/edit/delete normal.
5. Tutup dan buka lagi -> posisi terakhir konsisten.
6. Tidak ada spam error 429 berulang.

## 1b) Main Demo Smoke

Jalankan:

```bash
cd /home/amiir/AndroidStudioProjects
./scripts/run-main-demo-smoke.sh --collect-logs
```

Output:
- Report template manual: `docs/reports/main_demo_smoke_*.md`
- Log ringkas: `docs/reports/main_demo_smoke_*.log`

Flow yang dicakup:
1. Admin tambah kitab lalu publish.
2. Android cari kitab baru.
3. Android buka detail kitab.
4. Android baca PDF.
5. History, bookmark, dan reading note tersimpan.

Smoke test backend live untuk flow persistence inti:

```bash
cd /home/amiir/AndroidStudioProjects
./scripts/run-main-demo-api-smoke.sh
```

Output:
- Report backend live: `docs/reports/main_demo_api_smoke_*.md`

## 2) Laravel DB Migration Readiness

Jalankan:

```bash
cd /home/amiir/AndroidStudioProjects
./scripts/verify-laravel-db-readiness.sh
```

Mode apply migrasi (saat server DB siap):

```bash
./scripts/verify-laravel-db-readiness.sh --apply
```

Yang dicek otomatis:
- lint file backend kritikal
- lint semua migration
- validasi route `refresh-token` dan `page-bookmarks`
- koneksi DB + status migrasi
- deteksi migration pending

## Catatan environment saat ini
Jika keluar `Connection refused` di migrate status, artinya service DB belum aktif atau `.env` belum cocok.
Minimal yang harus valid:
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

## 3) Regression Runner Batch 1/5/8

Jalankan:

```bash
cd /home/amiir/AndroidStudioProjects
./scripts/run-project-regression.sh
```

Output:
- Report: `docs/reports/project_regression_*.md`
- Log per step: `docs/reports/project_regression_*_<step>.log`

Opsional:
- `--with-e2e` untuk memicu launcher E2E Android manual-assisted.

## 4) API Contract Lock (Batch #4)

Jalankan:

```bash
cd /home/amiir/AndroidStudioProjects
./scripts/check-openapi-contract.sh
```

Yang dicek:
- dokumen `openapi.yaml` valid OpenAPI 3.x
- seluruh path yang didokumentasikan tersedia di route `/api/v1/...`
- route `/api/...` tanpa versi tidak boleh bertambah di luar allowlist legacy

## 5) Observability Alert Check (Batch #5)

Jalankan:

```bash
cd /home/amiir/AndroidStudioProjects
./scripts/check-observability-alerts.sh
```

Env threshold opsional:
- `WINDOW_MINUTES` (default `60`)
- `MAX_5XX` (default `10`)
- `MAX_SLOW` (default `25`)
- `MAX_DURATION_MS` (default `5000`)

## Tambahan Implementasi Batch 1/5/8

1. Publish quality gate untuk admin:
- Publish sekarang diblok jika metadata utama tidak layak atau file PDF/cover tidak valid.
- Error message eksplisit: `Quality gate publish gagal: ...`

2. Observability API:
- Middleware tracing request aktif di group API.
- Response menambahkan header `X-Request-Id`.
- Slow request / warning / error API dicatat di `storage/logs/observability.log`.
- Event moderation dicatat di `storage/logs/moderation.log`.
