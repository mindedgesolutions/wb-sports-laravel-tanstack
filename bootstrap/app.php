<?php

use App\Http\Middleware\CookieToAuthHeader;
use App\Http\Middleware\PreventPassportTokenReplayMiddleware;
use App\Http\Middleware\RemovePoweredByMiddleware;
use App\Http\Middleware\StripCookieMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(HandleCors::class);
        $middleware->append(RemovePoweredByMiddleware::class);
        $middleware->append(StripCookieMiddleware::class);
        $middleware->api(prepend: CookieToAuthHeader::class);
        $middleware->alias([
            'cookie.auth' => CookieToAuthHeader::class,
            'prevent.replay' => PreventPassportTokenReplayMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
