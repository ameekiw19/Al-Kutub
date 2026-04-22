# Main Demo Smoke Test

Dokumen ini khusus untuk alur presentasi utama:

1. Admin tambah kitab.
2. Admin publish kitab.
3. Android cari kitab.
4. Android buka detail kitab.
5. Android baca PDF.
6. Android verifikasi history, bookmark, dan reading note tersimpan.

## Jalankan

```bash
cd /home/amiir/AndroidStudioProjects
./scripts/run-main-demo-smoke.sh --collect-logs
```

Jika hanya ingin menyiapkan template tanpa menunggu input manual:

```bash
./scripts/run-main-demo-smoke.sh --collect-logs --auto-finish
```

Untuk smoke test backend live yang langsung memverifikasi persistence flow Android:

```bash
./scripts/run-main-demo-api-smoke.sh
```

## Apa yang diverifikasi

- Backend feature test yang paling relevan untuk flow demo:
  - `KitabPublicationWorkflowTest`
  - `SearchApiTest`
  - `ReadingNoteIdempotencyTest`
  - `PublishedOnlyVisibilityTest`
- Android:
  - compile Kotlin
  - unit test debug
  - assemble debug
  - install APK ke device jika tersedia
- Evidence manual:
  - kitab berhasil dipublish admin
  - kitab ditemukan di search Android
  - detail kitab benar
  - PDF tidak stuck loading
  - history tersimpan
  - bookmark tersimpan
  - reading note tersimpan

## Output

- Report template: `docs/reports/main_demo_smoke_*.md`
- Log evidence: `docs/reports/main_demo_smoke_*.log`
- Report backend live: `docs/reports/main_demo_api_smoke_*.md`

## Catatan

- Script ini manual-assisted, karena langkah admin web dan interaksi reader Android memang bagian dari demo presentasi yang perlu dilihat langsung.
- Jika device Android belum terhubung, script tetap membuat report template lalu berhenti dengan status blocked.
- `run-main-demo-api-smoke.sh` menyiapkan user + kitab smoke, login via API Android, lalu memastikan search, detail, download/history, bookmark, dan reading note benar-benar tersimpan di backend lokal.
