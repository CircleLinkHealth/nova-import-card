<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Domain;

use App\EnrollmentInvitationsBatch;
use App\SelfEnrollment\AbstractSelfEnrollableUserIterator;
use App\SelfEnrollment\Jobs\SendInvitation;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Eloquent\Builder;

class InviteUnreachablePatients extends AbstractSelfEnrollableUserIterator
{
    /**
     * @var int
     */
    private $count;
    private $practiceId;

    public function __construct(int $practiceId, int $count)
    {
        $this->practiceId = $practiceId;
        $this->count      = $count;
    }

    public function action(User $user): void
    {
        $invitationsBatch = EnrollmentInvitationsBatch::create();
        SendInvitation::dispatch($user, $invitationsBatch->id);
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

    protected function limit(): ?int
    {
        return $this->count;
    }
}
