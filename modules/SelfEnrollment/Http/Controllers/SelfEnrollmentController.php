<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Http\Controllers;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\SelfEnrollment\AppConfig\SelfEnrollmentLetterVersionSwitch;
use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetterV2;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetter;
use CircleLinkHealth\SelfEnrollment\Helpers;
use CircleLinkHealth\SelfEnrollment\Http\Requests\EnrollmentLinkValidation;
use CircleLinkHealth\SelfEnrollment\Http\Requests\SelfEnrollableUserAuthRequest;
use CircleLinkHealth\SelfEnrollment\Services\EnrollmentBaseLetter;
use CircleLinkHealth\SelfEnrollment\Services\EnrollmentInvitationService;
use CircleLinkHealth\SelfEnrollment\Services\SelfEnrollmentLetterService;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SelfEnrollmentController extends Controller
{
    use AuthenticatesUsers;
    const BLUE_BUTTON_COLOR = '#12a2c4'; //@todo: remove all colors except default(blue). see metrics also.

    const DEFAULT_BUTTON_COLOR = '#12a2c4';

    const ENROLLEES_SURVEY_NAME          = 'Enrollees';
    const ENROLLMENT_LETTER_DEFAULT_LOGO = 'https://www.zilliondesigns.com/images/portfolio/healthcare-hospital/iStock-471629610-Converted.png';
    const ENROLLMENT_SURVEY_COMPLETED    = 'completed';
    const ENROLLMENT_SURVEY_IN_PROGRESS  = 'in_progress';
    const ENROLLMENT_SURVEY_PENDING      = 'pending';
    const RED_BUTTON_COLOR               = '#b1284c';

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
     * @param $enrollable
     * @param $responseStatus
     * @param mixed $experiencedError
     *
     * @return mixed
     */
    public function createEnrollenrollableInfoRequest($enrollable, $experiencedError = false)
    {
        return $enrollable->enrollableInfoRequest()->create(['experienced_error' => $experiencedError]);
    }

    /**
     * @param $enrollable
     *
     * @return bool
     */
    public function enrollableHasRequestedInfo($enrollable)
    {
        return $enrollable ? $enrollable->enrollableInfoRequest()->exists() : false;
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

        $user = $enrollee->user;
        if (Helpers::hasCompletedSelfEnrollmentSurvey($user)) {
//            Redirect to Survey Done Page (awv logout)
            return $this->createUrlAndRedirectToSurvey($user);
        }

        if ( ! $enrollee->enrollableInfoRequest()->exists()) {
            $this->createEnrollenrollableInfoRequest($enrollee);
            $this->enrollmentInvitationService->setEnrollmentCallOnDelivery($enrollee);
        }

        return $this->returnEnrolleeRequestedInfoMessage($enrollee);
    }

    public function enrollmentAuthForm(EnrollmentLinkValidation $request)
    {
        try {
            $loginFormData = $this->getLoginFormData($request);
        } catch (\Exception $e) {
            Log::error(json_encode($e));

            return view('selfEnrollment::EnrollmentSurvey.enrollableError', compact(['userId' => $request->input('enrollable_id')]));
        }

        return view(
            'selfEnrollment::EnrollmentSurvey.enrollmentSurveyLogin',
            [
                'userId'          => $loginFormData['user']->id,
                'isSurveyOnly'    => $request->input('is_survey_only'),
                'doctorsLastName' => $loginFormData['doctorsLastName'],
                'urlWithToken'    => $loginFormData['url_with_token'],
                'practiceName'    => $loginFormData['practiceName'],
            ]
        );
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

        $enrollable = Helpers::getEnrollableModel($user);

        if ($enrollable->enrollableInfoRequest()->exists()) {
            return $this->returnEnrolleeRequestedInfoMessage($user->enrollee);
        }

        $this->expirePastInvitationLink($enrollable);

        try {
            return $this->createUrlAndRedirectToSurvey($user);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            Log::critical("User id [$userId] could not redirect to AWV Enrollee Survey. ERROR: $message");

            return view('selfEnrollment::EnrollmentSurvey.enrollableError', compact('userId'));
        }
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

    public static function getLetterClassName(string $practiceName)
    {
        return Str::camel($practiceName.'_letter');
    }

    public function handleUnreachablePatientInvitation(User $patient)
    {
        if ($this->hasSurveyInProgress($patient)) {
            return redirect($this->getAwvInvitationLinkForUser($patient)->url);
        }

        if (Helpers::hasCompletedSelfEnrollmentSurvey($patient)) {
            $practiceNumber = $patient->primaryPractice->outgoing_phone_number;
            $doctorName     = $patient->getBillingProviderName();

            return view('selfEnrollment::enrollment-letters.enrolledMessagePage', compact('practiceNumber', 'doctorName'));
        }

        $this->expirePastInvitationLink($patient);

        return $this->createUrlAndRedirectToSurvey($patient);
    }

    /**
     * @param $user
     *
     * @return bool
     */
    public function hasSurveyInProgress(User $user)
    {
        if (Helpers::getSurveyInvitationLink($user)) {
            return Helpers::awvUserSurveyQuery($user, Helpers::getCurrentYearEnrolleeSurveyInstance())
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
        $awvHost     = parse_url(config('selfEnrollment.awv.url'), PHP_URL_HOST);
        if ($prevUrlHost !== $awvHost) {
            abort(403, 'Unauthorized action.');
        }

        $user = User::whereId($userId)->has('enrollee')->with('enrollee')->firstOrFail();

        if ($user->isSurveyOnly()) {
            if (SelfEnrollmentLetterVersionSwitch::loadNewVersionIfModelExists($user->primaryProgramId())){
                return $this->prepareLetterViewAndRedirectV2($user, true,  true);
            }

            return $this->prepareLetterViewAndRedirect($user, true,  true);
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

    protected function authenticate(SelfEnrollableUserAuthRequest $request)
    {
        $userId = intval($request->get('userId'));
        try {
            /** @var User $user */
            $user = Auth::loginUsingId((int) $userId, true);

            return $this->enrollableInvitationManager(
                $user
            );
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            Log::critical("[Self Enrollment Survey] User [$userId] could not login. EXCEPTION: $message");

            return view('selfEnrollment::EnrollmentSurvey.enrollableError', compact('userId'));
        }
    }

    protected function errorRequestedCallback(int $userId)
    {
        $enrollee = Enrollee::whereUserId($userId)->whereStatus(Enrollee::QUEUE_AUTO_ENROLLMENT)->first();

        if ($enrollee) {
            $this->enrollmentInvitationService->setEnrollmentCallOnDelivery($enrollee);

            if ( ! $enrollee->enrollableInfoRequest()->where('experienced_error', true)->exists()) {
                $this->createEnrollenrollableInfoRequest($enrollee, true);
            }
        } else {
            $message = "[SelfEnrollmentController#requestCallback] Callback Request Failed. Enrolle with user id : [$userId] and status : ".Enrollee::QUEUE_AUTO_ENROLLMENT.' was not found.';
            Log::error($message);
            sendSlackMessage('#self_enrollment_logs', $message);
        }

        return view('selfEnrollment::EnrollmentSurvey.requestCallbackConfirmation');
    }

    protected function logoutEnrollee(Request $request)
    {
        $practiceLetter  = null;
        $practiceName    = '';
        $surveyPracticeLogo = self::ENROLLMENT_LETTER_DEFAULT_LOGO;
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
            $surveyPracticeLogo = $practiceLetter->practice_logo_src;
        }

        Auth::logout();

        return view('selfEnrollment::EnrollmentSurvey.enrollableLogout', compact('surveyPracticeLogo', 'practiceName'));
    }

    /**
     * @param $enrollableId
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    private function createUrlAndRedirectToSurvey(User $user)
    {
        $enrolleesSurvey = Helpers::getEnrolleeSurvey();

        DB::table('users_surveys')->updateOrInsert(
            [
                'user_id'            => $user->id,
                'survey_instance_id' => Helpers::getCurrentYearEnrolleeSurveyInstance()->id,
                'survey_id'          => $enrolleesSurvey->id,
            ],
            [
                'status'     => 'pending',
                'start_date' => now()->toDateTimeString(),
            ]
        );

        $enrolleesSurveyUrl = url(config('selfEnrollment.awv.url')."/survey/enrollees/create-url/{$user->id}/{$enrolleesSurvey->id}");

        return redirect($enrolleesSurveyUrl);
    }

    private function enrollableInvitationManager(User $user)
    {
        if ($user->isSurveyOnly()) {
            return $this->handleEnrolleeInvitation($user);
        }

        return $this->handleUnreachablePatientInvitation($user);
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
        if ( ! $user = User::find($userId = intval($request->input('enrollable_id')))) {
            Log::warning($msg = "User[$userId] not found.");
            throw new \Exception($msg);
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
    private function handleEnrolleeInvitation(User $user)
    {
        $user->loadMissing(['enrollee.enrollableInfoRequest', 'enrollee.practice', 'enrollee.provider']);

        $enrollableInfoRequest = $user->enrollee->enrollableInfoRequest;
        if ( ! is_null($enrollableInfoRequest)) {
            if ($enrollableInfoRequest->where('experienced_error', true)->exists()) {
                return view('selfEnrollment::EnrollmentSurvey.requestCallbackConfirmation');
            }

            return $this->returnEnrolleeRequestedInfoMessage($user->enrollee);
        }

        if (Enrollee::ENROLLED === $user->enrollee->status) {
            $practiceNumber = $user->enrollee->practice->outgoing_phone_number;
            $doctorName     = optional($user->enrollee->provider)->last_name;

            return view('selfEnrollment::enrollment-letters.enrolledMessagePage', compact('practiceNumber', 'doctorName'));
        }

        if ($this->hasSurveyInProgress($user)) {
            return redirect($this->getAwvInvitationLinkForUser($user)->url);
        }

        if (SelfEnrollmentLetterVersionSwitch::loadNewVersionIfModelExists($user->primaryProgramId())){
            return $this->prepareLetterViewAndRedirectV2($user, true,  false);
        }

        return $this->prepareLetterViewAndRedirect($user, true,  false);
    }


    private function prepareLetterViewAndRedirectV2(User $userEnrollee, $isSurveyOnlyUser, $hideButtons)
    {
        $invitationLink = $userEnrollee->enrollee->getLastEnrollmentInvitationLink();

        if (! $invitationLink) {
            Log::channel('database')
                ->error('Latest enrollment link missing for user_id ['. $userEnrollee->id ."].");

            return view('selfEnrollment::EnrollmentSurvey.enrollableError');
        }

        $dateLetterSent = Carbon::parse($invitationLink->updated_at)->toDateString();
        $enrollablePractice     = $userEnrollee->primaryPractice;
        $enrollablePracticeId     = $enrollablePractice->id;

        $letterService = app(SelfEnrollmentLetterService::class);
        $letter = EnrollmentInvitationLetterV2::where('practice_id', $enrollablePracticeId)
            ->where('is_active', true)
            ->first();

        if (!$letter){
            $message = "[Self Enrollment Survey] Letter for practice_id [$enrollablePracticeId] not found.";
            Log::channel('database')->error($message);
            sendSlackMessage('#self_enrollment_logs', $message);
            return view('selfEnrollment::EnrollmentSurvey.enrollableError');
        }

        $letterForView = $letterService->createLetterToRender($userEnrollee, $letter, $dateLetterSent);

        if (! in_array(optional($userEnrollee->billingProviderUser())->id, $letterForView->allSignatoryProvidersIds()->toArray())){
            $message = "Patient with user_id:$userEnrollee->id and practice: [$enrollablePracticeId] has no billing provider match with the letter.
            Patient has been redirected to the ERROR page";
            sendSlackMessage('#self_enrollment_logs', $message);
            return view('selfEnrollment::EnrollmentSurvey.enrollableError');
        }

        return view('selfEnrollment::enrollment-letterV2', [
            'letter' => $letterForView,
            'hideButtons' => $hideButtons,
            'userEnrolleeId' => $userEnrollee->id,
            'isSurveyOnlyUser' => $isSurveyOnlyUser,
            'buttonColor'=>SelfEnrollmentController::DEFAULT_BUTTON_COLOR,
            'practiceName' => $enrollablePractice->display_name,
            'disableButtons' => false
        ]);
    }

    /**
     * @param $isSurveyOnlyUser
     * @param $hideButtons
     *
     * @throws \Exception
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function prepareLetterViewAndRedirect(User $userEnrollee, $isSurveyOnlyUser, $hideButtons)
    {
        $enrollablePrimaryPractice     = $userEnrollee->primaryPractice;
        $letterClass                   = ucfirst(self::getLetterClassName($enrollablePrimaryPractice->name));
        $practiceLetterClass           = ucfirst(str_replace(' ', '', "CircleLinkHealth\SelfEnrollment\Http\Controllers\PracticeSpecificLetter\ $letterClass"));
        $practiceLetterReflectionClass = new \ReflectionClass($practiceLetterClass);
        $baseLetter                    = (new EnrollmentBaseLetter(
            $enrollablePrimaryPractice,
            $userEnrollee,
            $isSurveyOnlyUser,
            $userEnrollee->enrollee,
            $hideButtons,
            $practiceLetterReflectionClass
        ))->getBaseLetter();

        return (new $practiceLetterClass($hideButtons, $baseLetter, $enrollablePrimaryPractice, $userEnrollee))->letterSpecificView();
    }

    public function adminLetterReview(int $practiceId, int $userId)
    {
        $user = User::findOrFail($userId);
        $letter = EnrollmentInvitationLetterV2::with('media','practice')
            ->where('is_active', true)
            ->where('practice_id', $practiceId)
            ->first();

        if (!$letter){
            Log::channel('database')
                ->error("[Self Enrollment Survey] Letter for practice_id [$practiceId] not found.");
            return view('selfEnrollment::EnrollmentSurvey.enrollableError');
        }

        $letterToRender = app(SelfEnrollmentLetterService::class)
            ->createLetterToRender($user, $letter, Carbon::now()->toDateString());

        return view('selfEnrollment::enrollment-letterV2', [
            'letter' => $letterToRender,
            'hideButtons' => false,
            'userEnrolleeId' => $userId,
            'isSurveyOnlyUser' => true,
            'buttonColor'=>SelfEnrollmentController::DEFAULT_BUTTON_COLOR,
            'dateLetterSent' => now()->toDateString(),
            'practiceName' => $letter->practice->display_name,
            'disableButtons'=>true
        ]);
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

        $providerName        = optional($enrollee->provider)->last_name;
        $practiceDisplayName = $enrollee->practice->display_name;
        $surveyPracticeLogo     = self::ENROLLMENT_LETTER_DEFAULT_LOGO;
        $practiceLetter      = EnrollmentInvitationLetter::wherePracticeId($enrollee->practice_id)->first();

        if (empty($providerName)) {
            Log::info("Enrollee with id [$enrollee->id] does not have a provider");
        }

        if ($practiceLetter && ! empty($practiceLetter->practice_logo_src)) {
            $surveyPracticeLogo = $practiceLetter->practice_logo_src;
        }

        $isSurveyOnly = true;

        return view('selfEnrollment::EnrollmentSurvey.enrollmentInfoRequested', compact(
            'practiceNumber',
            'providerName',
            'practiceDisplayName',
            'surveyPracticeLogo',
            'isSurveyOnly',
            'enrollee'
        ));
    }
}
