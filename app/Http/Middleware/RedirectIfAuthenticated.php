<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Middleware;

use Closure;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param string|null              $guard
     *
     * @return mixed
     */
    public function handle(
        $request,
        Closure $next,
        $guard = null
    ) {
        return $next($request);
    }
}
