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
     * @throws \Exception
     */
    public function index()
    {
        $user = auth()->user();

        if ( ! $user) {
            return redirect()->route('login', [])->send();
        }

        if ($user->roles->isEmpty()) {
            auth()->logout();

            throw new \Exception("Log in for User with id {$user->id} failed. User has no assigned Roles.");
        }

        if ($user->hasRole('administrator')) {
            return redirect()->route('admin.dashboard', [])->send();
        }

        if ($user->hasRole('saas-admin')) {
            return redirect()->route('saas-admin.home', [])->send();
        }

        if ($user->hasRole('care-ambassador')) {
            return redirect()->route('enrollment-center.dashboard', [])->send();
        }

        return redirect()->route('patients.dashboard', [])->send();
    }
}
