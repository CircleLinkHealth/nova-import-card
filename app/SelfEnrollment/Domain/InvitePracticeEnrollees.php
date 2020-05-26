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
    /**
     * @var int
     */
    private $amount;
    /**
     * @var array|string[]
     */
    private $channels;
    /**
     * @var null
     */
    private $color;

    /**
     * The number of patients we dispatched jobs to send invitations to.
     *
     * @var int
     */
    private $dispatched = 0;
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
        $this->amount     = $amount;
        $this->practiceId = $practiceId;
        $this->color      = $color;
        $this->channels   = $channels;
    }

    public function action(User $user): void
    {
        $invitationsBatch = EnrollmentInvitationsBatch::create();
        SendInvitation::dispatch($user, $invitationsBatch->id, $this->color, false, $this->channels);
    }

    public function query(): Builder
    {
        return User::ofPractice($this->practiceId)
            ->ofType('survey-only')
            ->whereHas('enrollee', function ($q) {
                $q->whereNull('source')
                // If an enrollmentInvitationLinks exists, it means we have already invited the patient,
                // and we do not want to send them another invitation.
                    ->whereDoesntHave('enrollmentInvitationLinks')
                    ->whereIn('status', [
                        Enrollee::QUEUE_AUTO_ENROLLMENT,
                    ]);
            });
    }

    protected function limit(): ?int
    {
        return $this->amount;
    }
}
