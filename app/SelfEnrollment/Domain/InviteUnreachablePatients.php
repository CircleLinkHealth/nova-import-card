<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Domain;

use App\EnrollmentInvitationsBatch;
use App\SelfEnrollment\AbstractSelfEnrollableUserIterator;
use App\SelfEnrollment\Jobs\SendInvitation;
use CircleLinkHealth\Customer\Entities\Patient;
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
        return User::ofPractice($this->practiceId)
            ->ofType('participant')
            ->whereHas('patientInfo', function ($q) {
                $q->where('ccm_status', Patient::UNREACHABLE);
            })
            ->whereHas('enrollee', function ($q) {
                $q->where('source', '=', Enrollee::UNREACHABLE_PATIENT)
                    // NOTE: "whereDoesntHave" makes sure we dont invite Unreachable/Non responded - Users (Patients) second time
                    ->whereDoesntHave('enrollmentInvitationLinks')
                    ->whereIn('status', [
                        Enrollee::QUEUE_AUTO_ENROLLMENT,
                    ]);
            });
    }

    protected function limit(): ?int
    {
        return $this->count;
    }
}
