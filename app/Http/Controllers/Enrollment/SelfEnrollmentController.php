<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\Helpers\SelfEnrollmentHelpers;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Enrollment\Auth\DB;
use App\Http\Controllers\Enrollment\Auth\EnrollmentAuthentication;
use App\Http\Requests\EnrollmentLinkValidation;
use App\Http\Requests\EnrollmentValidationRules;
use App\Services\Enrollment\EnrollmentInvitationService;
use App\Traits\EnrollableManagement;
use Carbon\Carbon;
use CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SelfEnrollmentController extends Controller
{
    use AuthenticatesUsers;
    use EnrollableManagement;
    use EnrollmentAuthentication;

    const DEFAULT_BUTTON_COLOR = '#4baf50';

    const ENROLLEES_SURVEY_NAME                = 'Enrollees';
    const ENROLLMENT_LETTER_DEFAULT_LOGO       = 'https://www.zilliondesigns.com/images/portfolio/healthcare-hospital/iStock-471629610-Converted.png';
    const RED_BUTTON_COLOR                     = '#b1284c';
    const SEND_NOTIFICATIONS_LIMIT_FOR_TESTING = 1;

    /**
     * @var EnrollmentInvitationService
     */
    private $enrollmentInvitationService;

    public function __construct(EnrollmentInvitationService $enrollmentInvitationService)
    {
        $this->middleware('guest')->except('logout');
        $this->enrollmentInvitationService = $enrollmentInvitationService;
    }

    /**
     * Prepares Enrollment letter pages.
     *
     * @param $enrollablePrimaryPractice
     * @param $isSurveyOnlyUser
     * @param mixed|null $provider
     * @param mixed      $hideButtons
     *
     * @return array
     */
    public function composeEnrollmentLetter(
        EnrollmentInvitationLetter $letter,
        User $userForEnrollment,
        $enrollablePrimaryPractice,
        $isSurveyOnlyUser,
        $provider = null,
        $hideButtons = false
    ) {
        // CA's phone numbers is the practice number
        $practiceNumber = $enrollablePrimaryPractice->outgoing_phone_number;
        if ($practiceNumber) {
            //remove +1 from phone number
            $formatted      = formatPhoneNumber($practiceNumber);
            $practiceNumber = "<a href='tel:$formatted'>$formatted</a>";
        }

        if (null === $provider) {
            $provider = $this->getEnrollableProvider($isSurveyOnlyUser, $userForEnrollment);
        }

        $practiceName = $enrollablePrimaryPractice->display_name;

        return $this->enrollmentInvitationService->createLetter(
            $practiceName,
            $letter,
            $practiceNumber,
            $provider,
            $hideButtons
        );
    }

    /**
     * @param $enrollable
     * @param $responseStatus
     *
     * @return mixed
     */
    public function createEnrollStatusRequestsInfo($enrollable)
    {
        return $enrollable->statusRequestsInfo()->create();
    }

    /**
     * @param $enrollable
     *
     * @return bool
     */
    public function enrollableHasRequestedInfo($enrollable)
    {
        return $enrollable ? $enrollable->statusRequestsInfo()->exists() : false;
    }

    /**
     * NOTE: Currently ONLY Enrollee model have the option to request info.
     *
     * @throws \Exception
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function enrolleeRequestsInfo(Request $request)
    {
        $enrollableId = $request->input('enrollable_id');
        $isSurveyOnly = $request->input('is_survey_only');

        /** @var Enrollee $enrollee */
        $enrollee = Enrollee::fromUserId($enrollableId);
        if ( ! $enrollee) {
            return "Enrollee[$enrollableId] not found";
        }

        if (is_null($enrollee->user_id)) {
            return "Enrollee [$enrollableId] user_id is null";
        }

        $userFromEnrollee = $this->getUserModelEnrollee($enrollee->user_id);

        if (SelfEnrollmentHelpers::hasCompletedSelfEnrollmentSurvey($userFromEnrollee)) {
//            Redirect to Survey Done Page (awv logout)
            return $this->generateUrlAndRedirectToSurvey($userFromEnrollee->id);
        }

        if ( ! $enrollee->statusRequestsInfo()->exists()) {
            $this->createEnrollStatusRequestsInfo($enrollee);
            $this->enrollmentInvitationService->setEnrollmentCallOnDelivery($enrollee);
            if ($isSurveyOnly) {
                $userModelEnrollee = User::find($enrollableId);
                $this->updateEnrolleeSurveyStatuses($enrollee->id, optional($userModelEnrollee)->id, null);
            }
        }

        return $this->returnEnrolleeRequestedInfoMessage($enrollee);
    }

    /**
     * @throws \Exception
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function enrollNow(Request $request)
    {
        $enrollableId      = $request->input('enrollable_id');
        $userForEnrollment = User::find($enrollableId);
        if ( ! $userForEnrollment) {
            throw new \Exception('There was an error. Please try again. [1]', 400);
        }
        $enrollable = $this->getEnrollableModelType($userForEnrollment);

        if ($enrollable->statusRequestsInfo()->exists()) {
            return $this->returnEnrolleeRequestedInfoMessage($enrollable);
        }

        $this->expirePastInvitationLink($enrollable);

        return $this->createUrlAndRedirectToSurvey($enrollableId);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     */
    public function getAwvInvitationLinkForUser(User $user)
    {
        return DB::table('invitation_links')
            ->where('patient_info_id', $user->patientInfo->id)
            ->first();
    }

    public function handleUnreachablePatientInvitation($patientUserId)
    {
        /** @var User $userModelEnrollee */
//        Note: this can be either Unreachable patient Or User created from enrollee
        $unrechablePatient = User::find($patientUserId);

        if ($this->hasSurveyInProgress($unrechablePatient)) {
            return redirect($this->getAwvInvitationLinkForUser($unrechablePatient)->url);
        }

        if (SelfEnrollmentHelpers::hasCompletedSelfEnrollmentSurvey($unrechablePatient)) {
            $practiceNumber = $unrechablePatient->primaryPractice->outgoing_phone_number;
            $doctorName     = $unrechablePatient->getBillingProviderName();

            return view('enrollment-consent.enrolledMessagePage', compact('practiceNumber', 'doctorName'));
        }

        $this->expirePastInvitationLink($unrechablePatient);

        return $this->generateUrlAndRedirectToSurvey($unrechablePatient->id);
    }

    /**
     * @param $userId
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function reviewLetter($userId)
    {
        $prevUrlHost = parse_url(url()->previous(), PHP_URL_HOST);
        $awvHost     = parse_url(config('services.awv.url'), PHP_URL_HOST);
        if ($prevUrlHost !== $awvHost) {
            abort(403, 'Unauthorized action.');
        }

        $user = User::whereId($userId)->firstOrFail();
        if ($user->hasRole('survey-only')) {
            $enrollee = Enrollee::fromUserId($userId);

            return $this->enrollmentLetterView($user, true, $enrollee, true);
        }
        abort(403, 'Unauthorized action.');
    }

    public function viewFormVisited(Request $request)
    {
        $isSurveyOnly = boolval($request->input('is_survey_only'));
        $userId       = intval($request->input('enrollable_id'));
        if ($isSurveyOnly) {
            $enrollee = Enrollee::fromUserId($userId);
            if ( ! $enrollee) {
                Log::warning("Enrollee for user with id $userId not found");
                throw new \Exception('User does not exist', 404);
            }
            $this->expirePastInvitationLink($enrollee);
        } else {
            $user = User::find($userId);
            $this->expirePastInvitationLink($user);
        }

        return response()->json([], 200);
    }

    protected function authenticate(EnrollmentValidationRules $request)
    {
        $userId = (int) $request->input('user_id');
        Auth::loginUsingId($userId, true);

        if (boolval($request->input('is_survey_only'))) {
            $enrollee = Enrollee::fromUserId($userId);

            if ( ! $enrollee) {
                abort(404);
            }

            $enrollee->selfEnrollmentStatus()->update([
                'logged_in' => true,
            ]);
        }

        return $this->enrollableInvitationManager(
            $userId,
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
        $practiceLogoSrc = SelfEnrollmentController::ENROLLMENT_LETTER_DEFAULT_LOGO;
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

    private function enrollableInvitationManager($enrollableId, $isSurveyOnlyUser)
    {
        if ($isSurveyOnlyUser) {
            return $this->handleEnrolleeInvitation($enrollableId);
        }

        return $this->handleUnreachablePatientInvitation($enrollableId);
    }

    /**
     * @param $isSurveyOnlyUser
     * @param $hideButtons
     *
     * @throws \Exception
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function enrollmentLetterView(User $userEnrollee, $isSurveyOnlyUser, Enrollee $enrollee, $hideButtons)
    {
        $enrollablePrimaryPractice = $userEnrollee->primaryPractice;
        $provider                  = $this->getEnrollableProvider($isSurveyOnlyUser, $userEnrollee);
        /** @var EnrollmentInvitationLetter $practiceLetter */
        $practiceLetter = EnrollmentInvitationLetter::where('practice_id', $enrollablePrimaryPractice->id)
            ->firstOrFail();
        $letterPages = $this->composeEnrollmentLetter(
            $practiceLetter,
            $userEnrollee,
            $enrollablePrimaryPractice,
            $isSurveyOnlyUser,
            $provider,
            $hideButtons
        );
        $practiceName           = $enrollablePrimaryPractice->display_name;
        $practiceLogoSrc        = $practiceLetter->practice_logo_src ?? self::ENROLLMENT_LETTER_DEFAULT_LOGO;
        $signatoryNameForHeader = $provider->display_name;
        $dateLetterSent         = '???';
        $buttonColor            = self::DEFAULT_BUTTON_COLOR;

        /** @var EnrollableInvitationLink $invitationLink */
        $invitationLink = $enrollee->getLastEnrollmentInvitationLink();
        if ($invitationLink) {
            $dateLetterSent = Carbon::parse($invitationLink->updated_at)->toDateString();
            $buttonColor    = $invitationLink->button_color;
        }

        return view('enrollment-consent.enrollmentInvitation', compact(
            'userEnrollee',
            'isSurveyOnlyUser',
            'letterPages',
            'practiceName',
            'practiceLogoSrc',
            'signatoryNameForHeader',
            'dateLetterSent',
            'hideButtons',
            'buttonColor',
        ));
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    private function getLoginFormData(Request $request)
    {
        $userId = $this->getUserId($request);
        
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

    /**
     * @param $userId
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    private function handleEnrolleeInvitation(int $userId)
    {
        $user     = User::findOrFail($userId);
        $enrollee = Enrollee::fromUserId($userId);

        if ( ! $enrollee) {
            throw new \Exception("Enrollee not found for user[$userId]");
        }

        if ($enrollee->statusRequestsInfo()->exists()) {
            return $this->returnEnrolleeRequestedInfoMessage($enrollee);
        }

        if (Enrollee::ENROLLED === $enrollee->status) {
            $practiceNumber = $enrollee->practice->outgoing_phone_number;
            $doctorName     = optional($enrollee->provider)->last_name;

            return view('enrollment-consent.enrolledMessagePage', compact('practiceNumber', 'doctorName'));
        }

        if ($this->hasSurveyInProgress($user)) {
            return redirect($this->getAwvInvitationLinkForUser($user)->url);
        }

        return $this->enrollmentLetterView($user, true, $enrollee, false);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function returnEnrolleeRequestedInfoMessage(Enrollee $enrollee)
    {
        $practiceNumber = $enrollee->practice->outgoing_phone_number;
        if ($practiceNumber) {
            //remove +1 from phone number
            $practiceNumber = formatPhoneNumber($practiceNumber);
        }
        $providerName    = $enrollee->provider->last_name;
        $practiceName    = $enrollee->practice->display_name;
        $practiceLogoSrc = self::ENROLLMENT_LETTER_DEFAULT_LOGO;
        $practiceLetter  = EnrollmentInvitationLetter::wherePracticeId($enrollee->practice_id)->first();
        if ($practiceLetter && ! empty($practiceLetter->practice_logo_src)) {
            $practiceLogoSrc = $practiceLetter->practice_logo_src;
        }

        $isSurveyOnly = true;

        return view('Enrollment.enrollmentInfoRequested', compact(
            'practiceNumber',
            'providerName',
            'practiceName',
            'practiceLogoSrc',
            'isSurveyOnly',
            'enrollee'
        ));
    }
}
