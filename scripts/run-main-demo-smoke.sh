#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ANDROID_DIR="$ROOT_DIR/AlKutub"
BACKEND_DIR="$ROOT_DIR/al-kutub"
PACKAGE_NAME="com.example.al_kutub"
MAIN_ACTIVITY="com.example.al_kutub/.MainActivity"
REPORT_DIR="$ROOT_DIR/docs/reports"
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
REPORT_FILE="$REPORT_DIR/main_demo_smoke_${TIMESTAMP}.md"
LOG_FILE="$REPORT_DIR/main_demo_smoke_${TIMESTAMP}.log"

SKIP_BACKEND=0
SKIP_ANDROID=0
SKIP_BUILD=0
COLLECT_LOGS=0
AUTO_FINISH=0

for arg in "$@"; do
  case "$arg" in
    --skip-backend) SKIP_BACKEND=1 ;;
    --skip-android) SKIP_ANDROID=1 ;;
    --skip-build) SKIP_BUILD=1 ;;
    --collect-logs) COLLECT_LOGS=1 ;;
    --auto-finish) AUTO_FINISH=1 ;;
    *)
      echo "Unknown arg: $arg"
      echo "Usage: $0 [--skip-backend] [--skip-android] [--skip-build] [--collect-logs] [--auto-finish]"
      exit 1
      ;;
  esac
done

mkdir -p "$REPORT_DIR"

run_step() {
  local name="$1"
  shift
  echo "[demo-smoke] $name"
  "$@"
}

if [[ "$SKIP_BACKEND" -eq 0 ]]; then
  run_step "Backend feature tests untuk flow demo" \
    bash -lc "cd '$BACKEND_DIR' && php artisan test --testsuite=Feature --filter='KitabPublicationWorkflowTest|SearchApiTest|ReadingNoteIdempotencyTest|PublishedOnlyVisibilityTest'"
fi

if [[ "$SKIP_ANDROID" -eq 0 && "$SKIP_BUILD" -eq 0 ]]; then
  run_step "Android build validation" \
    bash -lc "cd '$ANDROID_DIR' && ./gradlew :app:compileDebugKotlin :app:testDebugUnitTest :app:assembleDebug"
fi

DEVICE_ID=""
APK_PATH="$ANDROID_DIR/app/build/outputs/apk/debug/app-debug.apk"
if [[ "$SKIP_ANDROID" -eq 0 ]]; then
  DEVICE_ID="$(adb devices | awk 'NR>1 && $2==\"device\" {print $1; exit}')"
fi

cat > "$REPORT_FILE" <<REPORT
# Main Demo Smoke Test

- Date: $(date)
- Root: $ROOT_DIR
- Backend: $BACKEND_DIR
- Android: $ANDROID_DIR
- Device: ${DEVICE_ID:-not-connected}

## Tujuan
Memvalidasi alur demo presentasi:
1. Admin tambah kitab lalu publish.
2. Android cari kitab yang baru dipublish.
3. Android buka detail kitab.
4. Android masuk reader dan membaca kitab.
5. Data history, bookmark, dan reading note benar-benar tersimpan.

## Prasyarat
- Web backend bisa diakses dan akun admin siap.
- Android login memakai akun user biasa.
- PDF kitab valid dan publish status = published.
- Jika ingin bukti log, jalankan script ini dengan \`--collect-logs\`.

## Checklist Demo
- [ ] Admin login ke web admin.
- [ ] Admin tambah kitab baru dengan judul unik untuk demo.
- [ ] Admin submit review lalu publish kitab.
- [ ] Kitab baru muncul di hasil \`/api/v1/kitab/search\` atau halaman katalog user.
- [ ] Android login dengan user demo.
- [ ] Android search menemukan kitab baru.
- [ ] Android buka detail kitab yang benar.
- [ ] Android tekan aksi baca dan PDF terbuka tanpa stuck loading.
- [ ] Android keluar dari reader lalu item muncul di Riwayat.
- [ ] Android toggle bookmark lalu item muncul di halaman Bookmark.
- [ ] Android tambah catatan baca lalu catatan tetap ada setelah refresh / buka ulang.
- [ ] Tidak ada error AUTH_401 atau spam 429 yang merusak demo.

## Evidence Yang Dicatat
### Admin
- Judul kitab demo:
- ID kitab demo:
- Status publish:
- URL / screenshot halaman admin:

### Android Search
- Query yang dipakai:
- Hasil kitab ditemukan:

### Android Reader
- Halaman terakhir yang dibaca:
- Apakah PDF responsif:

### Persistence
- History tersimpan:
- Bookmark tersimpan:
- Reading note tersimpan:

## Verifikasi Tambahan
1. Buka halaman Riwayat Android dan pastikan kitab demo muncul.
2. Buka halaman Bookmark Android dan pastikan kitab demo muncul.
3. Buka halaman Catatan Baca Android dan pastikan note kitab demo muncul.
4. Jika perlu, verifikasi silang ke web user:
   - \`/history\`
   - \`/bookmarks\`
   - \`/reading-notes\`

## Observasi
- Isi temuan manual di sini.
REPORT

if [[ "$SKIP_ANDROID" -eq 0 ]]; then
  if [[ -z "$DEVICE_ID" ]]; then
    cat <<MSG
[demo-smoke][BLOCKED] Tidak ada device/emulator Android yang terhubung.

Report template tetap dibuat:
  $REPORT_FILE
MSG
    exit 2
  fi

  if [[ ! -f "$APK_PATH" ]]; then
    echo "[demo-smoke][FAIL] APK debug tidak ditemukan: $APK_PATH"
    exit 1
  fi

  run_step "Install APK ke device $DEVICE_ID" adb -s "$DEVICE_ID" install -r "$APK_PATH"
  run_step "Reset state aplikasi" adb -s "$DEVICE_ID" shell pm clear "$PACKAGE_NAME"

  if [[ "$COLLECT_LOGS" -eq 1 ]]; then
    adb -s "$DEVICE_ID" logcat -c || true
  fi

  run_step "Start app Android" adb -s "$DEVICE_ID" shell am start -n "$MAIN_ACTIVITY"

  cat <<MSG
[demo-smoke] Report template:
  $REPORT_FILE

[demo-smoke] Jalankan sekarang flow manual:
1. Admin tambah kitab -> submit review -> publish.
2. Android cari kitab -> buka detail -> baca.
3. Verifikasi Riwayat, Bookmark, dan Catatan Baca.
MSG

  if [[ "$AUTO_FINISH" -eq 0 ]]; then
    read -r -p "[demo-smoke] Tekan Enter setelah flow manual selesai..." _
  fi

  if [[ "$COLLECT_LOGS" -eq 1 ]]; then
    adb -s "$DEVICE_ID" logcat -d \
      | rg "SearchScreen|AdvancedSearchViewModel|KitabDetailViewModel|PdfViewerScreen|HistoryRepository|BookmarkViewModel|ReadingNoteViewModel|PageBookmark|AUTH_401|429|Too Many Requests" \
      > "$LOG_FILE" || true
    echo "[demo-smoke] Log evidence tersimpan: $LOG_FILE"
  fi
fi

echo "[demo-smoke] Selesai. Lengkapi observasi manual di: $REPORT_FILE"
