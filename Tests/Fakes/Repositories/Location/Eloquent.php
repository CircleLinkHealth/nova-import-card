<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Location;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Location\Stubs\ChargeableLocationMonthlySummaryStub;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;

class Eloquent implements LocationProcessorRepository
{
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
            ! $this->wasChargeableSummaryCreated($locationId, $chargeableServiceId, $month, $amount)
        );
    }

    public function paginatePatients(int $customerModelId, Carbon $monthYear, int $pageSize): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        // TODO: Implement paginatePatients() method.
    }

    public function patients(int $customerModelId, Carbon $monthYear): EloquentCollection
    {
        // TODO: Implement patients() method.
    }

    public function patientServices(int $customerModelId, Carbon $monthYear): Builder
    {
        // TODO: Implement patientServices() method.
    }

    public function patientsQuery(int $customerModelId, Carbon $monthYear): Builder
    {
        // TODO: Implement patientsQuery() method.
    }

    public function setChargeableLocationMonthlySummaryStubs(ChargeableLocationMonthlySummaryStub ...$chargeableLocationMonthlySummaryStubs)
    {
        $this->summaries = collect($chargeableLocationMonthlySummaryStubs);
    }

    public function store(int $locationId, int $chargeableServiceId, Carbon $month, ?float $amount = null): ChargeableLocationMonthlySummary
    {
        $this->summaries->push(
            $stub = new ChargeableLocationMonthlySummaryStub(
                $locationId,
                $chargeableServiceId,
                $month,
                $amount
            )
        );

        return $stub->toModel();
    }

    private function wasChargeableSummaryCreated(int $locationId, int $chargeableServiceId, Carbon $month, ?float $amount = null)
    {
        return 1 === $this->summaries->where('location_id', $locationId)
            ->where('chargeable_service_id', $chargeableServiceId)
            ->where('chargeable_month', $month)
//            ->when( ! is_null($amount), function ($s) use ($amount) {
//                $s->where('amount', $amount);
//            })
            ->count();
    }
    
    public function hasServicesForMonth(int $locationId, Carbon $month) : bool
    {
        return $this->summaries->where('location_id', $locationId)->where('chargeable_month', $month)->count() > 0;
    }
    
    public function pastMonthSummaries(int $locationId, Carbon $month){
        return new EloquentCollection($this->summaries->where('location_id', $locationId)->where('chargeable_month', '<', $month)->map(function ($stub){
            return $stub->toModel();
        }));
    }
}
