<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Middleware;

use App\Constants;
use App\User;
use Closure;

class AdminOrPracticeStaff
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
        /** @var User $loggedInUser */
        $loggedInUser = auth()->user();

        if ( ! $loggedInUser) {
            return redirect()->guest('login');
        }

        if ($loggedInUser->hasRole(Constants::PRACTICE_STAFF_ROLE_NAMES) || $loggedInUser->isAdmin()) {
            return $next($request);
        }

        abort(403);
    }
}
