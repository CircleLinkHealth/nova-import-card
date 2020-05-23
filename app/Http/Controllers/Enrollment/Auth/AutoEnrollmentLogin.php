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
use App\Traits\EnrollableManagement;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AutoEnrollmentLogin extends Controller
{
    use AuthenticatesUsers;
    use EnrollableManagement;
    use EnrollmentAuthentication;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function authenticate(EnrollmentValidationRules $request)
    {
        $manager = new AutoEnrollmentCenterController(new EnrollmentInvitationService());
        Auth::loginUsingId($request->input('user_id'), true);

        if (boolval($request->input('is_survey_only'))) {
            $enrollee = Enrollee::fromUserId($request->input('user_id'));

            if ( ! $enrollee) {
                abort(404);
            }

            $enrollee->selfEnrollmentStatuses()->update([
                'logged_in' => true,
            ]);
        }

        return $manager->enrollableInvitationManager(
            $request->input('user_id'),
            boolval($request->input('is_survey_only'))
        );
    }

    protected function enrollmentAuthForm(EnrollmentLinkValidation $request)
    {
        // Debugging logs
        $isFromBitly     = Str::contains($request->headers->get('user-agent', ''), 'bitly');
        $alreadyLoggedIn = auth()->check() ? 'yes' : 'no';
        $authId          = auth()->id() ?? 'null';
        $headers         = json_encode($request->headers->all());
        $userId          = $this->getUserId($request);
        Log::debug("enrollmentAuthForm - User is already logged in: $alreadyLoggedIn. EnrollableId[$userId]. isFromBitly[$isFromBitly].\nUser Id: $authId.\nHeaders: $headers");

        try {
            $loginFormData = $this->getLoginFormData($request);
        } catch (\Exception $e) {
            return view('EnrollmentSurvey.enrollableError');
        }
        $user            = $loginFormData['user'];
        $urlWithToken    = $loginFormData['url_with_token'];
        $practiceName    = $loginFormData['practiceName'];
        $doctorsLastName = $loginFormData['doctorsLastName'];
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

    /**
     * @throws \Exception
     *
     * @return array
     */
    private function getLoginFormData(Request $request)
    {
        $userId = $this->getUserId($request);

        /** @var User $user */
        $user = User::find($userId);
        if ( ! $user) {
            Log::warning("User[$userId] not found.");
            throw new \Exception('User not found');
        }

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
            'user'            => $user,
        ];
    }
}
