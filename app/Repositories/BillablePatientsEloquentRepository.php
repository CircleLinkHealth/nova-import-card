<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/05/2017
 * Time: 6:39 PM
 */

namespace App\Repositories;


use App\User;
use Carbon\Carbon;

class BillablePatientsEloquentRepository
{
    public function billablePatients($practiceId, Carbon $date)
    {
        $month = $date->startOfMonth()->toDateString();

        $result = User::with([
            'ccdProblems'      => function ($query) {
                $query->with(['icd10Codes', 'cpmProblem']);
            },
            'patientSummaries' => function ($query) use ($month) {
                $query->where('month_year', $month)
                      ->where('ccm_time', '>=', 1200)
                      ->with('chargeableServices');
            },
            'cpmProblems',
            'patientInfo',
            'primaryPractice',
            'careTeamMembers'  => function ($q) {
                $q->where('type', '=', 'billing_provider');
            },
        ])
                      ->has('patientInfo')
                      ->whereHas('patientSummaries', function ($query) use ($month) {
                          $query->where('month_year', $month)
                                ->where('ccm_time', '>=', 1200);
                      })
                      ->ofType('participant')
                      ->where('program_id', '=', $practiceId);

        return $result;
    }

    public function patientsWithSummaries($practiceId, Carbon $date)
    {
        $month = $date->firstOfMonth()->toDateString();

        return User::with([
            'patientSummaries' => function ($query) use ($month) {
                $query->where('month_year', $month)
                      ->where('ccm_time', '>=', 1200);
            },
        ])
                   ->whereHas('patientSummaries', function ($query) use ($month) {
                       $query->where('month_year', $month)
                             ->where('ccm_time', '>=', 1200);
                   })
                   ->ofType('participant')
                   ->where('program_id', '=', $practiceId);
    }
}