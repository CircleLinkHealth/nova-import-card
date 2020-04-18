<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\CareAmbassadorLog;
use App\EnrollableInvitationLink;
use App\Http\Controllers\Controller;
use App\Jobs\EnrollableSurveyCompleted;
use App\Jobs\FinalActionOnNonResponsivePatients;
use App\Jobs\SendEnrollmentPatientsReminder;
use App\Notifications\SendEnrollmentEmail;
use App\Services\Enrollment\AttachEnrolleeFamilyMembers;
use App\Services\Enrollment\EnrolleeCallQueue;
use App\Services\Enrollment\EnrollmentInvitationService;
use App\Traits\EnrollableManagement;
use App\TrixField;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use CircleLinkHealth\Eligibility\Entities\EnrollmentInvitationLetter;
use CircleLinkHealth\Eligibility\Jobs\ImportConsentedEnrollees;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class EnrollmentCenterController extends Controller
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

    public function consented(Request $request)
    {
        $careAmbassador = auth()->user()->careAmbassador;

        $enrollee = Enrollee::find($request->input('enrollee_id'));

        AttachEnrolleeFamilyMembers::attach($request);

        //update report for care ambassador:
        $report                       = CareAmbassadorLog::createOrGetLogs($careAmbassador->id);
        $report->no_enrolled          = $report->no_enrolled + 1;
        $report->total_calls          = $report->total_calls + 1;
        $report->total_time_in_system = $request->input('total_time_in_system');
        $report->save();

        $enrollee->setHomePhoneAttribute($request->input('home_phone'));
        $enrollee->setCellPhoneAttribute($request->input('cell_phone'));
        $enrollee->setOtherPhoneAttribute($request->input('other_phone'));

        //set preferred phone
        switch ($request->input('preferred_phone')) {
            case 'home':
                $enrollee->setPrimaryPhoneNumberAttribute($request->input('home_phone'));
                break;
            case 'cell':
                $enrollee->setPrimaryPhoneNumberAttribute($request->input('cell_phone'));
                break;
            case 'other':
                $enrollee->setPrimaryPhoneNumberAttribute($request->input('other_phone'));
                break;
            case 'agent':
                $enrollee->setPrimaryPhoneNumberAttribute($request->input('agent_phone'));
                $enrollee->agent_details = [
                    Enrollee::AGENT_PHONE_KEY        => $request->input('agent_phone'),
                    Enrollee::AGENT_NAME_KEY         => $request->input('agent_name'),
                    Enrollee::AGENT_EMAIL_KEY        => $request->input('agent_email'),
                    Enrollee::AGENT_RELATIONSHIP_KEY => $request->input('agent_relationship'),
                ];
                break;
            default:
                $enrollee->setPrimaryPhoneNumberAttribute($request->input('home_phone'));
        }

        $enrollee->address                 = $request->input('address');
        $enrollee->address_2               = $request->input('address_2');
        $enrollee->state                   = $request->input('state');
        $enrollee->city                    = $request->input('city');
        $enrollee->zip                     = $request->input('zip');
        $enrollee->email                   = $request->input('email');
        $enrollee->dob                     = $request->input('dob');
        $enrollee->last_call_outcome       = $request->input('consented');
        $enrollee->care_ambassador_user_id = $careAmbassador->user_id;

        $enrollee->total_time_spent = $enrollee->total_time_spent + $request->input('time_elapsed');

        $enrollee->attempt_count = $enrollee->attempt_count + 1;

        //we are adding this as Other Note on patient chart. Therefore, even if it's empty, save it to potentially overwrite previous outcome reasons e.g. when unreachable
        $enrollee->last_call_outcome_reason = $request->input('extra');

        if (is_array($request->input('days'))) {
            $enrollee->preferred_days = implode(', ', $request->input('days'));
        }

        if (is_array($request->input('times'))) {
            $enrollee->preferred_window = implode(', ', $request->input('times'));
        }

        $enrollee->status          = Enrollee::CONSENTED;
        $enrollee->consented_at    = Carbon::now()->toDateTimeString();
        $enrollee->last_attempt_at = Carbon::now()->toDateTimeString();

        $enrollee->save();

        $queue = explode(',', $request->input('queue'));
        $queue = collect(array_merge(
            $queue,
            explode(',', $request->input('confirmed_family_members'))
        ))->unique()->toArray();
        if ( ! empty($queue) && in_array($enrollee->id, $queue)) {
            unset($queue[array_search($enrollee->id, $queue)]);
        }

        ImportConsentedEnrollees::dispatch([$enrollee->id], $enrollee->batch);

        EnrolleeCallQueue::update($careAmbassador, $enrollee, $request->input('confirmed_family_members'));

        return redirect()->route('enrollment-center.dashboard');
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

    public function dashboard()
    {
        $careAmbassador = auth()->user()->careAmbassador;

        $enrollee = EnrolleeCallQueue::getNext($careAmbassador);

        if (null == $enrollee) {
            //no calls available
            return view('enrollment-ui.no-available-calls');
        }

        return view(
            'enrollment-ui.dashboard',
            [
                'enrollee' => $enrollee,
                'report'   => CareAmbassadorLog::createOrGetLogs($careAmbassador->id),
                'script'   => TrixField::careAmbassador($enrollee->lang)->first(),
                'provider' => $enrollee->provider,
            ]
        );
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

        if ($enrollee->statusRequestsInfo()->exists()
            && $enrollee->getLastEnrollmentInvitationLink()->manually_expired) {
            return $this->returnEnrolleeRequestedInfoMessage($enrollee);
        }

        if ($this->activeEnrollmentInvitationsExists($enrollee)) {
            $this->expirePastInvitationLink($enrollee);
            $this->createEnrollStatusRequestsInfo($enrollee);
            $this->enrollmentInvitationService->setEnrollmentCallOnDelivery($enrollee);
            $this->expirePastInvitationLink($enrollee);
            //            Delete User Created from Enrollee
            if ($isSurveyOnly) {
                $userModelEnrollee->delete();
            }

            return $this->returnEnrolleeRequestedInfoMessage($enrollee);
        }

        return 'Your link has expired, someone will contact you soon. Thank you';
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function enrolleesInvitationLetterBoard(Request $request)
    {
//        Remember all enrollees at this point have user model also
        if ( ! $request->hasValidSignature()) {
            abort(401);
        }
        $enrollableId     = $request->input('enrollable_id');
        $isSurveyOnlyUser = $request->input('is_survey_only');
//        for future: This can be either "user created from enrolle" or user (unreachable patient)
//        but we dont mind for now  - * see NOTE:on top of enrolleeRequestsInfo().
//        anyhow Seperation is done by $isSurveyOnly - "enrollees that became users ar survey onlys"
        $userEnrollee = $this->getUserModelEnrollee($enrollableId);
        /** @var Enrollee $enrollee */
        $enrollee = $this->getEnrollee($enrollableId);

        // This is when user requests to review the letter again while survey is in progrees.
        // Im using the second conditional also cause it sets survey to completed on pre - last question.
        if ($this->hasSurveyInProgress($userEnrollee)
            || $this->hasSurveyCompleted($userEnrollee)) {
            return $this->enrollmentLetterView($userEnrollee, $isSurveyOnlyUser, $enrollee, true);
        }

        // If enrollable is not survey only then create link AND redirect to survey
        if ( ! $isSurveyOnlyUser) {
            $this->expirePastInvitationLink($userEnrollee);

            return $this->createUrlAndRedirectToSurvey($enrollableId);
        }

        // IF $userForEnrollment is empty means that ENROLLEE has already enrolled and his user model is deleted
        if (empty($userEnrollee)
            && $isSurveyOnlyUser
            && ! $enrollee->statusRequestsInfo()->exists()) {
            return 'This enrollment invitation link has expired';
        }

        //Enrolle requested info before, so just taking him to the info
        if ($enrollee->getLastEnrollmentInvitationLink()->manually_expired
            && $enrollee->statusRequestsInfo()->exists()) {
            return $this->returnEnrolleeRequestedInfoMessage($enrollee);
        }

        return $this->enrollmentLetterView($userEnrollee, $isSurveyOnlyUser, $enrollee, false);
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
            'enrollable_id' => $request->input('enrolleeId'), 'survey_instance_id' => $this->getEnrolleesSurveyInstance()->id,
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
     *
     * @return array
     */
    public function getEnrollmentLetter(User $userForEnrollment, $enrollablePrimaryPractice, $isSurveyOnlyUser, $provider = null)
    {
        $practiceLetter = EnrollmentInvitationLetter::where('practice_id', $enrollablePrimaryPractice->id)->firstOrFail();

        // CA's phone numbers is the practice number
        $careAmbassadorPhoneNumber = $enrollablePrimaryPractice->outgoing_phone_number;

        if (null === $provider) {
            $provider = $this->getEnrollableProvider($isSurveyOnlyUser, $userForEnrollment);
        }

        $practiceName = $enrollablePrimaryPractice->name;

        return $this->enrollmentInvitationService->createLetter($practiceName, $practiceLetter, $careAmbassadorPhoneNumber, $provider);
    }

    public function inviteUnreachablesToEnrollTest()
    {
        Artisan::call('command:sendEnrollmentNotifications');

        return redirect()->back();
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rejected(Request $request)
    {
        $enrollee       = Enrollee::find($request->input('enrollee_id'));
        $careAmbassador = auth()->user()->careAmbassador;

        AttachEnrolleeFamilyMembers::attach($request);

        //soft_rejected or rejected
        $status = $request->input('status', Enrollee::REJECTED);

        //update report for care ambassador:
        $report = CareAmbassadorLog::createOrGetLogs($careAmbassador->id);

        if (Enrollee::REJECTED === $status) {
            $report->no_rejected = $report->no_rejected + 1;
        } else {
            $report->no_soft_rejected = $report->no_soft_rejected + 1;
        }

        $report->total_calls          = $report->total_calls + 1;
        $report->total_time_in_system = $request->input('total_time_in_system');
        $report->save();

        $enrollee->last_call_outcome = $request->input('reason');

        if ($request->input('reason_other')) {
            $enrollee->last_call_outcome_reason = $request->input('reason_other');
        }

        $enrollee->care_ambassador_user_id = $careAmbassador->user_id;

        $enrollee->status = $status;

        $enrollee->attempt_count    = $enrollee->attempt_count + 1;
        $enrollee->last_attempt_at  = Carbon::now()->toDateTimeString();
        $enrollee->total_time_spent = $enrollee->total_time_spent + $request->input('time_elapsed');

        $enrollee->save();

        EnrolleeCallQueue::update($careAmbassador, $enrollee, $request->input('confirmed_family_members'));

        return redirect()->route('enrollment-center.dashboard');
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

    public function sendEnrollmentReminderTestMethod()
    {
        try {
            SendEnrollmentPatientsReminder::dispatch();
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
            $invitationable = $url->invitationable()->firstOrFail();

            $isManuallyExpired = $url->manually_expired;


            if ($isManuallyExpired) {
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

    public function training()
    {
        return view('enrollment-ui.training');
    }

    public function triggerEnrollmentSeederTest()
    {
        try {
            Artisan::call('db:seed', ['--class' => 'PrepareDataForReEnrollmentTestSeeder']);
        } catch (\Exception $e) {
            return 'Somethings Wrong. Please try one more time...';
        }

        return 'All good to go...you can go back and refresh the page before you go to next step';
    }

    public function unableToContact(Request $request)
    {
        $enrollee       = Enrollee::find($request->input('enrollee_id'));
        $careAmbassador = auth()->user()->careAmbassador;

        AttachEnrolleeFamilyMembers::attach($request);

        //update report for care ambassador:
        $report                       = CareAmbassadorLog::createOrGetLogs($careAmbassador->id);
        $report->no_utc               = $report->no_utc + 1;
        $report->total_calls          = $report->total_calls + 1;
        $report->total_time_in_system = $request->input('total_time_in_system');
        $report->save();

        $enrollee->last_call_outcome = $request->input('reason');

        if ($request->input('reason_other')) {
            $enrollee->last_call_outcome_reason = $request->input('reason_other');
        }

        $enrollee->care_ambassador_user_id = $careAmbassador->user_id;

        if ('requested callback' == $request->input('reason')) {
            $enrollee->status = Enrollee::TO_CALL;
            if ($request->has('utc_callback')) {
                $enrollee->requested_callback = $request->input('utc_callback');
            }
        } else {
            $enrollee->status = Enrollee::UNREACHABLE;
        }

        $enrollee->attempt_count    = $enrollee->attempt_count + 1;
        $enrollee->last_attempt_at  = Carbon::now()->toDateTimeString();
        $enrollee->total_time_spent = $enrollee->total_time_spent + $request->input('time_elapsed');

        $enrollee->save();

        EnrolleeCallQueue::update($careAmbassador, $enrollee, $request->input('confirmed_family_members'));

        return redirect()->route('enrollment-center.dashboard');
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
        $letterPages               = $this->getEnrollmentLetter($userEnrollee, $enrollablePrimaryPractice, $isSurveyOnlyUser, $provider);
        $practiceName              = $enrollablePrimaryPractice->name;
        $signatoryNameForHeader    = $provider->display_name;
        $dateLetterSent            = Carbon::parse($enrollee->getLastEnrollmentInvitationLink()->updated_at)->toDateString();

        return view('enrollment-consent.enrollmentInvitation', compact('userEnrollee', 'isSurveyOnlyUser', 'letterPages', 'practiceName', 'signatoryNameForHeader', 'dateLetterSent', 'hideButtons'));
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
