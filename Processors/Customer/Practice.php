<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors\Customer;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\CustomerProcessor;
use CircleLinkHealth\CcmBilling\ValueObjects\BillablePatientsCountForMonthDTO;
use Illuminate\Pagination\LengthAwarePaginator;

class Practice implements CustomerProcessor
{
    private Location $locationProcessor;

    public function __construct(Location $locationProcessor)
    {
        $this->locationProcessor = $locationProcessor;
    }

    public function closeMonth(array $practiceIds, Carbon $month, int $actorId): void
    {
        $locations = $this->getLocations($practiceIds);
        $this->locationProcessor->closeMonth($locations, $month, $actorId);
    }

    public function counts(array $practiceIds, Carbon $month): BillablePatientsCountForMonthDTO
    {
        $locations = $this->getLocations($practiceIds);
        return $this->locationProcessor->counts($locations, $month);
    }

    public function fetchApprovablePatients(array $practiceIds, Carbon $month, int $pageSize = 30): LengthAwarePaginator
    {
        $locations = $this->getLocations($practiceIds);

        return $this->locationProcessor->fetchApprovablePatients($locations, $month, $pageSize);
    }

    public function openMonth(array $practiceIds, Carbon $month): void
    {
        $locations = $this->getLocations($practiceIds);
        $this->locationProcessor->openMonth($locations, $month);
    }

    public function processServicesForAllPatients(array $practiceIds, Carbon $month): void
    {
        $locations = $this->getLocations($practiceIds);
        $this->locationProcessor->processServicesForAllPatients($locations, $month);
    }

    private function getLocations(array $practiceIds): array
    {
        return \CircleLinkHealth\Customer\Entities\Location::whereIn('practice_id', $practiceIds)
            ->pluck('id')
            ->toArray();
    }
}
