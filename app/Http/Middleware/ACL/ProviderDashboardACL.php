<?php

namespace App\Http\Middleware\ACL;

use App\Practice;
use Closure;
use Illuminate\Support\Facades\Route;

class ProviderDashboardACL
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
        Closure $next,
        $role
    ) {
        $practiceSlug = Route::current()->getParameter('practiceSlug');
        $practice = Practice::whereName($practiceSlug)->first();


        //CLH Admins can see everything
        if (auth()->user()->hasRole('administrator')) {
            return $next($request);
        }


        $practice = auth()->user()->practice($practiceSlug);

        if (!$practice) {
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
