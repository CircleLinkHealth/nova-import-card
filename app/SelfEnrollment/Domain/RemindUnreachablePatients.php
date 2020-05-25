<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Domain;

use App\Helpers\SelfEnrollmentHelpers;
use CircleLinkHealth\Customer\Contracts\SelfEnrollable;
use App\SelfEnrollment\Jobs\SendSelfEnrollmentReminder;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Eloquent\Builder;

class RemindUnreachablePatients extends AbstractSelfEnrollableModelIterator
{
    public function action(SelfEnrollable $enrollable): void
    {
        SendSelfEnrollmentReminder::dispatch($enrollable);
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
