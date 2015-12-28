<?php namespace App\Http\Controllers\Admin;

use App\User;
use App\WpBlog;
use App\Observation;
use App\Role;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Auth;

class DashboardController extends Controller {

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
		$user = $wpUser = User::find(Auth::user()->ID);

		// switch dashboard view based on logged in user
		if($user->hasRole('administrator') || $user->hasRole('provider')) {

			$stats = array();
			$stats['totalPrograms'] = WpBlog::all()->count();
			$stats['totalUsers'] = User::all()->count();
			$stats['totalAdministrators'] = User::whereHas('roles', function($q) {
				$q->where('name', '=', 'administrator');
			})
				->get()->count();
			$stats['totalParticipants'] = User::whereHas('roles', function($q) {
					$q->where('name', '=', 'participant');
				})
				->get()->count();
			$stats['totalProviders'] = User::whereHas('roles', function($q) {
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
