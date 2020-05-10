<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

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
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        //this will redirect to url
        $redirectImmediate = $request->input('redirectImmediate', null);
        if ($redirectImmediate) {
            return $this->loggedOut($request)
                ?: redirect('/'.$redirectImmediate);
        }

        //this will pass redirect as query param
        $redirect = $request->input('redirectTo', null);
        $query    = '';
        if ($redirect) {
            $query = "?redirectTo=$redirect";
        }

        return $this->loggedOut($request)
            ?: redirect('/'.$query);
    }

    protected function redirectTo()
    {
        Log::debug('LoginController -> redirectTo');
        $redirectTo = null;

        $request = request();

        $queryParam = $request->query('redirectTo', null);
        if ($queryParam) {
            $redirectTo = urldecode($queryParam);
        } else {
            $referer = $request->header('referer', null);
            if ($referer) {
                $mixed = parse_url($referer);
                if (isset($mixed['query']) && Str::contains($mixed['query'], 'redirectTo')) {
                    $redirectTo = str_replace('redirectTo=', '', $mixed['query']);
                    if ($redirectTo) {
                        $redirectTo = urldecode($redirectTo);
                    }
                }
            }
        }

        $route = $redirectTo ?? '/manage-patients';

        //make sure url.intended is not set
        Session::pull('url.intended');

        Log::debug("Route => $route");

        return $route;
    }
}
