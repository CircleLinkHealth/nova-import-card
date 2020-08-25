<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientServicesQuery;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientUsersQuery;
use CircleLinkHealth\CcmBilling\Contracts\CustomerBillingProcessorRepository;
use Illuminate\Database\Eloquent\Builder;

class PracticeProcessorEloquentRepository implements CustomerBillingProcessorRepository
{
    use ApprovablePatientServicesQuery;
    use ApprovablePatientUsersQuery;

    public function paginatePatients(int $customerModelId, Carbon $chargeableMonth, int $pageSize): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->patients($customerModelId, $chargeableMonth)
            ->paginate($pageSize);
    }

    public function patients(int $customerModelId, Carbon $monthYear): Builder
    {
        return $this->approvablePatientUsersQuery($monthYear)
            ->ofPractice($customerModelId);
    }

    public function patientServices(int $customerModelId, Carbon $monthYear): Builder
    {
        //todo: fix relationship name
        return $this->approvablePatientServicesQuery($monthYear)
            ->whereHas('patient', fn ($q) => $q->ofPractice($customerModelId));
    }
}
