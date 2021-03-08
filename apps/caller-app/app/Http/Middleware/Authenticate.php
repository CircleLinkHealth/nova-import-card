<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Hash;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  string[]                 ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        /*
        if ($this->auth->guard($guard)->guest()) {
            return response('Unauthorized.', 401);
        }
        */

        $token = $request->input('cpm-token', null);
        if (empty($token) || ! Hash::check($this->getTokenString(), $token)) {
            return response('Unauthorized.', 401);
        }

        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if ( ! $request->expectsJson()) {
            return route('login');
        }
    }

    private function getTokenString()
    {
        return config('app.key').Carbon::today()->toDateString();
    }
}
