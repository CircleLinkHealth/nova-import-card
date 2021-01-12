<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Support\Facades\Hash;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        /*
        if ($this->auth->guard($guard)->guest()) {
            return response('Unauthorized.', 401);
        }
        */

        $token = $request->input('cpm-token', null);
        if (empty($token) || !Hash::check($this->getTokenString(), $token)) {
            return response('Unauthorized.', 401);
        }

        return $next($request);
    }

    private function getTokenString() {
        return config('app.key') . Carbon::today()->toDateString();
    }
}
