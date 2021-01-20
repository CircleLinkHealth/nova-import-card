<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

interface PracticeProcessorRepository
{
    public function billingStatuses(int $practiceId, Carbon $month);

    public function closeMonth(int $actorId, int $practiceId, Carbon $month);

    public function openMonth(int $practiceId, Carbon $month);

    public function paginatePatients(int $customerModelId, Carbon $chargeableMonth, int $pageSize): LengthAwarePaginator;

    public function patientServices(int $practiceId, Carbon $month): Builder;
}
