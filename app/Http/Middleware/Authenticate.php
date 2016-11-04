<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

class Authenticate {

	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * Create a new filter instance.
	 *
	 * @param  Guard  $auth
	 * @return void
	 */
	public function __construct(Guard $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if ($this->auth->guest())
		{
			if ($request->ajax())
			{
				return response('Unauthorized.', 401);
			}
			else
			{
                return redirect()->route('login');
			}
		}

        // ensure user->access_disabled and user->status are passable
        if(!$this->auth->guest()) {
            if (auth()->user()) {
                if(auth()->user()->status == 'Inactive' || auth()->user()->access_disabled == 1) {
                    auth()->logout();
                    session()->flush();
                    return redirect()->route('login', [])->withErrors(['Account access disabled'])->send();
                }
            }
        }

		return $next($request);
	}

}
