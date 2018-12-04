<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Jobs;

use App\PatientMonthlySummary;
use App\Repositories\PatientSummaryEloquentRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AttachBillableProblemsToSummary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $summary;

    /**
     * Create a new job instance.
     *
     * @param PatientMonthlySummary $summary
     */
    public function __construct(PatientMonthlySummary $summary)
    {
        $this->summary = $summary;
    }

    /**
     * Execute the job.
     *
     * @param PatientSummaryEloquentRepository $repo
     */
    public function handle(PatientSummaryEloquentRepository $repo)
    {
        $summary = $repo->attachBillableProblems($this->summary->patient, $this->summary);

//        commented out on purpose. https://github.com/CircleLinkHealth/cpm-web/issues/1573
//        $summary = $repo->attachChargeableService($summary, null, false);

        if (is_a($summary, PatientMonthlySummary::class)) {
            $summary->save();
        }
    }
}
