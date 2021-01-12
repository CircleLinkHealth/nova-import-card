<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers\Enrollment;

use CircleLinkHealth\CpmAdmin\Console\Commands\ManuallyCreateEnrollmentTestData;
use CircleLinkHealth\Eligibility\SelfEnrollment\Constants;
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
        UnreachablesFinalAction::dispatch(now()->subDays(Constants::DAYS_DIFF_FROM_FIRST_INVITE_TO_FINAL_ACTION));

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
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sendInvitesPanelTest()
    {
        return view('selfEnrollment.unreachablesInvitationPanel');
    }

    /**
     * @return string
     */
    public function triggerEnrollmentSeederTest(Request $request)
    {
        try {
            Artisan::call(ManuallyCreateEnrollmentTestData::class, ['practiceName' => $request->input('practice-select')]);
        } catch (\Exception $e) {
            return 'Somethings Wrong. Please try one more time...';
        }

        return 'You can go back and proceed';
    }
}
