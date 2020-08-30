<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Jobs;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\Customer\Entities\Location;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutoAttachServicesToNewLocation implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $locationId;

    protected Carbon $month;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $locationId)
    {
        $this->locationId = $locationId;
        $this->month      = Carbon::now()->startOfMonth()->startOfDay();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //todo: file in progress, subject to change after dev or business side input
        $location = Location::with(['practice.locations' => function ($location) {
            $location->with(['chargeableServiceSummaries' => function ($summary) {
                $summary->with(['chargeableService'])
                    ->createdOn($this->month, 'chargeable_month');
            }])
                ->whereHas('chargeableServiceSummaries', function ($summary) {
                    $summary->createdOn($this->month, 'chargeable_month');
                });
        }])
            ->findOrFail($this->locationId);

        $practiceLocations = $location->practice->locations;

        if (1 === $practiceLocations->count()) {
            sendSlackMessage('#channel-to-decide', 'Please head to page {url} and assign chargeable services to practice or location.');

            return;
        }

        $locationToCopy = $this->getLocationToCopyServicesFrom($practiceLocations);

        $toCreate = $locationToCopy->chargeableServiceSummaries->map(function (ChargeableLocationMonthlySummary $summary) {
            return [
                'chargeable_service_id' => $summary->chargeableService->id,
                'location_id'           => $this->locationId,
                'chargeable_month'      => $this->month,
            ];
        });

        ChargeableLocationMonthlySummary::insert($toCreate->toArray());
    }

    private function getLocationToCopyServicesFrom($practiceLocations): Location
    {
        $locationToCopy = $primaryLocation = $practiceLocations->where('is_primary', true)->first();

        if ( ! $primaryLocation) {
            $locationToCopy = $practiceLocations->first();
        }

        return $locationToCopy;
    }
}
