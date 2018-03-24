<?php
/**
 * Created by PhpStorm.
 * User: kakoushias
 * Date: 06/03/2018
 * Time: 12:21 AM
 */

namespace App\Services;


use App\CarePlan;
use App\Patient;
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
                            $subQuery->ccmStatus(Patient::PAUSED)
                                     ->where([['date_paused', '>=', $fromDate], ['date_paused', '<=', $toDate]]);
                        })
                              ->orWhere(function ($subQuery) use ($fromDate, $toDate) {
                                  $subQuery->ccmStatus(Patient::WITHDRAWN)
                                           ->where([
                                               ['date_withdrawn', '>=', $fromDate],
                                               ['date_withdrawn', '<=', $toDate],
                                           ]);
                              })
                              ->orWhere(function ($subQuery) use ($fromDate, $toDate) {
                                  $subQuery->ccmStatus(Patient::ENROLLED)
                                           ->where([
                                               ['registration_date', '>=', $fromDate],
                                               ['registration_date', '<=', $toDate],
                                           ]);
                              });
                    });
                },
                'carePlan'    => function ($c) use ($fromDate, $toDate) {
                    $c->where('status', CarePlan::TO_ENROLL)
                      ->where([['updated_at', '>=', $fromDate], ['updated_at', '<=', $toDate]]);
                },
            ])
                            ->whereHas('patientInfo', function ($patient) use ($fromDate, $toDate) {
                                $patient->where(function ($query) use ($fromDate, $toDate) {
                                    $query->where(function ($subQuery) use ($fromDate, $toDate) {
                                        $subQuery->ccmStatus(Patient::PAUSED)
                                                 ->where([
                                                     ['date_paused', '>=', $fromDate],
                                                     ['date_paused', '<=', $toDate],
                                                 ]);
                                    })
                                          ->orWhere(function ($subQuery) use ($fromDate, $toDate) {
                                              $subQuery->ccmStatus(Patient::WITHDRAWN)
                                                       ->where([
                                                           ['date_withdrawn', '>=', $fromDate],
                                                           ['date_withdrawn', '<=', $toDate],
                                                       ]);
                                          })
                                          ->orWhere(function ($subQuery) use ($fromDate, $toDate) {
                                              $subQuery->ccmStatus(Patient::ENROLLED)
                                                       ->where([
                                                           ['registration_date', '>=', $fromDate],
                                                           ['registration_date', '<=', $toDate],
                                                       ]);
                                          });
                                });
                            })
                            ->orWhere(function ($query) use ($fromDate, $toDate) {
                                $query->whereHas('patientInfo', function ($patient) {
                                    $patient->whereIn('ccm_status', [Patient::PAUSED, Patient::WITHDRAWN, Patient::ENROLLED]);
                                })
                                      ->whereHas('carePlan', function ($c) use ($fromDate, $toDate) {
                                          $c->where('status', CarePlan::TO_ENROLL)
                                            ->where([['updated_at', '>=', $fromDate], ['updated_at', '<=', $toDate]]);
                                      });
                            })
                            ->get();

        } else {
            $patients = User::with([
                'patientInfo' => function ($patient) {
                    $patient->whereIn('ccm_status', [Patient::PAUSED, Patient::WITHDRAWN, Patient::ENROLLED]);
                },
                'carePlan'    => function ($c) {
                    $c->where('status', CarePlan::TO_ENROLL);
                },
            ])
                            ->whereHas('patientInfo', function ($patient) {
                                $patient->whereIn('ccm_status',
                                    [Patient::PAUSED, Patient::WITHDRAWN, Patient::ENROLLED]);
                            })
                            ->orWhere(function ($query) {
                                $query->whereHas('patientInfo', function ($patient) {
                                    $patient->whereIn('ccm_status',
                                        [Patient::PAUSED, Patient::WITHDRAWN, Patient::ENROLLED]);
                                })
                                      ->whereHas('carePlan', function ($c) {
                                          $c->where('status', CarePlan::TO_ENROLL);
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


        return collect([
            'pausedPatients'    => $pausedCount,
            'withdrawnPatients' => $withdrawnCount,
            'enrolled'          => $enrolledCount,
            'gCodeHold'         => $gCodeHoldCount,
        ]);


    }




    public function countPatientsByCcmTime($patients, $fromDate, $toDate){


        $count['zero'] = 0;
        $count['0to5'] = 0;
        $count['5to10'] = 0;
        $count['10to15'] = 0;
        $count['15to20'] = 0;
        $count['20plus'] = 0;



        foreach ($patients as $patient){

            if ($patient->activities){

                $ccmTime = $this->repo->totalTimeForPatient($patient, $fromDate, $toDate, false);
                if ($ccmTime == 0){
                    $count['zero'] += 1;
                }
                if ($ccmTime > 0 and $ccmTime <= 300){
                    $count['0to5'] += 1;
                }
                if ($ccmTime > 300 and $ccmTime <= 600){
                    $count['5to10'] += 1;
                }
                if ($ccmTime > 600 and $ccmTime <= 900){
                    $count['10to15'] += 1;
                }
                if ($ccmTime > 900 and $ccmTime <= 1200){
                    $count['15to20'] += 1;
                }
                if ($ccmTime > 1200){
                    $count['20plus'] += 1;
                }
            }else{
                $count['zero'] += 1;
            }
        }

        return $count;
    }


}