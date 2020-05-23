<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

// This file is part of CarePlan Manager by CircleLink Health.

use App\Helpers\SelfEnrollmentHelpers;
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
        $testingMode = filter_var(AppConfig::pull('testing_enroll_sms', true), FILTER_VALIDATE_BOOLEAN) || App::environment('testing');

        if ($testingMode) {
            $practiceId    = SelfEnrollmentHelpers::getDemoPractice()->id;
            $twoDaysAgo    = now()->startOfDay();
            $untilEndOfDay = $twoDaysAgo->copy()->endOfDay();
        } else {
            $practiceId    = null;
            $twoDaysAgo    = now()->copy()->subHours(48)->startOfDay();
            $untilEndOfDay = $twoDaysAgo->copy()->endOfDay();
        }

        $this->getEnrolleeUsersToSendReminder($untilEndOfDay, $twoDaysAgo, $practiceId)
            ->chunk(100, function ($users) {
                $users->each(function (User $enrollable) {
                    SendSelfEnrollmentReminder::dispatch($enrollable);
                });
            });
    }

    private function getEnrolleeUsersToSendReminder(Carbon $untilEndOfDay, Carbon $twoDaysAgo, ?int $practiceId = null)
    {
        return $this->sharedReminderQuery($untilEndOfDay, $twoDaysAgo)
            ->whereHas('enrollee', function ($enrollee) {
                $enrollee->whereNull('source'); //Eliminates unreachable patients, and only fetches enrollees who have not yet enrolled.
            })->orderBy('created_at', 'asc')
            ->when($practiceId, function ($q) use ($practiceId) {
                return $q->where('program_id', $practiceId);
            });
    }
}
