<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Facades\BillingCache;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CachedLocationProcessorEloquentRepository implements LocationProcessorRepository
{
    protected LocationProcessorEloquentRepository $repo;

    public function __construct()
    {
        $this->repo = new LocationProcessorEloquentRepository();
    }

    public function availableLocationServiceProcessors(int $locationId, Carbon $chargeableMonth): AvailableServiceProcessors
    {
        return AvailableServiceProcessors::push(
            $this->getLocationSummaries($locationId, $chargeableMonth)
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

    public function getLocationSummaries(int $locationId, ?Carbon $month = null, bool $excludeLocked = true): ?EloquentCollection
    {
        if ( ! BillingCache::locationWasQueried($locationId)) {
            BillingCache::setLocationSummariesInCache($locationSummaries = $this->queryLocationServices($locationId));

            if ($locationSummaries->isEmpty()) {
                sendSlackMessage('#billing_alerts', "Warning! (From Cached Location Repo:) Location ({$locationId}) has no chargeable service summaries.");
            }
        }

        return BillingCache::getLocationSummaries($locationId)
            ->when( ! is_null($month), function ($collection) use ($month) {
                return $collection->where('chargeable_month', $month);
            })
            ->when($excludeLocked, function ($query) {
                return $query->where('is_locked', false);
            });
    }

    public function hasServicesForMonth(int $locationId, array $chargeableServiceCodes, Carbon $month): bool
    {
        return $this->getLocationSummaries($locationId)
            ->whereIn('chargeableService.code', $chargeableServiceCodes)
            ->where('chargeable_month', $month)
            ->isNotEmpty();
    }

    public function isLockedForMonth(int $locationId, string $chargeableServiceCode, Carbon $month): bool
    {
        $summaries = $this->getLocationSummaries($locationId, $month, false);

        return $summaries->isNotEmpty() && $summaries->every(function (ChargeableLocationMonthlySummary $summary) {
            return $summary->is_locked;
        });
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
        return $this->getLocationSummaries($locationId, $month)->isNotEmpty();
    }

    public function store(int $locationId, string $chargeableServiceCode, Carbon $month, float $amount = null): ChargeableLocationMonthlySummary
    {
        $summary = $this->repo->store($locationId, $chargeableServiceCode, $month, $amount);

        if ( ! BillingCache::locationWasQueried($locationId)) {
            BillingCache::setLocationSummariesInCache($this->queryLocationServices($locationId));

            return $summary;
        }

        $this->updateLocationSummariesInCache($locationId, $summary);

        return $summary;
    }

    public function storeUsingServiceId(int $locationId, int $chargeableServiceId, Carbon $month, float $amount = null): ChargeableLocationMonthlySummary
    {
        $summary = $this->repo->storeUsingServiceId($locationId, $chargeableServiceId, $month, $amount);

        if ( ! BillingCache::locationWasQueried($locationId)) {
            BillingCache::setLocationSummariesInCache($this->queryLocationServices($locationId));

            return $summary;
        }

        $this->updateLocationSummariesInCache($locationId, $summary);

        return $summary;
    }

    private function queryLocationServices(int $locationId, ?Carbon $month = null, bool $excludeLocked = true): Collection
    {
        return $this->repo->servicesForMonth($locationId, $month, $excludeLocked)->get();
    }

    private function updateLocationSummariesInCache(int $locationId, ChargeableLocationMonthlySummary $summary): void
    {
        $summaries = BillingCache::getLocationSummaries($locationId);

        if ($summaries->contains('id', $summary->id)) {
            $summaries->forgetUsingModelKey('id', $summary->id);
        }

        $summaries->push($summary);

        BillingCache::forgetLocationSummaries($locationId);
        BillingCache::setLocationSummariesInCache($summaries);
    }

    public function locationPatients($locationId, ?string $ccmStatus = null): Builder
    {
        return $this->repo->locationPatients($locationId, $ccmStatus);
    }
}
