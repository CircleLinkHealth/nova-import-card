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

class PracticeProcessorEloquentRepository implements CustomerBillingProcessorRepository
{
    use ApprovablePatientServicesQuery;
    use ApprovablePatientUsersQuery;

    public function patients(int $customerModelId, Carbon $monthYear, int $pageSize): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->approvablePatientUsersQuery($monthYear)
            ->ofPractice($customerModelId);
    }

    public function patientServices(int $customerModelId, Carbon $monthYear): Builder
    {
        return $this->approvablePatientServicesQuery($monthYear)
            ->whereHas('patient', fn ($q) => $q->ofPractice($customerModelId));
    }
}
