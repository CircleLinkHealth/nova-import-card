<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\SelfEnrollment\Domain;

use CircleLinkHealth\Eligibility\SelfEnrollment\AbstractSelfEnrollableUserIterator;
use CircleLinkHealth\Eligibility\SelfEnrollment\Jobs\SendReminder;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Database\Eloquent\Builder;

class RemindUnreachablePatients extends AbstractSelfEnrollableUserIterator
{
    /**
     * @var Carbon
     */
    protected $dateInviteSent;
    /**
     * @var int|null
     */
    protected $practiceId;

    /**
     * UnreachablesFinalAction constructor.
     */
    public function __construct(Carbon $dateInviteSent, ?int $practiceId = null, ?int $limit = null)
    {
        $this->practiceId     = $practiceId;
        $this->dateInviteSent = $dateInviteSent;
        $this->limit          = $limit;
    }

    public function action(User $patient): void
    {
        SendReminder::dispatch($patient);
    }

    public function query(): Builder
    {
        return User::haveEnrollableInvitationDontHaveReminder($this->dateInviteSent)
            ->ofType('participant')
            ->whereHas('patientInfo', function ($q) {
                $q->where('ccm_status', Patient::UNREACHABLE);
            })
            ->whereHas('enrollee', function ($enrollee) {
                $enrollee->where('status', Enrollee::QUEUE_AUTO_ENROLLMENT)
                    ->where('source', '=', Enrollee::UNREACHABLE_PATIENT);
            })
            ->when($this->practiceId, function ($q) {
                return $q->where('program_id', $this->practiceId);
            });
    }
}
