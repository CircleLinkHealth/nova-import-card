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

class ApproveBillablePatientsRepository
{
    public function billablePatients($practiceId, Carbon $date)
    {
        $month = $date->firstOfMonth()->toDateString();

        return User::with([
            'ccdProblems'      => function ($query) {
                $query->where('cpm_problem_id', '!=', 1)
                      ->with(['icd10Codes', 'cpmProblem']);
            },
            'billableProblems',
            'patientSummaries' => function ($query) use ($month) {
                $query->where('month_year', $month)
                      ->where('ccm_time', '>=', 1200);
            },
            'cpmProblems',
            'patientInfo',
            'primaryPractice',
        ])
                   ->has('patientInfo')
                   ->whereHas('patientSummaries', function ($query) use ($month) {
                       $query->where('month_year', $month)
                             ->where('ccm_time', '>=', 1200);
                   })
                   ->ofType('participant')
                   ->where('program_id', '=', $practiceId);
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