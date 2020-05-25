<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Domain;

use App\SelfEnrollment\Jobs\SendSelfEnrollmentReminder;
use App\Traits\EnrollmentReminderShared;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;

class RemindEnrollees extends AbstractUserIterator
{
    use EnrollmentReminderShared;

    public function action(User $user): void
    {
        SendSelfEnrollmentReminder::dispatch($user);
    }

    public function query(): Builder
    {
        return $this->sharedReminderQuery($this->untilEndOfDay, $this->twoDaysAgo)
            ->whereHas('enrollee', function ($enrollee) {
                $enrollee->whereNull('source'); //Eliminates unreachable patients, and only fetches enrollees who have not yet enrolled.
            })->orderBy('created_at', 'asc')
            ->when($this->practiceId, function ($q) {
                return $q->where('program_id', $this->practiceId);
            });
    }
}
