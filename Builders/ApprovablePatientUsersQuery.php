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
    public function approvablePatientUsersQuery(Carbon $monthYear): Builder
    {
        return User::with([
            'endOfMonthCcmStatusLog' => function ($q) use ($monthYear) {
                $q->createdOn($monthYear, 'month_year');
            },
            'attestedProblems' => function ($q) use ($monthYear) {
                $q->createdOn($monthYear, 'month_year');
            },
            'billingProvider.user',
            'patientInfo',
            'ccdProblems' => function ($problem) {
                $problem->with(['cpmProblem', 'codes', 'icd10Codes']);
            },
            'chargeableMonthlySummary' => function ($q) use ($monthYear) {
                $q->createdOn($monthYear, 'month_year');
            },
        ]);
    }
}
