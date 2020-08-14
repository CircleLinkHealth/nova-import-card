<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Processors;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Contracts\CustomerBillingProcesor;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Builder;

class Location implements CustomerBillingProcesor
{
    private $locationsIds;

    /**
     * Location constructor.
     */
    public function __construct(array $locationsIds)
    {
        $this->locationsIds = $locationsIds;
    }

    public function billablePatientsQuery(Carbon $monthYear): Builder
    {
        return
            User::with([
                'endOfMonthCcmStatusLog' => function ($q) use ($monthYear) {
                    $q->createdOn($monthYear, 'month_year');
                },
                'patientMonthlySummaries' => function ($q) use ($monthYear) {
                    $q->createdOn($monthYear, 'month_year');
                },
                'attestedProblems' => function ($q) use ($monthYear) {
                    $q
                        ->with([
                            'cpmProblem',
                            'icd10Codes',
                        ])
                        ->createdOn($monthYear, 'month_year');
                },
                'billingProvider.user',
                'patientInfo',
                'ccdProblems' => function ($problem) {
                    $problem->with(['cpmProblem', 'codes', 'icd10Codes']);
                },
                'chargeableMonthlySummary' => function ($q) use ($monthYear) {
                    $q->createdOn($monthYear, 'month_year');
                },
            ])->whereHas('patientInfo', fn ($q) => $q->whereIn('preferred_contact_location', $this->locationsIds));
    }

    public function patientBillableServicesQuery(Carbon $monthYear): Builder
    {
        // TODO: Implement patientBillableServicesQuery() method.
    }
}
