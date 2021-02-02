<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Location;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Location\Stubs\ChargeableLocationMonthlySummaryStub;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;

class Eloquent implements LocationProcessorRepository
{
    protected Builder $builder;

    protected Collection $locationAvailableServiceProcessors;
    protected Collection $summaries;

    public function approvableBillingStatuses(array $locationIds, Carbon $month, bool $withRelations = false): Builder
    {
        // TODO: Implement approvableBillingStatuses() method.
    }

    public function approvedBillingStatuses(array $locationIds, Carbon $month, bool $withRelations = false): Builder
    {
        // TODO: Implement approvedBillingStatuses() method.
    }

    public function assertChargeableSummaryCreated(int $locationId, int $chargeableServiceId, Carbon $month, ?float $amount = null): void
    {
        PHPUnit::assertTrue(
            $this->wasChargeableSummaryCreated($locationId, $chargeableServiceId, $month, $amount)
        );
    }

    public function assertChargeableSummaryNotCreated(int $locationId, int $chargeableServiceId, Carbon $month, ?float $amount = null): void
    {
        PHPUnit::assertFalse(
            $this->wasChargeableSummaryCreated($locationId, $chargeableServiceId, $month, $amount)
        );
    }

    public function availableLocationServiceProcessors(array $locationIds, Carbon $chargeableMonth): AvailableServiceProcessors
    {
        if ( ! isset($this->locationAvailableServiceProcessors)) {
            return new AvailableServiceProcessors();
        }
        $locationProcessorsForMonth = $this->locationAvailableServiceProcessors
            ->whereIn('location_id', $locationIds)
            ->where('chargeable_month', $chargeableMonth)
            ->first();

        return $locationProcessorsForMonth['available_service_processors'] ?? new AvailableServiceProcessors();
    }

    public function closeMonth(array $locationIds, Carbon $month, int $actorId): void
    {
        // TODO: Implement closeMonth() method.
    }

    public function enrolledPatients(array $locationIds, Carbon $monthYear): EloquentCollection
    {
        // TODO: Implement enrolledPatients() method.
    }

    public function getLocationSummaries(array $locationIds, ?Carbon $month = null, bool $excludeLocked = true): ?EloquentCollection
    {
        // TODO: Implement getLocationSummaries() method.
    }

    public function hasServicesForMonth(array $locationIds, array $chargeableServiceCodes, Carbon $month): bool
    {
        return $this->summaries->whereIn('location_id', $locationIds)
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

    public function locationPatients($locationId, ?string $ccmStats = null): Builder
    {
        // TODO: Implement locationPatients() method.
    }

    public function openMonth(array $locationIds, Carbon $month): void
    {
        // TODO: Implement openMonth() method.
    }

    public function paginatePatients(array $locationIds, Carbon $monthYear, int $pageSize): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        // TODO: Implement paginatePatients() method.
    }

    public function pastMonthSummaries(array $locationIds, Carbon $month): EloquentCollection
    {
        return $this->toEloquentCollection(
            $this->summaries->whereIn('location_id', $locationIds)
                ->where('chargeable_month', '<', $month)
        );
    }

    public function patients(array $locationIds, Carbon $monthYear): EloquentCollection
    {
        // TODO: Implement patients() method.
    }

    public function patientServices(array $locationIds, Carbon $monthYear): Builder
    {
        // TODO: Implement patientServices() method.
    }

    public function patientsQuery(array $locationIds, Carbon $monthYear, ?string $ccmStatus = null): Builder
    {
        return $this->builder;
    }

    public function servicesExistForMonth(array $locationIds, Carbon $month): bool
    {
        return $this->summaries
            ->whereIn('location_id', $locationIds)
            ->where('chargeable_month', $month)
            ->isNotEmpty();
    }

    public function setBuilder(Builder $builder)
    {
        $this->builder = $builder;
    }

    public function setChargeableLocationMonthlySummaryStubs(ChargeableLocationMonthlySummaryStub ...$chargeableLocationMonthlySummaryStubs)
    {
        $this->summaries = collect($chargeableLocationMonthlySummaryStubs);
    }

    public function setLocationProcessors(int $locationId, Carbon $month, AvailableServiceProcessors $availableServiceProcessors)
    {
        $toPush = [
            'location_id'                  => $locationId,
            'available_service_processors' => $availableServiceProcessors,
            'chargeable_month'             => $month,
        ];

        if ( ! isset($this->locationAvailableServiceProcessors)) {
            $this->locationAvailableServiceProcessors = collect([$toPush]);
        }

        $this->locationAvailableServiceProcessors->push($toPush);
    }

    public function store(int $locationId, int $chargeableServiceId, Carbon $month, ?float $amount = null): ChargeableLocationMonthlySummary
    {
        $this->summaries->push(
            $stub = (new ChargeableLocationMonthlySummaryStub())
                ->setLocationId($locationId)
                ->setChargeableServiceId($chargeableServiceId)
                ->setChargeableMonth($month)
                ->setAmount($amount)
        );

        return $stub->toModel();
    }

    private function toEloquentCollection(Collection $collection): EloquentCollection
    {
        return new EloquentCollection(
            $collection->map(function (ChargeableLocationMonthlySummaryStub $stub) {
                return $stub->toModel();
            })
        );
    }

    private function wasChargeableSummaryCreated(int $locationId, int $chargeableServiceId, Carbon $month, ?float $amount = null): bool
    {
        return 1 === $this->summaries->where('location_id', $locationId)
            ->where('chargeable_service_id', $chargeableServiceId)
            ->where('chargeable_month', $month)
            ->when( ! is_null($amount), function ($collection) use ($amount) {
                return $collection->where('amount', $amount);
            })
            ->unique()
            ->count();
    }
}
