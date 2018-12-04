<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Middleware;

use Closure;
use Session;

class AprimaCcdApiAuthAdapter
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
        if ( ! $request->filled('access_token')) {
            return response()->json(['error' => 'Access token not found on the request.'], 400);
        }

        /*
         * This will add an Authorization header to Aprima's request. We do this because we already gave
         * them documentation for the separate API we had, and we don't wanna have them change their stuff.
         */
        $request->headers->set('Authorization', "Bearer {$request->input('access_token')}");

        $user = \JWTAuth::parseToken()->authenticate();

        if ( ! $user) {
            return response()->json(['error' => 'Invalid Token'], 400);
        }

        //We do flash because flash data only lives with every request.
        //We don't wanna authenticate the Aprima API user for security.
        Session::flash('apiUser', $user);

        return $next($request);
    }
}
