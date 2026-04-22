<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $e)
    {
        if ($this->shouldReturnJsonError($request)) {
            return $this->renderJsonError($request, $e);
        }

        return parent::render($request, $e);
    }

    private function shouldReturnJsonError(Request $request): bool
    {
        if ($request->is('api/*')) {
            return true;
        }

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return true;
        }

        $acceptHeader = strtolower((string) $request->header('Accept', ''));
        $xRequestedWith = strtolower((string) $request->header('X-Requested-With', ''));

        return strpos($acceptHeader, 'application/json') !== false
            || $xRequestedWith === 'xmlhttprequest';
    }

    private function renderJsonError(Request $request, Throwable $e)
    {
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal. Silakan periksa kembali input Anda.',
                'errors' => $e->errors(),
            ], 422);
        }

        $status = $this->resolveStatusCode($e);
        $payload = [
            'success' => false,
            'message' => $this->resolveMessage($e, $status),
        ];

        if (config('app.debug')) {
            $payload['debug'] = $e->getMessage();
            $payload['exception'] = class_basename($e);
        }

        return response()->json($payload, $status);
    }

    private function resolveStatusCode(Throwable $e): int
    {
        if ($e instanceof AuthenticationException) {
            return 401;
        }

        if ($e instanceof AuthorizationException) {
            return 403;
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return 404;
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return 405;
        }

        if ($e instanceof TokenMismatchException) {
            return 419;
        }

        if ($e instanceof TooManyRequestsHttpException) {
            return 429;
        }

        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode();
        }

        return 500;
    }

    private function resolveMessage(Throwable $e, int $status): string
    {
        switch ($status) {
            case 401:
                return 'Unauthenticated';
            case 403:
                return 'Forbidden';
            case 404:
                return 'Resource tidak ditemukan.';
            case 405:
                return 'Metode request tidak diizinkan.';
            case 419:
                return 'Sesi login berakhir. Silakan muat ulang halaman dan coba lagi.';
            case 429:
                return 'Permintaan terlalu sering. Silakan coba lagi beberapa saat.';
            default:
                return 'Terjadi kesalahan pada server.';
        }
    }
}
