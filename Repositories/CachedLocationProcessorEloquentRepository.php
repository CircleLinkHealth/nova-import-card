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
use Illuminate\Database\Eloquent\Collection;

class CachedLocationProcessorEloquentRepository implements LocationProcessorRepository
{
    public function availableLocationServiceProcessors(int $locationId, Carbon $chargeableMonth): AvailableServiceProcessors
    {
        // TODO: Implement availableLocationServiceProcessors() method.
    }

    public function hasServicesForMonth(int $locationId, array $chargeableServiceCodes, Carbon $month): bool
    {
        // TODO: Implement hasServicesForMonth() method.
    }

    public function paginatePatients(int $customerModelId, Carbon $monthYear, int $pageSize): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        // TODO: Implement paginatePatients() method.
    }

    public function pastMonthSummaries(int $locationId, Carbon $month): Collection
    {
        // TODO: Implement pastMonthSummaries() method.
    }

    public function patients(int $customerModelId, Carbon $monthYear): Collection
    {
        // TODO: Implement patients() method.
    }

    public function patientServices(int $customerModelId, Carbon $monthYear): Builder
    {
        // TODO: Implement patientServices() method.
    }

    public function patientsQuery(int $customerModelId, Carbon $monthYear, ?string $ccmStatus = null): Builder
    {
        // TODO: Implement patientsQuery() method.
    }

    public function servicesExistForMonth(int $locationId, Carbon $month): bool
    {
        // TODO: Implement servicesExistForMonth() method.
    }

    public function store(int $locationId, string $chargeableServiceCode, Carbon $month, float $amount = null): ChargeableLocationMonthlySummary
    {
        // TODO: Implement store() method.
    }

    public function storeUsingServiceId(int $locationId, int $chargeableServiceId, Carbon $month, float $amount = null): ChargeableLocationMonthlySummary
    {
        // TODO: Implement storeUsingServiceId() method.
    }
}
