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
            ->whereHas(
                'carePlan',
                function ($q) {
                    $q->where(function ($q){
                        $q->whereNull('last_auto_qa_attempt_at')
                          ->orWhere(function ($q) {
                              $q->where('last_auto_qa_attempt_at', '<', now()->subHours(2));
                          });
                    })->where(function ($q){
                        $q->whereNull('drafted_at')
                          ->orWhere(function ($q) {
                              $q->where('drafted_at', '>', now()->subDays(1)->startOfDay());
                          });
                    });
                }
            )
            ->ofActiveBillablePractice(false)
            ->orderByDesc('id')
            ->each(function (User $patient) use ($approver) {
                ApproveIfValid::dispatch($patient, $approver);
            });
    }
}
