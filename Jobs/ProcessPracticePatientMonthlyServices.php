<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Location;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPracticePatientMonthlyServices implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Carbon $chargeableMonth;

    protected int $practiceId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $practiceId, Carbon $chargeableMonth = null)
    {
        $this->practiceId      = $practiceId;
        $this->chargeableMonth = $chargeableMonth ?? Carbon::now()->startOfMonth()->startOfDay();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Location::where('practice_id', $this->practiceId)
            ->each(function (Location $location) {
                ProcessLocationPatientMonthlyServices::dispatch($location->id, $this->chargeableMonth);
            });
    }
}
