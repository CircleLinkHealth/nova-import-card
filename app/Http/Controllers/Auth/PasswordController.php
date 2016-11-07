<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
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
	 * Get the password reset validation rules.
	 *
	 * @return array
	 */
	protected function getResetValidationRules()
	{
		return [
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|confirmed|min:6',
		];
	}

	/**
	 * Get the password reset credentials from the request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	protected function getResetCredentials(Request $request)
	{
		return $request->only(
            'email', 'password', 'password_confirmation', 'token'
		);
	}

	/**
	 * Get the needed credentials for sending the reset link.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	protected function getSendResetLinkEmailCredentials(Request $request)
	{
        return $request->only('email');
	}

	/**
	 * Validate the request of sending reset link.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return void
	 */
	protected function validateSendResetLinkEmail(Request $request)
	{
        $this->validate($request, ['email' => 'required|email']);
	}
}
