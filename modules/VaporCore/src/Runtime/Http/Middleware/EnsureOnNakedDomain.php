<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Laravel\Vapor\Runtime\Http\Middleware;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class EnsureOnNakedDomain
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  callable                 $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        if ('https://'.$request->getHttpHost() === $_ENV['APP_VANITY_URL']) {
            return $next($request);
        }

        if (true === config('vapor.redirect_to_root') &&
            0 === strpos($request->getHost(), 'www.')) {
            return new RedirectResponse(Str::replaceFirst(
                'www.',
                '',
                $request->fullUrl()
            ), 301);
        }

        if (false === config('vapor.redirect_to_root') &&
            false === strpos($request->getHost(), 'www.')) {
            return new RedirectResponse(str_replace(
                $request->getScheme().'://',
                $request->getScheme().'://www.',
                $request->fullUrl()
            ), 301);
        }

        return $next($request);
    }
}
