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
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutoEnrollmentLogin extends Controller
{
    use AuthenticatesUsers;
    use EnrollmentAuthentication;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function authenticate(EnrollmentValidationRules $request)
    {
        $manager = new AutoEnrollmentCenterController(new EnrollmentInvitationService());
        Auth::loginUsingId($request->input('user_id'), true);

        return $manager->enrollableInvitationManager(
            $request->input('user_id'),
            boolval($request->input('is_survey_only'))
        );
    }

    protected function enrollmentAuthForm(EnrollmentLinkValidation $request)
    {
        $loginFormData   = $this->getLoginFormData($request);
        $urlWithToken    = $loginFormData['url_with_token'];
        $practiceName    = $loginFormData['practiceName'];
        $doctorsLastName = $loginFormData['doctorsLastName'];
        $userId          = intval($request->input('enrollable_id'));
        $isSurveyOnly    = $request->input('is_survey_only');

        return view(
            'EnrollmentSurvey.enrollmentSurveyLogin',
            compact('userId', 'isSurveyOnly', 'doctorsLastName', 'practiceName', 'urlWithToken')
        );
    }

    protected function logoutEnrollee(Request $request)
    {
        $practiceLetter  = null;
        $practiceName    = '';
        $practiceLogoSrc = AutoEnrollmentCenterController::ENROLLMENT_LETTER_DEFAULT_LOGO;
        // Just checking if Enrollee. Patients(usres) are not allowed here.
        if ($request->input('isSurveyOnly')) {
            $enrollee = Enrollee::with('practice')->where('id', $request->input('enrolleeId'))->first();
            if (empty($enrollee)) {
                Auth::logout();
                throw new \Exception('User not found');
            }
            $practiceLetter = EnrollmentInvitationLetter::wherePracticeId($enrollee->practice_id)->first();
            $practiceName   = $enrollee->practice->display_name;
        }

        if ( ! empty($practiceLetter) && ! empty($practiceLetter->practice_logo_src)) {
            $practiceLogoSrc = $practiceLetter->practice_logo_src;
        }

        Auth::logout();

        return view('EnrollmentSurvey.enrollableLogout', compact('practiceLogoSrc', 'practiceName'));
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
            'url_with_token'  => $request->getRequestUri(),
            'practiceName'    => $user->getPrimaryPracticeName(),
            'doctor'          => $doctor,
            'doctorsLastName' => $doctorsLastName,
        ];
    }
}
