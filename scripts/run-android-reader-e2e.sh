#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ANDROID_DIR="$ROOT_DIR/AlKutub"
PACKAGE_NAME="com.example.al_kutub"
MAIN_ACTIVITY="com.example.al_kutub/.MainActivity"
REPORT_DIR="$ROOT_DIR/docs/reports"
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
REPORT_FILE="$REPORT_DIR/android_reader_e2e_${TIMESTAMP}.md"

SKIP_BUILD=0
COLLECT_LOGS=0
for arg in "$@"; do
  case "$arg" in
    --skip-build) SKIP_BUILD=1 ;;
    --collect-logs) COLLECT_LOGS=1 ;;
    *)
      echo "Unknown arg: $arg"
      echo "Usage: $0 [--skip-build] [--collect-logs]"
      exit 1
      ;;
  esac
done

mkdir -p "$REPORT_DIR"

if [[ ! -d "$ANDROID_DIR" ]]; then
  echo "Android project tidak ditemukan di $ANDROID_DIR"
  exit 1
fi

if [[ "$SKIP_BUILD" -eq 0 ]]; then
  echo "[e2e] Build validation: compile + unit test + assemble"
  (
    cd "$ANDROID_DIR"
    ./gradlew :app:compileDebugKotlin :app:testDebugUnitTest :app:assembleDebug
  )
fi

DEVICE_ID="$(adb devices | awk 'NR>1 && $2=="device" {print $1; exit}')"
if [[ -z "$DEVICE_ID" ]]; then
  cat <<'MSG'
[e2e][BLOCKED] Tidak ada device/emulator terhubung.

Hubungkan device lalu jalankan ulang:
  adb devices
  ./scripts/run-android-reader-e2e.sh --collect-logs
MSG
  exit 2
fi

APK_PATH="$ANDROID_DIR/app/build/outputs/apk/debug/app-debug.apk"
if [[ ! -f "$APK_PATH" ]]; then
  echo "[e2e][FAIL] APK tidak ditemukan: $APK_PATH"
  exit 1
fi

echo "[e2e] Installing APK ke device: $DEVICE_ID"
adb -s "$DEVICE_ID" install -r "$APK_PATH" >/dev/null

echo "[e2e] Reset app state (clear data)"
adb -s "$DEVICE_ID" shell am force-stop "$PACKAGE_NAME" || true
adb -s "$DEVICE_ID" shell pm clear "$PACKAGE_NAME" >/dev/null || true

echo "[e2e] Start app"
adb -s "$DEVICE_ID" shell am start -n "$MAIN_ACTIVITY" >/dev/null

if [[ "$COLLECT_LOGS" -eq 1 ]]; then
  adb -s "$DEVICE_ID" logcat -c
fi

cat > "$REPORT_FILE" <<REPORT
# Android Reader E2E Report

- Date: $(date)
- Device: $DEVICE_ID
- APK: $APK_PATH

## Scope
1. Login user
2. Buka kitab -> PDF terbuka cepat (bukan loading gantung)
3. Lanjut baca harus ke halaman terakhir
4. Tambah/edit/hapus marker halaman
5. Keluar PDF -> buka lagi -> posisi terakhir konsisten

## Result Template
- [ ] Login berhasil
- [ ] PDF terbuka tanpa stuck loading
- [ ] Continue reading menuju halaman terakhir
- [ ] Marker add/jump/edit/delete normal
- [ ] Progress/history tetap akurat
- [ ] Tidak ada spam 429 di log

## Observasi
- Isi hasil manual di sini.
REPORT

cat <<MSG
[e2e] App sudah dijalankan di device.
[e2e] Lakukan skenario manual sesuai report template:
  $REPORT_FILE
MSG

if [[ "$COLLECT_LOGS" -eq 1 ]]; then
  LOG_FILE="$REPORT_DIR/android_reader_e2e_${TIMESTAMP}.log"
  adb -s "$DEVICE_ID" logcat -d \
    | rg "PdfViewerScreen|HistoryRepository|OfflineSyncRepository|ReadingProgress|PageBookmark|KitabDetailViewModel|AUTH_401|429" \
    > "$LOG_FILE" || true
  echo "[e2e] Log ringkas tersimpan: $LOG_FILE"
fi

echo "[e2e] Done"
