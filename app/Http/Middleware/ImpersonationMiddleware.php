<?php namespace App\Http\Middleware;

use Closure;

class ImpersonationMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->isImpersonating()) {
            auth()->impersonate();
        }

        return $next($request);
    }
}
