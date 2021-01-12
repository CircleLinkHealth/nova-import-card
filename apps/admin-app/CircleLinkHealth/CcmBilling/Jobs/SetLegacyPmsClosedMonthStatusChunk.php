<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use Illuminate\Database\Eloquent\Builder;

class SetLegacyPmsClosedMonthStatusChunk extends ChunksEloquentBuilderJob
{
    protected Carbon $month;

    protected int $practiceId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $practiceId, Carbon $month)
    {
        $this->practiceId = $practiceId;
        $this->month      = $month;
    }

    public function getBuilder(): Builder
    {
        return PatientMonthlySummary::with('patient.patientInfo')
            ->whereHas('patient', fn ($p) => $p->ofPractice($this->practiceId)->has('patientInfo'))
            ->where('month_year', $this->month)
            ->offset($this->getOffset())
            ->limit($this->getLimit());
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->getBuilder()->each(function (PatientMonthlySummary $summary) {
            $summary->closed_ccm_status = $summary->patient->patientInfo->ccm_status;
            $summary->save();
        });
    }
}
