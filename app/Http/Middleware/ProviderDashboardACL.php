<?php

namespace App\Http\Middleware;

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

        if (auth()->user()->hasRole($role)
            && in_array($practice->id, auth()->user()->practices->pluck('id')->all())
        ) {
            return $next($request);
        }

        if (auth()->user()->practice($practice)
        ) {
            return $next($request);
        }

        abort(403, 'You do not have access to this page.');
    }
}
