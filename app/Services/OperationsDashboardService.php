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
     * @param null $practiceId
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCpmPatientTotals(Carbon $date, $dateType, $practiceId = null)
    {
        $fromDate = $date->copy()->startOfMonth()->toDateString();
        $toDate   = $date->copy()->endOfMonth()->toDateString();

        $totalPatients = $this->getTotalPatients();

        $monthPatients = $this->getTotalPatients($fromDate, $toDate);

        //If selecting specific day: go to day, show relevant week/month totals (EOW)
        if ($dateType == 'day') {

            $dayPatients = $this->getTotalPatients($fromDate);

            $fromDate = $date->startOfWeek()->toDateString();
            $toDate   = $date->endOfWeek()->toDateString();

            $weekPatients = $this->getTotalPatients($fromDate, $toDate);
        }

        //if selecting week:go to end of week totals, show last day of the week, month UTD totals from end of week.
        if ($dateType == 'week') {

            //last day of week for day totals
            $dayDate = $date->copy()->endOfWeek()->toDateString();

            $dayPatients = $this->getTotalPatients($dayDate);

            $fromDate = $date->copy()->startOfWeek()->toDateString();
            $toDate   = $date->copy()->endOfWeek()->toDateString();

            $weekPatients = $this->getTotalPatients($fromDate, $toDate);
        }

        //if selecting monthly:show EOM totals, show totals for last day of week, last week of month
        if ($dateType == 'month') {
            //last day of month for day totals
            $dayDate = $date->copy()->endOfMonth()->toDateString();

            $dayPatients = $this->getTotalPatients($dayDate);

            //last week of month
            $fromDate = $date->copy()->endOfMonth()->startOfWeek()->toDateString();
            $toDate   = $date->copy()->endOfMonth()->toDateString();

            $weekPatients = $this->getTotalPatients($fromDate, $toDate);


        }

        if($practiceId){
            $dayCount   = $this->countPatientsByStatus($this->filterPatientsByPractice($dayPatients, $practiceId));
            $weekCount  = $this->countPatientsByStatus($this->filterPatientsByPractice($weekPatients, $practiceId));
            $monthCount = $this->countPatientsByStatus($this->filterPatientsByPractice($monthPatients, $practiceId));
            $totalCount = $this->countPatientsByStatus($this->filterPatientsByPractice($totalPatients, $practiceId));


        }else{
            $dayCount   = $this->countPatientsByStatus($dayPatients);
            $weekCount  = $this->countPatientsByStatus($weekPatients);
            $monthCount = $this->countPatientsByStatus($monthPatients);
            $totalCount = $this->countPatientsByStatus($totalPatients);
        }


        return collect([
            'dayCount'   => $dayCount,
            'weekCount'  => $weekCount,
            'monthCount' => $monthCount,
            'totalCount' => $totalCount,
        ]);

    }


    /**
     * @param $fromDate
     * @param $toDate
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|static[]
     */
    public function getPausedPatients($fromDate, $toDate)
    {

        $patients = User::with(['patientInfo' => function ($patient) use ($fromDate, $toDate) {
            $patient->where('ccm_status', 'paused')
                    ->where('date_paused', '>=', $fromDate)
                    ->where('date_paused', '<=', $toDate);
        }])
                        ->whereHas('patientInfo', function ($patient) use ($fromDate, $toDate) {
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

        $filteredPatients = $patients->where('program_id', $practiceId)
                                     ->all();


        return $filteredPatients;


    }


    public function getModifiedByNonClh()
    {

    }

    /**
     * get all patients that date paused, withdrawn, or registered in month(same for all dateTypes)
     * dates are Carbon->toDateString()
     *
     * @param $fromDate
     * @param $toDate
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|static[]
     */
    public function getTotalPatients($fromDate = null, $toDate = null)
    {

        if ($fromDate and $toDate) {
            $patients = User::with([
                'patientInfo' => function ($patient) use ($fromDate, $toDate) {
                    $patient->whereIn('ccm_status', ['paused', 'withdrawn', 'enrolled'])
                        //switched from whereBetween
                            ->where([['date_paused', '>=', $fromDate], ['date_paused', '<=', $toDate]])
                            ->orWhere([['date_withdrawn', '>=', $fromDate], ['date_withdrawn', '<=', $toDate]])
                            ->orWhere([['registration_date', '>=', $fromDate], ['registration_date', '<=', $toDate]]);
                },
                'carePlan'    => function ($c) use ($fromDate, $toDate) {
                    $c->where('status', 'to_enroll')
                      ->where([['updated_at', '>=', $fromDate], ['updated_at', '<=', $toDate]]);
                },
            ])
                            ->whereHas('patientInfo', function ($patient) use ($fromDate, $toDate) {
                                $patient->whereIn('ccm_status', ['paused', 'withdrawn', 'enrolled'])
                                        ->where([['date_paused', '>=', $fromDate], ['date_paused', '<=', $toDate]])
                                        ->orWhere([
                                            ['date_withdrawn', '>=', $fromDate],
                                            ['date_withdrawn', '<=', $toDate],
                                        ])
                                        ->orWhere([
                                            ['registration_date', '>=', $fromDate],
                                            ['registration_date', '<=', $toDate],
                                        ]);
                            })
                            ->orWhereHas('carePlan', function ($c) use ($fromDate, $toDate) {
                                $c->where('status', 'to_enroll')
                                  ->where([['updated_at', '>=', $fromDate], ['updated_at', '<=', $toDate]]);
                            })
                            ->get();
        } elseif ($fromDate and $toDate == null) {
            $patients = User::with([
                'patientInfo' => function ($patient) use ($fromDate) {
                    $patient->whereIn('ccm_status', ['paused', 'withdrawn', 'enrolled'])
                            ->where('date_paused', $fromDate)
                            ->orWhere('date_withdrawn', $fromDate)
                            ->orWhere('registration_date', $fromDate);
                },
                'carePlan'    => function ($c) use ($fromDate, $toDate) {
                    $c->where('status', 'to_enroll')
                      ->where('updated_at', $fromDate);
                },
            ])
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
        } else {
            $patients = User::with([
                'patientInfo' => function ($patient) {
                    $patient->whereIn('ccm_status', ['paused', 'withdrawn', 'enrolled']);
                },
                'carePlan'    => function ($c) use ($fromDate, $toDate) {
                    $c->where('status', 'to_enroll');
                },
            ])
                            ->whereHas('patientInfo', function ($patient) {
                                $patient->whereIn('ccm_status', ['paused', 'withdrawn', 'enrolled']);
                            })
                            ->orWhereHas('carePlan', function ($c) {
                                $c->where('status', 'to_enroll');
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
        $paused    = [];
        $withdrawn = [];
        $enrolled  = [];
        $gCodeHold = [];

        $pausedCount    = null;
        $withdrawnCount = null;
        $enrolledCount  = null;
        $gCodeHoldCount = null;

            foreach ($patients as $patient) {
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

        $pausedCount    = count($paused);
        $withdrawnCount = count($withdrawn);
        $enrolledCount  = count($enrolled);
        $gCodeHoldCount = count($gCodeHold);



        return collect([
            'pausedPatients'    => $pausedCount,
            'withdrawnPatients' => $withdrawnCount,
            'enrolled'          => $enrolledCount,
            'gCodeHold'         => $gCodeHoldCount,
        ]);


    }


}