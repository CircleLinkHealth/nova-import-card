<?php
/**
 * Created by IntelliJ IDEA.
 * User: pangratioscosma
 * Date: 26/12/2018
 * Time: 6:15 PM
 */

namespace App\Http\Middleware;

use Closure;


class DisableHttpOptions
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->getMethod() === "OPTIONS") {
            return response('', 405);
        }

        return $next($request);
    }
}