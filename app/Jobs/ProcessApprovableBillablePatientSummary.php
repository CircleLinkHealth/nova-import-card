<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\Repositories\PatientSummaryEloquentRepository;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessApprovableBillablePatientSummary implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    private $summary;

    /**
     * Create a new job instance.
     */
    public function __construct(PatientMonthlySummary $summary)
    {
        $this->summary = $summary;
    }

    /**
     * Execute the job.
     */
    public function handle(PatientSummaryEloquentRepository $repo)
    {
        $summary = $repo->setApprovalStatusAndNeedsQA($this->summary);

        if (is_a($summary, PatientMonthlySummary::class)) {
            $summary->save();
        }
    }
}
