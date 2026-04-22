# Demo Checklist Al-Kutub

## Happy Path User (Android)

1. Login user berhasil.
2. Home tampil daftar kitab.
3. Search kitab dengan kata kunci + filter.
4. Buka detail kitab, beri rating dan komentar.
5. Download PDF dan buka viewer.
6. Progress baca muncul di history.
7. Tambah/hapus bookmark.
8. Buka notifikasi dan mark-as-read, unread count berkurang.
9. Buka pengaturan tema/notifikasi/2FA dari halaman akun.

## Happy Path Admin (Web)

1. Login admin berhasil.
2. Dashboard admin tampil statistik.
3. Tambah kitab baru.
4. Cek kitab masuk ke katalog user.
5. Kelola kategori/user/komentar.
6. Cek audit logs admin.

## Error Handling

1. Endpoint admin API diakses tanpa token -> 401.
2. Endpoint admin API diakses user non-admin -> 403.
3. Login gagal menampilkan pesan error.
4. Kitab tidak ditemukan menampilkan state error yang jelas.

## Final Pre-Show

1. `php artisan test` lulus.
2. `./gradlew :app:testDebugUnitTest` lulus.
3. Backend dan Android pakai base URL yang benar untuk environment demo.
4. Akun demo sudah siap (admin/user).
