<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiContractLockTest extends TestCase
{
    public function test_openapi_documented_paths_exist_in_v1_routes(): void
    {
        $specPath = base_path('docs/openapi.yaml');
        $this->assertFileExists($specPath, 'openapi.yaml tidak ditemukan');

        $spec = (string) file_get_contents($specPath);
        preg_match_all('/^  (\/[A-Za-z0-9\/\{\}\-_]+):$/m', $spec, $matches);
        $documentedPaths = array_values(array_unique($matches[1] ?? []));

        $allUris = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route) => ltrim((string) $route->uri(), '/'))
            ->values()
            ->all();

        foreach ($documentedPaths as $path) {
            $expectedPattern = '#^api/v1' . preg_replace('/\{[^}]+\}/', '[^/]+', $path) . '$#';
            $hasMatch = false;

            foreach ($allUris as $uri) {
                if (preg_match($expectedPattern, $uri) === 1) {
                    $hasMatch = true;
                    break;
                }
            }

            $this->assertTrue($hasMatch, "Path OpenAPI belum punya route v1: {$path}");
        }
    }

    public function test_unversioned_api_routes_must_be_in_legacy_allowlist(): void
    {
        $allowlistPath = base_path('docs/api_legacy_allowlist.txt');
        $this->assertFileExists($allowlistPath, 'Allowlist route legacy tidak ditemukan');

        $allowedLegacyUris = array_filter(array_map(
            static fn ($line) => trim($line),
            file($allowlistPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: []
        ));

        $unversionedApiUris = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route) => ltrim((string) $route->uri(), '/'))
            ->filter(fn ($uri) => str_starts_with($uri, 'api/') && !str_starts_with($uri, 'api/v1/'))
            ->unique()
            ->values()
            ->all();

        $unexpected = array_values(array_diff($unversionedApiUris, $allowedLegacyUris));

        $this->assertSame(
            [],
            $unexpected,
            'Ditemukan route API tanpa versi yang tidak ada di allowlist legacy: ' . implode(', ', $unexpected)
        );
    }
}
