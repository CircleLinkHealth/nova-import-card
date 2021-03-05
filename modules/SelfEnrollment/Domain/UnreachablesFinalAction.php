<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SelfEnrollment\Domain;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SelfEnrollment\AbstractSelfEnrollableUserIterator;
use CircleLinkHealth\SelfEnrollment\Helpers;
use CircleLinkHealth\SelfEnrollment\Services\EnrollmentInvitationService;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class UnreachablesFinalAction extends AbstractSelfEnrollableUserIterator
{
    /**
     * Before we put the patient in CA queue, we want to allow 5 days to pass, hoping that the enrollment letter will have reached patients before we call them.
     */
    const TO_CALL_AFTER_DAYS_HAVE_PASSED = 5;
    /**
     * @var Carbon
     */
    protected $dateInviteSent;
    /**
     * @var int|null
     */
    protected $practiceId;
    /**
     * @var EnrollmentInvitationService
     */
    private $service;

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
        //if enrollee has already been assigned to CA do not put back to call_queue
        //because this may result to a CA calling a declined patient again.
        if ( ! empty($patient->enrollee->care_ambassador_user_id)) {
            return;
        }

        if ($this->service()->isUnreachablePatient($patient)) {
            return;
        }

        if ( ! $this->patientHasLoggedIn($patient)) {
            $this->service()->markAsNonResponsive($patient->enrollee);
        }

        $callQueued = $this->service()->putIntoCallQueue($patient->enrollee, now()->addDays(self::TO_CALL_AFTER_DAYS_HAVE_PASSED));

        if ( ! $callQueued) {
            $slackChannel = Helpers::selfEnrollmentSlackLogChannel();
            $errorMessage = "Failed to change self unresponsive self enrollable [user_id:$patient->id] to call_queue status.";
            if ($slackChannel) {
                sendSlackMessage($slackChannel, $errorMessage);
            }
            Log::error($errorMessage);
        }
    }

    public function query(): Builder
    {
        return User::hasSelfEnrollmentInvite($this->dateInviteSent)
            ->hasSelfEnrollmentInviteReminder($this->dateInviteSent->copy()->addDays(2))
            ->hasSelfEnrollmentInviteReminder($this->dateInviteSent->copy()->addDays(4))
            ->whereHas('patientInfo', function ($patient) {
                $patient->where('ccm_status', Patient::UNREACHABLE);
            })->when($this->practiceId, function ($q) {
                return $q->where('program_id', $this->practiceId);
            })->whereHas('enrollee', function ($q) {
               $q->canSendSelfEnrollmentInvitation(false);
            })
            ->uniquePatients()
            ->with('enrollee');
    }

    private function patientHasLoggedIn(User $patient): bool
    {
        return $patient->loginEvents()->exists();
    }

    private function service()
    {
        if (is_null($this->service)) {
            $this->service = app(EnrollmentInvitationService::class);
        }

        return $this->service;
    }
}
