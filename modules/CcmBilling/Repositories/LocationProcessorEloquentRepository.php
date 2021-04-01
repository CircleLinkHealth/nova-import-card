<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Builders\ApprovableBillingStatusesQuery;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientServicesQuery;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientUsersQuery;
use CircleLinkHealth\CcmBilling\Builders\LocationServicesQuery;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LocationProcessorEloquentRepository implements LocationProcessorRepository
{
    use ApprovableBillingStatusesQuery;
    use ApprovablePatientServicesQuery;
    use ApprovablePatientUsersQuery;
    use LocationServicesQuery;

    public function approvableBillingStatuses(array $locationIds, Carbon $month, bool $withRelations = false): Builder
    {
        return $this->approvableBillingStatusesQuery($locationIds, $month, $withRelations);
    }

    public function approvedBillingStatuses(array $locationIds, Carbon $month, bool $withRelations = false): Builder
    {
        return $this->approvedBillingStatusesQuery($locationIds, $month, $withRelations);
    }

    public function availableLocationServiceProcessors(array $locationIds, Carbon $chargeableMonth): AvailableServiceProcessors
    {
        return AvailableServiceProcessors::push($this->locationServiceProcessors($locationIds, $chargeableMonth));
    }

    public function closeMonth(array $locationIds, Carbon $month, int $actorId): void
    {
        $this->changeMonthStatus($locationIds, $month, $actorId, true);
    }

    public function enrolledPatients(array $locationIds, Carbon $monthYear): Collection
    {
        return $this->patientsQuery($locationIds, $monthYear, Patient::ENROLLED)
            ->get();
    }

    public function getLocationSummaries(array $locationIds, ?Carbon $month = null, bool $excludeLocked = true): ?Collection
    {
        return $this->servicesForMonth($locationIds, $month, $excludeLocked)->get();
    }

    public function hasServicesForMonth(array $locationIds, array $chargeableServiceCodes, Carbon $month): bool
    {
        //todo: add test
        return $this->servicesForMonth($locationIds, $month)
            ->whereHas('chargeableService', fn ($cs) => $cs->whereIn('code', $chargeableServiceCodes))
            ->exists();
    }

    public function isLockedForMonth(array $locationIds, string $chargeableServiceCode, Carbon $month): bool
    {
        $summaries = ChargeableLocationMonthlySummary::whereIn('location_id', $locationIds)
            ->where('chargeable_month', '=', $month)
            ->get(['id', 'is_locked']);

        return $summaries->isNotEmpty() && $summaries->every(function (ChargeableLocationMonthlySummary $summary) {
            return $summary->is_locked;
        });
    }

    public function locationPatients(array $locationIds, ?string $ccmStatus = null): Builder
    {
        return User::ofType('participant')
            ->patientInLocations($locationIds, $ccmStatus);
    }

    public function openMonth(array $locationIds, Carbon $month): void
    {
        $this->changeMonthStatus($locationIds, $month, null, false);
    }

    public function paginatePatients(array $locationIds, Carbon $chargeableMonth, int $pageSize): LengthAwarePaginator
    {
        return $this->patientsQuery($locationIds, $chargeableMonth)->paginate($pageSize);
    }

    public function pastMonthSummaries(array $locationIds, Carbon $month): Collection
    {
        return $this->servicesForLocations($locationIds)
            ->where('chargeable_month', '<', $month)
            ->get();
    }

    public function patients(array $locationIds, Carbon $monthYear): Collection
    {
        return $this->patientsQuery($locationIds, $monthYear)->get();
    }

    public function patientServices(array $locationIds, Carbon $monthYear): Builder
    {
        return $this->approvablePatientServicesQuery($monthYear)
            ->whereHas('patient.patientInfo', fn ($info) => $info->whereIn('preferred_contact_location', $locationIds));
    }

    public function patientsQuery(array $locationIds, Carbon $monthYear, ?string $ccmStatus = null): Builder
    {
        return $this->approvablePatientUsersQuery($monthYear)
            ->patientInLocations($locationIds, $ccmStatus);
    }

    public function processableLocationPatientsForMonth(array $locationIds, Carbon $month): Builder
    {
        return User::ofType('participant')
            ->whereHas(
                'patientInfo',
                function ($info) use ($locationIds, $month) {
                    $info->whereIn('preferred_contact_location', $locationIds)
                        ->whereHas('location', function ($location) use ($month) {
                            $location->whereHas('chargeableServiceSummaries', function ($summary) use ($month) {
                                $summary->where('chargeable_month', $month)
                                    ->where('is_locked', false);
                            });
                        });
                }
            );
    }

    public function servicesExistForMonth(array $locationIds, Carbon $month): bool
    {
        return $this->servicesForLocations($locationIds)
            ->createdOn($month, 'chargeable_month')
            ->exists();
    }

    public function store(int $locationId, int $chargeableServiceId, Carbon $month, float $amount = null): ChargeableLocationMonthlySummary
    {
        return ChargeableLocationMonthlySummary::updateOrCreate(
            [
                'location_id'           => $locationId,
                'chargeable_service_id' => $chargeableServiceId,
                'chargeable_month'      => $month,
            ],
            [
                'amount' => $amount,
            ]
        );
    }

    private function changeMonthStatus(array $locationIds, Carbon $month, ?int $actorId, bool $isLocked)
    {
        $updated = PatientMonthlyBillingStatus::whereHas(
            'patientUser',
            fn ($q) => $q->patientInLocations($locationIds)
        )->where('chargeable_month', $month)
            ->update([
                'actor_id' => $actorId,
            ]);

        ChargeableLocationMonthlySummary::whereIn('location_id', $locationIds)
            ->where('chargeable_month', '=', $month)
            ->update([
                'is_locked' => $isLocked,
            ]);

        return $updated;
    }

    private function locationServiceProcessors(array $locationIds, Carbon $chargeableMonth): array
    {
        return $this->servicesForMonth($locationIds, $chargeableMonth)
            ->get()
            ->map(fn (ChargeableLocationMonthlySummary $summary) => $summary->getServiceProcessor())
            ->filter()
            ->values()
            ->toArray();
    }
}
