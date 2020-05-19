<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

// This file is part of CarePlan Manager by CircleLink Health.

use App\Traits\EnrollableManagement;
use App\Traits\EnrollmentReminderShared;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\AppConfig;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class SelfEnrollmentEnrolleesReminder implements ShouldQueue
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

    public function handle()
    {
        $twoDaysAgo    = Carbon::parse(now())->copy()->subHours(48)->startOfDay()->toDateTimeString();
        $untilEndOfDay = Carbon::parse($twoDaysAgo)->endOfDay()->toDateTimeString();
        $testingMode   = filter_var(AppConfig::pull('testing_enroll_sms', true), FILTER_VALIDATE_BOOLEAN)
        || App::environment('testing');

        if ($testingMode) {
            $practice      = $this->getDemoPractice();
            $twoDaysAgo    = Carbon::parse(now())->startOfDay()->toDateTimeString();
            $untilEndOfDay = Carbon::parse($twoDaysAgo)->copy()->endOfDay()->toDateTimeString();
            $this->getEnrolleUsersToSendReminder($untilEndOfDay, $twoDaysAgo, $practice->id)
                ->each(function (User $enrollable) {
                    SendEnrollmentReminders::dispatch($enrollable);
                });

            return;
        }

        $this->getEnrolleUsersToSendReminder($untilEndOfDay, $twoDaysAgo)
            ->each(function (User $enrollable) {
                SendEnrollmentReminders::dispatch($enrollable);
            });
    }

    /**
     * @param $untilEndOfDay
     * @param $twoDaysAgo
     * @param  int                                                                                                                                                         $practiceId
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection|User[]
     */
    private function getEnrolleUsersToSendReminder($untilEndOfDay, $twoDaysAgo, int $practiceId = null)
    {
        return $this->sharedReminderQuery($untilEndOfDay, $twoDaysAgo)
            ->whereHas('enrollee', function ($enrollee) {
                $enrollee->whereNull('source'); // Is not unreachable patient. It is Original enrollee.
            })->orderBy('created_at', 'asc')
            ->when($practiceId, function ($q) use ($practiceId) {
                return $q->where('program_id', $practiceId);
            })->get();
    }
}
