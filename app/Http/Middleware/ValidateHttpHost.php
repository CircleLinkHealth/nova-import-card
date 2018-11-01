<?php

namespace App\Http\Middleware;

use Closure;

class ValidateHttpHost
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
        $actualHost = env('SESSION_DOMAIN');

        $requestHost = $request->getHttpHost();

        if (!in_array($requestHost, [$actualHost, "www.$actualHost"])) {
            abort(404, "Suspicious host.");
        }

        return $next($request);
    }
}
