# Al-Kutub Android App

Aplikasi Android native (Kotlin + Jetpack Compose) untuk konsumsi API Al-Kutub.

## Fitur Utama

- Login/register + dukungan login 2FA
- Home, katalog, detail kitab
- Search dengan filter, suggestion, dan riwayat pencarian
- Bookmark, history, reading notes
- PDF viewer + tracking progress baca
- Notifikasi (FCM + polling API)
- Pengaturan akun: tema, notifikasi, keamanan 2FA

## Build & Run

```bash
./gradlew assembleDebug
```

## Konfigurasi API

BuildConfig di `app/build.gradle.kts`:

- Debug API: `http://10.0.2.2:8000/api/v1/`
- Debug Web: `http://10.0.2.2:8000/`

Pastikan backend Laravel berjalan sebelum menjalankan aplikasi.

## Testing

Unit test:

```bash
./gradlew :app:testDebugUnitTest
```

Instrumentation smoke test (butuh emulator/device):

```bash
./gradlew :app:connectedDebugAndroidTest
```

## Catatan Keamanan

- Session/token disimpan lewat encrypted preferences.
- HTTP logging meredaksi header `Authorization` dan dibatasi level debug.
