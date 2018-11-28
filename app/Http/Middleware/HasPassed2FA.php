<?php

namespace App\Http\Middleware;

use Closure;

class HasPassed2FA
{
    /**
     * This middleware will be applied to all routes, except the ones below
     *
     * @var array
     */
    protected $except = [
        'user.2fa.show.token.form',
        'user.logout',
        'user.2fa.one-touch-request.create',
        'user.2fa.one-touch-request.check',
        'user.2fa.token.sms',
        'user.2fa.token.voice',
        'user.2fa.token.verify',
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

        if ( ! in_array(\Route::currentRouteName(), $this->except)
             && optional(auth()->user())->is_authy_enabled
             && session('authy_status') != 'approved') {
            return redirect()->route('user.2fa.show.token.form');
        }

        if ($this->hasPassed2FA() && \Route::currentRouteName() == 'user.2fa.show.token.form') {
            return redirect()->to('/');
        }

        return $next($request);

    }

    private function hasPassed2FA()
    {
        return session('authy_status') == 'approved';
    }
}
