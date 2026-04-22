<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RequestTraceMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);
        $requestId = $this->resolveRequestId($request);

        Log::withContext([
            'request_id' => $requestId,
            'method' => $request->method(),
            'path' => $request->path(),
            'user_id' => optional($request->user())->id,
            'ip' => $request->ip(),
        ]);

        try {
            $response = $next($request);
        } catch (Throwable $e) {
            $this->logRequest($request, 500, (int) round((microtime(true) - $startedAt) * 1000), $requestId, $e->getMessage());
            throw $e;
        }

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $status = $response->getStatusCode();
        $response->headers->set('X-Request-Id', $requestId);

        $this->logRequest($request, $status, $durationMs, $requestId);

        return $response;
    }

    private function resolveRequestId(Request $request): string
    {
        $fromHeader = trim((string) $request->headers->get('X-Request-Id', ''));
        if ($fromHeader !== '' && strlen($fromHeader) <= 120) {
            return $fromHeader;
        }

        return (string) Str::uuid();
    }

    private function logRequest(
        Request $request,
        int $status,
        int $durationMs,
        string $requestId,
        ?string $errorMessage = null
    ): void {
        if (!$request->is('api/*')) {
            return;
        }

        $isSlowRequest = $durationMs >= (int) env('OBS_SLOW_REQUEST_MS', 1200);
        $forceSuccessLogs = (bool) env('OBS_LOG_SUCCESS', false);

        if ($status < 400 && !$isSlowRequest && !$forceSuccessLogs) {
            return;
        }

        $context = [
            'request_id' => $requestId,
            'method' => $request->method(),
            'path' => '/' . ltrim($request->path(), '/'),
            'status' => $status,
            'duration_ms' => $durationMs,
            'user_id' => optional($request->user())->id,
        ];

        if ($errorMessage !== null) {
            $context['error'] = $errorMessage;
        }

        if ($status >= 500) {
            Log::channel('observability')->error('API request failed', $context);
            return;
        }

        if ($status >= 400) {
            Log::channel('observability')->warning('API request warning', $context);
            return;
        }

        Log::channel('observability')->info('API slow request', $context);
    }
}
