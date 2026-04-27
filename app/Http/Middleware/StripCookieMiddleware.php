<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StripCookieMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // $response = $next($request);
        // $response->headers->remove('Set-Cookie');
        // return $response;

        $response = $next($request);

        $allowed = ['refresh_sports', 'refresh_services'];

        $cookies = $response->headers->getCookies();
        foreach ($cookies as $cookie) {
            if (!in_array($cookie->getName(), $allowed, true)) {
                $response->headers->removeCookie($cookie->getName());
            }
        }
        return $response;
    }
}
