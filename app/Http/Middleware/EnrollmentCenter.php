<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Middleware;

use Closure;

class EnrollmentCenter
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
        if ( ! auth()->user()->careAmbassador || ! auth()->user()->isCareAmbassador(true)) {
            return view('errors.403', [
                'message'   => 'You need to be a Care Ambassador to acccess this page.',
                'hideLinks' => true,
            ]);
        }

        return $next($request);
    }
}
