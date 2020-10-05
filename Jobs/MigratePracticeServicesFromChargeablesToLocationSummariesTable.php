<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use MichaelLedin\LaravelJob\Job;

class MigratePracticeServicesFromChargeablesToLocationSummariesTable extends Job
{
    protected Carbon $month;

    protected int $practiceId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $practiceId, Carbon $month = null)
    {
        $this->practiceId = $practiceId;
        $this->month      = $month ?? Carbon::now()->startOfMonth()->startOfDay();
    }

    public static function fromParameters(string ...$parameters)
    {
        $date = isset($parameters[1]) ? Carbon::parse($parameters[1]) : null;

        return new static((int) $parameters[0], $date);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $practice = Practice::with(['chargeableServices', 'locations'])
            ->findOrFail($this->practiceId);

        $chargeableServices = $practice->chargeableServices;

        if ($chargeableServices->isEmpty()) {
            return;
        }

        $toCreate = $chargeableServices->transform(function (ChargeableService $cs) {
            return [
                'chargeable_service_id' => $cs->id,
                'chargeable_month'      => $this->month,
            ];
        })
            ->filter()
            ->toArray();

        $practice->locations->each(function (Location $location) use ($toCreate) {
            $location->chargeableServiceSummaries()->createMany($toCreate);
        });
    }
}
