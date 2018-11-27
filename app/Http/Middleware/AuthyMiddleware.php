<?php

namespace App\Http\Middleware;

use Closure;

class AuthyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
//        if (app()->environment(['local'])) {
//            \Session::put('authy_verified', true);
//            return $next($request);
//        }

        if (\Route::currentRouteName() !== 'user.2fa.show.token.form' && optional(auth()->user())->is_authy_enabled && ! \Session::has('authy_verified')) {
            return redirect()->route('user.2fa.show.token.form');
        }

        return $next($request);

    }
}
