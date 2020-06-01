<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Domain;

use App\EnrollmentInvitationsBatch;
use App\Http\Controllers\Enrollment\SelfEnrollmentController;
use App\Notifications\Channels\CustomTwilioChannel;
use App\SelfEnrollment\AbstractSelfEnrollableUserIterator;
use App\SelfEnrollment\Jobs\SendInvitation;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
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
     * @var int
     */
    protected $limit;

    /**
     * @var int|mixed
     */
    private $practiceId;

    /**
     * InvitePracticeEnrollees constructor.
     */
    public function __construct(
        int $amount,
        int $practiceId,
        string $color = SelfEnrollmentController::DEFAULT_BUTTON_COLOR,
        array $channels = ['mail', CustomTwilioChannel::class]
    ) {
        $this->limit      = $amount;
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
                $q->whereNull('source')
                // If an enrollmentInvitationLink generated in the last 5 months exists, it means we have already invited the patient,
                // and we do not want to send them another invitation.
                    ->whereDoesntHave('enrollmentInvitationLinks', function ($q) {
                        $q->where('created_at', '>', now()->subMonths(5));
                    })
                    ->whereIn('status', [
                        Enrollee::QUEUE_AUTO_ENROLLMENT,
                    ]);
            });
    }

    private function getBatch(): EnrollmentInvitationsBatch
    {
        if (is_null($this->batch)) {
            $this->batch = EnrollmentInvitationsBatch::firstOrCreateAndRemember($this->practiceId, now()->format(EnrollmentInvitationsBatch::TYPE_FIELD_DATE_HUMAN_FORMAT).':'.$this->color);
        }

        return $this->batch;
    }
}
