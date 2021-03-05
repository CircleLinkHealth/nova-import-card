<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Domain;

use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SelfEnrollment\AbstractSelfEnrollableUserIterator;
use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationsBatch;
use CircleLinkHealth\SelfEnrollment\Http\Controllers\SelfEnrollmentController;
use CircleLinkHealth\SelfEnrollment\Jobs\SendInvitation;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Database\Eloquent\Builder;

class InvitePracticeEnrollees extends AbstractSelfEnrollableUserIterator
{
    private $batch;
    /**
     * @var array|string[]
     */
    private $channels;
    /**
     * @var null
     */
    private $color;
    /**
     * @var int|mixed
     */
    private $practiceId;

    /**
     * InvitePracticeEnrollees constructor.
     */
    public function __construct(
        int $limit,
        int $practiceId,
        string $color = SelfEnrollmentController::DEFAULT_BUTTON_COLOR,
        array $channels = ['mail', 'twilio']
    ) {
        $this->limit      = $limit;
        $this->practiceId = $practiceId;
        $this->color      = $color;
        $this->channels   = $channels;
    }

    public function action(User $patient): void
    {
        SendInvitation::dispatch($patient, $this->getBatch()->id, $this->color, false, $this->channels);
    }

    public function query(): Builder
    {
        return User::ofPractice($this->practiceId)
            ->ofType('survey-only')
            ->whereHas('enrollee', function ($q) {
                $q->canSendSelfEnrollmentInvitation(true);
            })->uniquePatients();
    }

    private function getBatch(): EnrollmentInvitationsBatch
    {
        if (is_null($this->batch)) {
            $this->batch = EnrollmentInvitationsBatch::firstOrCreateAndRemember($this->practiceId, now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.$this->color);
        }

        return $this->batch;
    }
}
