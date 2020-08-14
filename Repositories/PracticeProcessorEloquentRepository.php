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

class PracticeProcessorEloquentRepository implements CustomerBillingProcessorRepository
{
    use BillablePatientUsersQuery;

    public function patients(int $customerModelId, Carbon $monthYear, int $pageSize): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->billablePatientUsersQuery($monthYear)
            ->ofPractice($customerModelId);
    }

    public function patientServices(int $customerModelId, Carbon $monthYear): Builder
    {
        return BillableMonthlyChargeableServicesQuery::query($monthYear)
            ->whereHas('patient', fn ($q) => $q->ofPractice($customerModelId));
    }
}
