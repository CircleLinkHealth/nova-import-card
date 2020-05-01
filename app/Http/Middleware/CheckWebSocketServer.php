<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Client;

class CheckWebSocketServer
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
        if ( ! cache()->get('ws:server:working')) {
            try {
                $client = new Client();
                $url    = config('services.ws.server-url').'/';
                $res    = $client->get($url);

                $status = $res->getStatusCode();
                $body   = $res->getBody();
                if (200 == $status) {
                    cache()->put('ws:server:working', true, 300);
                } else {
                    cache()->forget('ws:server:working');
                }
            } catch (\Exception $ex) {
                cache()->forget('ws:server:working');
            }
        }

        view()->share('useOldTimeTracker', $request->has('useOldTimeTracker') ?? ! cache()->get('ws:server:working'));

        return $next($request);
    }
}
