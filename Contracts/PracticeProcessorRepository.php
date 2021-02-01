<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

interface PracticeProcessorRepository
{
    public function approvableBillingStatuses(int $practiceId, Carbon $month, bool $withRelations = false): Builder;

    public function approvedBillingStatuses(int $practiceId, Carbon $month, bool $withRelations = false): Builder;

    public function closeMonth(int $actorId, int $practiceId, Carbon $month);

    public function openMonth(int $practiceId, Carbon $month);

    public function patientServices(int $practiceId, Carbon $month): Builder;
}
