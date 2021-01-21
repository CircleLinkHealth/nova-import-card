<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Middleware;

use Closure;

class AddResponseOriginal
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        //Adding this line fixes the issue at
        //vendor/genealabs/laravel-caffeine/src/Http/Middleware/LaravelCaffeineDripMiddleware.php:51
        //where the package was looking for $response->original
        $response->original = optional($response)->original;

        return $response;
    }
}
