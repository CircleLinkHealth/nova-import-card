<?php namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Auth;

class patientProgramSecurity {

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
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		// admins can see and do all
		if ( !Auth::guest() && Auth::user()->hasRole(['administrator']) && app()->environment() != 'production') {
			return $next($request);
		}

		if(!Auth::user()) {
			return redirect()->guest('login');
		}

		if($request->route()->patientId) {
			// viewing a specific patient, get patients program_id
			$user = User::find($request->route()->patientId);
			if(!$user) {
				return response('Could not locate patient.', 401);
			} else {
				// security
				if($user->ID == Auth::user()->ID && !Auth::user()->can('users-view-self')) {
					abort(403);
				}
				if($user->ID != Auth::user()->ID && !Auth::user()->can('users-view-all')) {
					abort(403);
				}
				if(!in_array($user->ID, Auth::user()->viewablePatientIds())) {
					abort(403);
				}
			}
		} else {

		}

		return $next($request);
	}

}
