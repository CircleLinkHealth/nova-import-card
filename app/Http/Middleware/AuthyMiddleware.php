<?php

namespace App\Http\Middleware;

use Closure;

class AuthyMiddleware
{
    /**
     * This middleware will be applied to all routes, except the ones below
     *
     * @var array
     */
    protected $except = [
        'user.2fa.show.token.form',
        'user.logout',
        'user.2fa.approval-request.create',
        'user.2fa.approval-request.check',
    ];

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

        if ( ! in_array(\Route::currentRouteName(),
                $this->except) && optional(auth()->user())->is_authy_enabled && ! \Session::has('authy_verified')) {
            return redirect()->route('user.2fa.show.token.form');
        }

        return $next($request);

    }
}
