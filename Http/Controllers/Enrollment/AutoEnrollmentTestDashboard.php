<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers\Enrollment;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\SelfEnrollment\Console\Commands\SendSelfEnrollmentReminders;
use CircleLinkHealth\Eligibility\SelfEnrollment\Domain\InviteUnreachablePatients;
use CircleLinkHealth\Eligibility\SelfEnrollment\Domain\UnreachablesFinalAction;
use CircleLinkHealth\Eligibility\SelfEnrollment\Helpers;
use CircleLinkHealth\Eligibility\SelfEnrollment\Jobs\EnrollableSurveyCompleted;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class AutoEnrollmentTestDashboard extends Controller
{
    public function evaluateEnrolledForSurveyTest(Request $request)
    {
        $survey         = Helpers::getEnrolleeSurvey();
        $surveyInstance = DB::table('survey_instances')->where('survey_id', '=', $survey->id)->first();

        if (is_null($surveyInstance)) {
            throw new \Exception('Could not find survey instance for survey id '.$survey->id);
        }
        $data = [
            'enrollable_id'      => (int) $request->input('enrolleeId'),
            'survey_instance_id' => $surveyInstance->id,
        ];

        EnrollableSurveyCompleted::dispatch($data);
    }

    /**
     * @return string
     */
    public function finalActionTest()
    {
        UnreachablesFinalAction::dispatch(now()->subDays(CpmConstants::DAYS_DIFF_FROM_FIRST_INVITE_TO_FINAL_ACTION));

        return redirect(route('ca-director.index'))->with('message', 'Reminders Sent Successfully');
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
        return view('enrollment-letters.unreachablesInvitationPanel');
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
    public function triggerEnrollmentSeederTest(Request $request)
    {
        try {
            Artisan::call('create:selfEnrollmentTestData', ['practiceName' => $request->input('practice-select')]);
        } catch (\Exception $e) {
            return 'Somethings Wrong. Please try one more time...';
        }

        return 'You can go back and proceed';
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
