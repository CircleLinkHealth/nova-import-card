<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Domain;

use App\SelfEnrollment\Jobs\SendSelfEnrollmentInvitation;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Eloquent\Builder;

class InviteUnreachablePatients extends AbstractSelfEnrollableModelIterator
{
    public function action(User $userModel): void
    {
        SendSelfEnrollmentInvitation::dispatch($userModel->user);
    }

    public function query(): Builder
    {
        return Enrollee::whereNotNull('user_id')
            ->has('user')
            ->with('user')
            ->where('source', '=', Enrollee::UNREACHABLE_PATIENT)
            ->where('practice_id', $this->practiceId)
            // NOTE: "whereDoesntHave" makes sure we dont invite Unreachable/Non responded - Users (Patients) second time
            ->whereDoesntHave('enrollmentInvitationLinks')
            ->whereIn('status', [
                Enrollee::QUEUE_AUTO_ENROLLMENT,
            ])
            ->orderBy('id', 'asc');
    }
}
