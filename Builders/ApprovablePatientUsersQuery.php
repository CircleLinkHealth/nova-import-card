<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CcmBilling\Builders;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use CircleLinkHealth\Customer\Entities\User;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;
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
        $relations = [
            'primaryPractice'         => fn ($p)         => $p->with(['chargeableServices', 'pcmProblems', 'rpmProblems']),
            'endOfMonthCcmStatusLogs' => function ($q) use ($monthYear) {
                $q->createdOnIfNotNull($monthYear, 'chargeable_month');
            },
            'attestedProblems' => function ($q) use ($monthYear) {
                $q->with('ccdProblem.cpmProblem')
                    ->createdOnIfNotNull($monthYear, 'chargeable_month');
            },
            'billingProvider.user',
            'patientInfo.location.chargeableServiceSummaries' => function ($q) use ($monthYear) {
                $q->with(['chargeableService'])
                    ->createdOnIfNotNull($monthYear, 'chargeable_month');
            },
            'ccdProblems' => function ($problem) {
                $problem->forBilling();
            },
            'chargeableMonthlySummaries' => function ($q) use ($monthYear) {
                $q->with(['chargeableService'])
                    ->createdOnIfNotNull($monthYear, 'chargeable_month');
            },
            'forcedChargeableServices' => function ($f) use ($monthYear) {
                $f->where(fn ($q)    => $q->when( ! is_null($monthYear), fn ($q) => $q->where('chargeable_month', $monthYear)))
                        ->orWhere(fn ($q) => $q->where('chargeable_month', null));
            },
        ];

        if (Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG)) {
            $relations['chargeableMonthlySummariesView'] = function ($q) use ($monthYear) {
                $q->createdOnIfNotNull($monthYear, 'chargeable_month');
            };
            $relations['monthlyBillingStatus'] = function ($q) use ($monthYear) {
                $q->createdOnIfNotNull($monthYear, 'chargeable_month');
            };
        }

        return User::with($relations)->ofType('participant');
    }
}
