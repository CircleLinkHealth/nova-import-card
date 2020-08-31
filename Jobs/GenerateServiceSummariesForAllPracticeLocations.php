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

class GenerateServiceSummariesForAllPracticeLocations implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $month;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Carbon $month)
    {
        $this->month = $month;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Practice::with('locations')
            ->activeBillable()
            ->get()
            ->each(function (Practice $p) {
                foreach ($p->locations as $location) {
                    GenerateLocationSummaries::dispatch($location->id, $this->month);
                }
            });
    }
}
