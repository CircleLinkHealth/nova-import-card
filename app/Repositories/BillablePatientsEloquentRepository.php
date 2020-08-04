<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
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
            $relations
                ? $relations
                : BillableCPMPatientRelations::getDefaultWith($date)
        )
            ->has('patientInfo')
            ->whereHas(
                'patientSummaries',
                function ($query) use ($month, $showApprovedOnly) {
                    $query->where([
                        ['month_year', '=', $month],
                        ['no_of_successful_calls', '>=', 1],
                    ])
                        ->where(function ($q) {
                            $q->where(
                                'ccm_time',
                                '>=',
                                AlternativeCareTimePayableCalculator::MONTHLY_TIME_TARGET_IN_SECONDS
                            )
                                ->orWhere(
                                    'bhi_time',
                                    '>=',
                                    AlternativeCareTimePayableCalculator::MONTHLY_TIME_TARGET_IN_SECONDS
                                );
                        })
                        ->when($showApprovedOnly, function ($q) {
                        $q->where('approved', '=', true);
                    });
                }
            )
            ->ofType('participant')
            ->ofPractice($practiceId);
    }

    public function billablePatientSummaries($practiceId, Carbon $date, $ignoreWith = false)
    {
        $month = $date->startOfMonth();

        return PatientMonthlySummary::orderBy('needs_qa', 'desc')
            ->orderBy('no_of_successful_calls', 'asc')
            ->orderBy('rejected', 'asc')
            ->where('month_year', $month)
            ->where(function ($q) {
                $q->where(
                    'ccm_time',
                    '>=',
                    AlternativeCareTimePayableCalculator::MONTHLY_TIME_TARGET_IN_SECONDS
                )
                    ->orWhere(
                        'bhi_time',
                        '>=',
                        AlternativeCareTimePayableCalculator::MONTHLY_TIME_TARGET_IN_SECONDS
                    );
            })
            ->when(false === $ignoreWith, function ($q) use ($month, $practiceId) {
                return $q->with([
                    'attestedProblems' => function ($problem) {
                        $problem->with(['cpmProblem', 'codes']);
                    },
                    'patient' => function ($q) use ($month, $practiceId) {
                        $q->with([
                            'billingProvider',
                            'patientInfo' => function ($q) use ($month) {
                                $q->with([
                                    'ccmStatusRevisions' => function ($q) use ($month) {
                                        $endOfMonth = $month->endOfMonth();
                                        $q->where('created_at', '>=', $endOfMonth)->limit(1);
                                    },
                                ]);
                            },
                            'primaryPractice.chargeableServices',
                            'careTeamMembers' => function ($q) {
                                $q->where('type', '=', 'billing_provider');
                            },
                            'ccdProblems' => function ($problem) {
                                $problem->with(['cpmProblem', 'codes', 'icd10Codes']);
                            },
                        ]);
                    },
                    'chargeableServices',
                ]);
            })
            ->whereHas(
                'patient',
                function ($q) use ($practiceId) {
                    $q->whereHas('practices', function ($q) use ($practiceId) {
                        $q->where('id', '=', $practiceId);
                    })
                        ->orWhereHas('primaryPractice', function ($q) use ($practiceId) {
                            $q->where('id', '=', $practiceId);
                        });
                }
            );
    }
}
