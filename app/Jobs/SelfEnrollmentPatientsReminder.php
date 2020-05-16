<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Traits\EnrollableManagement;
use App\Traits\EnrollmentReminderShared;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SelfEnrollmentPatientsReminder implements ShouldQueue
{
    use Dispatchable;
    use EnrollableManagement;
    use EnrollmentReminderShared;
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
        $testingMode   = filter_var(AppConfig::pull('testing_enroll_sms', true), FILTER_VALIDATE_BOOLEAN);

        if ($testingMode) {
            $practice      = $this->getDemoPractice();
            $twoDaysAgo    = Carbon::parse(now())->startOfDay()->toDateTimeString();
            $untilEndOfDay = Carbon::parse($twoDaysAgo)->copy()->endOfDay()->toDateTimeString();
            $this->getUnreachablePatientsToSendReminder($untilEndOfDay, $twoDaysAgo)
                ->where('program_id', $practice->id)
                ->get()
                ->each(function (User $enrollable) {
                    SendEnrollmentReminders::dispatch($enrollable);
                });
        } else {
            $this->getUnreachablePatientsToSendReminder($untilEndOfDay, $twoDaysAgo)
                ->get()
                ->each(function (User $enrollable) {
                    SendEnrollmentReminders::dispatch($enrollable);
                });
        }
    }

    private function getUnreachablePatientsToSendReminder($untilEndOfDay, $twoDaysAgo)
    {
        return $this->sharedReminderQuery($untilEndOfDay, $twoDaysAgo)
            ->whereHas('enrollee', function ($enrollee) {
                $enrollee->where('source', '=', Enrollee::UNREACHABLE_PATIENT); //  It's NOT Original enrollee.
            });
    }
}
