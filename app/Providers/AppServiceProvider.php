<?php

namespace App\Providers;

use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
        // Response::macro('withoutCookies', function ($response) {
        //     $response->headers->remove('Set-Cookie');
        //     return $response;
        // });
        Passport::personalAccessTokensExpireIn(CarbonInterval::minutes(10));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));
    }
}
