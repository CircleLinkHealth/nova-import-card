<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 12/05/2017
 * Time: 6:39 PM
 */

namespace App\Repositories;


use App\PatientMonthlySummary;
use App\User;
use Carbon\Carbon;

class BillablePatientsEloquentRepository
{
    public function billablePatients($practiceId, Carbon $date)
    {
        $month = $date->startOfMonth();

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

    public function billablePatientSummaries($practiceId, Carbon $date)
    {
        $month = $date->startOfMonth();

        $result = PatientMonthlySummary::orderBy('needs_qa', 'desc')
                                       ->where('month_year', $month)
                                       ->where('ccm_time', '>=', 1200)
                                       ->with([
                                           'patient' => function ($q) use ($month, $practiceId) {
                                               $q->with([
                                                   'ccdProblems'     => function ($query) {
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
                                           'chargeableServices'
                                       ])
                                       // ->has('patient.patientInfo')
                                       ->whereHas('patient.practices', function ($q) use ($practiceId) {
                                           $q->where('id', '=', $practiceId);
                                       });

        return $result;
    }

    public function patientsWithSummaries($practiceId, Carbon $date)
    {
        $month = $date->startOfMonth();

        return User::whereHas('patientSummaries', function ($query) use ($month) {
                       $query->where('month_year', $month)
                             ->where('ccm_time', '>=', 1200);
                   })
                   ->ofType('participant')
                   ->where('program_id', '=', $practiceId);
    }
}