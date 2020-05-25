<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Domain;

use App\Helpers\SelfEnrollmentHelpers;
use App\SelfEnrollment\Jobs\SendReminder;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Eloquent\Builder;

class RemindUnreachablePatients extends SelfEnrollmentReminder
{
    public function action(User $user): void
    {
        SendReminder::dispatch($user);
    }

    public function query(): Builder
    {
        return SelfEnrollmentHelpers::enrollableUsersToRemindQuery($this->end, $this->start)
            ->whereHas('enrollee', function ($enrollee) {
                $enrollee->where('status', Enrollee::QUEUE_AUTO_ENROLLMENT)
                    ->where('source', '=', Enrollee::UNREACHABLE_PATIENT);
            })
            ->when($this->practiceId, function ($q) {
                return $q->where('program_id', $this->practiceId);
            });
    }
}
