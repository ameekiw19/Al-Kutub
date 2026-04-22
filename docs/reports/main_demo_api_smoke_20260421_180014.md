# Main Demo API Smoke Report

- Tanggal: 2026-04-21 18:00:16 WIB
- Backend: http://127.0.0.1:8000
- User smoke: `smoke_reader_demo`
- Kitab smoke: `Smoke Demo Kitab Android` (ID: `45`)

## Hasil
- Admin flow berhasil menyiapkan dan mempublikasikan kitab smoke via `KitabPublicationService`.
- Search API menemukan kitab smoke pada hasil teratas.
- Detail kitab dapat diambil melalui endpoint publik.
- Endpoint download mengembalikan file PDF non-kosong (`3468867` bytes).
- History tersimpan untuk kitab smoke dan muncul di daftar riwayat user.
- Bookmark tersimpan dan muncul di daftar bookmark user.
- Reading note tersimpan dan muncul di daftar reading notes user.

## Ringkasan Angka
- History total: `1`
- Bookmark total: `1`
- Reading notes total: `1`

## Artefak
- Setup JSON: `/tmp/tmp.iRbb8gHxL7`
- Login JSON: `/tmp/tmp.9XwB716OoJ`
- Search JSON: `/tmp/tmp.GpjmEF0ujm`
- Detail JSON: `/tmp/tmp.tnwCJvQgjZ`
- History store JSON: `/tmp/tmp.DbemII8Ttl`
- History list JSON: `/tmp/tmp.Jckwu7OU9v`
- Bookmark JSON: `/tmp/tmp.OP3MZEHdqS`
- Bookmark list JSON: `/tmp/tmp.SI0G15Ot0v`
- Reading note store JSON: `/tmp/tmp.hSxor6m34f`
- Reading note list JSON: `/tmp/tmp.3c6gyVkQf6`
- Downloaded PDF: `/tmp/tmp.qPFw4Sz2LA.pdf`
