<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;

class BillablePatientsEloquentRepository
{
    public function billablePatients($practiceId, Carbon $date)
    {
        $month = $date->startOfMonth();

        $result = User::with([
            'ccdProblems' => function ($query) {
                $query->with(['icd10Codes', 'cpmProblem']);
            },
            'patientSummaries' => function ($query) use ($month) {
                $query->where('month_year', $month)
                    ->where('total_time', '>=', 1200)
                    ->with('chargeableServices');
            },
            'cpmProblems',
            'patientInfo',
            'primaryPractice',
            'careTeamMembers' => function ($q) {
                $q->where('type', '=', 'billing_provider');
            },
        ])
            ->has('patientInfo')
            ->whereHas('patientSummaries', function ($query) use ($month) {
                $query->where('month_year', $month)
                    ->where('total_time', '>=', 1200);
            })
            ->ofType('participant')
            ->where('program_id', '=', $practiceId);

        return $result;
    }

    public function billablePatientSummaries($practiceId, Carbon $date)
    {
        $month = $date->startOfMonth();

        $result = PatientMonthlySummary::orderBy('needs_qa', 'desc')
            ->where('month_year', $month)
            ->where(function ($q) {
                $q->where('ccm_time', '>=', 1200)
                    ->orWhere('bhi_time', '>=', 1200);
            })
            ->with([
                'patient' => function ($q) use ($month, $practiceId) {
                    $q->with([
                        'ccdProblems' => function ($query) {
                            $query->with(['icd10Codes', 'cpmProblem']);
                        },
                        'cpmProblems',
                        'patientInfo',
                        'primaryPractice',
                        'careTeamMembers' => function ($q) {
                            $q->where('type', '=', 'billing_provider');
                        },
                    ]);
                },
                'chargeableServices',
            ])
            ->whereHas('patient', function ($q) use ($practiceId) {
                $q->whereHas('practices', function ($q) use ($practiceId) {
                    $q->where('practices.id', '=', $practiceId);
                });
            });

        return $result;
    }

    public function patientsWithSummaries($practiceId, Carbon $date)
    {
        $month = $date->startOfMonth();

        return User::whereHas('patientSummaries', function ($query) use ($month) {
            $query->where('month_year', $month)
                ->where('total_time', '>=', 1200);
        })
            ->ofType('participant')
            ->where('program_id', '=', $practiceId);
    }
}
