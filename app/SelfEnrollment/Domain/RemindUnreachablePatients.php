<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Domain;

use App\SelfEnrollment\Jobs\SendSelfEnrollmentReminder;
use App\Traits\EnrollmentReminderShared;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Eloquent\Builder;

class RemindUnreachablePatients extends AbstractUserIterator
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
                $enrollee->where('status', Enrollee::QUEUE_AUTO_ENROLLMENT)
                    ->where('source', '=', Enrollee::UNREACHABLE_PATIENT);
            })
            ->when($this->practiceId, function ($q) {
                return $q->where('program_id', $this->practiceId);
            });
    }
}
