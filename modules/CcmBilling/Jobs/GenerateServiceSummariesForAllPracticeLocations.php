<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Core\Jobs\EncryptedLaravelJob as Job;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;

class GenerateServiceSummariesForAllPracticeLocations extends Job implements ShouldBeEncrypted
{
    protected $month;

    /**
     * Create a new job instance.
     */
    public function __construct(Carbon $month = null)
    {
        $this->month = $month ?? Carbon::now()->startOfMonth()->startOfDay();
    }

    public static function fromParameters(string ...$parameters)
    {
        $date = isset($parameters[0]) ? Carbon::parse($parameters[0]) : null;

        return new static($date);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Practice::with('locations')
            ->get()
            ->each(function (Practice $p) {
                foreach ($p->locations as $location) {
                    GenerateLocationSummaries::dispatch($location->id, $this->month);
                }
            });
    }
}
