<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Builders;

use Carbon\Carbon;

class BillableMonthlyChargeableServicesQuery
{
    public static function query(Carbon $monthYear)
    {
//        return User::with([
//            'endOfMonthCcmStatusLog' => function ($q) use ($monthYear) {
//                $q->createdOn($monthYear, 'month_year');
//            },
//            'patientMonthlySummaries' => function ($q) use ($monthYear) {
//                $q->createdOn($monthYear, 'month_year');
//            },
//            'attestedProblems' => function ($q) use ($monthYear) {
//                $q
//                    ->with([
//                        'cpmProblem',
//                        'icd10Codes',
//                    ])
//                    ->createdOn($monthYear, 'month_year');
//            },
//            'billingProvider.user',
//            'patientInfo',
//            'ccdProblems' => function ($problem) {
//                $problem->with(['cpmProblem', 'codes', 'icd10Codes']);
//            },
//            'chargeableMonthlySummary' => function ($q) use ($monthYear) {
//                $q->createdOn($monthYear, 'month_year');
//            },
//        ]);
    }
}
