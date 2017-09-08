<?php namespace App\Http\Controllers;

class WelcomeController extends Controller
{

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
        if (auth()->user()) {
            if (auth()->user()->roles->isEmpty()) {
                auth()->logout();

                return view('errors.403', [
                    'hideLinks' => true,
                    'message'   => 'Unauthorized login request. This User has no assigned Roles.',
                ]);
            }

            if (auth()->user()->hasRole('administrator')) {
                return redirect()->route('admin.dashboard', [])->send();
            }

            if (auth()->user()->hasRole('provider')) {
                return redirect()->route('patients.dashboard', [])->send();
            }

            if (auth()->user()->hasRole('care-center')) {
                return redirect()->route('patients.dashboard', [])->send();
            }

            if (auth()->user()->hasRole('care-ambassador')) {
                return redirect()->route('enrollment-center.dashboard', [])->send();
            }

            return redirect()->route('patients.dashboard', [])->send();
        }

        return redirect()->route('login', [])->send();
    }

}
