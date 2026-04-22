#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
LARAVEL_DIR="$ROOT_DIR/al-kutub"

WINDOW_MINUTES="${WINDOW_MINUTES:-60}"
MAX_5XX="${MAX_5XX:-10}"
MAX_SLOW="${MAX_SLOW:-25}"
MAX_DURATION_MS="${MAX_DURATION_MS:-5000}"

cd "$LARAVEL_DIR"

RAW_JSON="$(php artisan observability:summary --minutes="$WINDOW_MINUTES" --json)"
echo "$RAW_JSON"

read_metric() {
  local key="$1"
  echo "$RAW_JSON" | php -r '$d=json_decode(stream_get_contents(STDIN), true); $k=$argv[1]; echo (int)($d[$k] ?? 0);' "$key"
}

ERR_5XX="$(read_metric errors_5xx)"
SLOW_COUNT="$(read_metric slow_requests)"
MAX_DURATION="$(read_metric max_duration_ms)"

FAILED=0

if (( ERR_5XX > MAX_5XX )); then
  echo "[observability][ALERT] errors_5xx=$ERR_5XX > $MAX_5XX"
  FAILED=1
fi

if (( SLOW_COUNT > MAX_SLOW )); then
  echo "[observability][ALERT] slow_requests=$SLOW_COUNT > $MAX_SLOW"
  FAILED=1
fi

if (( MAX_DURATION > MAX_DURATION_MS )); then
  echo "[observability][ALERT] max_duration_ms=$MAX_DURATION > $MAX_DURATION_MS"
  FAILED=1
fi

if (( FAILED == 1 )); then
  exit 2
fi

echo "[observability][PASS] within thresholds"
