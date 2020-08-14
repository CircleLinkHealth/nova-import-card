<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Modules\CcmBilling\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Builders\BillableMonthlyChargeableServicesQuery;
use CircleLinkHealth\CcmBilling\Builders\BillablePatientUsersQuery;
use CircleLinkHealth\CcmBilling\Contracts\CustomerBillingProcessorRepository;
use Illuminate\Database\Eloquent\Builder;

class LocationProcessorEloquentRepository implements CustomerBillingProcessorRepository
{
    use BillablePatientUsersQuery;
    
    public function patientServicesQuery(int $locationId, Carbon $monthYear): Builder
    {
        return BillableMonthlyChargeableServicesQuery::query($monthYear)
            ->whereHas('patient.patientInfo', fn ($q) => $q->where('preferred_contact_location', $locationId));
    }
    
    public function patients(int $locationId, Carbon $monthYear, int $pageSize):\Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->billablePatientUsersQuery($monthYear)
            ->whereHas('patientInfo', fn ($q) => $q->where('preferred_contact_location', $locationId))
            ->paginate($pageSize);
    }
}
