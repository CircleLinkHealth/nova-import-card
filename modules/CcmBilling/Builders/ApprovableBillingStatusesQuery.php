<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Builders;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use Illuminate\Database\Eloquent\Builder;

trait ApprovableBillingStatusesQuery
{
    public function approvableBillingStatusesQuery(array $locationIds, Carbon $monthYear, $withRelations = false): Builder
    {
        //note: due to issues with Laravel Pagination and orderByRaw, please make sure ABP works if for some reason you're editing
        return PatientMonthlyBillingStatus::orderByRaw("CASE
            WHEN status = 'needs_qa' THEN 1
            WHEN status = 'rejected' THEN 2
            ELSE 3
            END ASC, patient_user_id ASC")
            ->where('chargeable_month', '=', $monthYear)
            ->whereHas('patientUser', fn ($q) => $q->patientInLocations($locationIds))
            ->whereHas('patientUser.chargeableMonthlySummaries', fn ($q) => $q->createdOnIfNotNull($monthYear, 'chargeable_month')->where('is_fulfilled', '=', true))
            ->when($withRelations, function ($q) use ($monthYear) {
                return $q->with([
                    'patientUser' => fn ($q) => $q->with(array_merge($this->sharedUserRelations($monthYear), [
                        'endOfMonthCcmStatusLogs' => function ($q) use ($monthYear) {
                            $q->createdOnIfNotNull($monthYear, 'chargeable_month');
                        },
                        'ccdProblems' => function ($problem) {
                            $problem->forBilling();
                        },
                    ])),
                ]);
            });
    }

    public function approvedBillingStatusesQuery(array $locationIds, Carbon $monthYear, $withRelations = false): Builder
    {
        return PatientMonthlyBillingStatus::where('chargeable_month', '=', $monthYear)
            ->where('status', '=', 'approved')
            ->whereHas('patientUser', fn ($q) => $q->patientInLocations($locationIds))
            ->when($withRelations, function ($q) use ($monthYear) {
                return $q->with([
                    'patientUser' => fn ($q) => $q->with($this->sharedUserRelations($monthYear)),
                ]);
            });
    }

    private function sharedUserRelations(Carbon $monthYear): array
    {
        return [
            'billingProvider.user' => function ($q) {
                $q->with([
                    'providerInfo' => fn ($q) => $q->select(['id', 'user_id', 'specialty']),
                ])->select(['id', 'first_name', 'last_name', 'display_name', 'suffix']);
            },
            'chargeableMonthlySummaries' => fn ($q) => $q->createdOnIfNotNull($monthYear, 'chargeable_month'),
            'forcedChargeableServices'   => fn ($q)   => $q->where('chargeable_month', $monthYear->copy()->startOfMonth()->toDateString())->orWhereNull('chargeable_month'),
            'patientSummaries'           => fn ($q)           => $q->with(['allChargeableServices'])->createdOnIfNotNull($monthYear, 'month_year'),
            'chargeableMonthlyTime'      => fn ($q)      => $q->createdInMonthFromDateTimeField($monthYear, 'performed_at'),
            'patientInfo'                => fn ($q)                => $q->with(['location']),
            'attestedProblems'           => function ($q) use ($monthYear) {
                $q->with('ccdProblem.cpmProblem')
                    ->createdOnIfNotNull($monthYear, 'chargeable_month');
            },
        ];
    }
}
