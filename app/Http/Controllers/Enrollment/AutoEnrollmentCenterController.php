<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\EnrollableInvitationLink;
use App\Http\Controllers\Controller;
use App\Jobs\EnrollableSurveyCompleted;
use App\Jobs\FinalActionOnNonResponsivePatients;
use App\Jobs\SelfEnrollmentPatientsReminder;
use App\Notifications\SendEnrollmentEmail;
use App\Services\Enrollment\EnrollmentInvitationService;
use App\Traits\EnrollableManagement;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\DatabaseNotification;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
use Faker\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class AutoEnrollmentCenterController extends Controller
{
    use EnrollableManagement;

    const ENROLLEES = 'Enrollees';

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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function enrollableInvitationManager(Request $request)
    {
//        Remember all enrollees at this point have user model also
        if ( ! $request->hasValidSignature()) {
            abort(403, 'Unauthorized action.');
        }
        $enrollableId     = $request->input('enrollable_id');
        $isSurveyOnlyUser = $request->input('is_survey_only');

        if ($isSurveyOnlyUser) {
            return $this->manageEnrolleeInvitation($enrollableId);
        }

        return $this->manageUnreachablePatientInvitation($enrollableId);
    }

    public function enrolleeContactDetails(Request $request)
    {
        $enrollableId = $request->input('enrollee_id');
        $enrollee     = Enrollee::whereId($enrollableId)->firstOrFail();

        $enrolleeData = [
            'enrolleeFirstName' => $enrollee->first_name,
            'enrolleeLastName'  => $enrollee->last_name,
            'cellPhone'         => $enrollee->cell_phone,
            'homePhone'         => $cellPhone = $enrollee->home_phone,
            'otherPhone'        => $cellPhone = $enrollee->other_phone,
        ];

        return view('enrollment-consent.enrolleeDetails', compact('enrolleeData'));
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
            if ($isSurveyOnly) {
                $enrollee->update(['user_id' => null]);
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
        $pastActiveSurveyLink = $this->getSurveyInvitationLink($userForEnrollment->patientInfo->id);
        if (empty($pastActiveSurveyLink)) {
            return $this->createUrlAndRedirectToSurvey($enrollableId);
        }

        return redirect($pastActiveSurveyLink->url);
    }

    public function evaluateEnrolledForSurveyTest(Request $request)
    {
        $data = [
            'enrollable_id'      => $request->input('enrolleeId'),
            'survey_instance_id' => $this->getEnrolleesSurveyInstance()->id,
        ];

        EnrollableSurveyCompleted::dispatch($data);

        return 'enrolled successfully';
    }

    public function finalActionTest()
    {
        FinalActionOnNonResponsivePatients::dispatch(new EnrollmentInvitationService());

        return 'Done!';
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
     * @param $enrollable
     * @param $isSurveyOnly
     *
     * @return \App\User|array|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function getCareAmbassador($enrollable, $isSurveyOnly)
    {
        if ($isSurveyOnly) {
            $enrollee               = Enrollee::whereUserId($enrollable->id)->firstOrFail();
            $careAmbassadorIdExists = ! empty($enrollee->care_ambassador_user_id);

            return $careAmbassadorIdExists
                ? User::whereId($enrollee->care_ambassador_user_id)->firstOrFail()
                : [];
        }

        return $enrollable->careAmbassador;
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

    public function inviteUnreachablesToEnrollTest()
    {
        Artisan::call('command:sendEnrollmentNotifications');

        return redirect()->back();
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

    public function resetEnrollmentTest()
    {
        // TEST ONLY
        $users = User::withTrashed()
            ->with('notifications', 'patientInfo')
            ->whereHas('notifications', function ($notification) {
                $notification->where('type', SendEnrollmentEmail::class);
            })->where('created_at', '>', Carbon::parse(now())->startOfMonth())
            ->whereHas('patientInfo')
            ->get();

        $survey = $this->getEnrolleeSurvey();

        foreach ($users as $user) {
            $surveyInstance = DB::table('survey_instances')
                ->where('survey_id', '=', $survey->id)
                ->first();

            if ($user->checkForSurveyOnlyRole()) {
                /** @var Enrollee $enrollee */
                $enrollee = $this->getEnrollee($user->id);
                $this->getAwvUserSurvey($user, $surveyInstance)->delete();
                $user->notifications()->delete();
                $enrollee->enrollmentInvitationLink()->delete();
                $enrollee->statusRequestsInfo()->delete();

                DB::table('invitation_links')
                    ->where('patient_info_id', $user->patientInfo()->withTrashed()->first()->id)
                    ->delete();

                DB::table('answers')
                    ->where('user_id', $user->id)
                    ->where('survey_instance_id', $surveyInstance->id)
                    ->delete();

                $user->forceDelete();
            } else {
                $this->getAwvUserSurvey($user, $surveyInstance)->delete();
                $user->notifications()->delete();
                $user->enrollmentInvitationLink()->delete();
                $user->statusRequestsInfo()->delete();
                $user->patientInfo()->update(
                    [
                        'ccm_status' => \CircleLinkHealth\Customer\Entities\Patient::UNREACHABLE,
                    ]
                );

                $patientInfo = $user->patientInfo->withTrashed()->first();

                DB::table('invitation_links')
                    ->where('patient_info_id', $patientInfo->id)
                    ->delete();

                DB::table('answers')
                    ->where('user_id', $user->id)
                    ->where('survey_instance_id', $surveyInstance->id)
                    ->delete();
            }
        }

        return redirect()->back();
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

    public function sendEnrollmentReminderTestMethod()
    {
        try {
            SelfEnrollmentPatientsReminder::dispatch();
        } catch (\Exception $e) {
            return 'Something went wrong';
        }

        return 'Please check your email';
    }

    public function sendInvitesPanelTest()
    {
        $invitedPatientsUrls = EnrollableInvitationLink::select(['url', 'invitationable_id', 'invitationable_type', 'manually_expired'])->get();

        $invitationData = $invitedPatientsUrls->transform(function ($url) {
            $isEnrolleeClass = Enrollee::class === $url->invitationable_type;
            /** @var EnrollableInvitationLink $url */
            $invitationable = $url->invitationable()->first(); // If empty = was enrollee and its user model got deleted caused got enrolled.
            $isManuallyExpired = $url->manually_expired;

            if ($isManuallyExpired || empty($invitationable)) {
                return [
                    'invitationUrl'   => '',
                    'isEnrolleeClass' => '',
                    'name'            => '',
                    'dob'             => '',
                ];
            }

            $patientInfo = $isEnrolleeClass
                ? $invitationable->user()->withTrashed()->first()->patientInfo()->withTrashed()->first()
                : $invitationable->patientInfo()->withTrashed()->first();

            $name = $isEnrolleeClass
                ? $invitationable->user()->withTrashed()->first()->display_name
                : $invitationable->display_name;

            return [
                'invitationUrl'   => $url->url,
                'isEnrolleeClass' => $isEnrolleeClass,
                'name'            => $name,
                'dob'             => Carbon::parse($patientInfo->birth_date)->toDateString(),
            ];
        });

        return view('enrollment-consent.unreachablesInvitationPanel', compact('invitedPatientsUrls', 'invitationData'));
    }

    public function triggerEnrollmentSeederTest()
    {
        try {
            Artisan::call('db:seed', ['--class' => 'PrepareDataForReEnrollmentTestSeeder']);
        } catch (\Exception $e) {
            return 'Somethings Wrong. Please try one more time...';
        }

        return 'All good to go. You can go back and invite some test patients';
    }

    /**
     * This should NOT be here. It should be no where.
     */
    private function createAnEnrolleeModelForUserJustForTesting(User $user)
    {
//        So it can be rendere to CA ambassadors dashboard
        $faker = Factory::create();
        Enrollee::updateOrCreate(
            [
                'user_id' => $user->id,
            ],
            [
                'practice_id'               => $user->primary_practice_id,
                'mrn'                       => mt_rand(111111, 999999),
                'first_name'                => $user->first_name,
                'last_name'                 => $user->last_name,
                'address'                   => $user->address,
                'city'                      => $user->city,
                'state'                     => $user->state,
                'zip'                       => 44508,
                'primary_phone'             => $faker->phoneNumber,
                'other_phone'               => $faker->phoneNumber,
                'home_phone'                => $faker->phoneNumber,
                'cell_phone'                => $faker->phoneNumber,
                'dob'                       => \Carbon\Carbon::parse('1901-01-01'),
                'lang'                      => 'EN',
                'status'                    => Enrollee::ENROLLED, // tis should be call_gueue
                'primary_insurance'         => 'test',
                'secondary_insurance'       => 'test',
                'email'                     => $user->email,
                'referring_provider_name'   => 'Dr. Demo',
                'auto_enrollment_triggered' => true,
            ]
        );
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

        return view('enrollment-consent.enrollmentInvitation', compact('userEnrollee', 'isSurveyOnlyUser', 'letterPages', 'practiceName', 'signatoryNameForHeader', 'dateLetterSent', 'hideButtons'));
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

        return view('enrollment-consent.enrollmentInfoRequested', compact('practiceNumber', 'providerName'));
    }
}
