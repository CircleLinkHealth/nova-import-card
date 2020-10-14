<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CachedLocationProcessorEloquentRepository implements LocationProcessorRepository
{
    protected array $cachedLocationServices = [];

    protected array $queriedLocationServices = [];

    protected LocationProcessorEloquentRepository $repo;

    public function __construct()
    {
        $this->repo = new LocationProcessorEloquentRepository();
    }

    public function availableLocationServiceProcessors(int $locationId, Carbon $chargeableMonth): AvailableServiceProcessors
    {
        return AvailableServiceProcessors::push(
            $this->getLocationSummaries($locationId)
                ->where('chargeable_month', $chargeableMonth)
                ->map(fn (ChargeableLocationMonthlySummary $summary) => $summary->getServiceProcessor())
                ->filter()
                ->values()
                ->toArray()
        );
    }

    public function enrolledPatients(int $locationId, Carbon $monthYear): EloquentCollection
    {
        return $this->repo->enrolledPatients($locationId, $monthYear);
    }

    public function hasServicesForMonth(int $locationId, array $chargeableServiceCodes, Carbon $month): bool
    {
        return $this->getLocationSummaries($locationId)
            ->whereIn('chargeableService.code', $chargeableServiceCodes)
            ->where('chargeable_month', $month)
            ->isNotEmpty();
    }

    public function paginatePatients(int $customerModelId, Carbon $monthYear, int $pageSize): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->repo->paginatePatients($customerModelId, $monthYear, $pageSize);
    }

    public function pastMonthSummaries(int $locationId, Carbon $month): EloquentCollection
    {
        return $this->getLocationSummaries($locationId)
            ->where('chargeable_month', '<', $month);
    }

    public function patients(int $customerModelId, Carbon $monthYear): EloquentCollection
    {
        return $this->repo->patients($customerModelId, $monthYear);
    }

    public function patientServices(int $customerModelId, Carbon $monthYear): Builder
    {
        return $this->repo->patientServices($customerModelId, $monthYear);
    }

    public function patientsQuery(int $customerModelId, Carbon $monthYear, ?string $ccmStatus = null): Builder
    {
        return $this->repo->patientsQuery($customerModelId, $monthYear);
    }

    public function servicesExistForMonth(int $locationId, Carbon $month): bool
    {
        return $this->getLocationSummaries($locationId)
            ->where('chargeable_month', $month)
            ->isNotEmpty();
    }

    public function store(int $locationId, string $chargeableServiceCode, Carbon $month, float $amount = null): ChargeableLocationMonthlySummary
    {
        $summary = $this->repo->store($locationId, $chargeableServiceCode, $month, $amount);

        if ( ! in_array($locationId, $this->queriedLocationServices)) {
            $this->queryLocationServices($locationId);

            return $summary;
        }

        $this->cachedLocationServices[$locationId]->push($summary);

        return $summary;
    }

    public function storeUsingServiceId(int $locationId, int $chargeableServiceId, Carbon $month, float $amount = null): ChargeableLocationMonthlySummary
    {
        $summary = $this->repo->storeUsingServiceId($locationId, $chargeableServiceId, $month, $amount);
    
        if ( ! in_array($locationId, $this->queriedLocationServices)) {
            $this->queryLocationServices($locationId);
        
            return $summary;
        }
    
        $this->cachedLocationServices[$locationId]->push($summary);
    
        return $summary;
    }

    private function getLocationSummaries(int $locationId): ?EloquentCollection
    {
        if ( ! in_array($locationId, $this->queriedLocationServices)) {
            $this->queryLocationServices($locationId);
        }

        return $this->cachedLocationServices[$locationId];
    }

    private function queryLocationServices(int $locationId)
    {
        $this->cachedLocationServices[$locationId] = $this->repo->servicesForLocation($locationId)->get();
    }
}
