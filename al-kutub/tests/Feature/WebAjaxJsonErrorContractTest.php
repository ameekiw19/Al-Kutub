<?php

namespace Tests\Feature;

use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class WebAjaxJsonErrorContractTest extends TestCase
{
    private function ajaxHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ];
    }

    public function test_ajax_runtime_exception_returns_json_contract(): void
    {
        Route::middleware('web')->get('/_test/ajax-boom', function () {
            throw new RuntimeException('Boom');
        });

        $response = $this->withHeaders($this->ajaxHeaders())->get('/_test/ajax-boom');

        $response
            ->assertStatus(500)
            ->assertJsonStructure(['success', 'message'])
            ->assertJson(['success' => false]);
    }

    public function test_ajax_method_not_allowed_returns_json_contract(): void
    {
        Route::middleware('web')->post('/_test/post-only', function () {
            return response()->json(['ok' => true]);
        });

        $response = $this->withHeaders($this->ajaxHeaders())->get('/_test/post-only');

        $response
            ->assertStatus(405)
            ->assertJsonStructure(['success', 'message'])
            ->assertJson([
                'success' => false,
                'message' => 'Metode request tidak diizinkan.',
            ]);
    }

    public function test_ajax_token_mismatch_exception_returns_json_contract(): void
    {
        Route::middleware('web')->get('/_test/token-mismatch', function () {
            throw new TokenMismatchException('CSRF token mismatch');
        });

        $response = $this->withHeaders($this->ajaxHeaders())->get('/_test/token-mismatch');

        $response
            ->assertStatus(419)
            ->assertJsonStructure(['success', 'message'])
            ->assertJson([
                'success' => false,
                'message' => 'Sesi login berakhir. Silakan muat ulang halaman dan coba lagi.',
            ]);
    }

    public function test_ajax_admin_update_without_auth_returns_json_not_html_redirect(): void
    {
        $response = $this->withHeaders($this->ajaxHeaders())
            ->post('/admin/updatekitab/999999', []);

        $response
            ->assertStatus(401)
            ->assertJsonStructure(['message']);
    }
}

