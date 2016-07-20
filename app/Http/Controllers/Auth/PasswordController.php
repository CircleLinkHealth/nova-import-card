<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\CLH\Traits\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class PasswordController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Password Reset Controller
	|--------------------------------------------------------------------------
	|
	| This controller is responsible for handling password reset requests
	| and uses a simple trait to include this behavior. You're free to
	| explore this trait and override any methods you wish to tweak.
	|
	*/

	use ResetsPasswords;

	/**
	 * Create a new password controller instance.
	 *
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\PasswordBroker  $passwords
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('guest');
	}

	/**
	 * Get the needed credentials for sending the reset link.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	protected function getSendResetLinkEmailCredentials(Request $request)
	{
		return $request->only('user_email');
	}
}
