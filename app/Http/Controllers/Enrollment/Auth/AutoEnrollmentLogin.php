<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Enrollment\AutoEnrollmentCenterController;
use App\Http\Requests\EnrollmentLinkValidation;
use App\Http\Requests\EnrollmentValidationRules;
use App\Services\Enrollment\EnrollmentInvitationService;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutoEnrollmentLogin extends Controller
{
    use AuthenticatesUsers;
    use EnrollmentAuthLink;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function authenticate(EnrollmentValidationRules $request)
    {
        $manager = new AutoEnrollmentCenterController(new EnrollmentInvitationService());
        Auth::loginUsingId($request->input('user_id'), true);

        return $manager->enrollableInvitationManager($request->input('user_id'), boolval($request->input('is_survey_only')));
    }

    protected function enrollmentAuthForm(EnrollmentLinkValidation $request)
    {
        $loginFormData   = $this->getLoginFormData($request);
        $practiceName    = $loginFormData['practiceName'];
        $doctorsLastName = $loginFormData['doctorsLastName'];
        $userId          = intval($request->input('enrollable_id'));
        $isSurveyOnly    = $request->input('is_survey_only');

        return view('enrollmentSurvey.enrollmentSurveyLogin', compact('userId', 'isSurveyOnly', 'doctorsLastName', 'practiceName'));
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
            'isSurveyOnly' => $user->hasRole('survey-only'),
            //            'user'            => $user,
            'practiceName'    => $user->getPrimaryPracticeName(),
            'doctor'          => $doctor,
            'doctorsLastName' => $doctorsLastName,
        ];
    }
}
