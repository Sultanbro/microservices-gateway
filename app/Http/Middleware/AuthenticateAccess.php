<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class AuthenticateAccess
{
    public function handle($request, Closure $next)
    {
        $authorization = $request->header('Authorization');
        if (!is_string($authorization) || $authorization === '') {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        foreach (config('services.gateway.allowed_secrets', []) as $secret) {
            if (is_string($secret) && $secret !== '' && hash_equals($secret, $authorization)) {
                return $next($request);
            }
        }

        abort(Response::HTTP_UNAUTHORIZED);
    }
}
