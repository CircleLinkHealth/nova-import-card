<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

// This file is part of CarePlan Manager by CircleLink Health.

use App\Http\Controllers\Enrollment\AutoEnrollmentCenterController;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSelfEnrollmentInvitationToPracticeEnrollees implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const SURVEY_ONLY = 'survey-only';
    /**
     * @var int
     */
    private $amount;
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
     * @var Role
     */
    private $surveyRole;

    /**
     * SendSelfEnrollmentInvitationToPracticeEnrollees constructor.
     */
    public function __construct(
        int $amount,
        int $practiceId,
        string $color = AutoEnrollmentCenterController::DEFAULT_BUTTON_COLOR
    ) {
        $this->amount     = $amount;
        $this->practiceId = $practiceId;
        $this->color      = $color;
    }

    /**
     * Execute the job.
     *
     * @param Enrollee|null $enrollee
     *
     * @return void
     */
    public function handle()
    {
        $this->getEnrollees($this->practiceId)
            ->orderBy('id', 'asc')
            ->whereNotNull('user_id')
            ->has('user')
            ->with('user')
            ->select(['user_id'])
            ->chunk(100, function ($enrollees) {
                foreach ($enrollees as $enrollee) {
                    SendSelfEnrollmentInvitationToEligiblePatient::dispatch($enrollee->user, $this->color);

                    if (++$this->dispatched === $this->amount) {
                        //break chunking
                        return false;
                    }
                }
            });
    }

    /**
     * Get the Enrollees to invite.
     *
     * @param $practiceId
     *
     * @return Builder
     */
    private function getEnrollees($practiceId)
    {
        return Enrollee::where('practice_id', $practiceId)
            ->whereNull('source')
            // If an enrollmentInvitationLinks exists, it means we have already invited the patient,
            // and we do not want to send them another invitation.
            ->whereDoesntHave('enrollmentInvitationLinks')
            ->whereIn('status', [
                Enrollee::QUEUE_AUTO_ENROLLMENT,
            ]);
    }
}
