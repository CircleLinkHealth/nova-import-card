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
        'user.inactivity-logout',
        'user.logout',
        'user.2fa.one-touch-request.create',
        'user.2fa.one-touch-request.check',
        'user.2fa.token.sms',
        'user.2fa.token.voice',
        'user.2fa.token.verify',
        'user.settings.manage',
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
//            \Session::put('authy_status', 'approved');
//            return $next($request);
//        }

        $user         = optional(auth()->user());
        $currentRoute = \Route::currentRouteName();

        if (in_array(\Route::currentRouteName(), $this->except)) {
            return $next($request);
        }

        if ($user->hasRole('administrator') && ! $user->is_authy_enabled && $currentRoute != 'user.settings.manage') {
            return redirect()->route('user.settings.manage');
        }

        if ( ! isAllowedToSee2FA() || ! $user->is_authy_enabled) {
            if ($currentRoute == 'user.2fa.show.token.form') {
                return redirect()->back();
            }

            return $next($request);
        }

        if (session('authy_status') != 'approved') {
            return redirect()->route('user.2fa.show.token.form');
        }

        if ($this->hasPassed2FA() && \Route::currentRouteName() == 'user.2fa.show.token.form') {
            return redirect()->back();
        }

        return $next($request);

    }

    private function hasPassed2FA()
    {
        return session('authy_status') == 'approved';
    }
}
