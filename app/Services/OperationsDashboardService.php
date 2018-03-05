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

    public function getPausedPatients(Carbon $fromDate, Carbon $toDate)
    {


        $patients = User::whereHas('patientInfo', function ($patient) use ($fromDate, $toDate) {
            $patient->where('ccm_status', 'paused')
                    ->where('date_paused', '>', $fromDate)
                    ->where('date_paused', '<', $toDate);
        })
                        ->get();

        return $patients;

    }

    public function getPatientsByPractice($practiceId)
    {

        $patients = User::ofType('participant')
                        ->where([])
                        ->get();


        foreach ($patients as $patient) {

            if ($patient->status == 'paused') {


            }

        }

    }
}