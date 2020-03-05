<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Traits;

use App\Services\OpsDashboardService;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\TimeTracking\Entities\PageTimer;

trait NursePerformanceCalculations
{
    /**
     * In dashboard is named : "Avg CCM Time Per Successful Patient". It changes daily (Similar to case completion).
     *
     * @param $patientsForMonth
     *
     * @return float|int
     */
    public function estAvgCCMTimePerMonth(Carbon $date, $patientsForMonth)
    {
//        Is completed patient different than successful patient?
        $totalAmountOfCompletedPatientsPerMonth = $this->getTotalCompletedPatientsOfNurse($date, $patientsForMonth);
        $totalCCMtimeOnCompletedPatients        = 0 === $this->queryPatientMonthlySum($date, $patientsForMonth)->sum('ccm_time') ? 1
            : $this->queryPatientMonthlySum($date, $patientsForMonth)->sum('ccm_time');

        return round((float) (($totalAmountOfCompletedPatientsPerMonth / $totalCCMtimeOnCompletedPatients) * 100), 2);
    }

    public function estHoursToCompleteCaseLoadMonth(User $nurse, Carbon $date, $patientsForMonth)
    {
        $avgCompletionPerPatient = $this->getAvgCompletionTime($nurse, $date, $patientsForMonth);
        $incompletePatientsCount = $this->getIncompletePatientsCount($patientsForMonth);

        return round(($avgCompletionPerPatient * $incompletePatientsCount) / 60, 1);
        //        This is the old calculation
//        return round($patients->where('patient_time', '<', 20)->sum('patient_time_left') / 60, 1);
    }

    public function getAvgCompletionTime(User $nurse, Carbon $date, $patientsForMonth)
    {
        $start = $date->startOfMonth()->toDateString();
        $end   = $date->endOfMonth()->toDateString();

        $totalCompletedPatientsForMonth = $this->getTotalCompletedPatientsOfNurse($date, $patientsForMonth);

        $totalCPMtimeForMonth = $nurse->pageTimersAsProvider()
            ->where('start_time', '>=', $start)
            ->where('start_time', '<=', $end)
            ->sum('billable_duration');

        if (0 === $totalCPMtimeForMonth) {
            $totalCPMtimeForMonth = 1;
        }

        return ($totalCompletedPatientsForMonth / $totalCPMtimeForMonth) * 100;
    }

    public function getIncompletePatientsCount($patientsForMonth)
    {
        $incompletePatients = [];
        foreach ($patientsForMonth as $patient) {
            $successfulCalls = $patient->successful_calls;
//             If its the opposite of "completed" then this should be enough.
            if (0 === $successfulCalls) {
                $incompletePatients[] = $patient->patient_id;
            }
        }

        return collect($incompletePatients)->count();
    }

    /**
     * @param $nurse
     * @param $date
     *
     * @return mixed
     */
    public function getTotalMonthSystemTimeSeconds($nurse, $date)
    {
        return PageTimer::where('provider_id', $nurse->id)
            ->createdInMonth($date, 'start_time')
            ->sum('billable_duration');
    }

    /**
     * Amount of completed patients for the month.
     *
     * @param $date
     * @param $patientsForMonth
     *
     * @return int
     */
    private function getTotalCompletedPatientsOfNurse($date, $patientsForMonth)
    {
        return $this->queryPatientMonthlySum($date, $patientsForMonth)->count();
    }

    /**
     * @param $patientsForMonth
     *
     * @return \Illuminate\Support\Collection
     */
    private function queryPatientMonthlySum(Carbon $date, $patientsForMonth)
    {
        $results = [];
        foreach ($patientsForMonth as $patient) {
            if ($patient->successful_calls >= 1) {
                $patientMonthlySum = PatientMonthlySummary::with('patient')
                    ->where('patient_id', $patient->patient_id)
                    ->where('ccm_time', '>=', OpsDashboardService::TWENTY_MINUTES) // should i use > or >=?
//                    ->where('no_of_successful_calls', '>=', 1)
                    ->where('month_year', $date->startOfMonth())
                    ->first();

                if ( ! empty($patientMonthlySum)) {
                    $results[] = $patientMonthlySum;
                }
            }
        }

        return collect($results);
    }
}
