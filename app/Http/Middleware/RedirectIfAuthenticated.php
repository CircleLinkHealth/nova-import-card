<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {

            if (auth()->user()->hasRole('participant')) {
                //show a welcome message and ask patient to open AWV with the link provided
                return redirect()->route('home');
            } else {
                return redirect()->route('patient.list');
            }

        }
        return $next($request);
    }
}
