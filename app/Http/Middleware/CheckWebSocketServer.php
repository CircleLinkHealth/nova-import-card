<?php

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Client;

class CheckWebSocketServer
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
        if (!cache()->get('ws:server:working')) {
            try {
                $client = new Client();
                $url = env('WS_SERVER_URL') . '/';
                $res = $client->get($url);

                $status = $res->getStatusCode();
                $body = $res->getBody();
                if ($status == 200) {
                    cache()->put('ws:server:working', true, 5);
                }
                else {
                    cache()->forget('ws:server:working');
                }
            }
            catch (\Exception $ex) {
                cache()->forget('ws:server:working');
            }
        }

        view()->share('useOldTimeTracker', $request->has('useOldTimeTracker') ?? !cache()->get('ws:server:working'));
        return $next($request);
    }
}
