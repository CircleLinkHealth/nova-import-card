<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface LocationProcessorRepository
{
    public function availableLocationServiceProcessors(int $locationId, Carbon $chargeableMonth): AvailableServiceProcessors;

    public function hasServicesForMonth(int $locationId, array $chargeableServiceCodes, Carbon $month): bool;

    public function paginatePatients(int $customerModelId, Carbon $monthYear, int $pageSize): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    public function pastMonthSummaries(int $locationId, Carbon $month): Collection;

    public function patients(int $customerModelId, Carbon $monthYear): Collection;

    public function patientServices(int $customerModelId, Carbon $monthYear): Builder;

    public function patientsQuery(int $customerModelId, Carbon $monthYear, ?string $ccmStatus = null): Builder;

    public function servicesExistForMonth(int $locationId, Carbon $month): bool;

    public function store(int $locationId, string $chargeableServiceCode, Carbon $month, float $amount = null): ChargeableLocationMonthlySummary;

    public function storeUsingServiceId(int $locationId, int $chargeableServiceId, Carbon $month, float $amount = null): ChargeableLocationMonthlySummary;
    
    public function enrolledPatients(int $locationId, Carbon $monthYear): Collection;
}
