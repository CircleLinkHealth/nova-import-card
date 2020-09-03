<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Domain\Customer;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Contracts\PracticeProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Events\LocationServicesAttached;
use CircleLinkHealth\Customer\Entities\Location;

class AutoAttachServicesToNewLocation
{
    protected int $locationId;
    protected LocationProcessorRepository $locationRepo;
    protected Carbon $month;
    protected PracticeProcessorRepository $practiceRepo;

    public function __construct(int $locationId, Carbon $month)
    {
        $this->locationId = $locationId;
        $this->month      = $month;
    }

    public function attach()
    {
        $location = $this->locationRepo()->locationWithPracticeLocationsWithSummaries($this->locationId, $this->month);

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
        //todo: file in progress, subject to change after dev or business side input
        (new static($locationId, $month))->attach();
    }

    private function getLocationToCopyServicesFrom($practiceLocations): Location
    {
        $locationToCopy = $primaryLocation = $practiceLocations->where('is_primary', true)->first();

        if ( ! $primaryLocation) {
            $locationToCopy = $practiceLocations->first();
        }

        return $locationToCopy;
    }

    private function locationRepo(): LocationProcessorRepository
    {
        if (is_null($this->locationRepo)) {
            $this->locationRepo = app(LocationProcessorRepository::class);
        }

        return $this->locationRepo;
    }

    private function practiceRepo(): PracticeProcessorRepository
    {
        if (is_null($this->practiceRepo)) {
            $this->practiceRepo = app(PracticeProcessorRepository::class);
        }

        return $this->practiceRepo;
    }
}
