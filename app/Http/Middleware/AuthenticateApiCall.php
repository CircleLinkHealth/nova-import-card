<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

use App\ApiKey;

class AuthenticateApiCall {

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
		if ( $this->auth->guest() )
		{
			$appKey = $request->header('X-Authorization');

			if (ApiKey::checkKeyExists($appKey))
			{
				redirect()->intended();
			}
			else
			{
				return response('Unauthorized.', 401);
			}
		}

		return $next($request);
	}

}
