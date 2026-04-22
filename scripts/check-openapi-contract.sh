#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
LARAVEL_DIR="$ROOT_DIR/al-kutub"

if [[ ! -d "$LARAVEL_DIR" ]]; then
  echo "[openapi-check][FAIL] Laravel project tidak ditemukan: $LARAVEL_DIR"
  exit 1
fi

cd "$LARAVEL_DIR"

echo "[openapi-check] Lint OpenAPI YAML structure (basic)"
if ! grep -Eq '^openapi:[[:space:]]*3\.' docs/openapi.yaml; then
  echo "[openapi-check][FAIL] docs/openapi.yaml tidak valid atau versi OpenAPI bukan 3.x"
  exit 1
fi

echo "[openapi-check] Running contract lock tests"
php artisan test --filter=ApiContractLockTest

echo "[openapi-check][PASS] OpenAPI contract lock passed"
