<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Jenssegers\Agent\Agent;

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

    use AuthenticatesUsers {
        login as traitLogin;
    }

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
        $this->middleware('guest', ['except' => 'logout']);
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

    /**
     * @param Request $request
     *
     * @return void
     */
    public function login(Request $request)
    {
        $this->usernameOrEmail($request);
        $loginResponse = $this->traitLogin($request);

        if ( ! $this->validateBrowserCompatibility()) {
            $this->sendInvalidBrowserResponse();
        }

        return $loginResponse;
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
        if ( ! $request->filled('email')) {
            return false;
        }

        $request->merge(array_map('trim', $request->input()));

        if ( ! str_contains($request->input('email'), '@')) {
            $this->username = 'username';

            $request->merge([
                'username' => $request->input('email'),
            ]);
        }
    }

    /**
     * Check whether the user is using a supported browser.
     *
     * @return bool
     */
    public function validateBrowserCompatibility()
    {
        $agent = new Agent();

        if ( ! $agent->isIE()) {
            return true;
        }

        return false;
    }

    /**
     * @return void
     * @throws ValidationException
     */
    public function sendInvalidBrowserResponse()
    {
        $messages = [
            'invalid-browser' => "I'm sorry, you may be using a version of Internet Explorer (IE) that we don't support. 
            We recommend you use Chrome. 
            <br>If you must use IE, please use IE11 or later.
            <br>If you must use IE v10 or earlier, please e-mail <a href='mailto:contact@circlelinkhealth.com'>contact@circlelinkhealth.com</a>",
        ];

        if (auth()->user()->hasRole('care-center')) {
            auth()->logout();

            $messages = [
                'invalid-browser-force-switch' => 'Care Coaches are required to use Chrome. Please switch to Chrome and try logging in again.',
            ];
        }

        throw ValidationException::withMessages($messages);
    }
}
