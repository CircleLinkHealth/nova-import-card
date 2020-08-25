<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAllPracticePatientMonthlyServices implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Carbon $chargeableMonth;

    protected bool $fulfill;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Carbon $chargeableMonth, bool $fulfill)
    {
        $this->chargeableMonth = $chargeableMonth;
        $this->fulfill         = $fulfill;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Practice::activeBillable()
            ->chunk(10, function ($practices) {
                foreach ($practices as $practice) {
                    ProcessPracticePatientMonthlyServices::dispatch($practice->id, $this->chargeableMonth, $this->fulfill);
                }
            });
    }
}
