<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

// This file is part of CarePlan Manager by CircleLink Health.

use App\Notifications\SendEnrollmentEmail;
use App\Traits\EnrollableManagement;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class SelfEnrollmentPatientsReminder implements ShouldQueue
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
        $twoDaysAgo    = Carbon::parse(now())->copy()->subHours(48)->startOfDay()->toDateTimeString();
        $untilEndOfDay = Carbon::parse($twoDaysAgo)->endOfDay()->toDateTimeString();
        $testingEnv    = App::environment(['testing']);
        //@todo:Change later to staging.
        if ( ! $testingEnv) {
            $practice      = $this->getDemoPractice();
            $twoDaysAgo    = Carbon::parse(now())->startOfDay()->toDateTimeString();
            $untilEndOfDay = Carbon::parse($twoDaysAgo)->copy()->endOfDay()->toDateTimeString();
            $this->getUsersToSendReminder($untilEndOfDay, $twoDaysAgo)
                ->where('program_id', $practice->id)
                ->get()
                ->each(function (User $enrollable) {
                    SendEnrollmentReminders::dispatch($enrollable);
                });
        } else {
            $this->getUsersToSendReminder($untilEndOfDay, $twoDaysAgo)
                ->get()
                ->each(function (User $enrollable) {
                    SendEnrollmentReminders::dispatch($enrollable);
                });
        }
    }

    private function getUsersToSendReminder($untilEndOfDay, $twoDaysAgo)
    {
        return  User::whereHas('notifications', function ($notification) use ($untilEndOfDay, $twoDaysAgo) {
            $notification
                ->where('data->is_reminder', false)
                ->where([
                    ['created_at', '>=', $twoDaysAgo],
                    ['created_at', '<=', $untilEndOfDay],
                ])->where('type', SendEnrollmentEmail::class);
        })

//            If still unreachable means user did not choose to "Enroll Now" in invitation mail.
            ->whereHas('patientInfo', function ($patient) use ($twoDaysAgo, $untilEndOfDay) {
                $patient->where('ccm_status', Patient::UNREACHABLE)->where([
                    ['date_unreachable', '>=', $twoDaysAgo],
                    ['date_unreachable', '<=', $untilEndOfDay],
                ]);
            });
    }
}
