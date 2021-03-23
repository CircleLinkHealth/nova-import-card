<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetLegacyPmsClosedMonthStatus implements ShouldQueue, ShouldBeEncrypted
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        PatientMonthlySummary::whereHas('patient', fn ($p) => $p->ofPractice($this->practiceId)->has('patientInfo'))
            ->where('month_year', $this->month)
            ->chunkIntoJobs(100, new SetLegacyPmsClosedMonthStatusChunk($this->practiceId, $this->month));
    }
}
