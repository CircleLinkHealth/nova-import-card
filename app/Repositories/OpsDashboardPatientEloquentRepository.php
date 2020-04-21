<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Repositories;

use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;

class OpsDashboardPatientEloquentRepository
{
    /**
     * get all patients that date paused, withdrawn, or registered in month(same for all dateTypes)
     * dates are Carbon->toDateTimeString().
     *
     * @param $fromDate
     * @param $toDate
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|static[]
     */
    public function getPatientsByStatus($fromDate = null, $toDate = null)
    {
        if ($fromDate and $toDate) {
            $patients = User::with([
                'patientInfo' => function ($patient) use ($fromDate, $toDate) {
                    $patient->byStatus($fromDate, $toDate);
                },
            ])
                ->whereHas('patientInfo', function ($patient) use ($fromDate, $toDate) {
                    $patient->byStatus($fromDate, $toDate);
                })
                ->get();
        } else {
            $patients = User::with([
                'patientInfo' => function ($patient) {
                    $patient->whereIn('ccm_status', [Patient::PAUSED, Patient::WITHDRAWN, Patient::ENROLLED]);
                },
            ])
                ->whereHas('patientInfo', function ($patient) {
                    $patient->whereIn(
                        'ccm_status',
                        [Patient::PAUSED, Patient::WITHDRAWN, Patient::ENROLLED]
                    );
                })
                ->get();
        }

        return $patients;
    }
}
