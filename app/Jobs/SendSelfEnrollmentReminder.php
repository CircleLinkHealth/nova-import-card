<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Helpers\SelfEnrollmentHelpers;
use App\Traits\EnrollableManagement;
use CircleLinkHealth\Customer\EnrollableRequestInfo\EnrollableRequestInfo;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
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

        $enrollee = Enrollee::where('user_id', $this->patient->id)->with('statusRequestsInfo', 'enrollmentInvitationLinks')->has('enrollmentInvitationLinks')->firstOrFail();

        if ($enrollee->statusRequestsInfo instanceof EnrollableRequestInfo) {
            return;
        }

        if ($enrollee->enrollmentInvitationLinks->count() > 1) {
            return;
        }

        $invitation = $enrollee->enrollmentInvitationLinks->first();

        SendSelfEnrollmentInvitationToEligiblePatient::dispatch($this->patient, optional($invitation)->button_color, true);
    }
}
