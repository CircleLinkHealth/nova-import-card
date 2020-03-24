<?php

namespace App\Jobs;
/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */


use App\Notifications\SendEnrollementSms;
use App\Notifications\SendEnrollmentEmail;
use App\Traits\EnrollableManagement;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class SendEnrollmentPatientsReminder implements ShouldQueue
{
    use Dispatchable;
    use EnrollableManagement;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $twoDaysAgo = Carbon::parse(now())->copy()->subHours(48)->startOfDay()->toDateTimeString();
        $untilEndOfDay = Carbon::parse($twoDaysAgo)->endOfDay()->toDateTimeString();
        $testingMode = App::environment(['review', 'local']);

        if ($testingMode) {
            $twoDaysAgo = Carbon::parse(now())->startOfMonth()->toDateTimeString();
            $untilEndOfDay = Carbon::parse($twoDaysAgo)->copy()->endOfMonth()->toDateTimeString();
        }

        User::whereHas('notifications', function ($notification) use ($untilEndOfDay, $twoDaysAgo) {
            $notification->where([
                ['created_at', '>=', $twoDaysAgo],
                ['created_at', '<=', $untilEndOfDay],
            ])->where('type', SendEnrollmentEmail::class);
        })
//            If still unreachable means user did not choose to "Enroll Now" in invitation mail.
            ->whereHas('patientInfo', function ($patient) use ($twoDaysAgo, $untilEndOfDay) {
                $patient->where('ccm_status', 'unreachable')->where([
                    ['date_unreachable', '>=', $twoDaysAgo],
                    ['date_unreachable', '<=', $untilEndOfDay],
                ]);
            })
            ->get()
            ->each(function (User $enrollable) {
                $isSurveyOnly = $enrollable->checkForSurveyOnlyRole();
//                Currently only Enrollees can request Info but ... covers both.
                $hasRequestedInfoOnInvitation = $isSurveyOnly
                    ? Enrollee::whereUserId($enrollable->id)->first()->statusRequestsInfo()->exists()
                    : $enrollable->statusRequestsInfo()->exists();

                // $hasRequestedInfoOnInvitation & hasSurveyCompleted should never be used for 'surveyOnlyUsers' cause if
                // patient requested info or completed survey the user model would be deleted, hence it will never be collected
                if (!$hasRequestedInfoOnInvitation || !$this->hasSurveyInProgress($enrollable) || $this->hasSurveyCompleted($enrollable)) {
                    $enrollable->notify(new SendEnrollmentEmail($enrollable, true));
//                    $enrollable->notify(new SendEnrollementSms($enrollable, true));
                }
            });
    }
}
