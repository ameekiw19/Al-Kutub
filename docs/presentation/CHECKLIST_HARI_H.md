# Checklist Hari-H Sidang

Checklist ini dibuat supaya presentasi stabil dan mengurangi panic.

## H-1 (Malam Sebelum Sidang)

1. Jalankan readiness check:
   `./scripts/run-final-presentation-check.sh`
2. Pastikan backend bisa jalan:
   `cd al-kutub && php artisan serve`
3. Pastikan Android build sukses:
   `cd AlKutub && ./gradlew :app:assembleDebug`
4. Siapkan akun demo:
   - admin
   - user
5. Siapkan 1-2 kitab demo yang mudah dicari.
6. Pastikan alur Android ini berhasil:
   - search
   - detail
   - reader
   - history
   - bookmark
   - reading note
7. Buka `docs/presentation/PRESENTASI_AL_KUTUB.html` dan cek tampilan normal.
8. Latih naskah `docs/presentation/SKRIP_DEMO_7_MENIT.md` minimal 2 kali.

## Hari-H (60 Menit Sebelum Presentasi)

1. Jalankan backend.
2. Login admin web dan user Android.
3. Cek endpoint utama singkat:
   - list kitab
   - search kitab
4. Pastikan emulator/device Android siap dan baterai cukup.
5. Tutup aplikasi yang bisa ganggu performa.
6. Siapkan urutan tab yang akan dibuka agar tidak bingung.

## Hari-H (10 Menit Sebelum Presentasi)

1. Buka halaman awal presentasi.
2. Buka tab admin dashboard.
3. Buka Android di halaman home.
4. Siapkan query pencarian kitab yang sudah pasti ada hasilnya.
5. Tarik napas, mulai sesuai urutan skrip.

## Fallback Plan

1. Jika jaringan lambat:
   Tunjukkan data persistence (history/bookmark/note) terlebih dahulu.
2. Jika reader PDF lambat:
   Lanjut ke dashboard admin, lalu kembali ke Android.
3. Jika ada bug minor UI:
   Jelaskan bahwa data core tetap aman dan endpoint utama tetap berjalan.

## Red Flags Yang Harus Dihindari

1. Menambah fitur baru di hari-H.
2. Mengubah struktur database mendadak.
3. Mengganti endpoint URL mendekati waktu presentasi.
4. Menjalankan test besar tepat sebelum mulai tanpa waktu recovery.

