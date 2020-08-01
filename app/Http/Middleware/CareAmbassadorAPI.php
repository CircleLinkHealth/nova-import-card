<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Middleware;

use Closure;

class CareAmbassadorAPI
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
        if ( ! auth()->user()->isCareAmbassador(true) || ! auth()->user()->careAmbassador) {
            return response()->json(['message' => 'Only Care Ambassadors can access these endpoints.'], 403);
        }

        return $next($request);
    }
}
