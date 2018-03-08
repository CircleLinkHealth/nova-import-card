<?php
/**
 * Created by PhpStorm.
 * User: kakoushias
 * Date: 06/03/2018
 * Time: 12:21 AM
 */

namespace App\Services;


use App\User;
use Carbon\Carbon;

class OperationsDashboardService
{


    /**
     * get total patients, return count for each time category, for each status category
     *
     * @param Carbon $date
     * @param $dateType
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCpmPatientTotals(Carbon $date, $dateType)
    {
        $fromDate = $date->startOfMonth();
        $toDate   = $date->endOfMonth();

        $monthPatients = $this->getTotalPatients($fromDate, $toDate);

        //If selecting specific day: go to day, show relevant week/month totals (EOW)
        if ($dateType == 'day') {

            $dayPatients = $this->getTotalPatients($fromDate);

            $fromDate = $date->startOfWeek();
            $toDate   = $date->endOfWeek();

            $weekPatients = $this->getTotalPatients($fromDate, $toDate);
        }

        //if selecting week:go to end of week totals, show last day of the week, month UTD totals from end of week.
        if ($dateType == 'week') {

            //last day of week for day totals
            $dayDate = $date->endOfWeek();

            $dayPatients = $this->getTotalPatients($dayDate);

            $fromDate = $date->startOfWeek();
            $toDate   = $date->endOfWeek();

            $weekPatients = $this->getTotalPatients($fromDate, $toDate);
        }

        //if selecting monthly:show EOM totals, show totals for last day of week, last week of month
        if ($dateType == 'month') {


        }

        $dayCount   = $this->countPatientsByStatus($dayPatients);
        $weekCount  = $this->countPatientsByStatus($weekPatients);
        $monthCount = $this->countPatientsByStatus($monthPatients);


        return collect([
            'dayCount'   => $dayCount,
            'weekCount'  => $weekCount,
            'monthCount' => $monthCount,
        ]);

    }


    /**
     * @param Carbon $fromDate
     * @param Carbon $toDate
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|static[]
     */
    public function getPausedPatients(Carbon $fromDate, Carbon $toDate)
    {


        $patients = User::whereHas('patientInfo', function ($patient) use ($fromDate, $toDate) {
            $patient->where('ccm_status', 'paused')
                    ->where('date_paused', '>=', $fromDate)
                    ->where('date_paused', '<=', $toDate);
        })
                        ->get();


        return $patients;

    }


    /**
     * @param $patients
     * @param $practiceId
     *
     * @return \Illuminate\Support\Collection
     */
    public function filterPatientsByPractice($patients, $practiceId)
    {

        $filteredPatients = $patients->whereHas('primaryPractice', function ($p) use ($practiceId) {
            $p->where('id', $practiceId);
        })
                                     ->get();

        $patientsCount = $this->countPatientsByStatus($patients);

        return $patientsCount;


    }


    public function getModifiedByNonClh()
    {

    }

    /**
     * get all patients that date paused, withdrawn, or registered in month(same for all dateTypes)
     *
     * @param Carbon $fromDate
     * @param Carbon $toDate
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|static[]
     */
    public function getTotalPatients(Carbon $fromDate, Carbon $toDate = null)
    {

        if ($toDate) {
            $patients = User::with([
                'patientInfo' => function ($patient) use ($fromDate, $toDate) {
                    $patient->whereIn('ccm_status', ['paused', 'withdrawn', 'enrolled'])
                            ->where([['date_paused', '>', $fromDate], ['date_paused', '<', $toDate]])
                            ->orWhere([['date_withdrawn', '>', $fromDate], ['date_withdrawn', '<', $toDate]])
                            ->orWhere([['registration_date', '>', $fromDate], ['registration_date', '<', $toDate]]);
                },
                'carePlan'    => function ($c) use ($fromDate, $toDate) {
                    $c->where('status', 'to_enroll')
                      ->where([['updated_at', '>', $fromDate], ['updated_at', '<', $toDate]]);
                },
            ])
                            ->whereHas('patientInfo', function ($patient) use ($fromDate, $toDate) {
                                $patient->whereIn('ccm_status', ['paused', 'withdrawn', 'enrolled'])
                                        ->where([['date_paused', '>', $fromDate], ['date_paused', '<', $toDate]])
                                        ->orWhere([
                                            ['date_withdrawn', '>', $fromDate],
                                            ['date_withdrawn', '<', $toDate],
                                        ])
                                        ->orWhere([
                                            ['registration_date', '>', $fromDate],
                                            ['registration_date', '<', $toDate],
                                        ]);
                            })
                            ->orWhereHas('carePlan', function ($c) use ($fromDate, $toDate) {
                                $c->where('status', 'to_enroll')
                                ->where([['updated_at', '>', $fromDate], ['updated_at', '<', $toDate]]);
                            })
                            ->get();
        } else {
            $patients = User::with(['patientInfo', 'carePlan'])
                            ->whereHas('patientInfo', function ($patient) use ($fromDate) {
                                $patient->whereIn('ccm_status', ['paused', 'withdrawn', 'enrolled'])
                                        ->where('date_paused', $fromDate)
                                        ->orWhere('date_withdrawn', $fromDate)
                                        ->orWhere('registration_date', $fromDate);
                            })
                            ->orWhereHas('carePlan', function ($c) use ($fromDate) {
                                $c->where('status', 'to_enroll')
                                  ->where('updated_at', $fromDate);
                            })
                            ->get();
        }


        return $patients;
    }

    /**
     * @param $patients
     *
     * @return \Illuminate\Support\Collection
     */
    public function countPatientsByStatus($patients)
    {
        $paused = [];
        $withdrawn = [];
        $enrolled = [];
        $gCodeHold = [];

        foreach ($patients as $patient){
            if ($patient->patientInfo->ccm_status == 'paused'){
                $paused[] = $patient;
            }
            if ($patient->patientInfo->ccm_status == 'withdrawn'){
                $withdrawn[] = $patient;
            }
            if ($patient->patientInfo->ccm_status == 'enrolled'){
                $enrolled[] = $patient;
            }
            if ($patient->carePlan->status == 'to_enroll'){
                $gCodeHold[] = $patient;
            }
        }

        $pausedCount = count($paused);
        $withdrawnCount = count($withdrawn);
        $enrolledCount = count($enrolled);
        $gCodeHoldCount = count($gCodeHold);




        return collect([
            'pausedPatients'    => $pausedCount,
            'withdrawnPatients' => $withdrawnCount,
            'enrolled'          => $enrolledCount,
            'gCodeHold'         => $gCodeHoldCount,
        ]);


    }


//    /**
//     *
//     * filters a collection of patients according to the date(s) given.
//     *
//     * @param $patients
//     * @param Carbon $date
//     * @param Carbon|null $toDate
//     *
//     * @return mixed
//     */
//    public function filterPatients($patients, Carbon $date, Carbon $toDate = null)
//    {
//
//        if ($toDate == null) {
//            $filteredPatients = $patients->whereHas('patientInfo', function ($patient) use ($date) {
//                $patient->whereIn('ccm_status', ['paused', 'withdrawn', 'enrolled'])
//                        ->where('date_paused', $date)
//                        ->orWhere('date_withdrawn', $date)
//                        ->orWhere('registration_date', $date);
//            })->orWhereHas('carePlan', function ($c) use ($date) {
//                $c->where('status', 'to_enroll')
//                  ->where('updated_at', $date);
//            })
//                                         ->get();
//        } else {
//            $filteredPatients = $patients->whereHas('patientInfo', function ($patient) use ($date, $toDate) {
//                $patient->whereIn('ccm_status', ['paused', 'withdrawn', 'enrolled'])
//                        ->whereBetween('date_paused', [$date, $toDate])
//                        ->orWhereBetween('date_withdrawn', [$date, $toDate])
//                        ->orWhereBetween('registration_date', [$date, $toDate]);
//            })->orWhereHas('carePlan', function ($c) use ($date, $toDate) {
//                $c->where('status', 'to_enroll')
//                  ->whereBetween('updated_at', [$date, $toDate]);
//            })
//                                         ->get();
//
//        }
//
//        return $filteredPatients;
//
//    }
}