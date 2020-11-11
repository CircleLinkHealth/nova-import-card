<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Middleware\ACL;

use CircleLinkHealth\Customer\Entities\Practice;
use Closure;
use Illuminate\Support\Facades\Route;

class ProviderDashboardACL
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed                    $role
     *
     * @return mixed
     */
    public function handle(
        $request,
        Closure $next,
        $role
    ) {
        //CLH Admins can see everything
        if (auth()->user()->hasRole(['administrator', 'saas-admin', 'saas-admin-view-only'])) {
            return $next($request);
        }

        $practiceSlug = Route::current()->parameter('practiceSlug');
        $practice     = Practice::whereName($practiceSlug)->first();

        $practice = auth()->user()->practice($practiceSlug);

        if ( ! $practice) {
            abort(404, 'This Practice does not exist.');
        }

        if ($practice->pivot->has_admin_rights
            || auth()->user()->id == $practice->user_id
        ) {
            return $next($request);
        }

        abort(403, 'You do not have access to this page.');
    }
}
