<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Tests\Fakes\Repositories\Location;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\CustomerProcessorRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Eloquent implements CustomerProcessorRepository
{
    public function paginatePatients(int $customerModelId, Carbon $monthYear, int $pageSize): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        // TODO: Implement paginatePatients() method.
    }

    public function patients(int $customerModelId, Carbon $monthYear): Collection
    {
        // TODO: Implement patients() method.
    }

    public function patientServices(int $customerModelId, Carbon $monthYear): Builder
    {
        // TODO: Implement patientServices() method.
    }

    public function patientsQuery(int $customerModelId, Carbon $monthYear): Builder
    {
        // TODO: Implement patientsQuery() method.
    }
}
