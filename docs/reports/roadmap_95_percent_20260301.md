# Checklist Eksekusi 2 Minggu (87% -> 95%+)

Tanggal mulai: 2026-03-01
Target: parity user Web-Android + stabilitas API + quality gate + observability minimum untuk penilaian proyek.

## Target Akhir (Terukur)
1. Parity Web User vs Android User: >= 92%
2. Build Android debug: PASS
3. Laravel feature tests utama: PASS
4. Kontrak error API konsisten JSON pada endpoint kritikal
5. Ada bukti regression & smoke test yang terdokumentasi

## Sprint Plan Harian

### Hari 1 - Baseline & Gap Lock
1. Finalisasi matriks parity user (reader/progress/marker/history/notes/account).
2. Lock daftar gap P0-P1.
3. Output: `docs/reports/web_android_user_parity_audit_*.md` (sudah tersedia).

### Hari 2 - Parity UX P0 (Android Account)
1. Selaraskan label menu dengan aksi aktual.
2. Pastikan entry account ke fitur utama jelas dan konsisten.
3. Output: patch `AccountScreen` + smoke check navigasi menu.

### Hari 3 - Parity Flow P0 (Reading Notes Entry)
1. Samakan entry point catatan baca lintas layar (detail/reader/account).
2. Gunakan istilah UI yang konsisten bahasa Indonesia.
3. Output: patch navigasi + uji manual end-to-end notes.

### Hari 4 - Reader UX Alignment
1. Samakan kontrol navigasi halaman penting (jump/manual input atau alternatif yang setara).
2. Pastikan tidak ada loading gantung.
3. Output: patch reader + video/screenshot bukti flow.

### Hari 5 - Hardening API Error Contract (Laravel)
1. Standarkan respons error API ke JSON untuk endpoint user/admin kritikal.
2. Pastikan request AJAX/admin edit kitab tidak mendapat HTML error page.
3. Output: handler + test untuk validasi format error.

### Hari 6 - Hardening API Error Contract Lanjutan
1. Uji cabang 401/403/404/422/429/500 di endpoint utama.
2. Konsistenkan message dan struktur payload error.
3. Output: test report + perbaikan gap.

### Hari 7 - Smoke Test Tengah Sprint
1. Uji manual flow: auth, katalog, detail, baca PDF, marker, notes, history, bookmark, account.
2. Dokumentasi bug residual.
3. Output: `docs/reports/android_reader_e2e_after_manual.log` + ringkasan temuan.

### Hari 8 - E2E Automation Dasar
1. Tambah automation skenario kritikal backend (feature test) dan minimal UI flow.
2. Fokus: login -> baca -> progress -> lanjut baca -> marker.
3. Output: script test + laporan pass/fail.

### Hari 9 - CI Gate
1. Pastikan pipeline memblok merge jika compile/test gagal.
2. Sertakan minimal: Laravel feature tests + Android compileDebugKotlin.
3. Output: workflow CI + badge/status dokumentasi.

### Hari 10 - Regression Lock
1. Tambah regression checklist final untuk fitur user kritikal.
2. Simpan command baku untuk reproduksi test.
3. Output: `docs/reports/project_regression_*.md`.

### Hari 11 - Observability Minimum
1. Tambah logging terstruktur untuk error reader/sync/auth.
2. Kategorikan log level (W untuk 429, E untuk fatal).
3. Output: panduan log debugging lintas Laravel-Android.

### Hari 12 - Observability Lanjutan
1. Korelasikan request id/session id pada log penting.
2. Validasi troubleshooting path dari laporan error ke sumber.
3. Output: dokumen runbook troubleshooting.

### Hari 13 - Final UAT
1. Jalankan UAT checklist dengan skenario kosong, terisi, offline, token kadaluarsa.
2. Catat defect terakhir dan patch cepat.
3. Output: UAT report + status ready presentasi.

### Hari 14 - Closing & Presentasi
1. Final regression + freeze branch presentasi.
2. Siapkan ringkasan fitur selesai vs backlog.
3. Output: `docs/reports/final_readiness_20260314.md`.

## Acceptance Criteria Final
1. User membuka kitab langsung ke PDF (tanpa loading gantung).
2. Lanjut baca selalu ke halaman terakhir yang tersimpan.
3. Bookmark halaman per kitab stabil: add/edit/delete/jump persist.
4. 429 tidak memutus flow: data aman lokal + sync eventual consistency.
5. Admin edit kitab tidak memunculkan error parsing HTML di klien AJAX.
6. Test utama dan compile berjalan sukses berulang.

## Prioritas Eksekusi (Jika Waktu Terbatas)
1. P0: parity flow membaca + API error JSON contract.
2. P1: CI gate + regression automation.
3. P2: observability dan quality polish.

## Command Verifikasi Standar
1. Laravel tests:
```bash
cd /home/amiir/AndroidStudioProjects/al-kutub
php artisan test
```
2. Android compile:
```bash
cd /home/amiir/AndroidStudioProjects/AlKutub
./gradlew :app:compileDebugKotlin
```
3. Log audit reader:
```bash
adb -s emulator-5554 logcat -d | rg "PdfViewerScreen|HistoryRepository|OfflineSyncRepository|ReadingProgress|PageBookmark|AUTH_401|429"
```
