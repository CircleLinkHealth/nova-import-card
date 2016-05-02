<?php namespace App\Http\Controllers\Auth;

use App\User;
use App\AppConfig;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use App\CLH\Traits\Auth\AuthenticatesAndRegistersUsers;
use DateTime;

class AuthController extends Controller {

	use AuthenticatesAndRegistersUsers;

	/**
	 * Create a new authentication controller instance.
	 *
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
	 * @return void
	 */
	public function __construct(Guard $auth, Registrar $registrar)
	{
		$this->auth = $auth;
		$this->registrar = $registrar;

		$this->middleware('guest', ['except' => 'getLogout']);

		if ( \Auth::check() ) {
			\Auth::logout();
				\Session::flush();
				return redirect()->route('login', [])->send();
			
		}
	}


	/**
	 * Show the application registration form.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getRegister()
	{
		if ( \Auth::check() ) {
			return view('auth.register');
		} else {
			return redirect()->back();
		}

	}


	/**
	 * Handle a registration request for the application.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function postRegister(Request $request)
	{
		if ( \Auth::check() ) {
			$validator = $this->registrar->validator($request->all());

			if ($validator->fails())
			{
				$this->throwValidationException(
					$request, $validator
				);
			}

			$this->auth->login($this->registrar->create($request->all()));

			return redirect($this->redirectPath());
		} else {
			return redirect()->back();
		}
	}

	public function redirectPath()
	{

		// run AppConfig app_config processes here
		$appConfigs = AppConfig::all();

		// cur_month_ccm_time_last_reset
		$curMonthCcmTimeLastReset = $appConfigs->where('config_key', 'cur_month_ccm_time_last_reset')->first();
		if($curMonthCcmTimeLastReset) {
			$curMonthStartDate = (new DateTime('first day of this month'))->format('Y-m-d');
			$curMonthStartDate = $curMonthStartDate . ' 12:00:01';
			if($curMonthCcmTimeLastReset->config_value < $curMonthStartDate) {
				// reset all users time
				$users = User::whereHas('patientInfo', function ($q) {
					$q->where('cur_month_activity_time', '>', '0');
				})->get();

				foreach($users as $user) {
					$user->patientInfo()->update(array("cur_month_activity_time" => "0"));
				}

				// update config timestamp
				$curMonthCcmTimeLastReset->config_value = date('Y-m-d H:i:s');
				$curMonthCcmTimeLastReset->save();
			}
		}

		// switch destination based on role
		if($this->auth->user()) {
			if($this->auth->user()->can('is-administrator')) {
				return '/admin';
			} else if($this->auth->user()->can('is-provider')) {
				return '/manage-patients/dashboard';
			} else if($this->auth->user()->can('is-care-center')) {
				return '/manage-patients/dashboard';
			} else {
				return '/manage-patients/dashboard';
			}
		}
		/*
		$role = $this->auth->user()->roles[0]->name;

		switch ($role)
		{
			case 'administrator': return '/admin';
			case 'manager': return '/manage-patients/dashboard';
			case 'participant': return '/manage-patients/dashboard';
			case 'provider': return '/manage-patients/dashboard';
			default: return '/manage-patients/dashboard';
		}
		*/
	}
}
