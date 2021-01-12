<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param Request $request
     *
     * @return string
     */
    protected function redirectTo($request)
    {
        if ( ! $request->expectsJson()) {
            Log::debug('Authenticate Middleware -> route login');

            $redirectTo  = $this->getRedirectPath($request);
            $redirectUrl = route('login', $request->query->all());

            return $redirectUrl.( ! empty($redirectTo)
                    ? "?redirectTo=$redirectTo"
                    : '');
        }
    }

    private function getRedirectPath(Request $request)
    {
        $path = $request->path();
        if ('/' === $path) {
            return;
        }

        return urlencode($request->path());
    }
}
