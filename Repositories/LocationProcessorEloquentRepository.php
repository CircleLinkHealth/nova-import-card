<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientServicesQuery;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientUsersQuery;
use CircleLinkHealth\CcmBilling\Contracts\CustomerBillingProcessorRepository;
use CircleLinkHealth\CcmBilling\Contracts\PatientChargeableServiceProcessor;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LocationProcessorEloquentRepository implements CustomerBillingProcessorRepository
{
    use ApprovablePatientServicesQuery;
    use ApprovablePatientUsersQuery;

    public function availableLocationServiceProcessors(int $locationId, Carbon $chargeableMonth): AvailableServiceProcessors
    {
        return AvailableServiceProcessors::push($this->getProcessorsFromLocationServiceCodes($locationId, $chargeableMonth));
    }

    public function paginatePatients(int $locationId, Carbon $chargeableMonth, int $pageSize): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->patientsQuery($locationId, $chargeableMonth)->paginate($pageSize);
    }

    public function patients(int $locationId, Carbon $monthYear): Collection
    {
        return $this->patientsQuery($locationId, $monthYear)->get();
    }

    public function patientServices(int $locationId, Carbon $monthYear): Builder
    {
        return $this->approvablePatientServicesQuery($monthYear)
            ->whereHas('patient.patientInfo', fn ($q) => $q->where('preferred_contact_location', $locationId));
    }

    public function patientsQuery(int $locationId, Carbon $monthYear): Builder
    {
        return $this->approvablePatientUsersQuery($monthYear)
            ->whereHas('patientInfo', fn ($q) => $q->where('preferred_contact_location', $locationId));
    }

    public function servicesForMonth($locationId, Carbon $chargeableMonth): Builder
    {
        return ChargeableLocationMonthlySummary::with(['chargeableService' => function ($cs) {
            $cs->select('code');
        }])
            ->where('location_id', $locationId)
            ->createdOn($chargeableMonth, 'chargeable_month')
            ->get();
    }

    private function getProcessorsFromLocationServiceCodes(int $locationId, Carbon $chargeableMonth): array
    {
        return $this->servicesForMonth($locationId, $chargeableMonth)
            ->map([$this, 'getProcessorUsingCode']);
    }

    private function getProcessorUsingCode(ChargeableLocationMonthlySummary $clms): PatientChargeableServiceProcessor
    {
        return $clms->chargeableService->processor();
    }
}
