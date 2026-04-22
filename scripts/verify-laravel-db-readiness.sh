#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
LARAVEL_DIR="$ROOT_DIR/al-kutub"
APPLY=0

for arg in "$@"; do
  case "$arg" in
    --apply) APPLY=1 ;;
    *)
      echo "Unknown arg: $arg"
      echo "Usage: $0 [--apply]"
      exit 1
      ;;
  esac
done

if [[ ! -d "$LARAVEL_DIR" ]]; then
  echo "[readiness][FAIL] Laravel project tidak ditemukan: $LARAVEL_DIR"
  exit 1
fi

cd "$LARAVEL_DIR"

PASS=0
WARN=0
FAIL=0

ok()   { echo "[readiness][PASS] $*"; PASS=$((PASS+1)); }
warn() { echo "[readiness][WARN] $*"; WARN=$((WARN+1)); }
err()  { echo "[readiness][FAIL] $*"; FAIL=$((FAIL+1)); }

echo "[readiness] Laravel version"
php artisan --version >/dev/null
ok "Laravel artisan dapat dijalankan"

echo "[readiness] PHP extension check"
if php -m | rg -qi '^pdo_mysql$'; then
  ok "pdo_mysql tersedia"
else
  err "pdo_mysql tidak tersedia"
fi

echo "[readiness] Lint critical backend files"
php -l app/Http/Controllers/ApiAuth.php >/dev/null && ok "ApiAuth.php valid"
php -l app/Http/Controllers/ApiPageBookmarkController.php >/dev/null && ok "ApiPageBookmarkController.php valid"
php -l routes/api.php >/dev/null && ok "routes/api.php valid"

echo "[readiness] Lint all migration files"
while IFS= read -r file; do
  php -l "$file" >/dev/null || { err "Syntax invalid: $file"; }
done < <(find database/migrations -maxdepth 1 -type f -name '*.php' | sort)
ok "Semua migration file lulus PHP lint"

echo "[readiness] Route checks"
V1_REFRESH_ROUTES="$(php artisan route:list --path=api/v1/refresh-token | rg -c 'api/v1/refresh-token' || true)"
LEGACY_REFRESH_ROUTES="$(php artisan route:list --path=api/refresh-token | rg -c 'api/refresh-token' || true)"
V1_PAGE_BOOKMARK_ROUTES="$(php artisan route:list --path=api/v1/page-bookmarks | rg -c 'api/v1/page-bookmarks' || true)"

if [[ "$V1_REFRESH_ROUTES" -ge 1 ]]; then
  ok "Route v1 refresh-token terdaftar"
else
  err "Route v1 refresh-token tidak ditemukan"
fi

if [[ "$LEGACY_REFRESH_ROUTES" -ge 1 ]]; then
  ok "Route legacy refresh-token terdaftar"
else
  warn "Route legacy refresh-token tidak ditemukan"
fi

if [[ "$V1_PAGE_BOOKMARK_ROUTES" -ge 3 ]]; then
  ok "Route page-bookmarks (GET/POST/DELETE) terdaftar"
else
  err "Route page-bookmarks belum lengkap"
fi

echo "[readiness] DB connectivity + migration status"
STATUS_OUT="$(mktemp)"
if php artisan migrate:status >"$STATUS_OUT" 2>&1; then
  ok "Koneksi DB OK dan migrate:status berhasil"

  PENDING_COUNT="$(rg -c '\|\s*No\s*\|' "$STATUS_OUT" || true)"
  if [[ "$PENDING_COUNT" -gt 0 ]]; then
    warn "Masih ada $PENDING_COUNT migration pending"
    if [[ "$APPLY" -eq 1 ]]; then
      echo "[readiness] Menjalankan migrate --force"
      if php artisan migrate --force; then
        ok "Migrasi berhasil dieksekusi"
      else
        err "Migrasi gagal saat --apply"
      fi
    else
      warn "Jalankan dengan --apply atau jalankan manual: php artisan migrate --force"
    fi
  else
    ok "Tidak ada migration pending"
  fi
else
  DB_HOST="$(rg '^DB_HOST=' .env | cut -d'=' -f2- || true)"
  DB_PORT="$(rg '^DB_PORT=' .env | cut -d'=' -f2- || true)"
  DB_NAME="$(rg '^DB_DATABASE=' .env | cut -d'=' -f2- || true)"

  warn "DB belum bisa diakses (migrate:status gagal)"
  warn "Current env DB => host=$DB_HOST port=$DB_PORT database=$DB_NAME"
  warn "Pastikan service MySQL/MariaDB aktif dan kredensial .env valid"
  echo "--- migrate:status error ---"
  sed -n '1,80p' "$STATUS_OUT"
  echo "----------------------------"
fi

rm -f "$STATUS_OUT"

echo
printf '[readiness] Summary: PASS=%d WARN=%d FAIL=%d\n' "$PASS" "$WARN" "$FAIL"

if [[ "$FAIL" -gt 0 ]]; then
  exit 1
fi

if [[ "$WARN" -gt 0 ]]; then
  exit 2
fi

exit 0
