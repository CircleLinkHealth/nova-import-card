<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Middleware;

use Closure;

class LogoutIfAccessDisabled
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handle(
        $request,
        Closure $next
    ) {
        if (auth()->check()) {
            if ('Inactive' == auth()->user()->status || 1 == auth()->user()->access_disabled) {
                auth()->logout();
                session()->flush();

                return redirect()->route('login', [])
                    ->withErrors(['Account access disabled'])
                    ->send();
            }
        }

        return $next($request);
    }
}
