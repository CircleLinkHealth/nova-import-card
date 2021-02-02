<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

interface LocationProcessorRepository
{
    public function approvedBillingStatuses(array $locationIds, Carbon $month, bool $withRelations = false): Builder;
    
    public function approvableBillingStatuses(array $locationIds, Carbon $month, bool $withRelations = false): Builder;

    public function availableLocationServiceProcessors(array $locationIds, Carbon $chargeableMonth): AvailableServiceProcessors;

    public function closeMonth(array $locationIds, Carbon $month, int $actorId): void;

    public function enrolledPatients(array $locationIds, Carbon $monthYear): Collection;

    public function getLocationSummaries(array $locationIds, ?Carbon $month = null, bool $excludeLocked = true): ?EloquentCollection;

    public function hasServicesForMonth(array $locationIds, array $chargeableServiceCodes, Carbon $month): bool;

    public function isLockedForMonth(array $locationIds, string $chargeableServiceCode, Carbon $month): bool;

    public function locationPatients(array $locationIds, ?string $ccmStats = null): Builder;

    public function openMonth(array $locationIds, Carbon $month): void;

    public function paginatePatients(array $locationIds, Carbon $monthYear, int $pageSize): LengthAwarePaginator;

    public function pastMonthSummaries(array $locationIds, Carbon $month): Collection;

    public function patients(array $locationIds, Carbon $monthYear): Collection;

    public function patientServices(array $locationIds, Carbon $monthYear): Builder;

    public function patientsQuery(array $locationIds, Carbon $monthYear, ?string $ccmStatus = null): Builder;

    public function servicesExistForMonth(array $locationIds, Carbon $month): bool;

    public function store(int $locationId, int $chargeableServiceId, Carbon $month, float $amount = null): ChargeableLocationMonthlySummary;
}
