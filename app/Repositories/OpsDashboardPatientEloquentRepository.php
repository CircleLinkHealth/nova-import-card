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
use App\Practice;
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
                    $patient->byStatus($fromDate, $toDate);
                },
                //                'carePlan'    => function ($c) use ($fromDate, $toDate) {
                //                    $c->where('status', CarePlan::TO_ENROLL)
                //                      ->where([['updated_at', '>=', $fromDate], ['updated_at', '<=', $toDate]]);
                //                },
            ])
                            ->whereHas('patientInfo', function ($patient) use ($fromDate, $toDate) {
                                $patient->byStatus($fromDate, $toDate);
                            })
//                            ->orWhere(function ($query) use ($fromDate, $toDate) {
//                                $query->whereHas('patientInfo', function ($patient) {
//                                    $patient->whereIn('ccm_status',
//                                        [Patient::PAUSED, Patient::WITHDRAWN, Patient::ENROLLED]);
//                                })
//                                      ->whereHas('carePlan', function ($c) use ($fromDate, $toDate) {
//                                          $c->where('status', CarePlan::TO_ENROLL)
//                                            ->where([['updated_at', '>=', $fromDate], ['updated_at', '<=', $toDate]]);
//                                      });
//                            })
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
//                            ->orWhere(function ($query) {
//                                $query->whereHas('patientInfo', function ($patient) {
//                                    $patient->whereIn('ccm_status',
//                                        [Patient::PAUSED, Patient::WITHDRAWN, Patient::ENROLLED]);
//                                })
//                                      ->whereHas('carePlan', function ($c) {
//                                          $c->where('status', CarePlan::TO_ENROLL);
//                                      });
//                            })
                            ->get();
        }


        return $patients;
    }

}

