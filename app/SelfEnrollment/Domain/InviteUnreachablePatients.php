<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Domain;

use App\EnrollmentInvitationsBatch;
use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use App\SelfEnrollment\AbstractSelfEnrollableUserIterator;
use App\SelfEnrollment\Jobs\SendInvitation;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Database\Eloquent\Builder;

class InviteUnreachablePatients extends AbstractSelfEnrollableUserIterator
{
    private $batch;
    /**
     * @var int
     */
    private $count;
    private $practiceId;

    public function __construct(int $practiceId, int $limit)
    {
        $this->practiceId = $practiceId;
        $this->limit      = $limit;
    }

    public function action(User $patient): void
    {
        SendInvitation::dispatch($patient, $this->getBatch()->id);
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

    private function getBatch(): EnrollmentInvitationsBatch
    {
        if (is_null($this->batch)) {
            $this->batch = EnrollmentInvitationsBatch::firstOrCreateAndRemember($this->practiceId, now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.SelfEnrollmentController::DEFAULT_BUTTON_COLOR);
        }

        return $this->batch;
    }
}
