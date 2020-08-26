<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\AutoCarePlanQAApproval;

use CircleLinkHealth\Customer\AppConfig\CarePlanAutoApprover;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Patients implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @param \CircleLinkHealth\Eligibility\ProcessEligibilityService $importService
     */
    public function handle()
    {
        User::patientsPendingCLHApproval($approver = CarePlanAutoApprover::user())
            ->ofActiveBillablePractice(false)
            ->orderByDesc('id')
            ->each(function (User $patient) use ($approver) {
                ApproveIfValid::dispatch($patient, $approver);
            });
    }
}
