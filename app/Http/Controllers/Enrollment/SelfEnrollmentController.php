<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnrollmentLinkValidation;
use App\Http\Requests\SelfEnrollableUserAuthRequest;
use App\SelfEnrollment\Helpers;
use App\Services\Enrollment\EnrollmentBaseLetter;
use App\Services\Enrollment\EnrollmentInvitationService;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SelfEnrollmentController extends Controller
{
    use AuthenticatesUsers;
    const BLUE_BUTTON_COLOR = '#12a2c4';

    const DEFAULT_BUTTON_COLOR = '#4baf50';

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
     *
     * @return mixed
     */
    public function createEnrollenrollableInfoRequest($enrollable)
    {
        return $enrollable->enrollableInfoRequest()->create();
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

        if (Helpers::hasCompletedSelfEnrollmentSurvey($enrollee->user)) {
//            Redirect to Survey Done Page (awv logout)
            return $this->createUrlAndRedirectToSurvey($enrollee->user);
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

            return view('EnrollmentSurvey.enrollableError');
        }

        return view(
            'EnrollmentSurvey.enrollmentSurveyLogin',
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

            return view('enrollment-letters.enrolledMessagePage', compact('practiceNumber', 'doctorName'));
        }

        $this->expirePastInvitationLink($patient);

        return $this->createUrlAndRedirectToSurvey($patient);
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
        $surveyLink = Helpers::getSurveyInvitationLink($notifiable->patientInfo);
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
            return $this->prepareLetterViewAndRedirect($user, true, $user->enrollee, true);
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
        return $this->enrollableInvitationManager(
            Auth::loginUsingId((int) $request->input('user_id'), true)
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

        $enrolleesSurveyUrl = url(config('services.awv.url')."/survey/enrollees/create-url/{$user->id}/{$enrolleesSurvey->id}");

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

        if ( ! is_null($user->enrollee->enrollableInfoRequest)) {
            return $this->returnEnrolleeRequestedInfoMessage($user->enrollee);
        }

        if (Enrollee::ENROLLED === $user->enrollee->status) {
            $practiceNumber = $user->enrollee->practice->outgoing_phone_number;
            $doctorName     = optional($user->enrollee->provider)->last_name;

            return view('enrollment-letters.enrolledMessagePage', compact('practiceNumber', 'doctorName'));
        }

        if ($this->hasSurveyInProgress($user)) {
            return redirect($this->getAwvInvitationLinkForUser($user)->url);
        }

        return $this->prepareLetterViewAndRedirect($user, true, $user->enrollee, false);
    }

    /**
     * @param $isSurveyOnlyUser
     * @param $hideButtons
     *
     * @throws \Exception
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function prepareLetterViewAndRedirect(User $userEnrollee, $isSurveyOnlyUser, Enrollee $enrollee, $hideButtons)
    {
        $enrollablePrimaryPractice     = $userEnrollee->primaryPractice;
        $letterClass                   = ucfirst(self::getLetterClassName($enrollablePrimaryPractice->name));
        $practiceLetterClass           = ucfirst(str_replace(' ', '', "App\Http\Controllers\Enrollment\PracticeSpecificLetter\ $letterClass"));
        $practiceLetterReflectionClass = new \ReflectionClass($practiceLetterClass);
        $baseLetter                    = (new EnrollmentBaseLetter(
            $enrollablePrimaryPractice,
            $userEnrollee,
            $isSurveyOnlyUser,
            $enrollee,
            $hideButtons,
            $practiceLetterReflectionClass
        ))->getBaseLetter();

        return (new $practiceLetterClass($hideButtons, $baseLetter, $enrollablePrimaryPractice, $userEnrollee))->letterSpecificView();
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
        $practiceLogoSrc     = self::ENROLLMENT_LETTER_DEFAULT_LOGO;
        $practiceLetter      = EnrollmentInvitationLetter::wherePracticeId($enrollee->practice_id)->first();

        if (empty($providerName)) {
            Log::info("Enrollee with id [$enrollee->id] does not have a provider");
        }

        if ($practiceLetter && ! empty($practiceLetter->practice_logo_src)) {
            $practiceLogoSrc = $practiceLetter->practice_logo_src;
        }

        $isSurveyOnly = true;

        return view('Enrollment.enrollmentInfoRequested', compact(
            'practiceNumber',
            'providerName',
            'practiceDisplayName',
            'practiceLogoSrc',
            'isSurveyOnly',
            'enrollee'
        ));
    }
}
