<?php

namespace App\Http\Middleware;

use App\Entities\Invite;
use Closure;

class CheckOnboardingInvite
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
        //Salespeople don't need an invite, as they're creating practices for others
        if (auth()->user()) {
            if (auth()->user()->hasPermission('use-onboarding')) {
                return $next($request);
            }
        }

        //Otherwise, a code is required
        $code = $request->route('code');

        if (!$code) {
            abort(403);
        }

        $invite = Invite::whereCode($code)
            ->exists();

        if (!$invite) {
            abort(403);
        }

        return $next($request);
    }
}
