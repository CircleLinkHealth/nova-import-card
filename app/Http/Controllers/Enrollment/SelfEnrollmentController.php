<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\Helpers\SelfEnrollmentHelpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\EnrollmentLinkValidation;
use App\Http\Requests\EnrollmentValidationRules;
use App\Services\Enrollment\EnrollmentInvitationService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SelfEnrollmentController extends Controller
{
    use AuthenticatesUsers;

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
            $provider = $userForEnrollment->billingProviderUser();
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
        $enrollee = Enrollee::whereUserId($enrollableId)->has('user')->with('user')->firstOrFail();

        if (SelfEnrollmentHelpers::hasCompletedSelfEnrollmentSurvey($enrollee->user)) {
//            Redirect to Survey Done Page (awv logout)
            return $this->createUrlAndRedirectToSurvey($enrollee->user);
        }

        if ( ! $enrollee->statusRequestsInfo()->exists()) {
            $this->createEnrollStatusRequestsInfo($enrollee);
            $this->enrollmentInvitationService->setEnrollmentCallOnDelivery($enrollee);
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
        $userId = $request->input('enrollable_id');

        if ( ! is_numeric($userId)) {
            throw new \Exception("EnrollableId `$userId` needs to be numeric", 400);
        }

        $user = User::has('enrollee')->with('enrollee')->findOrFail($userId);

        $enrollable = SelfEnrollmentHelpers::getEnrollableModel($user);

        if ($enrollable->statusRequestsInfo()->exists()) {
            return $this->returnEnrolleeRequestedInfoMessage($user->enrollee);
        }

        $this->expirePastInvitationLink($enrollable);

        return $this->createUrlAndRedirectToSurvey($user);
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
        $unrechablePatient = User::findOrFail($patientUserId);

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
     * @param $notifiable
     *
     * @return bool
     */
    public function hasSurveyInProgress(User $notifiable)
    {
//        For nova request. At that point enrollees will ot have User model, hence they didnt get invited yet.
//        if (Enrollee::class === get_class($notifiable)) {
//            return false;
//        }
        $surveyLink = SelfEnrollmentHelpers::getSurveyInvitationLink($notifiable->patientInfo);
        if ( ! empty($surveyLink)) {
            $surveyInstance = DB::table('survey_instances')
                ->where('survey_id', '=', $surveyLink->survey_id)
                ->first();

            return DB::table('users_surveys')
                ->where('user_id', '=', $notifiable->id)
                ->where('survey_instance_id', '=', $surveyInstance->id)
                ->where('status', '=', 'in_progress')
                ->exists();
        }

        return false;
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

        $user = User::whereId($userId)->has('enrollee')->with('enrollee')->firstOrFail();

        if ($user->isSurveyOnly()) {
            return $this->enrollmentLetterView($user, true, $user->enrollee, true);
        }

        abort(403, 'Unauthorized action.');
    }

    public function viewFormVisited(Request $request)
    {
        $isSurveyOnly = boolval($request->input('is_survey_only'));
        $userId       = intval($request->input('enrollable_id'));
        if ($isSurveyOnly) {
            $this->expirePastInvitationLink(Enrollee::whereUserId($userId)->firstOrFail());
        } else {
            $user = User::find($userId);
            $this->expirePastInvitationLink($user);
        }

        return response()->json([], 200);
    }

    protected function authenticate(EnrollmentValidationRules $request)
    {
        $userId = (int) $request->input('user_id');
        $user   = Auth::loginUsingId($userId, true);

        if ($user->isSurveyOnly()) {
            Enrollee::whereUserId($userId)->firstOrFail()->selfEnrollmentStatus()->update([
                'logged_in' => true,
            ]);
        }

        return $this->enrollableInvitationManager(
            $user
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

    /**
     * @param $enrollableId
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    private function createUrlAndRedirectToSurvey($enrollableId)
    {
        $enrolleesSurvey = SelfEnrollmentHelpers::getEnrolleeSurvey();

        DB::table('users_surveys')->updateOrInsert(
            [
                'user_id'            => $enrollableId,
                'survey_instance_id' => SelfEnrollmentHelpers::getCurrentYearEnrolleeSurveyInstance()->id,
                'survey_id'          => $enrolleesSurvey->id,
            ],
            [
                'status'     => 'pending',
                'start_date' => now()->toDateTimeString(),
            ]
        );

        $enrolleesSurveyUrl = url(config('services.awv.url')."/survey/enrollees/create-url/{$enrollableId}/{$enrolleesSurvey->id}");

        return redirect($enrolleesSurveyUrl);
    }

    private function enrollableInvitationManager(User $user)
    {
        if ($user->isSurveyOnly()) {
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
        $provider                  = $userEnrollee->billingProviderUser();
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
     * @param $enrollable
     */
    private function expirePastInvitationLink($enrollable)
    {
        Log::debug("expirePastInvitationLink called for $enrollable->id");

        $pastInvitationLinksQuery = $enrollable->enrollmentInvitationLinks()->where('manually_expired', false);

        if ($pastInvitationLinksQuery->exists()) {
            $pastInvitationLinksQuery->update(['manually_expired' => true]);
        }
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

    private function getUserId($request)
    {
        return intval($request->input('enrollable_id'));
    }

    /**
     * @param $userId
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    private function handleEnrolleeInvitation(User $user)
    {
        $user->loadMissing(['enrollee.statusRequestsInfo', 'enrollee.practice', 'enrollee.provider']);

        if ( ! is_null($user->enrollee->statusRequestsInfo)) {
            return $this->returnEnrolleeRequestedInfoMessage($user->enrollee);
        }

        if (Enrollee::ENROLLED === $user->enrollee->status) {
            $practiceNumber = $user->enrollee->practice->outgoing_phone_number;
            $doctorName     = optional($user->enrollee->provider)->last_name;

            return view('enrollment-consent.enrolledMessagePage', compact('practiceNumber', 'doctorName'));
        }

        if ($this->hasSurveyInProgress($user)) {
            return redirect($this->getAwvInvitationLinkForUser($user)->url);
        }

        return $this->enrollmentLetterView($user, true, $user->enrollee, false);
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
