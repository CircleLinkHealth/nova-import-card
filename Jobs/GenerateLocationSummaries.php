<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Processors\Customer\Location;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateLocationSummaries implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $locationId;

    protected Carbon $monthYear;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $locationId, Carbon $monthYear)
    {
        $this->locationId = $locationId;
        $this->monthYear  = $monthYear;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app(Location::class)->processServicesForLocation($this->locationId, $this->monthYear);
    }
}
