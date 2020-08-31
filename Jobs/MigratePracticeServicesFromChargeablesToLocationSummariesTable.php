<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MigratePracticeServicesFromChargeablesToLocationSummariesTable implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Carbon $month;

    protected int $practiceId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $practiceId, Carbon $month)
    {
        $this->practiceId = $practiceId;
        $this->month      = $month;
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
