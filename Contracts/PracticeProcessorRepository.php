<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

interface PracticeProcessorRepository
{
    public function closeMonth(int $actorId, int $practiceId, Carbon $month);

    public function openMonth(int $practiceId, Carbon $month);

    public function paginatePatients(int $customerModelId, Carbon $chargeableMonth, int $pageSize): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    public function patientServices(int $practiceId, Carbon $month): Builder;
}
