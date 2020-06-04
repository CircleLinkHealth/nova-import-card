<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\Console\Commands\SendSelfEnrollmentReminders;
use App\Http\Controllers\Controller;
use App\SelfEnrollment\Constants;
use App\SelfEnrollment\Domain\InvitePracticeEnrollees;
use App\SelfEnrollment\Domain\InviteUnreachablePatients;
use App\SelfEnrollment\Domain\UnreachablesFinalAction;
use App\SelfEnrollment\Helpers;
use Carbon\Carbon;
use CircleLinkHealth\Customer\EnrollableInvitationLink\EnrollableInvitationLink;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class AutoEnrollmentTestDashboard extends Controller
{
    /**
     * @return string
     */
    public function finalActionTest()
    {
        UnreachablesFinalAction::dispatch(now()->subDays(Constants::DAYS_DIFF_FROM_FIRST_INVITE_TO_FINAL_ACTION));

        return redirect(route('ca-director.index'))->with('message', 'Reminders Sent Successfully');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function inviteEnrolleesToEnrollTest(Request $request)
    {
        InvitePracticeEnrollees::dispatch(
            $request->input('amount'),
            $request->input('practice_id'),
            $request->input('color')
        );

        return redirect()->back()->with('message', 'Invited Successfully');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function inviteUnreachablesToEnrollTest(Request $request)
    {
        InviteUnreachablePatients::dispatch(
            $request->input('practice_id'),
            $request->input('amount')
        );

        return redirect()->back()->with('message', 'Invited Successfully');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetEnrollmentTest()
    {
        $practice = $this->getDemoPractice();
        // TEST ONLY
        $users = User::withTrashed()
            ->with('notifications', 'patientInfo', 'enrollee')
            ->where('program_id', '=', $practice->id)
            ->hasSelfEnrollmentInvite()
            ->where('created_at', '>', Carbon::parse(now())->startOfMonth())
            ->whereHas('patientInfo')
            ->get();

        $survey = Helpers::getEnrolleeSurvey();

        foreach ($users as $user) {
            $surveyInstance = DB::table('survey_instances')
                ->where('survey_id', '=', $survey->id)
                ->first();

            if ($user->isSurveyOnly()) {
                $this->deleteTestAwvUser($user, $surveyInstance);
                $user->notifications()->delete();
                $user->enrollee->enrollmentInvitationLinks()->delete();
                $user->enrollee->enrollableInfoRequest()->delete();

                DB::table('invitation_links')
                    ->where('patient_info_id', $user->patientInfo()->withTrashed()->first()->id)
                    ->delete();

                DB::table('answers')
                    ->where('user_id', $user->id)
                    ->where('survey_instance_id', $surveyInstance->id)
                    ->delete();
            } else {
                $this->deleteTestAwvUser($user, $surveyInstance);
                $user->notifications()->delete();
                $user->enrollmentInvitationLinks()->delete();
                $user->enrollableInfoRequest()->delete();
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
     * @return string
     */
    public function sendEnrolleesReminderTestMethod()
    {
        try {
            SendSelfEnrollmentReminders::dispatchEnrolleeReminders($this->getDemoPractice()->id);
        } catch (\Exception $e) {
            return 'Something went wrong';
        }

        return 'Please check your email';
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
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

    public function sendPatientsReminderTestMethod()
    {
//        try {
//            SelfEnrollmentEnrolleesReminder::dispatch();
//        } catch (\Exception $e) {
//            return 'Something went wrong';
//        }
//
//        return 'Please check your email';
    }

    /**
     * @return string
     */
    public function triggerEnrollmentSeederTest()
    {
        try {
            Artisan::call('db:seed', ['--class' => 'PrepareDataForReEnrollmentTestSeeder']);
        } catch (\Exception $e) {
            return 'Somethings Wrong. Please try one more time...';
        }

        return 'You can go back and proceed to Step 2.';
    }

    private function deleteTestAwvUser(User $user, $surveyInstance)
    {
        if ( ! Helpers::awvUserSurveyQuery($user, $surveyInstance)->exists()) {
            return;
        }

        Helpers::awvUserSurveyQuery($user, $surveyInstance)->delete();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    private function getDemoPractice()
    {
        return \Cache::remember('demo_practice_object', 2, function () {
            return Practice::where('name', '=', 'demo')->firstOrFail();
        });
    }
}
