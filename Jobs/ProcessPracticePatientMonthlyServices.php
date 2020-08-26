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

    protected bool $fulfill;

    protected int $practiceId;
    
    /**
     * Create a new job instance.
     *
     * @param int $practiceId
     * @param Carbon $chargeableMonth
     * @param bool $fulfill
     */
    public function __construct(int $practiceId, Carbon $chargeableMonth, bool $fulfill)
    {
        $this->practiceId      = $practiceId;
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
        Location::where('practice_id', $this->practiceId)
            ->get()
            ->each(function (Location $location) {
                ProcessLocationPatientMonthlyServices::dispatch($location->id, $this->chargeableMonth, $this->fulfill);
            });
    }
}
