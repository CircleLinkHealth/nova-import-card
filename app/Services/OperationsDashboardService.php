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
        $fromDate = $date->copy()->startOfMonth()->startOfDay()->toDateTimeString();
        $toDate   = $date->copy()->endOfMonth()->endOfDay()->toDateTimeString();

        $totalPatients = $this->getTotalPatients();

        $monthPatients = $this->getTotalPatients($fromDate, $toDate);

        //If selecting specific day: go to day, show relevant week/month totals (EOW)
        if ($dateType == 'day') {

            $dayFromDate = $date->copy()->startOfDay()->toDateTimeString();
            $dayToDate   = $date->copy()->endOfDay()->toDateTimeString();
            $dayPatients = $this->getTotalPatients($dayFromDate, $dayToDate);

            $fromDate = $date->copy()->startOfWeek()->startOfDay()->toDateTimeString();
            $toDate   = $date->copy()->endOfWeek()->endOfDay()->toDateTimeString();

            $weekPatients = $this->getTotalPatients($fromDate, $toDate);
        }

        //if selecting week:go to end of week totals, show last day of the week, month UTD totals from end of week.
        if ($dateType == 'week') {

            //last day of week for day totals
            $dayFromDate = $date->copy()->endOfWeek()->startOfDay()->toDateTimeString();
            $dayToDate   = $date->copy()->endOfWeek()->endOfDay()->toDateTimeString();

            $dayPatients = $this->getTotalPatients($dayFromDate, $dayToDate);

            $fromDate = $date->copy()->startOfWeek()->startOfDay()->toDateTimeString();
            $toDate   = $date->copy()->endOfWeek()->endOfDay()->toDateTimeString();

            $weekPatients = $this->getTotalPatients($fromDate, $toDate);
        }

        //if selecting monthly:show EOM totals, show totals for last day of week, last week of month
        if ($dateType == 'month') {
            //last day of month for day totals
            $dayFromDate = $date->copy()->endOfMonth()->startOfDay()->toDateTimeString();
            $dayToDate   = $date->copy()->endOfMonth()->endOfDay()->toDateTimeString();

            $dayPatients = $this->getTotalPatients($dayFromDate, $dayToDate);

            //last week of month
            $fromDate = $date->copy()->endOfMonth()->startOfWeek()->startOfDay()->toDateTimeString();
            $toDate   = $date->copy()->endOfMonth()->endOfDay()->toDateTimeString();

            $weekPatients = $this->getTotalPatients($fromDate, $toDate);


        }

        if ($practiceId) {
            $dayCount   = $this->countPatientsByStatus($this->filterPatientsByPractice($dayPatients, $practiceId));
            $weekCount  = $this->countPatientsByStatus($this->filterPatientsByPractice($weekPatients, $practiceId));
            $monthCount = $this->countPatientsByStatus($this->filterPatientsByPractice($monthPatients, $practiceId));
            $totalCount = $this->countPatientsByStatus($this->filterPatientsByPractice($totalPatients, $practiceId));


        } else {
            $dayCount   = $this->countPatientsByStatus($dayPatients);
            $weekCount  = $this->countPatientsByStatus($weekPatients);
            $monthCount = $this->countPatientsByStatus($monthPatients);
            $totalCount = $this->countPatientsByStatus($totalPatients);
        }

//        dd([$weekPatients, $weekCount]);


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

        $patients = User::with([
            'patientInfo' => function ($patient) use ($fromDate, $toDate) {
                $patient->where('ccm_status', 'paused')
                        ->where('date_paused', '>=', $fromDate)
                        ->where('date_paused', '<=', $toDate);
            },
        ])
                        ->whereHas('patientInfo', function ($patient) use ($fromDate, $toDate) {
                            $patient->where('ccm_status', 'paused')
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


    public function getModifiedByNonClh()
    {

    }

    /**
     * get all patients that date paused, withdrawn, or registered in month(same for all dateTypes)
     * dates are Carbon->toDateTimeString()
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
                    $patient->where(function ($query) use ($fromDate, $toDate) {
                        $query->where(function ($subQuery) use ($fromDate, $toDate) {
                            $subQuery->where('ccm_status', 'paused')
                                     ->where([['date_paused', '>=', $fromDate], ['date_paused', '<=', $toDate]]);
                        })
                              ->orWhere(function ($subQuery) use ($fromDate, $toDate) {
                                  $subQuery->where('ccm_status', 'withdrawn')
                                           ->where([['date_withdrawn', '>=', $fromDate], ['date_withdrawn', '<=', $toDate],]);
                              })
                              ->orWhere(function ($subQuery) use ($fromDate, $toDate) {
                                  $subQuery->where('ccm_status', 'enrolled')
                                           ->where([['registration_date', '>=', $fromDate], ['registration_date', '<=', $toDate],]);
                              });
                    });
                },
                'carePlan'    => function ($c) use ($fromDate, $toDate) {
                    $c->where('status', 'to_enroll')
                      ->where([['updated_at', '>=', $fromDate], ['updated_at', '<=', $toDate]]);
                },
            ])
                            ->whereHas('patientInfo', function ($patient) use ($fromDate, $toDate) {
                                $patient->where(function ($query) use ($fromDate, $toDate) {
                                    $query->where(function ($subQuery) use ($fromDate, $toDate) {
                                        $subQuery->where('ccm_status', 'paused')
                                                 ->where([['date_paused', '>=', $fromDate], ['date_paused', '<=', $toDate],]);
                                    })
                                          ->orWhere(function ($subQuery) use ($fromDate, $toDate) {
                                              $subQuery->where('ccm_status', 'withdrawn')
                                                       ->where([['date_withdrawn', '>=', $fromDate], ['date_withdrawn', '<=', $toDate],]);
                                          })
                                          ->orWhere(function ($subQuery) use ($fromDate, $toDate) {
                                              $subQuery->where('ccm_status', 'enrolled')
                                                       ->where([['registration_date', '>=', $fromDate], ['registration_date', '<=', $toDate],]);
                                          });
                                });
                            })
                            ->orWhere(function ($query) use ($fromDate, $toDate) {
                                $query->has('patientInfo')
                                      ->whereHas('carePlan', function ($c) use ($fromDate, $toDate) {
                                          $c->where('status', 'to_enroll')
                                            ->where([['updated_at', '>=', $fromDate], ['updated_at', '<=', $toDate]]);
                                      });
                            })
                            ->get();

        } else {
            $patients = User::with([
                'patientInfo' => function ($patient) {
                    $patient->whereIn('ccm_status', ['paused', 'withdrawn', 'enrolled']);
                },
                'carePlan'    => function ($c) {
                    $c->where('status', 'to_enroll');
                },
            ])
                            ->whereHas('patientInfo', function ($patient) {
                                $patient->whereIn('ccm_status', ['paused', 'withdrawn', 'enrolled']);
                            })
                            ->orWhere(function ($query) {
                                $query->has('patientInfo')
                                      ->whereHas('carePlan', function ($c) {
                                          $c->where('status', 'to_enroll');
                                      });
                            })
                            ->get();
        }


        return $patients;
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