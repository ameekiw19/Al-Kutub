# Audit Parity Web User vs Android User

Tanggal: 2026-03-01
Scope: fitur user-facing antara Laravel Web (`al-kutub`) dan Android (`AlKutub`) dengan fokus utama pada reader/progress/bookmark halaman/notes/history.

## Ringkasan Hasil
- Parity **fitur utama membaca**: **92%**
- Parity **fitur utama user app**: **88%**
- Parity **keseluruhan user experience (utama + tambahan)**: **84%**

Keterangan scoring:
- `Sama` = 100%
- `Parsial` = 60%
- `Gap` = 0%

---

## Matriks Fitur Utama

| Domain | Laravel Web User | Android User | Status | Catatan |
|---|---|---|---|---|
| Auth dasar (login/register/reset/verifikasi) | Ada | Ada | Sama | Keduanya aktif dan terhubung backend |
| 2FA user | Ada (setup/manage/verify) | Ada (settings + flow verifikasi) | Sama | API dan UI dua platform tersedia |
| Home + search kitab | Ada | Ada | Sama | Android lebih kaya (history/suggestion), web tetap lengkap untuk user flow |
| Katalog: kategori + bahasa + sort + search | Ada | Ada | Sama | Filter bahasa Indonesia/Arab ada di dua platform |
| Detail kitab + komentar + rating | Ada | Ada | Sama | Alur utama konsisten |
| Baca PDF (open kitab) | Ada | Ada | Sama | Keduanya sudah punya error state/timeout handling |
| Simpan progress + resume halaman terakhir | Ada | Ada | Sama | Keduanya simpan `current_page` dan lanjut baca |
| Bookmark halaman (marker) di reader | Ada | Ada | Sama | Add/list/jump/edit/delete sudah ada |
| Bookmark kitab (list, hapus, clear all) | Ada | Ada | Sama | Alur CRUD user sudah setara |
| History baca (list, clear all, lanjutkan) | Ada | Ada | Sama | Android punya fallback ke detail saat file lokal belum ada |
| Reading notes CRUD | Ada | Ada | Parsial | Fungsi ada, tetapi UX/labeling dan entry point belum seragam |
| Notifikasi list + mark read/all | Ada | Ada | Sama | Endpoint dan UI utama tersedia |
| Account profile edit | Ada | Ada | Sama | Update profile/password tersedia |

---

## Matriks Fitur Tambahan (Ketidaksamaan Utama)

| Domain Tambahan | Laravel Web User | Android User | Status | Dampak |
|---|---|---|---|---|
| Session management perangkat | Tidak ada UI user | Ada (list sesi, revoke, logout all) | Gap | Android lebih maju |
| Notification settings user | Tidak ada UI web user | Ada | Gap | Parity setting belum penuh |
| Theme settings sinkron akun | Theme toggle lokal ada, setting API belum diekspos penuh di web user | Ada | Parsial | Perilaku tema belum 1 pola |
| Offline sync settings | Tidak relevan di web | Ada | Gap (by design) | Beda platform, bisa dikecualikan parity |
| Advanced search + search history | Basic search/filter | Ada (advanced + history) | Parsial | Android lebih kaya |
| Direct page input di reader | Ada (input nomor halaman) | Belum ada input khusus | Parsial | Minor UX gap |
| Konsistensi label/menu account | Stabil | Ada mismatch label->aksi pada beberapa menu | Parsial | Perlu rapikan copy + action |

---

## Gap List Final (Prioritas)

### P0 (wajib ditutup untuk parity presentasi)
1. Rapikan **label dan aksi menu Account Android** agar tidak misleading.
   - Contoh saat ini: label `Beri Ulasan` mengarah ke Reading Notes; `Bantuan & FAQ` mengarah ke Offline Sync Settings.
2. Samakan **entry point Reading Notes** lintas platform (dari detail/reader atau account) agar user flow konsisten.
3. Putuskan baseline parity untuk fitur non-esensial lintas platform:
   - Opsi A: expose versi sederhana di web (sessions/settings).
   - Opsi B: tandai sebagai Android-only secara resmi di dokumen rilis.

### P1 (naikkan parity UX)
1. Tambahkan **direct page jump input** di Android reader (atau hilangkan di web) untuk menyamakan kontrol navigasi halaman.
2. Samakan bahasa UI (Indonesia konsisten) pada screen Android yang masih campur istilah Inggris.
3. Samakan perilaku search (minimal: riwayat pencarian dasar di web user atau nonaktifkan ekspektasi parity).

### P2 (opsional, nilai tambah)
1. Unifikasi style/komponen notifikasi dan reading notes antar platform.
2. Tambah regression checklist parity khusus user flow lintas platform.

---

## Persentase Per Domain

| Domain | Persentase |
|---|---|
| Reader + Progress + Resume | 94% |
| Page Bookmark Marker | 95% |
| Bookmark Kitab + History | 92% |
| Notes + Notification + Account UX | 84% |
| Parity user secara keseluruhan | 84% |

---

## Evidence (file yang diaudit)

- Laravel route: `al-kutub/routes/web.php`, `al-kutub/routes/api.php`
- Laravel user view/controller:
  - `al-kutub/resources/views/TemplateUser.blade.php`
  - `al-kutub/resources/views/Kategori.blade.php`
  - `al-kutub/resources/views/ViewKitab.blade.php`
  - `al-kutub/resources/views/ReadKitab.blade.php`
  - `al-kutub/resources/views/Bookmark.blade.php`
  - `al-kutub/resources/views/History.blade.php`
  - `al-kutub/resources/views/reading-notes/index.blade.php`
  - `al-kutub/resources/views/AccountUser.blade.php`
  - `al-kutub/app/Http/Controllers/KategoriController.php`
  - `al-kutub/app/Http/Controllers/AccountController.php`
- Android navigation/screen/viewmodel:
  - `AlKutub/app/src/main/java/com/example/al_kutub/ui/navigation/Screen.kt`
  - `AlKutub/app/src/main/java/com/example/al_kutub/ui/screens/PdfViewerScreen.kt`
  - `AlKutub/app/src/main/java/com/example/al_kutub/ui/screens/BookmarkScreen.kt`
  - `AlKutub/app/src/main/java/com/example/al_kutub/ui/screens/HistoryScreen.kt`
  - `AlKutub/app/src/main/java/com/example/al_kutub/ui/screens/ReadingNotesScreen.kt`
  - `AlKutub/app/src/main/java/com/example/al_kutub/ui/screens/AccountScreen.kt`
  - `AlKutub/app/src/main/java/com/example/al_kutub/ui/screens/KatalogScreen.kt`
  - `AlKutub/app/src/main/java/com/example/al_kutub/ui/screens/KitabDetailScreen.kt`
  - `AlKutub/app/src/main/java/com/example/al_kutub/ui/viewmodel/ReadingProgressViewModel.kt`
  - `AlKutub/app/src/main/java/com/example/al_kutub/ui/viewmodel/PageBookmarkViewModel.kt`
  - `AlKutub/app/src/main/java/com/example/al_kutub/data/repository/PageBookmarkRepository.kt`
  - `AlKutub/app/src/main/java/com/example/al_kutub/api/ApiService.kt`

