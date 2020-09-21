<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Practice;
use MichaelLedin\LaravelJob\Job;

class CheckLocationSummariesHaveBeenCreated extends Job
{
    protected Carbon $month;

    public function __construct(Carbon $month = null)
    {
        $this->month = $month;
    }

    public static function fromParameters(string ...$parameters)
    {
        $date = isset($parameters[0]) ? Carbon::parse($parameters[0]) : null;

        return new static($date);
    }

    public function getMonth(): Carbon
    {
        return $this->month;
    }

    public function handle()
    {
        Practice::activeBillable()
            ->with(['locations.chargeableServiceSummaries' => fn ($summary) => $summary->createdOn($this->getMonth(), 'chargeable_month')])
            ->get()
            ->each(function (Practice $practice) {
                foreach ($practice->locations as $location) {
                    if ($location->chargeableServiceSummaries->isEmpty()) {
                        GenerateLocationSummaries::dispatch($location->id, $this->getMonth());
                    }
                }
            });
    }
}
