#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="/home/amiir/AndroidStudioProjects"
BACKEND_DIR="$ROOT_DIR/al-kutub"
REPORT_DIR="$ROOT_DIR/docs/reports"
TIMESTAMP="$(date +%Y%m%d_%H%M%S)"
REPORT_PATH="$REPORT_DIR/main_demo_api_smoke_${TIMESTAMP}.md"
SERVER_LOG="/tmp/main_demo_api_smoke_server_${TIMESTAMP}.log"
SERVER_PID=""

BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"
API_BASE="${BASE_URL%/}/api/v1"
SMOKE_ADMIN_USERNAME="${SMOKE_ADMIN_USERNAME:-smoke_admin_demo}"
SMOKE_READER_USERNAME="${SMOKE_READER_USERNAME:-smoke_reader_demo}"
SMOKE_READER_PASSWORD="${SMOKE_READER_PASSWORD:-SmokePass123!}"
SMOKE_READER_EMAIL="${SMOKE_READER_EMAIL:-smoke_reader_demo@example.test}"
SMOKE_KITAB_TITLE="${SMOKE_KITAB_TITLE:-Smoke Demo Kitab Android}"

mkdir -p "$REPORT_DIR"

cleanup() {
  if [[ -n "$SERVER_PID" ]] && kill -0 "$SERVER_PID" >/dev/null 2>&1; then
    kill "$SERVER_PID" >/dev/null 2>&1 || true
    wait "$SERVER_PID" 2>/dev/null || true
  fi
}
trap cleanup EXIT

log() {
  printf '[main-demo-api-smoke] %s\n' "$*"
}

require_command() {
  if ! command -v "$1" >/dev/null 2>&1; then
    echo "Perintah wajib tidak ditemukan: $1" >&2
    exit 1
  fi
}

json_get() {
  local file="$1"
  local expression="$2"
  php -r '
$data = json_decode(file_get_contents($argv[1]), true);
if (!is_array($data)) {
    fwrite(STDERR, "JSON tidak valid: " . $argv[1] . PHP_EOL);
    exit(2);
}
$expr = $argv[2];
$segments = array_values(array_filter(explode(".", $expr), static fn ($part) => $part !== ""));
$cursor = $data;
foreach ($segments as $segment) {
    if (preg_match("/^([^\[]+)\[(\d+)\]$/", $segment, $matches)) {
        $key = $matches[1];
        $index = (int) $matches[2];
        if (!is_array($cursor) || !array_key_exists($key, $cursor) || !is_array($cursor[$key]) || !array_key_exists($index, $cursor[$key])) {
            exit(3);
        }
        $cursor = $cursor[$key][$index];
        continue;
    }
    if (!is_array($cursor) || !array_key_exists($segment, $cursor)) {
        exit(3);
    }
    $cursor = $cursor[$segment];
}
if (is_array($cursor)) {
    echo json_encode($cursor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} elseif (is_bool($cursor)) {
    echo $cursor ? "true" : "false";
} elseif ($cursor === null) {
    echo "";
} else {
    echo (string) $cursor;
}
' "$file" "$expression"
}

assert_json_equals() {
  local file="$1"
  local expression="$2"
  local expected="$3"
  local actual
  if ! actual="$(json_get "$file" "$expression")"; then
    echo "Gagal membaca JSON path '$expression' dari $file" >&2
    cat "$file" >&2 || true
    exit 1
  fi
  if [[ "$actual" != "$expected" ]]; then
    echo "Asersi gagal untuk '$expression': expected '$expected', actual '$actual'" >&2
    cat "$file" >&2 || true
    exit 1
  fi
}

assert_json_nonempty() {
  local file="$1"
  local expression="$2"
  local actual
  if ! actual="$(json_get "$file" "$expression")"; then
    echo "Gagal membaca JSON path '$expression' dari $file" >&2
    cat "$file" >&2 || true
    exit 1
  fi
  if [[ -z "$actual" ]]; then
    echo "Asersi non-empty gagal untuk '$expression'" >&2
    cat "$file" >&2 || true
    exit 1
  fi
}

wait_for_http() {
  local url="$1"
  local attempts="${2:-20}"
  local delay_seconds="${3:-1}"
  for ((i = 1; i <= attempts; i++)); do
    if curl -fsS "$url" >/dev/null 2>&1; then
      return 0
    fi
    sleep "$delay_seconds"
  done
  return 1
}

require_command php
require_command curl

if [[ ! -d "$BACKEND_DIR" ]]; then
  echo "Backend directory tidak ditemukan: $BACKEND_DIR" >&2
  exit 1
fi

sample_pdf="$(find "$BACKEND_DIR/public/pdf" -maxdepth 1 -type f -iname '*.pdf' | head -n 1)"
sample_cover="$(find "$BACKEND_DIR/public/cover" -maxdepth 1 -type f ! -name 'placeholder_*' \( -iname '*.jpg' -o -iname '*.jpeg' -o -iname '*.png' -o -iname '*.webp' \) | head -n 1)"

if [[ -z "$sample_pdf" || -z "$sample_cover" ]]; then
  echo "Sample PDF atau cover tidak ditemukan di public assets." >&2
  exit 1
fi

if ! wait_for_http "$API_BASE/kitab?limit=1" 1 1; then
  log "Backend lokal belum aktif, menjalankan php artisan serve di $BASE_URL"
  (
    cd "$BACKEND_DIR"
    php artisan serve --host=127.0.0.1 --port=8000 >"$SERVER_LOG" 2>&1
  ) &
  SERVER_PID=$!

  if ! wait_for_http "$API_BASE/kitab?limit=1" 25 1; then
    echo "Backend lokal gagal aktif. Lihat log: $SERVER_LOG" >&2
    exit 1
  fi
fi

SMOKE_PDF_BASENAME="$(basename "$sample_pdf")"
SMOKE_COVER_BASENAME="$(basename "$sample_cover")"

setup_json="$(mktemp)"
login_json="$(mktemp)"
search_json="$(mktemp)"
detail_json="$(mktemp)"
download_file="$(mktemp --suffix=.pdf)"
history_store_json="$(mktemp)"
history_list_json="$(mktemp)"
bookmark_json="$(mktemp)"
bookmark_list_json="$(mktemp)"
note_store_json="$(mktemp)"
note_list_json="$(mktemp)"

log "Menyiapkan data smoke demo"
(
  cd "$BACKEND_DIR"
  SMOKE_PDF_BASENAME="$SMOKE_PDF_BASENAME" \
  SMOKE_COVER_BASENAME="$SMOKE_COVER_BASENAME" \
  SMOKE_ADMIN_USERNAME="$SMOKE_ADMIN_USERNAME" \
  SMOKE_READER_USERNAME="$SMOKE_READER_USERNAME" \
  SMOKE_READER_PASSWORD="$SMOKE_READER_PASSWORD" \
  SMOKE_READER_EMAIL="$SMOKE_READER_EMAIL" \
  SMOKE_KITAB_TITLE="$SMOKE_KITAB_TITLE" \
  php artisan tinker --execute='
use App\Models\Bookmark;
use App\Models\History;
use App\Models\Kitab;
use App\Models\ReadingNote;
use App\Models\User;
use App\Services\KitabPublicationService;
use Illuminate\Support\Facades\Hash;

$admin = User::updateOrCreate(
    ["username" => getenv("SMOKE_ADMIN_USERNAME")],
    [
        "email" => getenv("SMOKE_ADMIN_USERNAME") . "@example.test",
        "password" => Hash::make("SmokeAdmin123!"),
        "role" => "admin",
        "phone" => "620000000001",
        "deskripsi" => "Smoke admin demo",
        "is_verified_by_admin" => true,
        "admin_verified_at" => now(),
        "email_verified_at" => now(),
    ]
);

$reader = User::updateOrCreate(
    ["username" => getenv("SMOKE_READER_USERNAME")],
    [
        "email" => getenv("SMOKE_READER_EMAIL"),
        "password" => Hash::make(getenv("SMOKE_READER_PASSWORD")),
        "role" => "user",
        "phone" => "620000000002",
        "deskripsi" => "Smoke reader demo",
        "is_verified_by_admin" => true,
        "admin_verified_at" => now(),
        "admin_verified_by" => $admin->id,
        "email_verified_at" => now(),
    ]
);

Bookmark::where("user_id", $reader->id)->delete();
History::where("user_id", $reader->id)->delete();
ReadingNote::where("user_id", $reader->id)->delete();

$service = app(KitabPublicationService::class);
$payload = [
    "judul" => getenv("SMOKE_KITAB_TITLE"),
    "penulis" => "Penguji Smoke Test",
    "deskripsi" => "Kitab khusus smoke test untuk memverifikasi alur demo Android dari pencarian sampai penyimpanan progres baca.",
    "kategori" => "Ushul Fiqih",
    "bahasa" => "Indonesia",
    "file_pdf" => getenv("SMOKE_PDF_BASENAME"),
    "cover" => getenv("SMOKE_COVER_BASENAME"),
    "views" => 17,
    "downloads" => 9,
    "viewed_by" => [],
];

$kitab = Kitab::where("judul", getenv("SMOKE_KITAB_TITLE"))->first();
if (!$kitab) {
    $kitab = $service->createDraft($payload, $admin->id, "Smoke demo setup");
} else {
    if ($kitab->publication_status === "published") {
        $kitab = $service->returnToDraft($kitab, $admin->id, "Reset smoke demo");
    } elseif ($kitab->publication_status === "review") {
        $kitab = $service->returnToDraft($kitab, $admin->id, "Reset smoke demo");
    }
    $kitab = $service->updateWithRevision($kitab, $payload, $admin->id, "Refresh smoke demo");
}

if ($kitab->publication_status === "draft") {
    $kitab = $service->submitForReview($kitab, $admin->id, "Smoke demo submit");
}
if ($kitab->publication_status === "review") {
    $kitab = $service->publish($kitab, $admin->id, "Smoke demo publish");
}

echo json_encode([
    "admin_username" => $admin->username,
    "reader_username" => $reader->username,
    "reader_password" => getenv("SMOKE_READER_PASSWORD"),
    "kitab_id" => $kitab->id_kitab,
    "kitab_title" => $kitab->judul,
    "kitab_status" => $kitab->publication_status,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
'
) >"$setup_json"

assert_json_equals "$setup_json" "kitab_status" "published"
kitab_id="$(json_get "$setup_json" "kitab_id")"

log "Login smoke user"
curl -fsS "$API_BASE/login" \
  -H "Accept: application/json" \
  -X POST \
  --data-urlencode "username=$SMOKE_READER_USERNAME" \
  --data-urlencode "password=$SMOKE_READER_PASSWORD" \
  >"$login_json"

assert_json_equals "$login_json" "success" "true"
token="$(json_get "$login_json" "data.token")"
assert_json_nonempty "$login_json" "data.token"

auth_header="Authorization: Bearer $token"

log "Verifikasi search"
curl -fsS "$API_BASE/kitab/search?search=$(php -r 'echo rawurlencode($argv[1]);' "$SMOKE_KITAB_TITLE")&limit=10" \
  -H "Accept: application/json" \
  >"$search_json"

assert_json_equals "$search_json" "success" "true"
assert_json_equals "$search_json" "data[0].idKitab" "$kitab_id"

log "Verifikasi detail kitab"
curl -fsS "$API_BASE/kitab/$kitab_id" \
  -H "Accept: application/json" \
  >"$detail_json"

assert_json_equals "$detail_json" "success" "true"

log "Simulasi buka dan baca PDF"
curl -fsS "$API_BASE/kitab/$kitab_id/download" \
  -H "Accept: application/json" \
  -H "$auth_header" \
  -o "$download_file"

if [[ ! -s "$download_file" ]]; then
  echo "File PDF hasil download kosong." >&2
  exit 1
fi

curl -fsS "$API_BASE/history" \
  -H "Accept: application/json" \
  -H "$auth_header" \
  -X POST \
  --data-urlencode "kitab_id=$kitab_id" \
  --data-urlencode "current_page=7" \
  --data-urlencode "total_pages=21" \
  --data-urlencode "last_position=page_7" \
  --data-urlencode "reading_time_minutes=12" \
  >"$history_store_json"

assert_json_equals "$history_store_json" "success" "true"

curl -fsS "$API_BASE/history" \
  -H "Accept: application/json" \
  -H "$auth_header" \
  >"$history_list_json"

assert_json_equals "$history_list_json" "success" "true"
assert_json_equals "$history_list_json" "data.raw_histories[0].kitab.idKitab" "$kitab_id"

log "Verifikasi bookmark"
curl -fsS "$API_BASE/bookmarks/$kitab_id/toggle" \
  -H "Accept: application/json" \
  -H "$auth_header" \
  -X POST \
  >"$bookmark_json"

assert_json_equals "$bookmark_json" "status" "success"
assert_json_equals "$bookmark_json" "is_bookmarked" "true"

curl -fsS "$API_BASE/bookmarks" \
  -H "Accept: application/json" \
  -H "$auth_header" \
  >"$bookmark_list_json"

assert_json_equals "$bookmark_list_json" "status" "success"
assert_json_equals "$bookmark_list_json" "data[0].kitab.idKitab" "$kitab_id"

log "Verifikasi reading note"
curl -fsS "$API_BASE/reading-notes" \
  -H "Accept: application/json" \
  -H "$auth_header" \
  -X POST \
  --data-urlencode "kitab_id=$kitab_id" \
  --data-urlencode "note_content=Smoke note untuk verifikasi presentasi." \
  --data-urlencode "page_number=7" \
  --data-urlencode "highlighted_text=Smoke highlight halaman 7" \
  --data-urlencode "note_color=#FFF176" \
  --data-urlencode "is_private=1" \
  --data-urlencode "client_request_id=main-demo-smoke-$TIMESTAMP" \
  >"$note_store_json"

assert_json_equals "$note_store_json" "success" "true"

curl -fsS "$API_BASE/reading-notes?kitab_id=$kitab_id" \
  -H "Accept: application/json" \
  -H "$auth_header" \
  >"$note_list_json"

assert_json_equals "$note_list_json" "success" "true"
assert_json_equals "$note_list_json" "data.data[0].kitab_id" "$kitab_id"

download_bytes="$(wc -c <"$download_file" | tr -d ' ')"
history_count="$(json_get "$history_list_json" "data.total")"
bookmark_count="$(json_get "$bookmark_list_json" "total")"
note_count="$(json_get "$note_list_json" "data.total")"

cat >"$REPORT_PATH" <<EOF
# Main Demo API Smoke Report

- Tanggal: $(date '+%Y-%m-%d %H:%M:%S %Z')
- Backend: $BASE_URL
- User smoke: \`$SMOKE_READER_USERNAME\`
- Kitab smoke: \`$SMOKE_KITAB_TITLE\` (ID: \`$kitab_id\`)

## Hasil
- Admin flow berhasil menyiapkan dan mempublikasikan kitab smoke via \`KitabPublicationService\`.
- Search API menemukan kitab smoke pada hasil teratas.
- Detail kitab dapat diambil melalui endpoint publik.
- Endpoint download mengembalikan file PDF non-kosong (\`${download_bytes}\` bytes).
- History tersimpan untuk kitab smoke dan muncul di daftar riwayat user.
- Bookmark tersimpan dan muncul di daftar bookmark user.
- Reading note tersimpan dan muncul di daftar reading notes user.

## Ringkasan Angka
- History total: \`$history_count\`
- Bookmark total: \`$bookmark_count\`
- Reading notes total: \`$note_count\`

## Artefak
- Setup JSON: \`$setup_json\`
- Login JSON: \`$login_json\`
- Search JSON: \`$search_json\`
- Detail JSON: \`$detail_json\`
- History store JSON: \`$history_store_json\`
- History list JSON: \`$history_list_json\`
- Bookmark JSON: \`$bookmark_json\`
- Bookmark list JSON: \`$bookmark_list_json\`
- Reading note store JSON: \`$note_store_json\`
- Reading note list JSON: \`$note_list_json\`
- Downloaded PDF: \`$download_file\`
EOF

log "Smoke test selesai. Report: $REPORT_PATH"
