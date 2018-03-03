<?php

namespace App\Jobs;

use App\PatientMonthlySummary;
use App\Repositories\PatientSummaryEloquentRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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
     *
     * @return void
     */
    public function handle(PatientSummaryEloquentRepository $repo)
    {
        $summary = $repo->attachBillableProblems($this->summary->patient, $this->summary);

        $summary = $repo->attachDefaultChargeableService($summary, null, false);

        if (is_a($summary, PatientMonthlySummary::class)) {
            $summary->save();
        }
    }
}
