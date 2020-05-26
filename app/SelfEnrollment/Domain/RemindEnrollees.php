<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Domain;

use App\SelfEnrollment\AbstractSelfEnrollmentReminder;
use App\SelfEnrollment\Jobs\SendReminder;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;

class RemindEnrollees extends AbstractSelfEnrollmentReminder
{
    public function action(User $user): void
    {
        SendReminder::dispatch($user);
    }

    public function query(): Builder
    {
        return User::haveEnrollableInvitationDontHaveReminder($this->dateInviteSent)
            ->whereHas('enrollee', function ($enrollee) {
                $enrollee->whereNull('source'); //Eliminates unreachable patients, and only fetches enrollees who have not yet enrolled.
            })->doesntHave('enrollableInfoRequest')->orderBy('created_at', 'asc')
            ->when($this->practiceId, function ($q) {
                return $q->where('program_id', $this->practiceId);
            });
    }
}
