<?php namespace App\Http\Controllers\Admin;

use App\User;
use App\Program;
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

		$roles = Role::all();

		// switch dashboard view based on logged in user
		if($user->can('admin-access')) {

			$stats = array();
			$roleStats = array();
			$stats['totalPrograms'] = Program::all()->count();
			$stats['totalUsers'] = User::all()->count();
			foreach($roles as $role) {
				$roleStats[$role->name] = User::whereHas('roles', function($q) use($role) {
					$q->where('name', '=', $role->name);
				})
					->get()->count();
			}
			/*
			foreach($roles as $role) {
				$stats['totalAdministrators'] = User::whereHas('roles', function ($q) {
					$q->where('name', '=', 'administrator');
				})
					->get()->count();
				$stats['totalCareCenter'] = User::whereHas('roles', function ($q) {
					$q->where('name', '=', 'care-center');
				})
					->get()->count();
				$stats['totalParticipants'] = User::whereHas('roles', function ($q) {
					$q->where('name', '=', 'participant');
				})
					->get()->count();
				$stats['totalProviders'] = User::whereHas('roles', function ($q) {
					$q->where('name', '=', 'provider');
				})
					->get()->count();
			}
			*/

			return view('admin/dashboard', compact(['user', 'stats', 'roleStats']));

		} else {

			return redirect()->route('patients.dashboard', [])->send();

		}

		return view('home', ['user' => $user]);
	}



	/**
	 * Display the specified resource.
	 *
	 * @param  int  $patientId
	 * @return Response
	 */
	public function testplan(Request $request)
	{

		$patient = User::find('393');
		return view('admin.testplan', compact(['patient']));
	}

}
