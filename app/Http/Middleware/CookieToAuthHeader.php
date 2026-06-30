<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CookieToAuthHeader
{
    public function handle(Request $request, Closure $next, string $system)
    {
        $token = $request->cookie("access_{$system}");

        if ($token) {
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        return $next($request);
    }
}
