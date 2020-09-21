<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompareCurrentAndLegacyBillingData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(Carbon $month = null)
    {
        $this->month = $month ?? Carbon::now()->startOfMonth()->startOfDay();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //get all enrolled patients
        //get all services including unfulfilled
        //get PMS

        //compare that foreach CS there's a chargeable Location summary
        //compare that PMS time matches with CPMS View time

        //gather Patient Ids and prompt investigation via slack
    }
}
