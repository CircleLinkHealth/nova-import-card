<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;
use App\Notifications\SendEnrollmentEmail;
use App\Services\Enrollment\EnrollmentInvitationService;
use App\Traits\EnrollableManagement;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AutoEnrollmentCenterController extends Controller
{
    use EnrollableManagement;

    const ENROLLEES                            = 'Enrollees';
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
        /** @var Enrollee $enrollee */
        $enrollableId      = $request->input('enrollable_id');
        $isSurveyOnly      = $request->input('is_survey_only');
        $enrollee          = $this->getEnrollee($enrollableId);
        $userModelEnrollee = $this->getUserModelEnrollee($enrollableId);

        if (
            $enrollee->statusRequestsInfo()->exists()
            && $enrollee->getLastEnrollmentInvitationLink()->manually_expired
        ) {
            return $this->returnEnrolleeRequestedInfoMessage($enrollee);
        }

        if ($this->activeEnrollmentInvitationsExists($enrollee)) {
            $this->expirePastInvitationLink($enrollee);
            $this->createEnrollStatusRequestsInfo($enrollee);
            $this->enrollmentInvitationService->setEnrollmentCallOnDelivery($enrollee);
            //            Delete User Created from Enrollee
//            Unreachables cant request info yet.
            if ($isSurveyOnly) {
                $enrollee->update(['user_id' => null, 'auto_enrollment_triggered' => true]);
                $userModelEnrollee->delete();
            }

            return $this->returnEnrolleeRequestedInfoMessage($enrollee);
        }

        return 'Your link has expired, someone will contact you soon. Thank you';
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function enrollNow(Request $request)
    {
        $enrollableId      = $request->input('enrollable_id');
        $userForEnrollment = $this->getUserModelEnrollee($enrollableId);
        $enrollable        = $this->getEnrollableModelType($userForEnrollment);

        //      This can happen only on the first redirect and if page is refreshed
        if ($this->enrollableHasRequestedInfo($enrollable)) {
            return 'Action Not Allowed';
        }

        $this->expirePastInvitationLink($enrollable);
//        $pastActiveSurveyLink = $this->getSurveyInvitationLink($userForEnrollment->patientInfo->id);
        if (empty($pastActiveSurveyLink)) {
            return $this->createUrlAndRedirectToSurvey($enrollableId);
        }

        return redirect($pastActiveSurveyLink->url);
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
    public function getEnrollmentLetter(
        User $userForEnrollment,
        $enrollablePrimaryPractice,
        $isSurveyOnlyUser,
        $provider = null,
        $hideButtons = false
    ) {
        $practiceLetter = EnrollmentInvitationLetter::where('practice_id', $enrollablePrimaryPractice->id)
            ->firstOrFail();

        // CA's phone numbers is the practice number
        $practiceNumber = $enrollablePrimaryPractice->outgoing_phone_number;

        if (null === $provider) {
            $provider = $this->getEnrollableProvider($isSurveyOnlyUser, $userForEnrollment);
        }

        $practiceName = $enrollablePrimaryPractice->display_name;

        return $this->enrollmentInvitationService->createLetter(
            $practiceName,
            $practiceLetter,
            $practiceNumber,
            $provider,
            $hideButtons
        );
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
        $letterPages               = $this->getEnrollmentLetter(
            $userEnrollee,
            $enrollablePrimaryPractice,
            $isSurveyOnlyUser,
            $provider,
            $hideButtons
        );
        $practiceName           = $enrollablePrimaryPractice->name;
        $signatoryNameForHeader = $provider->display_name;
        $dateLetterSent         = Carbon::parse($enrollee->getLastEnrollmentInvitationLink()->updated_at)->toDateString();
        $pastActiveLink         = $this->pastActiveInvitationLinks($enrollee);
        $buttonColor            = '#4baf50';

        if ( ! empty($pastActiveLink)) {
            $buttonColor = $pastActiveLink->button_color;
        }

        return view('enrollment-consent.enrollmentInvitation', compact(
            'userEnrollee',
            'isSurveyOnlyUser',
            'letterPages',
            'practiceName',
            'signatoryNameForHeader',
            'dateLetterSent',
            'hideButtons',
            'buttonColor',
        ));
    }

    private function getEnrolleeFromNotification($enrollableId)
    {
        $notification = DatabaseNotification::where('type', SendEnrollmentEmail::class)
            ->where('notifiable_id', $enrollableId)
            ->first();

        return Enrollee::whereId($notification->data['enrollee_id'])->first();
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
        //        If user is deleted and enrollee is null
        //         Then Enrollable has been enrolled and his temporary user is deleted.
        //        Enrollee user_id is detached when user model is deleted.
        //        @todo: Not great/clear solution. Come back to this
        if (is_null($enrollee) && is_null($userCreatedFromEnrollee)) {
            $enrollee = $this->getEnrolleeFromNotification($enrollableId);
        }

        if ( ! $this->enrollableHasRequestedInfo($enrollee) && 'enrolled' === $enrollee->status) {
            $practiceNumber = $enrollee->practice->outgoing_phone_number;
            $doctorName     = optional($enrollee->provider)->last_name;

            return view('enrollment-consent.enrolledMessagePage', compact('practiceNumber', 'doctorName'));
        }

        $linkIsManuallyExpired = $enrollee->getLastEnrollmentInvitationLink()->manually_expired;

        if ($linkIsManuallyExpired && $enrollee->statusRequestsInfo()->exists()) {
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
        $providerName   = $enrollee->provider->last_name;

        return view('Enrollment.enrollmentInfoRequested', compact('practiceNumber', 'providerName'));
    }
}
