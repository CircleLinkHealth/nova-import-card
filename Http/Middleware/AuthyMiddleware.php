<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\TwoFA\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthyMiddleware
{
    public const SESSION_REDIRECT_KEY = 'url.2fa.success';

    /**
     * This middleware will be applied to all routes, except the ones below.
     *
     * @var array
     */
    protected $except = [
        'user.2fa.one-touch-request.create',
        'user.2fa.one-touch-request.check',
        'user.2fa.show.token.form',
        'user.2fa.store',
        'user.2fa.token.sms',
        'user.2fa.token.voice',
        'user.2fa.token.qr-code',
        'user.2fa.token.verify',
        'user.inactivity-logout',
        'user.logout',
        'user.settings.manage',
    ];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ( ! (bool) config('auth.two_fa_enabled')) {
            \Session::put('authy_status', 'approved');

            if (\Route::is('user.2fa.show.token.form')) {
                return redirect()->back();
            }

            return $next($request);
        }

        if (in_array(\Route::currentRouteName(), $this->except)) {
            return $next($request);
        }

        $user      = optional(auth()->user());
        $authyUser = optional($user->authyUser);

        if (isAllowedToSee2FA() && ! $authyUser->is_authy_enabled && ! \Route::is('user.settings.manage')) {
            $this->storeRedirect($request);

            return redirect()->route('user.settings.manage');
        }

        if ( ! isAllowedToSee2FA() || ! $authyUser->is_authy_enabled) {
            if (\Route::is('user.2fa.show.token.form')) {
                return redirect()->back();
            }

            return $next($request);
        }

        if ( ! $this->hasPassed2FA()) {
            $this->storeRedirect($request);

            return redirect()->route('user.2fa.show.token.form');
        }

        if (\Route::is('user.2fa.show.token.form')) {
            return redirect()->back();
        }

        $this->clearRedirect($request);

        return $next($request);
    }

    private function clearRedirect(Request $request)
    {
        $request->session()->remove(self::SESSION_REDIRECT_KEY);
    }

    private function hasPassed2FA()
    {
        return 'approved' == session('authy_status');
    }

    private function storeRedirect(Request $request)
    {
        $session = $request->session();
        if ($session->has(self::SESSION_REDIRECT_KEY)) {
            return;
        }
        $session->put(self::SESSION_REDIRECT_KEY, $request->url());
    }
}
