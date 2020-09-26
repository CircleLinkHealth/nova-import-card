<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Customer;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Events\LocationServicesAttached;
use CircleLinkHealth\Customer\Entities\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Not used. Chargeable Services are manually added to new locations.
 */
class AutoAttachServicesToNewLocation
{
    protected int $locationId;
    protected Carbon $month;

    public function __construct(int $locationId, Carbon $month)
    {
        $this->locationId = $locationId;
        $this->month      = $month;
    }

    public function attach()
    {
        $location = $this->locationWithPracticeLocationsWithSummaries($this->locationId, $this->month);

        $practice          = $location->practice;
        $practiceLocations = $practice->locations;

        if ($practiceLocations->isEmpty()) {
            $url = route('provider.dashboard.manage.chargeable-services', [
                'practiceSlug' => $practice->name,
            ]);
            $link = "<$url|Corresponding Practice's Chargeable Service management page>";
            //todo:decide channel
            sendSlackMessage('#channel-to-decide', "Auto attachment of Chargeable Services for new Location with ID:$this->locationId failed.
            Please head to $link and assign chargeable services to practice or location.");

            return;
        }

        $locationToCopy = $this->getLocationToCopyServicesFrom($practiceLocations);

        $locationToCopy->chargeableServiceSummaries->each(function (ChargeableLocationMonthlySummary $summary) {
            $this->locationRepo()->store($this->locationId, $summary->chargeable_service_id, $this->month);
        });

        event(new LocationServicesAttached($this->locationId));
    }

    public static function execute(int $locationId, Carbon $month)
    {
        (new static($locationId, $month))->attach();
    }

    /**
     * @return Builder|Builder[]|Collection|Model
     */
    public function locationWithPracticeLocationsWithSummaries(int $locationId, ?Carbon $month = null)
    {
        //todo: add trait query
        return Location::with([
            'practice.locations' => function ($location) use ($month) {
                //todo: add scope
                $location->with([
                    'chargeableServiceSummaries' => function ($summary) use ($month) {
                        $summary->with(['chargeableService'])
                            ->when( ! is_null($month), function ($q) use ($month) {
                                $q->createdOn($month, 'chargeable_month');
                            });
                    },
                ])
                    ->when( ! is_null($month), function ($q) use ($month) {
                        $q->whereHas('chargeableServiceSummaries', function ($summary) {
                            $summary->createdOn($this->month, 'chargeable_month');
                        });
                    });
            }, ])
            ->find($locationId);
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
