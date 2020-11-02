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
use CircleLinkHealth\Customer\Entities\ChargeableService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;

class Eloquent implements LocationProcessorRepository
{
    protected Builder $builder;

    protected Collection $locationAvailableServiceProcessors;
    protected Collection $summaries;

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

    public function availableLocationServiceProcessors(int $locationId, Carbon $chargeableMonth): AvailableServiceProcessors
    {
        if ( ! isset($this->locationAvailableServiceProcessors)) {
            return new AvailableServiceProcessors();
        }
        $locationProcessorsForMonth = $this->locationAvailableServiceProcessors
            ->where('location_id', $locationId)
            ->where('chargeable_month', $chargeableMonth)
            ->first();

        return $locationProcessorsForMonth['available_service_processors'] ?? new AvailableServiceProcessors();
    }

    public function enrolledPatients(int $locationId, Carbon $monthYear): EloquentCollection
    {
        // TODO: Implement enrolledPatients() method.
    }

    public function getLocationSummaries(int $locationId, ?Carbon $month = null): ?EloquentCollection
    {
        // TODO: Implement getLocationSummaries() method.
    }

    public function hasServicesForMonth(int $locationId, array $chargeableServiceCodes, Carbon $month): bool
    {
        return $this->summaries->where('location_id', $locationId)
            ->whereIn('chargeableService.code', $chargeableServiceCodes)
            ->where('chargeable_month', $month)
            ->isNotEmpty();
    }

    public function paginatePatients(int $customerModelId, Carbon $monthYear, int $pageSize): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        // TODO: Implement paginatePatients() method.
    }

    public function pastMonthSummaries(int $locationId, Carbon $month): EloquentCollection
    {
        return $this->toEloquentCollection(
            $this->summaries->where('location_id', $locationId)
                ->where('chargeable_month', '<', $month)
        );
    }

    public function patients(int $customerModelId, Carbon $monthYear): EloquentCollection
    {
        // TODO: Implement patients() method.
    }

    public function patientServices(int $customerModelId, Carbon $monthYear): Builder
    {
        // TODO: Implement patientServices() method.
    }

    public function patientsQuery(int $customerModelId, Carbon $monthYear, ?string $ccmStatus = null): Builder
    {
        return $this->builder;
    }

    public function servicesExistForMonth(int $locationId, Carbon $month): bool
    {
        return $this->summaries
            ->where('location_id', $locationId)
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

    public function store(int $locationId, string $chargeableServiceCode, Carbon $month, ?float $amount = null): ChargeableLocationMonthlySummary
    {
        $this->summaries->push(
            $stub = (new ChargeableLocationMonthlySummaryStub())
                ->setLocationId($locationId)
                ->setChargeableServiceId(ChargeableService::getChargeableServiceIdUsingCode($chargeableServiceCode))
                ->setChargeableMonth($month)
                ->setAmount($amount)
        );

        return $stub->toModel();
    }

    public function storeUsingServiceId(int $locationId, int $chargeableServiceId, Carbon $month, ?float $amount = null): ChargeableLocationMonthlySummary
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
