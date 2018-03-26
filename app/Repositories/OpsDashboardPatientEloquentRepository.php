<?php
/**
 * Created by PhpStorm.
 * User: kakoushias
 * Date: 24/03/2018
 * Time: 2:10 AM
 */

namespace App\Repositories;


use App\Activity;
use App\CarePlan;
use App\Patient;
use App\User;

class OpsDashboardPatientEloquentRepository
{

    /**
     * get all patients that date paused, withdrawn, or registered in month(same for all dateTypes)
     * dates are Carbon->toDateTimeString()
     *
     * @param $fromDate
     * @param $toDate
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|static[]
     */
    public function getPatientsByStatus($fromDate = null, $toDate = null)
    {
        //may not need case where no dates are given
        //may not need Gcode hold ATM

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
                                    $patient->whereIn('ccm_status',
                                        [Patient::PAUSED, Patient::WITHDRAWN, Patient::ENROLLED]);
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
     * gets all patients that have any CCM Time for the given date range
     *
     * @param $fromDate
     * @param $toDate
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|static[]
     */
    public function getPatientsByCcmTime($fromDate, $toDate)
    {
        //we may not need this
        //need with 0 time as well
        $patients = User::with([
            'patientInfo' => function ($patient) {
                $patient->where('ccm_status', Patient::ENROLLED);
            },
            'activities'  => function ($q) use ($fromDate, $toDate) {
                $q->where([['performed_at', '>=', $fromDate], ['performed_at', '<=', $toDate]]);
            },
        ])
                        ->whereHas('patientInfo', function ($patient) {
                            $patient->where('ccm_status', Patient::ENROLLED);
                        })
                        ->whereHas('activities', function ($q) use ($fromDate, $toDate) {
                            $q->where([['performed_at', '>=', $fromDate], ['performed_at', '<=', $toDate]]);
                        })
            //memory running out
                        ->take(10)
                        ->get();

        return $patients;
    }

    public function totalTimeForPatient(
        User $p,
        $fromDate,
        $toDate,
        $format = false
    ) {
        $raw = Activity::where('patient_id', $p->id)
                       ->where('performed_at', '>', $fromDate)
                       ->where('performed_at', '<', $toDate)
                       ->sum('duration');

        if ($format) {
            return round($raw / 60, 2);
        }

        return $raw;
    }

    public function getEnrolledPatients($fromDate, $toDate)
    {

        $patients = User::with([
            'patientInfo' => function ($patient) {
                $patient->where('ccm_status', Patient::ENROLLED);
            },
            'activities',
        ])
                        ->whereHas('patientInfo', function ($patient) {
                            $patient->where('ccm_status', Patient::ENROLLED);
                        })
            //memory running out
                        ->take(10)
                        ->get();

        return $patients;
    }

    public function getTotalActivePatientCount()
    {
        $patients = User::whereHas('primaryPractice', function($q){
            $q->active();
        })->count();
    }

}