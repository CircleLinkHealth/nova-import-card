<?php

namespace App\Http\Middleware;

use Closure;

class LogoutIfAccessDisabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle(
        $request,
        Closure $next
    ) {
        if (auth()->check()) {
            if (auth()->user()->status == 'Inactive' || auth()->user()->access_disabled == 1) {
                auth()->logout();
                session()->flush();

                return redirect()->route('login', [])
                    ->withErrors(['Account access disabled'])
                    ->send();
            }
        }

        return $next($request);
    }
}
