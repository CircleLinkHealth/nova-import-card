<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Builders;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;

trait ApprovablePatientUsersQuery
{
    public function approvablePatientUserQuery(int $patientId, Carbon $monthYear = null): Builder
    {
        return $this->approvablePatientUsersQuery($monthYear)
            ->where('id', $patientId);
    }

    public function approvablePatientUsersQuery(Carbon $monthYear = null): Builder
    {
        return User::with([
            'endOfMonthCcmStatusLogs' => function ($q) use ($monthYear) {
                $q->createdOnIfNotNull($monthYear, 'chargeable_month');
            },
            'attestedProblems' => function ($q) use ($monthYear) {
                $q->createdOnIfNotNull($monthYear, 'chargeable_month');
            },
            'billingProvider.user',
            'patientInfo',
            'ccdProblems' => function ($problem) {
                $problem->isBillable();
            },
            'chargeableMonthlySummaries' => function ($q) use ($monthYear) {
                $q->with(['chargeableService'])
                    ->createdOnIfNotNull($monthYear, 'chargeable_month');
            },
            'chargeableMonthlySummariesView' => function ($q) use ($monthYear) {
                $q->createdOnIfNotNull($monthYear, 'chargeable_month');
            },
        ]);
    }
}
