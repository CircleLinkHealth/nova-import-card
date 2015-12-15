<?php namespace App\Http\Controllers;

use Auth;

class WelcomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('guest');
	}

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
		//dd(Auth::redirectPath());
		if(Auth::user()) {
			$role = Auth::user()->roles[0]->name;

			switch ($role) {
				case 'administrator':
					return redirect()->route('admin.dashboard', [])->send();
				case 'manager':
					return redirect()->route('admin.dashboard', [])->send();
				case 'participant':
					return redirect()->route('patients.dashboard', [])->send();
				case 'provider':
					return redirect()->route('patients.dashboard', [])->send();
				default:
					return view('welcome');
			}
		}
		return view('welcome');
	}

}
