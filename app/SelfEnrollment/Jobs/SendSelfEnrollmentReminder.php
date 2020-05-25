<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\SelfEnrollment\Jobs;

use App\Helpers\SelfEnrollmentHelpers;
use CircleLinkHealth\Customer\EnrollableRequestInfo\EnrollableRequestInfo;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSelfEnrollmentReminder implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var User
     */
    public $patient;

    /**
     * Create a new job instance.
     */
    public function __construct(User $patient)
    {
        $this->patient = $patient;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (SelfEnrollmentHelpers::hasCompletedSelfEnrollmentSurvey($this->patient)) {
            return;
        }

        $this->patient->loadMissing(['enrollee.statusRequestsInfo', 'enrollee.enrollmentInvitationLinks']);

        if (empty($this->patient->enrollee)) {
            throw new \Exception("user[{$this->patient->id}] does not have an enrollee.");
        }

        if ($this->patient->enrollee->statusRequestsInfo instanceof EnrollableRequestInfo) {
            return;
        }

        if ($this->patient->enrollee->enrollmentInvitationLinks->count() > 1) {
            return;
        }

        $invitation = $this->patient->enrollee->enrollmentInvitationLinks->first();

        SendSelfEnrollmentInvitation::dispatch($this->patient, optional($invitation)->button_color, true);
    }
}
