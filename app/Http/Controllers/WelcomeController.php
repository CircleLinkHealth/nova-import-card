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
		if(Auth::user()) {
			$role = Auth::user()->roles[0]->name;

			switch ($role) {
				case 'administrator':
					return redirect('/admin');
				case 'manager':
					return redirect('/admin');
				case 'participant':
					return redirect('/manage-pataients');
				case 'provider':
					return redirect('/manage-pataients');
				default:
					return view('welcome');
			}
		}
		return view('welcome');
	}

}
