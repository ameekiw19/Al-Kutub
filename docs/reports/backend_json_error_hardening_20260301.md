# Backend JSON Error Hardening (Laravel) - 2026-03-01

## Tujuan
Mencegah error frontend seperti `Unexpected token '<'` saat request AJAX/admin dengan memastikan exception non-2xx tetap merespons JSON.

## Perubahan Implementasi
1. `app/Exceptions/Handler.php`
   - Tambah global renderer untuk request AJAX/API:
     - Deteksi request JSON via `api/*`, `expectsJson`, `wantsJson`, `ajax`, header `Accept`, `X-Requested-With`.
   - Standarisasi payload error:
     - `success: false`
     - `message`
     - `errors` untuk validation
     - `debug`, `exception` saat `APP_DEBUG=true`
   - Mapping status:
     - `401` unauthenticated
     - `403` forbidden
     - `404` not found/model not found
     - `405` method not allowed
     - `419` token mismatch
     - `429` too many requests
     - default `500`

2. `app/Http/Middleware/Authenticate.php`
   - Hapus pola `abort(response()->json(...))` di `redirectTo()`.
   - Kembalikan behavior standar Laravel:
     - request JSON/AJAX -> `null` (biar jadi `AuthenticationException` -> JSON via `Handler`)
     - request web biasa -> redirect `/login`.

3. `app/Http/Middleware/RoleMiddleware.php`
   - Konsistenkan deteksi request JSON lewat helper `expectsApiJson()`.
   - Response role/auth mismatch tetap JSON untuk AJAX/API.

4. Test baru:
   - `tests/Feature/WebAjaxJsonErrorContractTest.php`
   - Cakupan:
     - runtime exception (500) untuk AJAX -> JSON
     - method not allowed (405) untuk AJAX -> JSON
     - token mismatch (419) untuk AJAX -> JSON
     - akses admin update tanpa auth -> 401 JSON (bukan HTML redirect)

## Hasil Verifikasi
1. `php artisan test tests/Feature/WebAjaxJsonErrorContractTest.php` -> PASS (4/4)
2. `php artisan test tests/Feature/ApiContractLockTest.php` -> PASS (2/2)
3. `php artisan test tests/Feature/SecurityHardeningTest.php` -> PASS (4/4)
4. `php -l` semua file yang diubah -> no syntax errors

## Dampak
1. Endpoint admin berbasis fetch/AJAX lebih tahan terhadap exception global.
2. Frontend tidak lagi tergantung asumsi response HTML pada error; kontrak JSON konsisten.
3. Regression safety meningkat karena ada feature test khusus kontrak error.
