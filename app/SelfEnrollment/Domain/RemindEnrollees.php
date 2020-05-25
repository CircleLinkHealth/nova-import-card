<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Domain;

use App\Helpers\SelfEnrollmentHelpers;
use App\SelfEnrollment\Jobs\SendSelfEnrollmentReminder;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;

class RemindEnrollees extends AbstractSelfEnrollableModelIterator
{
    public function action(User $user): void
    {
        SendSelfEnrollmentReminder::dispatch($user);
    }

    public function query(): Builder
    {
        return SelfEnrollmentHelpers::enrollableUsersToRemindQuery($this->end, $this->start)
            ->whereHas('enrollee', function ($enrollee) {
                $enrollee->whereNull('source'); //Eliminates unreachable patients, and only fetches enrollees who have not yet enrolled.
            })->orderBy('created_at', 'asc')
            ->when($this->practiceId, function ($q) {
                return $q->where('program_id', $this->practiceId);
            });
    }
}
