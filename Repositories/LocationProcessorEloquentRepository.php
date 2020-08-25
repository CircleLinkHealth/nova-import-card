<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientServicesQuery;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientUsersQuery;
use CircleLinkHealth\CcmBilling\Builders\AvailableLocationServicesQuery;
use CircleLinkHealth\CcmBilling\Contracts\CustomerBillingProcessorRepository;
use CircleLinkHealth\CcmBilling\Entities\ChargeableLocationMonthlySummary;
use CircleLinkHealth\CcmBilling\ValueObjects\AvailableServiceProcessors;
use Illuminate\Database\Eloquent\Builder;

class LocationProcessorEloquentRepository implements CustomerBillingProcessorRepository
{
    use ApprovablePatientServicesQuery;
    use ApprovablePatientUsersQuery;
    use AvailableLocationServicesQuery;

    public function availableLocationServiceProcessors(int $locationId, Carbon $chargeableMonth): AvailableServiceProcessors
    {
        return AvailableServiceProcessors::push($this->getProcessorsFromLocationServiceCodes($locationId, $chargeableMonth));
    }

    public function patients(int $locationId, Carbon $monthYear): Builder
    {
        return $this->approvablePatientUsersQuery($monthYear)
            ->whereHas('patientInfo', fn ($q) => $q->where('preferred_contact_location', $locationId));
    }
    
    public function paginatePatients(int $locationId, Carbon $chargeableMonth, int $pageSize) : \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->patients($locationId, $chargeableMonth)->paginate($pageSize);
        
    }

    public function patientServices(int $locationId, Carbon $monthYear): Builder
    {
        return $this->approvablePatientServicesQuery($monthYear)
            ->whereHas('patient.patientInfo', fn ($q) => $q->where('preferred_contact_location', $locationId));
    }

    private function getProcessorsFromLocationServiceCodes(int $locationId, Carbon $chargeableMonth): array
    {
        return $this->servicesForMonth($locationId, $chargeableMonth)->get()->map([$this, 'getProcessorUsingCode']);
    }

    private function getProcessorUsingCode(ChargeableLocationMonthlySummary $clms)
    {
        return $clms->chargeableService->processor();
    }
}
