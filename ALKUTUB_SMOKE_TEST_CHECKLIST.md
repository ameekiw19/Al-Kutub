# Al-Kutub Smoke Test Checklist

## Auth
- Register user baru berhasil.
- Login user berhasil dan token tersimpan.
- Login invalid return error yang jelas.

## Core Reading
- Home tampil data user login (bukan mock).
- Katalog memuat list kitab dari API.
- Detail kitab bisa dibuka dari Home/Katalog.
- PDF viewer menyimpan progress halaman dan waktu baca.

## Engagement
- Bookmark toggle berhasil dari detail.
- History terisi setelah membaca.
- Komentar tambah/hapus berjalan.
- Rating kitab tersimpan.

## Notifications
- Notifikasi list tampil di screen notifikasi.
- Unread count tampil.
- Klik notifikasi membuka detail kitab (jika ada ID kitab).

## Settings
- Edit profile berhasil.
- Theme toggle berjalan.
- Reading notes CRUD berjalan.
- Logout membersihkan sesi.

## Negative Cases
- Token expired diarahkan login ulang.
- Endpoint 404/422 menampilkan pesan yang mudah dipahami.
- Saat offline, app tidak crash dan menampilkan error koneksi.
