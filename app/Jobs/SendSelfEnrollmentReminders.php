<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Helpers\SelfEnrollmentHelpers;
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

class SendSelfEnrollmentReminders implements ShouldQueue
{
    use Dispatchable;
    use EnrollableManagement;
    use EnrollmentReminderShared;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    const REMIND_ENROLLEES            = 'enrollees';
    const REMIND_UNREACHABLE_PATIENTS = 'unreachable_patients';
    /**
     * @var string
     */
    private $groupToRemind;

    /**
     * SendSelfEnrollmentReminders constructor.
     */
    public function __construct(string $groupToRemind)
    {
        $this->groupToRemind = $groupToRemind;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ( ! in_array($this->groupToRemind, [self::REMIND_ENROLLEES, self::REMIND_UNREACHABLE_PATIENTS])) {
            throw new \Exception("Unknown group `{$this->groupToRemind}`. Valid options are `".self::REMIND_UNREACHABLE_PATIENTS.'` and '.self::REMIND_ENROLLEES);
        }

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

        $query = null;

        if (self::REMIND_UNREACHABLE_PATIENTS === $this->groupToRemind) {
            $query = $this->getUnreachablePatientsToSendReminder($untilEndOfDay, $twoDaysAgo, $practiceId);
        } elseif (self::REMIND_ENROLLEES === $this->groupToRemind) {
            $query = $this->getEnrolleeUsersToSendReminder($untilEndOfDay, $twoDaysAgo, $practiceId);
        }

        if (is_null($query)) {
            return;
        }

        $query->chunk(100, function ($users) {
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

    private function getUnreachablePatientsToSendReminder(Carbon $untilEndOfDay, Carbon $twoDaysAgo, ?int $practiceId = null)
    {
        return $this->sharedReminderQuery($untilEndOfDay, $twoDaysAgo)
            ->whereHas('enrollee', function ($enrollee) {
                $enrollee->where('source', '=', Enrollee::UNREACHABLE_PATIENT); //  It's NOT Original enrollee.
            })
            ->when($practiceId, function ($q) use ($practiceId) {
                return $q->where('program_id', $practiceId);
            });
    }
}
