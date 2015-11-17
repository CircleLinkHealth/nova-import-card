<?php namespace App\Http\Controllers;

use App\WpUser;
use App\WpBlog;
use App\WpUserMeta;
use Auth;

class HomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Home Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders your application's "dashboard" for users that
	| are authenticated. Of course, you are free to change or remove the
	| controller as you wish. It is just here to get your app started!
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
		$user = $wpUser = WpUser::find(Auth::user()->ID);

		// switch dashboard view based on logged in user
		if($user->hasRole('administrator') || $user->hasRole('provider')) {

			$stats = array();
			$stats['totalPrograms'] = WpBlog::all()->count();
			$stats['totalUsers'] = WpUser::all()->count();
			$stats['totalAdministrators'] = WpUser::whereHas('roles', function($q) {
				$q->where('name', '=', 'administrator');
			})
				->get()->count();
			$stats['totalParticipants'] = WpUser::whereHas('roles', function($q) {
					$q->where('name', '=', 'participant');
				})
				->get()->count();
			$stats['totalProviders'] = WpUser::whereHas('roles', function($q) {
				$q->where('name', '=', 'provider');
			})
				->get()->count();

			return view('admin/dashboard', compact(['user', 'stats']));

		} else if($user->hasRole('participant')) {

			return view('patient/dashboard', ['user' => $user]);

		} else {

			return view('home', ['user' => $user]);

		}
	}

}
