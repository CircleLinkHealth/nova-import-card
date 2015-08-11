<?php namespace App\Http\Controllers;

use App\WpUser;
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
		$userMeta = Auth::user()->meta->lists('meta_value', 'meta_key');
		return view('home', ['user' => $user, 'userMeta' => $userMeta]);
	}

}
