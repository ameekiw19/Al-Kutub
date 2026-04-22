#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKEND_DIR="$ROOT_DIR/al-kutub"
ANDROID_DIR="$ROOT_DIR/AlKutub"
REPORT_DIR="$ROOT_DIR/docs/reports"
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
REPORT_FILE="$REPORT_DIR/presentation_readiness_${TIMESTAMP}.md"
LOG_FILE="$REPORT_DIR/presentation_readiness_${TIMESTAMP}.log"

SKIP_ANDROID=0
SKIP_BACKEND=0

for arg in "$@"; do
  case "$arg" in
    --skip-android) SKIP_ANDROID=1 ;;
    --skip-backend) SKIP_BACKEND=1 ;;
    *)
      echo "Unknown arg: $arg"
      echo "Usage: $0 [--skip-android] [--skip-backend]"
      exit 1
      ;;
  esac
done

mkdir -p "$REPORT_DIR"

{
  echo "# Presentation Readiness Report"
  echo
  echo "- Date: $(date)"
  echo "- Root: $ROOT_DIR"
  echo "- Backend: $BACKEND_DIR"
  echo "- Android: $ANDROID_DIR"
  echo
  echo "## Steps"
} > "$REPORT_FILE"

log_step() {
  local title="$1"
  shift
  echo "[presentation-check] $title"
  {
    echo "- [ ] $title"
  } >> "$REPORT_FILE"

  if "$@" >> "$LOG_FILE" 2>&1; then
    sed -i "s/- \\[ \\] $title/- [x] $title/" "$REPORT_FILE"
  else
    {
      echo
      echo "## FAILURE"
      echo "Step gagal: $title"
      echo "Lihat log: $LOG_FILE"
    } >> "$REPORT_FILE"
    echo "[presentation-check][FAIL] Step gagal: $title"
    echo "Lihat log: $LOG_FILE"
    exit 1
  fi
}

if [[ "$SKIP_BACKEND" -eq 0 ]]; then
  log_step "Laravel security hardening test" \
    bash -lc "cd '$BACKEND_DIR' && php artisan test tests/Feature/SecurityHardeningTest.php"
  log_step "Laravel OpenAPI contract lock test" \
    bash -lc "cd '$BACKEND_DIR' && php artisan test tests/Feature/ApiContractLockTest.php"
  log_step "Laravel web ajax json contract test" \
    bash -lc "cd '$BACKEND_DIR' && php artisan test tests/Feature/WebAjaxJsonErrorContractTest.php"
fi

if [[ "$SKIP_ANDROID" -eq 0 ]]; then
  log_step "Android unit tests (debug)" \
    bash -lc "cd '$ANDROID_DIR' && ./gradlew :app:testDebugUnitTest"
fi

{
  echo
  echo "## Result"
  echo "READY - semua step yang dipilih lulus."
  echo
  echo "## Files"
  echo "- Report: $REPORT_FILE"
  echo "- Log: $LOG_FILE"
} >> "$REPORT_FILE"

echo "[presentation-check] Selesai."
echo "[presentation-check] Report: $REPORT_FILE"
echo "[presentation-check] Log: $LOG_FILE"

