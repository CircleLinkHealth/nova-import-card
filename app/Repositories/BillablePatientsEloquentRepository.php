<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use App\Algorithms\Invoicing\AlternativeCareTimePayableCalculator;
use App\Constants;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;

class BillablePatientsEloquentRepository
{
    public function billablePatients(
        $practiceId,
        Carbon $date,
        array $with = [
            'careTeamMembers',
            'patientInfo',
            'primaryPractice',
            'patientSummaries',
        ]
    ) {
        $month = $date->startOfMonth();

        $result = User::with(
            collect(
                [
                    'patientSummaries' => function ($query) use ($month) {
                        $query->where('month_year', $month)
                              ->where('total_time', '>=', AlternativeCareTimePayableCalculator::MONTHLY_TIME_TARGET_IN_SECONDS)
                              ->where('no_of_successful_calls', '>=', 1)
                              ->with('chargeableServices')
                              ->with('attestedProblems.cpmProblem')
                              ->with('attestedProblems.icd10Codes');
                    },
                    'patientInfo',
                    'primaryPractice',
                    'careTeamMembers'  => function ($q) {
                        $q->where('type', '=', 'billing_provider');
                    },
                ]
            )->reject(
                function ($item, $key) use ($with) {
                    if (in_array($key, $with)) {
                        return false;
                    }
                    if (in_array($item, $with)) {
                        return false;
                    }
                    
                    return true;
                }
            )->all()
        )
            ->has('patientInfo')
            ->whereHas(
                          'patientSummaries',
                          function ($query) use ($month) {
                              $query->where('month_year', $month)
                                  ->where('total_time', '>=', AlternativeCareTimePayableCalculator::MONTHLY_TIME_TARGET_IN_SECONDS)
                                  ->where('no_of_successful_calls', '>=', 1);
                          }
                      )
            ->ofType('participant')
            ->where('program_id', '=', $practiceId);

        return $result;
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
                                                                   'careTeamMembers'  => function ($q) {
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
