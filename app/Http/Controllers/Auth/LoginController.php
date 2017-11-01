<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    protected $username = 'email';


    /**
     * Create a new controller instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->middleware('guest', ['except' => 'logout']);

        $this->usernameOrEmail($request);
    }

    /**
     * Determine whether log in input is email or username, and do the needful to authenticate
     *
     * @param Request $request
     *
     * @return bool
     */
    public function usernameOrEmail(Request $request)
    {
        if (!$request->filled('email')) {
            return false;
        }

        $request->merge(array_map('trim', $request->input()));

        if (!str_contains($request->input('email'), '@')) {
            $this->username = 'username';

            $request->merge([
                'username' => $request->input('email'),
            ]);
        }
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return $this->username;
    }
}
