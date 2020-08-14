<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Modules\CcmBilling\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientServicesQuery;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientUsersQuery;
use CircleLinkHealth\CcmBilling\Contracts\CustomerBillingProcessorRepository;
use Illuminate\Database\Eloquent\Builder;

class LocationProcessorEloquentRepository implements CustomerBillingProcessorRepository
{
    use ApprovablePatientServicesQuery;
    use ApprovablePatientUsersQuery;
    
    public function patientServices(int $locationId, Carbon $monthYear): Builder
    {
        return $this->approvablePatientServicesQuery($monthYear)
            ->whereHas('patient.patientInfo', fn ($q) => $q->where('preferred_contact_location', $locationId));
    }
    
    public function patients(int $locationId, Carbon $monthYear, int $pageSize):\Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->approvablePatientUsersQuery($monthYear)
            ->whereHas('patientInfo', fn ($q) => $q->where('preferred_contact_location', $locationId))
            ->paginate($pageSize);
    }
}
