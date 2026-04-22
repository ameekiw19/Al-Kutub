# Android Reader Validation - 2026-03-01

## Scope
1. Verifikasi stabilitas build setelah implementasi fitur `Lompat ke Halaman`.
2. Verifikasi unit test untuk flow reader/progress/anti-429.
3. Verifikasi smoke instrumentation test dasar.

## Perubahan Fitur Reader
1. Tambah aksi `Hal.` di top bar untuk membuka dialog lompat halaman.
2. Tambah aksi tap pada indikator progres (`current/total`) untuk membuka dialog lompat halaman.
3. Tambah dialog input nomor halaman dengan validasi rentang `1..pageCount`.
4. Normalisasi label kecil marker (`Marker`, `Ubah marker`).

## Hasil Verifikasi
1. `./gradlew :app:compileDebugKotlin` -> **PASS**
2. `./gradlew :app:testDebugUnitTest` -> **PASS**
3. `./gradlew :app:connectedDebugAndroidTest -Pandroid.testInstrumentationRunnerArguments.class=com.example.al_kutub.ui.MainActivitySmokeTest`
   - Run 1: gagal karena selector lama (`Daftar sekarang`) -> **fix** ke selector terbaru.
   - Run 2: gagal karena `No connected devices`.
   - Run 3: gagal flakey `No compose hierarchies found` karena popup permission runtime.
   - Fix tambahan: tambahkan `GrantPermissionRule` + dependency `androidx.test:rules`.
   - Run 4: **PASS** (2 test selesai di `Pixel_7(AVD) - 15`).
4. Evidence log:
   - `docs/reports/android_reader_e2e_after_manual.log` saat ini **0 baris** karena smoke test belum menjalankan flow reader (belum ada interaksi buka PDF).

## Dampak Teknis
1. Reader kini memiliki navigasi manual ke halaman tertentu, memperkuat parity web-android untuk alur baca.
2. Coverage unit test inti untuk stabilitas reader/progress tetap hijau.
3. Smoke test UI kini lebih stabil terhadap popup permission runtime pada emulator.
4. Evidence reader perlu diisi dari run manual flow baca kitab (open PDF, lanjut baca, marker, keluar-masuk).

## Aksi Lanjutan (langsung bisa dijalankan)
1. Jalankan manual flow reader di emulator: buka detail kitab -> baca PDF -> lompat halaman -> kembali -> lanjut baca lagi.
2. Ambil log evidence:
```bash
adb -s emulator-5554 logcat -d | rg "PdfViewerScreen|HistoryRepository|OfflineSyncRepository|ReadingProgress|PageBookmark|KitabDetailViewModel|AUTH_401|API call failed: 429|Too Many Requests" > /home/amiir/AndroidStudioProjects/docs/reports/android_reader_e2e_after_manual.log
```
