<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RemovePoweredByMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
        $response->headers->remove('X-Server');
        $response->headers->remove('X-AspNet-Version');
        $response->headers->remove('X-AspNetMvc-Version');
        return $response;
    }
}
