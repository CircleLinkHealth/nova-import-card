<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientServicesQuery;
use CircleLinkHealth\CcmBilling\Builders\ApprovablePatientUsersQuery;
use CircleLinkHealth\CcmBilling\Contracts\PracticeProcessorRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PracticeProcessorEloquentRepository implements PracticeProcessorRepository
{
    use ApprovablePatientServicesQuery;
    use ApprovablePatientUsersQuery;

    public function paginatePatients(int $customerModelId, Carbon $chargeableMonth, int $pageSize): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->patientsQuery($customerModelId, $chargeableMonth)
            ->paginate($pageSize);
    }

    public function patients(int $customerModelId, Carbon $monthYear): Collection
    {
        return $this->patientsQuery($customerModelId, $monthYear)->get();
    }

    public function patientServices(int $practiceId, Carbon $monthYear): Builder
    {
        return $this->approvablePatientServicesQuery($monthYear)
            ->whereHas('patient', fn ($q) => $q->ofPractice($practiceId));
    }

    public function patientsQuery(int $customerModelId, Carbon $monthYear): Builder
    {
        return $this->approvablePatientUsersQuery($monthYear)
            ->ofPractice($customerModelId);
    }

    public function practiceWithLocationsWithSummaries(int $practiceId, ?Carbon $month = null): Builder
    {
        //todo: to deprecate - was initially added for auto-assign CS to newly created Location which is an idea we're probably scrapping
    }
}
