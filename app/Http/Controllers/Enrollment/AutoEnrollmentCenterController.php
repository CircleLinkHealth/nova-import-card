<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;
use App\Services\Enrollment\EnrollmentInvitationService;
use App\Traits\EnrollableManagement;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoEnrollmentCenterController extends Controller
{
    use EnrollableManagement;

    const ENROLLEES                            = 'Enrollees';
    const ENROLLMENT_LETTER_DEFAULT_LOGO       = 'https://www.zilliondesigns.com/images/portfolio/healthcare-hospital/iStock-471629610-Converted.png';
    const SEND_NOTIFICATIONS_LIMIT_FOR_TESTING = 1;

    /**
     * @var EnrollmentInvitationService
     */
    private $enrollmentInvitationService;

    /**
     * EnrollmentCenterController constructor.
     */
    public function __construct(EnrollmentInvitationService $enrollmentInvitationService)
    {
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
            $practiceNumber = formatPhoneNumber($practiceNumber);
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
        return $enrollable->statusRequestsInfo()->exists();
    }

    /**
     * @param $enrollableId
     * @param $isSurveyOnlyUser
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function enrollableInvitationManager($enrollableId, $isSurveyOnlyUser)
    {
        if ($isSurveyOnlyUser) {
            return $this->manageEnrolleeInvitation($enrollableId);
        }

        return $this->manageUnreachablePatientInvitation($enrollableId);
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
        $enrollee = $this->getEnrollee($enrollableId);
        if ( ! $enrollee) {
            return "Enrollee[$enrollableId] not found";
        }

        if ( ! $enrollee->statusRequestsInfo()->exists()) {
            $this->createEnrollStatusRequestsInfo($enrollee);
            $this->enrollmentInvitationService->setEnrollmentCallOnDelivery($enrollee);
            // Delete User Created from Enrollee
            // Unreachables cant request info yet.
            if ($isSurveyOnly) {
                $userModelEnrollee = $this->getUserModelEnrollee($enrollableId);
                $this->updateEnrolleeSurveyStatuses($enrollee->id, optional($userModelEnrollee)->id, null);
                $enrollee->update(['user_id' => null, 'auto_enrollment_triggered' => true]);
            }
        }

        return $this->returnEnrolleeRequestedInfoMessage($enrollee);
    }

    /**
     * @throws \Exception
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function enrollNow(Request $request)
    {
        $enrollableId      = $request->input('enrollable_id');
        $userForEnrollment = $this->getUserModelEnrollee($enrollableId);
        if ( ! $userForEnrollment) {
            throw new \Exception('There was an error. Please try again. [1]', 400);
        }
        $enrollable = $this->getEnrollableModelType($userForEnrollment);

        //      This can happen only on the first redirect and if page is refreshed
        if ($this->enrollableHasRequestedInfo($enrollable)) {
            throw new \Exception('There was an error. A care coach will contact you soon. [2]', 400);
        }

        $this->expirePastInvitationLink($enrollable);

        return $this->createUrlAndRedirectToSurvey($enrollableId);
        /*
        $pastActiveSurveyLink = $this->getSurveyInvitationLink($userForEnrollment->patientInfo->id);
        if (empty($pastActiveSurveyLink)) {
            return $this->createUrlAndRedirectToSurvey($enrollableId);
        }
        return redirect($pastActiveSurveyLink->url);
        */
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

    public function manageUnreachablePatientInvitation($enrollableId)
    {
        /** @var User $userModelEnrollee */
//        Note: this can be either Unreachable patient Or User created from enrollee
        $unrechablePatient = $this->getUserModelEnrollee($enrollableId);

        if ($this->hasSurveyInProgress($unrechablePatient)) {
            return redirect($this->getAwvInvitationLinkForUser($unrechablePatient)->url);
        }

        if ($this->hasSurveyCompleted($unrechablePatient)) {
            $practiceNumber = $unrechablePatient->primaryPractice->outgoing_phone_number;
            $doctorName     = $unrechablePatient->getBillingProviderName();

            return view('enrollment-consent.enrolledMessagePage', compact('practiceNumber', 'doctorName'));
        }

        $this->expirePastInvitationLink($unrechablePatient);

        return $this->createUrlAndRedirectToSurvey($unrechablePatient->id);
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
            $enrollee = $this->getEnrollee($userId);

            return $this->enrollmentLetterView($user, true, $enrollee, true);
        }
        abort(403, 'Unauthorized action.');
    }

    public function viewFormVisited(Request $request)
    {
        $isSurveyOnly = boolval($request->input('is_survey_only'));
        $userId       = intval($request->input('enrollable_id'));
        if ($isSurveyOnly) {
            $enrollee = $this->getEnrollee($userId);
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

    /**
     * @param $isSurveyOnlyUser
     * @param $hideButtons
     *
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
        $practiceName           = $enrollablePrimaryPractice->name;
        $practiceLogoSrc        = $practiceLetter->practice_logo_src ?? self::ENROLLMENT_LETTER_DEFAULT_LOGO;
        $signatoryNameForHeader = $provider->display_name;
        $dateLetterSent         = Carbon::parse($enrollee->getLastEnrollmentInvitationLink()->updated_at)->toDateString();
        $buttonColor            = '#4baf50';

        if ($isSurveyOnlyUser) {
            $enrollable = $userEnrollee;
        }

        $enrollable = $enrollee;

        if ( ! is_null($enrollee->getLastEnrollmentInvitationLink())) {
            $buttonColor = $enrollee->getLastEnrollmentInvitationLink()->button_color;
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
     * @param $enrollableId
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    private function manageEnrolleeInvitation($enrollableId)
    {
        /** @var Enrollee $enrollee */
        $enrollee = $this->getEnrollee($enrollableId);
        /** @var User $userModelEnrollee */
//        Note: this can be either Unreachable patient Or User created from enrollee
        $userCreatedFromEnrollee = $this->getUserModelEnrollee($enrollableId);
        // If enrollee get enrolled, then its user model is also deleted
        // We can assume is enrollee for now,  since is the only model that can request info.
        if (is_null($enrollee) && is_null($userCreatedFromEnrollee)) {
            $enrollee = $this->getEnrolleeFromNotification($enrollableId);
        }

        if ( ! $this->enrollableHasRequestedInfo($enrollee) && 'enrolled' === $enrollee->status) {
            $practiceNumber = $enrollee->practice->outgoing_phone_number;
            $doctorName     = optional($enrollee->provider)->last_name;

            return view('enrollment-consent.enrolledMessagePage', compact('practiceNumber', 'doctorName'));
        }

        if ($enrollee->statusRequestsInfo()->exists()) {
            return $this->returnEnrolleeRequestedInfoMessage($enrollee);
        }

        if ($this->hasSurveyInProgress($userCreatedFromEnrollee)) {
            return redirect($this->getAwvInvitationLinkForUser($userCreatedFromEnrollee)->url);
        }

        return $this->enrollmentLetterView($userCreatedFromEnrollee, true, $enrollee, false);
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
