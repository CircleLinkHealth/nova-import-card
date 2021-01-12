<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\AutoCarePlanQAApproval;

use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ConsentedEnrollees implements ShouldQueue
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
        $this->consentedEnrollees()->orderBy('consented_at')
            ->each(function (Enrollee $enrollee) {
                ImportAndApproveEnrollee::dispatch($enrollee);
            });
    }

    private function consentedEnrollees()
    {
        return Enrollee::where('status', Enrollee::CONSENTED)
            ->whereHas('practice', function ($q) {
                $q->activeBillable()->whereIsDemo(0);
            })->with('user.patientInfo');
    }
}
