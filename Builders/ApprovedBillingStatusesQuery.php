<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Builders;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use Illuminate\Database\Eloquent\Builder;

trait ApprovedBillingStatusesQuery
{
    public function approvedBillingStatusesQuery(int $practiceId, Carbon $monthYear, $withRelations = false): Builder
    {
        return PatientMonthlyBillingStatus::where('chargeable_month', '=', $monthYear)
            ->where('status', '=', 'approved')
            ->whereHas('patientUser', fn ($q) => $q->ofPractice($practiceId))
            ->when($withRelations, function ($q) use ($monthYear) {
                return $q->with([
                    'chargeableMonthlySummariesView' => fn ($q) => $q->createdOnIfNotNull($this->date, 'chargeable_month'),
                    'patientUser'                    => fn ($q)                    => $q->with([
                        'patientInfo'      => fn ($q)      => $q->with(['billingProvider.user', 'location']),
                        'attestedProblems' => function ($q) use ($monthYear) {
                            $q->with('ccdProblem.cpmProblem')
                                ->createdOnIfNotNull($monthYear, 'chargeable_month');
                        },
                    ]),
                ]);
            });
    }
}
