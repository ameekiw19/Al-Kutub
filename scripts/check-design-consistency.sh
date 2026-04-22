#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

PRIORITY_VIEWS=(
  "$ROOT_DIR/al-kutub/resources/views/Template.blade.php"
  "$ROOT_DIR/al-kutub/resources/views/TemplateUser.blade.php"
  "$ROOT_DIR/al-kutub/resources/views/Login.blade.php"
  "$ROOT_DIR/al-kutub/resources/views/Register.blade.php"
  "$ROOT_DIR/al-kutub/resources/views/2fa/verify.blade.php"
  "$ROOT_DIR/al-kutub/resources/views/LandingPage.blade.php"
)

FAILED=0

echo "[design-check] checking font consistency..."
for file in "${PRIORITY_VIEWS[@]}"; do
  if rg -n "Nunito|Roboto|Arial|system-ui" "$file" >/dev/null; then
    echo "[design-check][FAIL] Non-design-system font found in $file"
    rg -n "Nunito|Roboto|Arial|system-ui" "$file" || true
    FAILED=1
  fi
done

echo "[design-check] checking hardcoded token variables..."
for file in "${PRIORITY_VIEWS[@]}"; do
  if rg -n --pcre2 -- "--(primary|primary-color|primary-dark|text-color|background-color|card-bg|border-color|accent|danger)\s*:\s*#[0-9A-Fa-f]{3,8}" "$file" >/dev/null; then
    echo "[design-check][FAIL] Hardcoded token variable color found in $file"
    rg -n --pcre2 -- "--(primary|primary-color|primary-dark|text-color|background-color|card-bg|border-color|accent|danger)\s*:\s*#[0-9A-Fa-f]{3,8}" "$file" || true
    FAILED=1
  fi
done

if [[ "$FAILED" -ne 0 ]]; then
  echo "[design-check] FAILED"
  exit 1
fi

echo "[design-check] PASSED"
