<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment\Auth;

use App\Http\Controllers\Controller;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AutoEnrollmentLogin extends Controller
{
    use AuthenticatesUsers {
        username as traitUsername;
        credentials as traitCredentials;
        validateLogin as traitValidateLogin;
        showLoginForm as traitShowLoginForm;
    }

    use EnrollmentAuthLink;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * @return array
     */
    protected function credentials(Request $request)
    {
        $input = $request->only($this->username());

        return [
            'name' => $input[$this->username()],
            'dob'  => Carbon::now()->startOfDay(),
        ];
    }

    protected function enrollmentloginForm(Request $request)
    {
        $this->authenticate($request);
        $loginFormData   = $this->getLoginFormData($request);
        $practiceName    = $loginFormData['practiceName'];
        $doctorsLastName = $loginFormData['doctorsLastName'];

        return view('enrollmentSurvey.enrollmentSurveyLogin');
    }

    /**
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard|mixed
     */
    protected function guard()
    {
        return Auth::guard();
    }

    protected function redirectTo($request)
    {
//        return route('login');
    }

    protected function username()
    {
        return 'display_name';
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'birth_date'      => 'required|date',
            //            'url' => 'required|string',
        ]);
    }

    private function getLoginFormData(Request $request)
    {
        $user            = $this->getUserValidated($request);
        $doctor          = $user->billingProviderUser();
        $doctorsLastName = '???';
        if ($doctor) {
            $doctorsLastName = $doctor->display_name;
        }

        return [
            'isSurveyOnly'    => $user->hasRole('survey-only'),
            'user'            => $user,
            'practiceName'    => $user->getPrimaryPracticeName(),
            'doctor'          => $doctor,
            'doctorsLastName' => $doctorsLastName,
        ];
    }
}
