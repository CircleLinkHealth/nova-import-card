<?php namespace App\Http\Middleware;

use App\WpUser;
use Closure;
use Illuminate\Contracts\Auth\Guard;

class programCheck {

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
		if($request->route()->patientId) {
			// viewing a specific patient, get patients program_id
			$user = WpUser::find($request->route()->patientId);
			if(!$user) {
				return response('Could not locate patient.', 401);
			} else {
				$request->session()->put('activeProgramId', $user->program_id);
			}
			// should validate here that viewing user has access to users program

		} else {
			if ($request->session()->has('activeProgramId')) {
				// good, program already set
			} else {
				if (!empty($this->auth->user()->program_id)) {
					$request->session()->put('activeProgramId', $this->auth->user()->program_id);
				}
			}
		}

		// in the end ensure theres a program set to view
		if (!$request->session()->has('activeProgramId')) {
			return response('Unauthorized program access.', 401);
		}

		// here we will enforce validation that restricts provider->program access

		return $next($request);
	}

}
