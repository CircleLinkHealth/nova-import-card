<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Middleware;

use Closure;

class CheckPatientUserData
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ( ! auth()->user()->hasRole('participant')) {
            auth()->logout();

            return redirect('login')->withErrors(['This page can be accessed only by patients.']);
        }

        if ( ! auth()->user()->carePlan) {
            auth()->logout();

            return redirect('login')->withErrors(['Care Plan does not exist. Please contact CircleLink Health Support.']);
        }

        return $next($request);
    }
}
