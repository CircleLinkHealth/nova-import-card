<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientServicesQuery;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientUsersQuery;
use CircleLinkHealth\CcmBilling\Builders\LocationServicesQuery;
use CircleLinkHealth\CcmBilling\Contracts\LocationProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LocationProcessorEloquentRepository implements LocationProcessorRepository
{
    use ApprovablePatientServicesQuery;
    use ApprovablePatientUsersQuery;
    use LocationServicesQuery;

    public function availableLocationServiceProcessors(int $locationId, Carbon $chargeableMonth): AvailableServiceProcessors
    {
        return AvailableServiceProcessors::push($this->locationServiceProcessors($locationId, $chargeableMonth));
    }

    public function enrolledPatients(int $locationId, Carbon $monthYear): Collection
    {
        return $this->patientsQuery($locationId, $monthYear, Patient::ENROLLED)
            ->get();
    }

    public function paginatePatients(int $locationId, Carbon $chargeableMonth, int $pageSize): LengthAwarePaginator
    {
        return $this->patientsQuery($locationId, $chargeableMonth)->paginate($pageSize);
    }

    public function pastMonthSummaries(int $locationId, Carbon $month): Collection
    {
        return $this->servicesForLocation($locationId)
            ->where('chargeable_month', '<', $month)
            ->get();
    }

    public function patients(int $locationId, Carbon $monthYear): Collection
    {
        return $this->patientsQuery($locationId, $monthYear)->get();
    }

    public function patientServices(int $locationId, Carbon $monthYear): Builder
    {
        return $this->approvablePatientServicesQuery($monthYear)
            ->whereHas('patient.patientInfo', fn ($info) => $info->where('preferred_contact_location', $locationId));
    }

    public function patientsQuery(int $locationId, Carbon $monthYear, ?string $ccmStatus = null): Builder
    {
        return $this->approvablePatientUsersQuery($monthYear)
            ->whereHas(
                'patientInfo',
                function ($info) use ($locationId, $ccmStatus) {
                    $info->where('preferred_contact_location', $locationId)
                        ->when( ! is_null($ccmStatus), function ($query) use ($ccmStatus) {
                            $query->ccmStatus($ccmStatus);
                        });
                }
            );
    }

    public function store(int $locationId, string $chargeableServiceCode, Carbon $month, float $amount = null): ChargeableLocationMonthlySummary
    {
        return ChargeableLocationMonthlySummary::updateOrCreate(
            [
                'location_id'           => $locationId,
                'chargeable_service_id' => ChargeableService::getChargeableServiceIdUsingCode($chargeableServiceCode),
                'chargeable_month'      => $month,
            ],
            [
                'amount' => $amount,
            ]
        );
    }

    public function storeUsingServiceId(int $locationId, int $chargeableServiceId, Carbon $month, float $amount = null): ChargeableLocationMonthlySummary
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

    private function locationServiceProcessors(int $locationId, Carbon $chargeableMonth): array
    {
        return $this->servicesForMonth($locationId, $chargeableMonth)
            ->get()
            ->map(fn (ChargeableLocationMonthlySummary $summary) => $summary->getServiceProcessor())
            ->values()
            ->toArray();
    }
    
    public function hasServicesForMonth(int $locationId, Carbon $month): bool
    {
        return $this->servicesForLocation($locationId)
            ->createdOn($month, 'chargeable_month')
            ->exist();
    }
}
