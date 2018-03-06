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
     * get patients for month, the rest will be filtered in the controller?
     *
     * @param Carbon $date
     */
    public function getCpmPatientTotals(Carbon $date)
    {
        $fromDate = $date->startOfMonth();
        $toDate = $date->addMonth();

        //get all patients that date paused, withdrawn, or registered in month

        $patients = User::whereHas('patientInfo', function ($patient) use ($fromDate, $toDate) {
            $patient->where('ccm_status', 'paused')
                    ->where('date_paused', '>=', $fromDate)
                    ->where('date_paused', '<=', $toDate);
        })
                        ->get();

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
     * @param $practiceId
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPatientsByPractice($practiceId)
    {

        //better to get from program id?
        $patients = User::ofType('participant')
                        ->whereHas('primaryPractice', function ($p) use ($practiceId) {
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
     * @param $patients
     *
     * @return \Illuminate\Support\Collection
     */
    private function countPatientsByStatus($patients)
    {

        $paused = $patients->whereHas('patientInfo', function ($p) {
            $p->where('ccm_status', 'paused');
        })->count();


        $withdrawn = $patients->whereHas('patientInfo', function ($p) {
            $p->where('ccm_status', 'withdrawn');
        })->count();

        $enrolled = $patients->whereHas('patientInfo', function ($p) {
            $p->where('ccm_status', 'enrolled');
        })->count();

        $gCodeHold = $patients->whereHas('carePlan', function ($c) {
            $c->where('status', 'to_enroll');
        });


        return collect([
            'pausedPatients'    => $paused,
            'withdrawnPatients' => $withdrawn,
            'enrolled'          => $enrolled,
            'gCodeHold'         => $gCodeHold,
        ]);


    }
}