<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Builders;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\PatientMonthlyBillingStatus;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\ChargeableService;
use Illuminate\Database\Eloquent\Builder;

trait ApprovableBillingStatusesQuery
{
    public function approvableBillingStatusesQuery(array $locationIds, Carbon $monthYear, $withRelations = false): Builder
    {
        $twentyMinIds = ChargeableService::cached()
            ->whereIn('code', [ChargeableService::CCM, ChargeableService::BHI, ChargeableService::RPM, ChargeableService::GENERAL_CARE_MANAGEMENT])
            ->pluck('id')
            ->toArray();

        $thirtyMinIds = ChargeableService::cached()
            ->whereIn('code', [ChargeableService::PCM])
            ->pluck('id')
            ->toArray();

        return PatientMonthlyBillingStatus::orderByRaw('FIELD(status, "needs_qa", "rejected", "approved")')
            ->where('chargeable_month', '=', $monthYear)
            ->whereHas('patientUser', fn ($q) => $q->patientInLocations($locationIds))
            ->whereHas('chargeableMonthlyTime', function ($q) use ($monthYear, $twentyMinIds, $thirtyMinIds) {
                $q->createdOnIfNotNull($monthYear, 'chargeable_month')
                    ->where(function ($q) use ($twentyMinIds, $thirtyMinIds) {
                        $q->where(function ($q) use ($twentyMinIds) {
                            $q->whereIn('chargeable_service_id', $twentyMinIds)
                                ->where('total_time', '>=', CpmConstants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS);
                        })
                            ->orWhere(function ($q) use ($thirtyMinIds) {
                                $q->whereIn('chargeable_service_id', $thirtyMinIds)
                                    ->where('total_time', '>=', CpmConstants::MONTHLY_BILLABLE_PCM_TIME_TARGET_IN_SECONDS);
                            });
                    });
            })
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
            'chargeableMonthlyTime'      => fn ($q)      => $q->createdOnIfNotNull($monthYear, 'chargeable_month'),
            'patientInfo'                => fn ($q)                => $q->with(['location']),
            'attestedProblems'           => function ($q) use ($monthYear) {
                $q->with('ccdProblem.cpmProblem')
                    ->createdOnIfNotNull($monthYear, 'chargeable_month');
            },
        ];
    }
}
