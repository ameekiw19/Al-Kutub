#!/usr/bin/env bash
set -uo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
REPORT_DIR="$ROOT_DIR/docs/reports"
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
REPORT_FILE="$REPORT_DIR/project_regression_${TIMESTAMP}.md"

SKIP_READINESS=0
SKIP_LARAVEL_TESTS=0
SKIP_ANDROID=0
WITH_E2E=0
SKIP_OPENAPI_CONTRACT=0

for arg in "$@"; do
  case "$arg" in
    --skip-readiness) SKIP_READINESS=1 ;;
    --skip-laravel-tests) SKIP_LARAVEL_TESTS=1 ;;
    --skip-android) SKIP_ANDROID=1 ;;
    --skip-openapi-contract) SKIP_OPENAPI_CONTRACT=1 ;;
    --with-e2e) WITH_E2E=1 ;;
    *)
      echo "Unknown arg: $arg"
      echo "Usage: $0 [--skip-readiness] [--skip-laravel-tests] [--skip-openapi-contract] [--skip-android] [--with-e2e]"
      exit 1
      ;;
  esac
done

mkdir -p "$REPORT_DIR"

declare -a STEP_NAMES=()
declare -a STEP_STATUS=()
declare -a STEP_LOGS=()

run_step() {
  local name="$1"
  local command="$2"
  local log_file="$REPORT_DIR/project_regression_${TIMESTAMP}_$(echo "$name" | tr '[:upper:]' '[:lower:]' | tr ' /' '__').log"

  echo "[regression] Running: $name"
  bash -lc "$command" >"$log_file" 2>&1
  local code=$?

  STEP_NAMES+=("$name")
  STEP_LOGS+=("$log_file")

  if [[ $code -eq 0 ]]; then
    STEP_STATUS+=("PASS")
    echo "[regression][PASS] $name"
  else
    STEP_STATUS+=("FAIL")
    echo "[regression][FAIL] $name (see $log_file)"
  fi
}

if [[ $SKIP_READINESS -eq 0 ]]; then
  run_step "Laravel DB Readiness" "cd '$ROOT_DIR' && ./scripts/verify-laravel-db-readiness.sh --apply"
fi

if [[ $SKIP_LARAVEL_TESTS -eq 0 ]]; then
  run_step "Laravel Feature Tests (Batch 1/5/8)" \
    "cd '$ROOT_DIR/al-kutub' && php artisan test --testsuite=Feature --filter='KitabPublicationWorkflowTest|RequestTraceMiddlewareTest|PublishedOnlyVisibilityTest|ReadingNoteIdempotencyTest|SecurityHardeningTest|SessionManagementApiTest'"
fi

if [[ $SKIP_OPENAPI_CONTRACT -eq 0 ]]; then
  run_step "OpenAPI Contract Lock" \
    "cd '$ROOT_DIR' && ./scripts/check-openapi-contract.sh"
fi

if [[ $SKIP_ANDROID -eq 0 ]]; then
  run_step "Android Compile + Unit Test" \
    "cd '$ROOT_DIR/AlKutub' && ./gradlew :app:compileDebugKotlin :app:testDebugUnitTest"
fi

if [[ $WITH_E2E -eq 1 ]]; then
  run_step "Android Reader E2E (manual assisted)" \
    "cd '$ROOT_DIR' && ./scripts/run-android-reader-e2e.sh --skip-build --collect-logs"
fi

{
  echo "# Project Regression Report"
  echo
  echo "- Date: $(date)"
  echo "- Root: $ROOT_DIR"
  echo
  echo "## Steps"
  for i in "${!STEP_NAMES[@]}"; do
    echo "- ${STEP_NAMES[$i]}: ${STEP_STATUS[$i]}"
    echo "  log: ${STEP_LOGS[$i]}"
  done
} > "$REPORT_FILE"

FAIL_COUNT=0
for status in "${STEP_STATUS[@]}"; do
  if [[ "$status" == "FAIL" ]]; then
    FAIL_COUNT=$((FAIL_COUNT+1))
  fi
done

echo "[regression] Report: $REPORT_FILE"

if [[ $FAIL_COUNT -gt 0 ]]; then
  echo "[regression] Completed with $FAIL_COUNT failing step(s)."
  exit 1
fi

echo "[regression] Completed successfully."
exit 0
