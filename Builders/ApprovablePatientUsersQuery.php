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
    public function approvablePatientUserQuery(int $patientId, Carbon $monthYear): Builder
    {
        return $this->approvablePatientUsersQuery($monthYear)
            ->where('id', $patientId);
    }

    public function approvablePatientUsersQuery(Carbon $monthYear): Builder
    {
        return User::with([
            'endOfMonthCcmStatusLogs' => function ($q) use ($monthYear) {
                $q->createdOn($monthYear, 'chargeable_month');
            },
            'attestedProblems' => function ($q) use ($monthYear) {
                $q->createdOn($monthYear, 'chargeable_month');
            },
            'billingProvider.user',
            'patientInfo',
            'ccdProblems' => function ($problem) {
                $problem->isBillable();
            },
            'chargeableMonthlySummaries' => function ($q) use ($monthYear) {
                $q->with(['chargeableService'])
                    ->createdOn($monthYear, 'chargeable_month');
            },
        ]);
    }
}
