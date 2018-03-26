<?php
/**
 * Created by PhpStorm.
 * User: kakoushias
 * Date: 06/03/2018
 * Time: 12:21 AM
 */

namespace App\Services;


use App\Patient;
use App\Practice;
use App\Repositories\OpsDashboardPatientEloquentRepository;
use App\User;
use Carbon\Carbon;

class OpsDashboardService
{

    private $repo;

    public function __construct()
    {
        $this->repo = new OpsDashboardPatientEloquentRepository();
    }

    /**
     * get total patients, return count for each time category, for each status category
     *
     * @param Carbon $date
     * @param $dateType
     *
     * @param null $practiceId
     *
     * @return \Illuminate\Support\Collection
     */
//    public function getCpmPatientTotals(Carbon $date, $dateType, $practiceId = null)
//    {
//        $fromDate = $date->copy()->startOfMonth()->startOfDay()->toDateTimeString();
//        $toDate   = $date->copy()->endOfMonth()->endOfDay()->toDateTimeString();
//
//        $totalPatients = $this->getTotalPatients();
//
//        $monthPatients = $this->getTotalPatients($fromDate, $toDate);
//
//        //If selecting specific day: go to day, show relevant week/month totals (EOW)
//        if ($dateType == 'day') {
//
//            $dayFromDate = $date->copy()->startOfDay()->toDateTimeString();
//            $dayToDate   = $date->copy()->endOfDay()->toDateTimeString();
//            $dayPatients = $this->getTotalPatients($dayFromDate, $dayToDate);
//
//            $fromDate = $date->copy()->startOfWeek()->startOfDay()->toDateTimeString();
//            $toDate   = $date->copy()->endOfWeek()->endOfDay()->toDateTimeString();
//
//            $weekPatients = $this->getTotalPatients($fromDate, $toDate);
//        }
//
//        //if selecting week:go to end of week totals, show last day of the week, month UTD totals from end of week.
//        if ($dateType == 'week') {
//
//            //last day of week for day totals
//            $dayFromDate = $date->copy()->endOfWeek()->startOfDay()->toDateTimeString();
//            $dayToDate   = $date->copy()->endOfWeek()->endOfDay()->toDateTimeString();
//
//            $dayPatients = $this->getTotalPatients($dayFromDate, $dayToDate);
//
//            $fromDate = $date->copy()->startOfWeek()->startOfDay()->toDateTimeString();
//            $toDate   = $date->copy()->endOfWeek()->endOfDay()->toDateTimeString();
//
//            $weekPatients = $this->getTotalPatients($fromDate, $toDate);
//        }
//
//        //if selecting monthly:show EOM totals, show totals for last day of week, last week of month
//        if ($dateType == 'month') {
//            //last day of month for day totals
//            $dayFromDate = $date->copy()->endOfMonth()->startOfDay()->toDateTimeString();
//            $dayToDate   = $date->copy()->endOfMonth()->endOfDay()->toDateTimeString();
//
//            $dayPatients = $this->getTotalPatients($dayFromDate, $dayToDate);
//
//            //last week of month
//            $fromDate = $date->copy()->endOfMonth()->startOfWeek()->startOfDay()->toDateTimeString();
//            $toDate   = $date->copy()->endOfMonth()->endOfDay()->toDateTimeString();
//
//            $weekPatients = $this->getTotalPatients($fromDate, $toDate);
//
//
//        }
//
//        if ($practiceId) {
//            $dayCount   = $this->countPatientsByStatus($this->filterPatientsByPractice($dayPatients, $practiceId));
//            $weekCount  = $this->countPatientsByStatus($this->filterPatientsByPractice($weekPatients, $practiceId));
//            $monthCount = $this->countPatientsByStatus($this->filterPatientsByPractice($monthPatients, $practiceId));
//            $totalCount = $this->countPatientsByStatus($this->filterPatientsByPractice($totalPatients, $practiceId));
//
//
//        } else {
//            $dayCount   = $this->countPatientsByStatus($dayPatients);
//            $weekCount  = $this->countPatientsByStatus($weekPatients);
//            $monthCount = $this->countPatientsByStatus($monthPatients);
//            $totalCount = $this->countPatientsByStatus($totalPatients);
//        }
//
////        dd([$weekPatients, $weekCount]);
//
//
//        return collect([
//            'dayCount'   => $dayCount,
//            'weekCount'  => $weekCount,
//            'monthCount' => $monthCount,
//            'totalCount' => $totalCount,
//        ]);
//
//    }

    /**
     *
     *
     *
     * @param $fromDate
     * @param $toDate
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|static[]
     */
    public function getPausedPatients($fromDate, $toDate)
    {

        $patients = User::with([
            'patientInfo' => function ($patient) use ($fromDate, $toDate) {
                $patient->ccmStatus(Patient::PAUSED)
                        ->where('date_paused', '>=', $fromDate)
                        ->where('date_paused', '<=', $toDate);
            },
        ])
                        ->whereHas('patientInfo', function ($patient) use ($fromDate, $toDate) {
                            $patient->ccmStatus(Patient::PAUSED)
                                    ->where('date_paused', '>=', $fromDate)
                                    ->where('date_paused', '<=', $toDate);
                        })
                        ->get();


        return $patients;

    }


    /**
     * Filters a collection of Users by practice id.
     *
     * @param $patients
     * @param $practiceId
     *
     * @return \Illuminate\Support\Collection
     */
    public function filterPatientsByPractice($patients, $practiceId)
    {
        $filteredPatients = $patients->where('program_id', $practiceId)
                                     ->all();

        return $filteredPatients;

    }



    public function filterPatientsByStatus($patients, $status)
    {

    }


    /**
     *
     * Counts a collection of Users by their status.
     *
     * @param $patients
     *
     * @return \Illuminate\Support\Collection
     */
    public function countPatientsByStatus($patients)
    {
        $paused    = [];
        $withdrawn = [];
        $enrolled  = [];
        $gCodeHold = [];

        $pausedCount    = null;
        $withdrawnCount = null;
        $enrolledCount  = null;
        $gCodeHoldCount = null;

        foreach ($patients as $patient) {
            if ($patient->patientInfo) {
                if ($patient->patientInfo->ccm_status == 'paused') {
                    $paused[] = $patient;
                }
                if ($patient->patientInfo->ccm_status == 'withdrawn') {
                    $withdrawn[] = $patient;
                }
                if ($patient->patientInfo->ccm_status == 'enrolled') {
                    $enrolled[] = $patient;
                }
                if ($patient->carePlan) {
                    if ($patient->carePlan->status == 'to_enroll') {
                        $gCodeHold[] = $patient;
                    }
                }

            }

        }

        $pausedCount    = count($paused);
        $withdrawnCount = count($withdrawn);
        $enrolledCount  = count($enrolled);
        $gCodeHoldCount = count($gCodeHold);
        $delta          = $this->calculateDelta($enrolledCount, $pausedCount, $withdrawnCount);


        return collect([
            'pausedPatients'    => $pausedCount,
            'withdrawnPatients' => $withdrawnCount,
            'enrolled'          => $enrolledCount,
            'gCodeHold'         => $gCodeHoldCount,
            'delta'             => $delta,
        ]);


    }


    /**
     * categorizes patient count by ccmTime
     *
     * @param $patients
     * @param $fromDate
     * @param $toDate
     *
     * @return mixed
     */
    public function countPatientsByCcmTime($patients, $fromDate, $toDate)
    {

        $count['zero']   = 0;
        $count['0to5']   = 0;
        $count['5to10']  = 0;
        $count['10to15'] = 0;
        $count['15to20'] = 0;
        $count['20plus'] = 0;
        $count['total']  = 0;

        foreach ($patients as $patient) {

            if ($patient->activities) {

                $ccmTime = $this->repo->totalTimeForPatient($patient, $fromDate, $toDate, false);
                if ($ccmTime == 0) {
                    $count['zero'] += 1;
                }
                if ($ccmTime > 0 and $ccmTime <= 300) {
                    $count['0to5'] += 1;
                }
                if ($ccmTime > 300 and $ccmTime <= 600) {
                    $count['5to10'] += 1;
                }
                if ($ccmTime > 600 and $ccmTime <= 900) {
                    $count['10to15'] += 1;
                }
                if ($ccmTime > 900 and $ccmTime <= 1200) {
                    $count['15to20'] += 1;
                }
                if ($ccmTime > 1200) {
                    $count['20plus'] += 1;
                }
            } else {
                $count['zero'] += 1;
            }
        }
        $count['total'] = $count['zero'] + $count['0to5'] + $count['5to10'] + $count['10to15'] + $count['15to20'] + $count['20plus'];
        if ($count['total'] == 0) {
            return null;
        }

        return $count;
    }


    /**
     * Returns counts categorised by ccmTime ranges for a single practice, for a given date range
     *
     * @param $practice
     * @param $patients
     * @param $fromDate
     * @param $toDate
     *
     * @return array
     */
    public function getPracticeCcmTotalCounts($practice, $fromDate, $toDate)
    {

        $filteredPatients = $this->getEnrolledPatientsFilteredByPractice($practice, $fromDate, $toDate);
        $counts           = $this->countPatientsByCcmTime($filteredPatients, $fromDate, $toDate);

        return $counts;
    }

    /**
     * Returns counts for enrolled, paused, withdrawn and delta for a single practice
     *
     * @param $practice
     * @param $fromDate
     * @param $toDate
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPracticeCountsByStatus($practice, $fromDate, $toDate)
    {
        $patientsByStatus = $this->repo->getPatientsByStatus($fromDate, $toDate);
        $filteredPatients = $this->filterPatientsByPractice($patientsByStatus, $practice->id);
        $counts           = $this->countPatientsByStatus($filteredPatients);

        return $counts;

    }


    /**
     * Returns all the data needed for a row(for a single practice) in Daily Tab.
     *
     * @param $practice
     * @param $date
     *
     * @return \Illuminate\Support\Collection
     */
    public function dailyReportRow($practice, $date)
    {
        $date = new Carbon($date);
        $fromDate = $date->copy()->startOfMonth()->startOfDay()->toDateTimeString();

        //ccm from date must be start of month
        $ccmCounts = $this->getPracticeCcmTotalCounts($practice, $fromDate, $date->toDateTimeString());
        //total for day before
        $priorDay = $date->copy()->subDay(1)->toDateTimeString();

        $priorDayCcmCounts           = $this->getPracticeCcmTotalCounts($practice, $fromDate, $priorDay);
        $ccmCounts['priorDayTotals'] = $priorDayCcmCounts['total'];
        $ccmTotal                    = collect($ccmCounts);

        $countsByStatus = $this->getPracticeCountsByStatus($practice, $fromDate, $date->toDateTimeString());

        return collect([
            'ccmCounts'      => $ccmTotal,
            'countsByStatus' => $countsByStatus,
        ]);

    }

    public function getCcmTimeAverageByPractice(){

    }

    public function getEnrolledPatientsFilteredByPractice($practice, $fromDate, $toDate){
        $enrolledPatients = $this->repo->getEnrolledPatients($fromDate, $toDate);
        $filteredPatients = $this->filterPatientsByPractice($enrolledPatients, $practice->id);

        return $filteredPatients;
    }

    public function calculateHoursBehind($date){
        $date = new Carbon($date);

        //(AvgMinT - AvgMinA)*TotActPt/60
        $totActPt = $this->repo->getTotalActivePatientCount();

        //date current day or last day completed 11:00 pm?
        $startOfMonth = $date->copy()->startOfMonth()->startOfDay();
        $endOfMonth = $date->copy()->endOfMonth()->endOfDay();
        $workingDaysElapsed = $this->calculateWeekdays($startOfMonth->toDateTimeString(), $date->toDateTimeString());
        $workingDaysMonth = $this->calculateWeekdays($startOfMonth->toDateTimeString(), $endOfMonth->toDateTimeString());
        $avgMinT = $workingDaysElapsed/$workingDaysMonth * 35;

        $totalCcm = [];
        $practices = Practice::active()->get();
        foreach ($practices as $practice){
            $patients = $this->getEnrolledPatientsFilteredByPractice($practice, $startOfMonth->toDateTimeString(), $date->toDateTimeString());
            foreach ($patients as $patient){
                $totalCcm[] = $this->repo->totalTimeForPatient($patient, $startOfMonth->toDateTimeString(), $date->toDateTimeString());
            }
        }
        $totalCcm = array_filter($totalCcm);
        if (!count($totalCcm) == 0){
            $average = array_sum($totalCcm)/count($totalCcm);
            $avgMinA = $average;
        }else{
            $avgMinA = 0;
        }



        $hoursBehind = ($avgMinT - $avgMinA) * $totActPt / 60;

        return $hoursBehind;


    }

    /**
     * Gcode hold not calculated at the moment, to be added
     *
     * @param $enrolled
     * @param $paused
     * @param $withdrawn
     *
     * @return mixed
     */
    public function calculateDelta($enrolled, $paused, $withdrawn)
    {

        $delta = $enrolled - $paused - $withdrawn;

        return $delta;
    }


    /**
     * @param $fromDate
     * @param $toDate
     *
     * @return int
     */
    public function calculateWeekdays($fromDate, $toDate){

        return Carbon::parse($fromDate)->diffInDaysFiltered(function(Carbon $date) {
            return !$date->isWeekend();
        }, new Carbon($toDate));

    }


}