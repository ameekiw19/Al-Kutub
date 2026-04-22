<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (
            $request->expectsJson()
            || $request->wantsJson()
            || $request->ajax()
            || $request->is('api/*')
            || strpos(strtolower((string) $request->header('Accept', '')), 'application/json') !== false
            || strtolower((string) $request->header('X-Requested-With', '')) === 'xmlhttprequest'
        ) {
            return null;
        }

        return '/login';
    }

}
