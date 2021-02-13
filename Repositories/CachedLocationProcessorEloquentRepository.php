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
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CachedLocationProcessorEloquentRepository implements LocationProcessorRepository
{
    protected LocationProcessorEloquentRepository $repo;

    public function __construct()
    {
        $this->repo = new LocationProcessorEloquentRepository();
    }

    public function approvableBillingStatuses(array $locationIds, Carbon $month, bool $withRelations = false): Builder
    {
        return $this->repo->approvableBillingStatuses($locationIds, $month, $withRelations);
    }

    public function approvedBillingStatuses(array $locationIds, Carbon $month, bool $withRelations = false): Builder
    {
        return $this->repo->approvedBillingStatuses($locationIds, $month, $withRelations);
    }

    public function availableLocationServiceProcessors(array $locationIds, Carbon $chargeableMonth): AvailableServiceProcessors
    {
        return AvailableServiceProcessors::push(
            $this->getLocationSummaries($locationIds, $chargeableMonth)
                ->map(fn (ChargeableLocationMonthlySummary $summary) => $summary->getServiceProcessor())
                ->filter()
                ->values()
                ->toArray()
        );
    }

    public function closeMonth(array $locationIds, Carbon $month, int $actorId): void
    {
        $this->repo->closeMonth($locationIds, $month, $actorId);

        /** @var ChargeableLocationMonthlySummary[]|EloquentCollection $summaries */
        $summaries = $this->getLocationSummaries($locationIds, $month, true);
        foreach ($summaries as $summary) {
            $summary->is_locked = true;
            $this->updateLocationSummariesInCache($summary->location_id, $summary);
        }
    }

    public function enrolledPatients(array $locationIds, Carbon $monthYear): EloquentCollection
    {
        return $this->repo->enrolledPatients($locationIds, $monthYear);
    }

    public function getLocationSummaries(array $locationIds, ?Carbon $month = null, bool $excludeLocked = true): ?EloquentCollection
    {
        $toQuery = [];
        foreach ($locationIds as $locationId) {
            if ( ! BillingCache::locationWasQueried($locationId)) {
                $toQuery[] = $locationId;
            }
        }
        if ( ! empty($toQuery)) {
            BillingCache::setLocationSummariesInCache($locationSummaries = $this->queryLocationServices($toQuery));
            if ($locationSummaries->isEmpty()) {
                $str = implode(', ', $toQuery);
                sendSlackMessage('#billing_alerts', "Warning! (From Cached Location Repo:) Locations ({$str}) have no chargeable service summaries.");
            }
        }

        return BillingCache::getLocationSummaries($locationIds)
            ->when( ! is_null($month), function ($collection) use ($month) {
                return $collection->where('chargeable_month', $month);
            })
            ->when($excludeLocked, function ($query) {
                return $query->where('is_locked', false);
            });
    }

    public function hasServicesForMonth(array $locationIds, array $chargeableServiceCodes, Carbon $month): bool
    {
        return $this->getLocationSummaries($locationIds)
            ->whereIn('chargeableService.code', $chargeableServiceCodes)
            ->where('chargeable_month', $month)
            ->isNotEmpty();
    }

    public function isLockedForMonth(array $locationIds, string $chargeableServiceCode, Carbon $month): bool
    {
        $summaries = $this->getLocationSummaries($locationIds, $month, false);

        return $summaries->isNotEmpty() && $summaries->every(function (ChargeableLocationMonthlySummary $summary) {
            return $summary->is_locked;
        });
    }

    public function locationPatients(array $locationIds, ?string $ccmStatus = null): Builder
    {
        return $this->repo->locationPatients($locationIds, $ccmStatus);
    }

    public function openMonth(array $locationIds, Carbon $month): void
    {
        $this->repo->openMonth($locationIds, $month);

        /** @var ChargeableLocationMonthlySummary[]|EloquentCollection $summaries */
        $summaries = $this->getLocationSummaries($locationIds, $month, true);
        foreach ($summaries as $summary) {
            $summary->is_locked = false;
            $this->updateLocationSummariesInCache($summary->location_id, $summary);
        }
    }

    public function paginatePatients(array $locationIds, Carbon $monthYear, int $pageSize): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->repo->paginatePatients($locationIds, $monthYear, $pageSize);
    }

    public function pastMonthSummaries(array $locationIds, Carbon $month): EloquentCollection
    {
        return $this->getLocationSummaries($locationIds)
            ->where('chargeable_month', '<', $month);
    }

    public function patients(array $locationIds, Carbon $monthYear): EloquentCollection
    {
        return $this->repo->patients($locationIds, $monthYear);
    }

    public function patientServices(array $locationIds, Carbon $monthYear): Builder
    {
        return $this->repo->patientServices($locationIds, $monthYear);
    }

    public function patientsQuery(array $locationIds, Carbon $monthYear, ?string $ccmStatus = null): Builder
    {
        return $this->repo->patientsQuery($locationIds, $monthYear);
    }

    public function servicesExistForMonth(array $locationIds, Carbon $month): bool
    {
        return $this->getLocationSummaries($locationIds, $month)->isNotEmpty();
    }

    public function store(int $locationId, int $chargeableServiceId, Carbon $month, float $amount = null): ChargeableLocationMonthlySummary
    {
        $summary = $this->repo->store($locationId, $chargeableServiceId, $month, $amount);

        if ( ! BillingCache::locationWasQueried($locationId)) {
            BillingCache::setLocationSummariesInCache($this->queryLocationServices([$locationId]));

            return $summary;
        }

        $this->updateLocationSummariesInCache($locationId, $summary);

        return $summary;
    }

    private function queryLocationServices(array $locationIds, ?Carbon $month = null, bool $excludeLocked = true): EloquentCollection
    {
        return $this->repo->servicesForMonth($locationIds, $month, $excludeLocked)->get();
    }

    private function updateLocationSummariesInCache(int $locationId, ChargeableLocationMonthlySummary $summary): void
    {
        $summaries = BillingCache::getLocationSummaries([$locationId]);

        if ($summaries->contains('id', $summary->id)) {
            $summaries->forgetUsingModelKey('id', $summary->id);
        }

        $summaries->push($summary);

        BillingCache::forgetLocationSummaries([$locationId]);
        BillingCache::setLocationSummariesInCache($summaries);
    }
    
    public function processableLocationPatientsForMonth(array $locationIds, Carbon $month): Builder
    {
        return $this->repo->processableLocationPatientsForMonth($locationIds, $month);
    }
}
