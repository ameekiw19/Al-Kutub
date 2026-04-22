# Skrip Demo 7 Menit (Final)

Dokumen ini adalah naskah presentasi praktis untuk sidang SMA.
Target: jelas, singkat, dan minim risiko saat live demo.

## Struktur Waktu

1. `00:00 - 00:40` Pembukaan proyek
2. `00:40 - 01:40` Arsitektur singkat web + Android
3. `01:40 - 03:20` Demo Android (user flow)
4. `03:20 - 05:20` Demo web admin (content management)
5. `05:20 - 06:20` Bukti kualitas (security, test, contract)
6. `06:20 - 07:00` Penutup dan nilai manfaat

## Naskah Siap Ucap

## 1) Pembukaan (`00:00 - 00:40`)

Kalimat:

"Assalamu'alaikum, saya memperkenalkan Al-Kutub, platform perpustakaan digital Islam berbasis web dan Android.  
Tujuan proyek ini adalah memudahkan membaca, mencari, dan mengelola kitab secara terstruktur."

## 2) Arsitektur (`00:40 - 01:40`)

Kalimat:

"Sistem ini terdiri dari dua aplikasi:
- Backend web Laravel untuk admin, API, autentikasi, notifikasi, dan manajemen data.
- Aplikasi Android Kotlin Jetpack Compose untuk pengguna umum.

Android mengakses API `v1`, jadi data user seperti history, bookmark, dan catatan baca tersimpan terpusat di backend."

## 3) Demo Android (`01:40 - 03:20`)

Urutan klik:

1. Login user di Android.
2. Search kitab.
3. Buka detail kitab.
4. Masuk pembaca PDF.
5. Kembali ke app, tunjukkan history.
6. Toggle bookmark, lalu buka halaman bookmark.
7. Tambah reading note, lalu refresh halaman note.

Kalimat saat demo:

"Di sini user bisa cari kitab, baca PDF, dan aktivitas baca otomatis tersimpan.  
Setelah keluar dari reader, history, bookmark, dan catatan baca tetap ada, artinya persistence berjalan."

## 4) Demo Admin Web (`03:20 - 05:20`)

Urutan klik:

1. Login admin.
2. Buka halaman manajemen kitab.
3. Tambah kitab.
4. Tunjukkan dashboard statistik.

Kalimat saat demo:

"Di sisi admin, data kitab bisa dikelola dari web.  
Perubahan ini langsung mendukung kebutuhan user di aplikasi Android karena memakai backend yang sama."

## 5) Bukti Kualitas (`05:20 - 06:20`)

Kalimat:

"Untuk kualitas, saya rapikan keamanan dan kontrak API:
- endpoint sensitif sudah diproteksi role dan auth.
- route debug dibatasi hanya local environment.
- kontrak OpenAPI dan route backend sudah sinkron.

Pengujian utama juga lulus di backend dan Android."

Tambahkan jika ditanya:

"Saya juga menyiapkan smoke test demo agar alur presentasi bisa diulang konsisten."

## 6) Penutup (`06:20 - 07:00`)

Kalimat:

"Kesimpulannya, Al-Kutub sudah memenuhi kebutuhan inti:
- User bisa membaca dengan nyaman dari Android.
- Admin bisa mengelola konten dari web.
- Data tersimpan konsisten dan sistem lebih siap produksi.

Terima kasih, saya siap untuk sesi tanya jawab."

## Cadangan Jika Demo Tersendat

1. Jika internet/device lambat, lanjutkan dengan hasil data yang sudah tersimpan (history/bookmark/note) sebagai bukti persistence.
2. Jika PDF lambat dibuka, pindah dulu ke fitur lain lalu kembali ke reader.
3. Jika ada error minor UI, fokus ke tujuan sistem dan tunjukkan data backend tetap konsisten.

## File Pendukung

- HTML presentasi: `docs/presentation/PRESENTASI_AL_KUTUB.html`
- Smoke test: `docs/MAIN_DEMO_SMOKE_TEST.md`
- Readiness script: `scripts/run-final-presentation-check.sh`

