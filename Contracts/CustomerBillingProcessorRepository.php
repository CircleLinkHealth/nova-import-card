<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

interface CustomerBillingProcessorRepository
{
    public function patients(int $customerModelId, Carbon $monthYear): Builder;
    
    public function paginatePatients(int $customerModelId, Carbon $monthYear, int $pageSize): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    public function patientServices(int $customerModelId, Carbon $monthYear): Builder;
}
