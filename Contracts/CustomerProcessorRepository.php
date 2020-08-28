<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface CustomerProcessorRepository
{
    public function paginatePatients(int $customerModelId, Carbon $monthYear, int $pageSize): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    public function patients(int $customerModelId, Carbon $monthYear): Collection;

    public function patientServices(int $customerModelId, Carbon $monthYear): Builder;

    public function patientsQuery(int $customerModelId, Carbon $monthYear): Builder;
}
