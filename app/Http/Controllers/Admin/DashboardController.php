<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Practice;
use App\Role;
use App\User;
use Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

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
        $user = Auth::user();

        // switch dashboard view based on logged in user
        if ($user->hasRole('administrator')) {
            return view('admin.dashboard', compact(['user']));
        } else {
            return redirect()->route('patients.dashboard', [])->send();
        }
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
