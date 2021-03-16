<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Core\Jobs\EncryptedLaravelJob as Job;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;

class CheckLocationSummariesHaveBeenCreated extends Job implements ShouldBeEncrypted
{
    protected Carbon $month;

    public function __construct(Carbon $month)
    {
        $this->month = $month;
    }

    public static function fromParameters(string ...$parameters)
    {
        return new static(Carbon::parse($parameters[0]));
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
