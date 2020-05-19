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
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

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

//        Temporary. Need a solution here. This class is triggered by a scheduled command.
        $practice = Practice::where('name', '=', 'Commonwealth Pain & Spine')->first();

        if (empty($practice) && ! $testingMode) {
            Log::critical('Practice with name [Commonwealth Pain & Spine] does not exist.');

            return;
        }

        if ($testingMode) {
            $practice                = $this->getDemoPractice();
            $twoDaysAgo              = Carbon::parse(now())->startOfDay()->toDateTimeString();
            $untilEndOfDay           = Carbon::parse($twoDaysAgo)->copy()->endOfDay()->toDateTimeString();
            $enrolleesToSendReminder = $this->getEnrolleUsersToSendReminder($untilEndOfDay, $twoDaysAgo, $practice->id);
            if (empty($enrolleesToSendReminder)) {
                Log::info('There are no Enrollees to send reminders to.');

                return;
            }
            $enrolleesToSendReminder->each(function (User $enrollable) {
                SendEnrollmentReminders::dispatch($enrollable);
            });

            return;
        }

        $enrolleesToSendReminder = $this->getEnrolleUsersToSendReminder($untilEndOfDay, $twoDaysAgo, $practice->id);
        if (empty($enrolleesToSendReminder)) {
            Log::info('There are no Enrollees to send reminders to.');

            return;
        }
        $enrolleesToSendReminder->each(function (User $enrollable) {
            SendEnrollmentReminders::dispatch($enrollable);
        });
    }

    /**
     * @param $untilEndOfDay
     * @param $twoDaysAgo
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection|User[]
     */
    private function getEnrolleUsersToSendReminder($untilEndOfDay, $twoDaysAgo, int $practiceId)
    {
        return  $this->sharedReminderQuery($untilEndOfDay, $twoDaysAgo)
            ->whereHas('enrollee', function ($enrollee) {
                $enrollee->whereNull('source')  // Is not unreachable patient. It is Original enrollee.
                    ->where('status', Enrollee::QUEUE_AUTO_ENROLLMENT); // Has not yet enrolled or requested info
            })->orderBy('created_at', 'asc')
            ->when($practiceId, function ($q) use ($practiceId) {
                return $q->where('program_id', $practiceId);
            })->get();
    }
}
