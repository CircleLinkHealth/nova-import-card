<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use App\Constants;
use App\Relationships\BillableCPMPatientRelations;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;

class BillablePatientsEloquentRepository
{
    public function billablePatients(
        $practiceId,
        Carbon $date,
        array $relations = null,
        bool $showApprovedOnly = false
    ) {
        $month = $date->startOfMonth();

        return User::with(
            $relations ? $relations : BillableCPMPatientRelations::getDefaultWith($date)
        )
            ->has('patientInfo')
            ->whereHas(
                'patientSummaries',
                function ($query) use ($month, $showApprovedOnly) {
                    $wheres = [
                        ['month_year', '=', $month],
                        ['total_time', '>=', AlternativeCareTimePayableCalculator::MONTHLY_TIME_TARGET_IN_SECONDS],
                        ['no_of_successful_calls', '>=', 1],
                    ];

                    if (true === $showApprovedOnly) {
                        $wheres[] = ['approved', '=', true];
                    }

                    $query->where($wheres);
                }
            )
            ->ofType('participant')
            ->ofPractice($practiceId);
    }

    public function billablePatientSummaries($practiceId, Carbon $date, $ignoreWith = false)
    {
        $month = $date->startOfMonth();

        $result = PatientMonthlySummary::with(
            [
                'attestedProblems' => function ($problem) {
                    $problem->with(['cpmProblem', 'codes']);
                },
            ]
        )
            ->orderBy('needs_qa', 'desc')
            ->orderBy('no_of_successful_calls', 'asc')
            ->orderBy('rejected', 'asc')
            ->where('month_year', $month)
            ->where(
                function ($q) {
                    $q->where('ccm_time', '>=', AlternativeCareTimePayableCalculator::MONTHLY_TIME_TARGET_IN_SECONDS)
                        ->orWhere('bhi_time', '>=', AlternativeCareTimePayableCalculator::MONTHLY_TIME_TARGET_IN_SECONDS);
                }
            )
            ->when(
                false === $ignoreWith,
                function ($q) use ($month, $practiceId) {
                    return $q->with(
                                                   [
                                                       'patient' => function ($q) use ($month, $practiceId) {
                                                           $q->with(
                                                               [
                                                                   'patientInfo',
                                                                   'primaryPractice',
                                                                   'careTeamMembers' => function ($q) {
                                                                       $q->where('type', '=', 'billing_provider');
                                                                   },
                                                               ]
                                                           );
                                                       },
                                                       'chargeableServices',
                                                   ]
                                               );
                }
            )
            ->whereHas(
                'patient',
                function ($q) use ($practiceId) {
                    $q->whereHas(
                                                   'practices',
                                                   function ($q) use ($practiceId) {
                                                       $q->where('id', '=', $practiceId);
                                                   }
                                               )->orWhereHas(
                                                   'primaryPractice',
                                                   function ($q) use ($practiceId) {
                                                       $q->where('id', '=', $practiceId);
                                                   }
                                               );
                }
            );

        return $result;
    }
}
