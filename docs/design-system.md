# Al-Kutub Design System

## Tujuan
Dokumen ini menyatukan bahasa desain Laravel (web) dan Android (Compose) agar konsisten untuk demo dan pengembangan lanjutan.

## Single Source of Truth
1. Token lintas platform: `docs/design-tokens.json`
2. Adapter Laravel: `al-kutub/app/Http/Constants/SharedColors.php`
3. Partial token web: `al-kutub/resources/views/partials/design-tokens.blade.php`
4. Android shared theme: `AlKutub/app/src/main/java/com/example/al_kutub/ui/theme/SharedColors.kt`, `Theme.kt`, `Type.kt`

## Aturan Implementasi
1. Gunakan warna dari token role (`primary`, `surface`, `on-surface`, dll), bukan hex langsung.
2. Font utama lintas platform: `Poppins`.
3. Web dark mode wajib memakai `data-theme="light|dark"`.
4. Android UI wajib mengutamakan `MaterialTheme.colorScheme.*`.

## Class Primitive Web
Gunakan class berikut untuk komponen dasar:
1. `ak-card`
2. `ak-btn`, `ak-btn-primary`, `ak-btn-outline`
3. `ak-input`
4. `ak-nav`
5. `ak-chip`
6. `ak-badge`

File CSS: `al-kutub/public/assets/compiled/css/al-kutub-design-system.css`

## Checklist Review
1. Tidak ada font `Nunito`/`Roboto` pada halaman prioritas.
2. Tidak ada hardcoded color untuk variabel token utama di Blade prioritas.
3. Android screen prioritas memakai `MaterialTheme.colorScheme` untuk warna utama.
4. CI menjalankan `scripts/check-design-consistency.sh`.
