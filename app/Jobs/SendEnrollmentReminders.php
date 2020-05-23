<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Traits\EnrollableManagement;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEnrollmentReminders implements ShouldQueue
{
    use Dispatchable;
    use EnrollableManagement;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var User
     */
    public $enrollable;

    /**
     * Create a new job instance.
     */
    public function __construct(User $enrollable)
    {
        $this->enrollable = $enrollable;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $enrollable = $this->enrollable;

        if ($enrollable->isSurveyOnly()) {
            $enrollable = Enrollee::where('user_id', $this->enrollable->id)->firstOrFail();
        }

        if ( ! $enrollable) {
            Log::critical("Cannot find user or enrollee[$enrollable->user_id]. Will not send enrollment email.");

            return;
        }

        if ($enrollable->statusRequestsInfo()->exists()) {
            return;
        }
        if ($this->hasCompletedSelfEnrollmentSurvey($this->enrollable)) {
            return;
        }
        
        SendSelfEnrollmentInvitationToEligiblePatient::dispatch($this->enrollable);
    }
}
