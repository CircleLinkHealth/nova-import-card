<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Enrollment;

use App\EnrollableInvitationLink;
use App\Http\Controllers\Controller;
use App\Jobs\EnrollableSurveyCompleted;
use App\Jobs\FinalActionOnNonResponsivePatients;
use App\Jobs\SelfEnrollmentEnrollees;
use App\Jobs\SelfEnrollmentPatientsReminder;
use App\Jobs\SelfEnrollmentUnreachablePatients;
use App\LoginLogout;
use App\Notifications\SendEnrollmentEmail;
use App\Services\Enrollment\EnrollmentInvitationService;
use App\Traits\EnrollableManagement;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class AutoEnrollmentTestDashboard extends Controller
{
    use EnrollableManagement;

    public function evaluateEnrolledForSurveyTest(Request $request, AutoEnrollmentCenterController $autoEnrollmentCenterController)
    {
        $data = [
            'enrollable_id'      => $request->input('enrolleeId'),
            'survey_instance_id' => $autoEnrollmentCenterController->getEnrolleesSurveyInstance()->id,
        ];

        EnrollableSurveyCompleted::dispatch($data);

        return 'enrolled successfully';
    }

    public function finalActionTest()
    {
        FinalActionOnNonResponsivePatients::dispatch(new EnrollmentInvitationService());

        return 'Done!';
    }

    public function inviteEnrolleesToEnrollTest()
    {
        SelfEnrollmentEnrollees::dispatchNow();

        return redirect()->back();
    }

    public function inviteUnreachablesToEnrollTest()
    {
        SelfEnrollmentUnreachablePatients::dispatchNow();

        return redirect()->back();
    }

    public function resetEnrollmentTest(AutoEnrollmentCenterController $autoEnrollmentCenterController)
    {
        // TEST ONLY
        $users = User::withTrashed()
            ->with('notifications', 'patientInfo')
            ->whereHas('notifications', function ($notification) {
                $notification->where('type', SendEnrollmentEmail::class);
            })->where('created_at', '>', Carbon::parse(now())->startOfMonth())
            ->whereHas('patientInfo')
            ->get();

        $survey = $autoEnrollmentCenterController->getEnrolleeSurvey();

        foreach ($users as $user) {
            $surveyInstance = DB::table('survey_instances')
                ->where('survey_id', '=', $survey->id)
                ->first();

            if ($user->checkForSurveyOnlyRole()) {
                /** @var Enrollee $enrollee */
                $enrollee = $autoEnrollmentCenterController->getEnrollee($user->id);
                $autoEnrollmentCenterController->getAwvUserSurvey($user, $surveyInstance)->delete();
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

                $hasLoggedIn = $autoEnrollmentCenterController->hasViewedLetterOrSurvey($user->id);
                //                Just for live testing so i can reset the test
                if ($hasLoggedIn) {
                    LoginLogout::where('user_id', $user->id)->delete();
                }
                $user->forceDelete();
            } else {
                $autoEnrollmentCenterController->getAwvUserSurvey($user, $surveyInstance)->delete();
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

        return 'You can go back and proceed to Step 2.';
    }
}
