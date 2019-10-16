<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

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
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Log the user out of the application.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $redirect = $request->input('redirectTo', null);
        $query    = '';
        if ($redirect) {
            $query = "?redirectTo=$redirect";
        }

        return $this->loggedOut($request)
            ?: redirect('/' . $query);
    }

    protected function redirectTo()
    {
        $request    = request();
        $referer    = $request->header('referer', null);
        $redirectTo = null;
        if ($referer) {
            $mixed = parse_url($referer);
            if (isset($mixed['query']) && str_contains($mixed['query'], 'redirectTo')) {
                $redirectTo = str_replace('redirectTo=', '', $mixed['query']);
                if ($redirectTo) {
                    $redirectTo = urldecode($redirectTo);
                }
            }
        }

        $route = $redirectTo ?? '/manage-patients';

        //fixme: don't know why I had to do this
        //without it, it would redirect to home page '/'
        Session::put('url.intended', $route);

        return $route;
    }
}
