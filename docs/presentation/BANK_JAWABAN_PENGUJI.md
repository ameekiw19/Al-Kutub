# Bank Jawaban Penguji (Siap Pakai)

Gunakan jawaban ini sebagai template. Sesuaikan gaya bicara kamu saat presentasi.

## 1) "Kenapa kamu pilih topik ini?"

Jawaban:
"Karena kebutuhan akses kitab digital cukup besar, terutama untuk belajar mandiri.
Saya ingin membuat platform yang tidak hanya menampilkan konten, tapi juga membantu progress baca user."

## 2) "Kenapa pakai web dan Android sekaligus?"

Jawaban:
"Saya pisahkan peran:
- web untuk admin mengelola data,
- Android untuk pengalaman membaca user.
Dengan begitu alur penggunaan lebih realistis seperti aplikasi produksi."

## 3) "Apa bedanya proyek ini dengan CRUD biasa?"

Jawaban:
"Selain CRUD data kitab, proyek ini punya alur end-to-end:
- autentikasi user/admin,
- pembacaan PDF,
- persistence history, bookmark, dan reading note,
- notifikasi, dashboard, dan kontrak API versioning."

## 4) "Bagaimana keamanan project kamu?"

Jawaban:
"Endpoint admin diproteksi `auth` dan role admin.
Route debug dibatasi hanya untuk environment local.
Kontrak API dan route disinkronkan lewat test agar endpoint tidak bocor atau miss."

## 5) "Kalau ada user nakal, apa mitigasinya?"

Jawaban:
"Saya sudah pakai pemisahan role dan middleware.
Di pengembangan berikutnya saya prioritaskan rate limiting lebih granular dan monitoring suspicious request."

## 6) "Bagaimana kamu pastikan fitur tidak rusak?"

Jawaban:
"Saya pakai test flow penting di backend dan unit test Android.
Sebelum demo, saya jalankan smoke test untuk skenario utama presentasi."

## 7) "Kenapa route kamu pakai versioning `v1`?"

Jawaban:
"Supaya perubahan API di masa depan tidak merusak aplikasi klien lama.
Versioning bikin migrasi lebih aman dan terukur."

## 8) "Kenapa masih ada route lama?"

Jawaban:
"Saya pertahankan sebagian route legacy untuk backward compatibility.
Tapi canonical route baru sudah saya rapikan dan dipakai di UI terbaru."

## 9) "Apa tantangan terbesar saat bikin ini?"

Jawaban:
"Menjaga konsistensi antara web, API, dan Android.
Karena itu saya fokus ke sinkronisasi kontrak API, proteksi route admin, dan perapihan struktur agar maintainable."

## 10) "Apa yang akan kamu kembangkan jika ada waktu tambahan?"

Jawaban:
"Prioritas saya bukan nambah banyak fitur dulu, tapi memperdalam kualitas:
- observability,
- hardening security,
- dan optimasi pengalaman reader di Android."

## 11) "Apa kontribusi paling penting kamu di proyek ini?"

Jawaban:
"Menyatukan flow end-to-end yang benar-benar jalan:
admin kelola kitab di web, lalu user menemukan dan membaca di Android, dan data baca tersimpan konsisten."

## 12) "Kalau dinilai dari kesiapan produksi, statusnya apa?"

Jawaban:
"Sudah layak demo dan layak portofolio serius.
Untuk produksi penuh masih perlu hardening lanjutan seperti audit keamanan periodik dan monitoring operasional."

## Tips Saat Menjawab

1. Jawab langsung 1 inti dulu, baru detail.
2. Jangan defensif kalau ada kritik, ubah jadi roadmap improvement.
3. Selalu tarik jawaban ke nilai proyek: fungsional, konsisten, dan maintainable.

