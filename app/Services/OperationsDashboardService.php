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

        $monthPatients = $this->getTotalPatientsForMonth($fromDate, $toDate);

        //If selecting specific day: go to day, show relevant week/month totals (EOW)
        if ($dateType == 'day') {

            $dayPatients = $this->filterPatients($monthPatients, $date);

            $fromDate = $date->startOfWeek();
            $toDate   = $date->endOfWeek();

            $weekPatients = $this->filterPatients($monthPatients, $fromDate, $toDate);
        }

        //if selecting week:go to end of week totals, show last day of the week, month UTD totals from end of week.
        if ($dateType == 'week') {

            //last day of week for day totals
            $dayDate = $date->endOfWeek();

            $dayPatients = $this->filterPatients($monthPatients, $dayDate);

            $fromDate = $date->startOfWeek();
            $toDate   = $date->endOfWeek();

            $weekPatients = $this->filterPatients($monthPatients, $fromDate, $toDate);
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

        //change this to filter total patients by practice?

        //better to get from program id?
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
    public function getTotalPatientsForMonth(Carbon $fromDate, Carbon $toDate){

        $patients = User::whereHas('patientInfo', function ($patient) use ($fromDate, $toDate) {
            $patient->whereIn('ccm_status', ['paused', 'withdrawn', 'enrolled'])
                    ->whereBetween('date_paused', [$fromDate, $toDate])
                    ->orWhereBetween('date_withdrawn', [$fromDate, $toDate])
                    ->orWhereBetween('registration_date', [$fromDate, $toDate]);
        })->orWhereHas('carePlan', function ($c) use ($fromDate, $toDate) {
            $c->where('status', 'to_enroll')
              ->whereBetween('updated_at', [$fromDate, $toDate]);
        })
            ->get();


        return $patients;
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


    /**
     *
     * filters a collection of patients according to the date(s) given.
     *
     * @param $patients
     * @param Carbon $date
     * @param Carbon|null $toDate
     *
     * @return mixed
     */
    public function filterPatients($patients, Carbon $date, Carbon $toDate = null)
    {

        if ($toDate == null) {
            $filteredPatients = $patients->whereHas('patientInfo', function ($patient) use ($date) {
                $patient->whereIn('ccm_status', ['paused', 'withdrawn', 'enrolled'])
                        ->where('date_paused', $date)
                        ->orWhere('date_withdrawn', $date)
                        ->orWhere('registration_date', $date);
            })->orWhereHas('carePlan', function ($c) use ($date) {
                $c->where('status', 'to_enroll')
                  ->where('updated_at', $date);
            })
                                         ->get();
        } else {
            $filteredPatients = $patients->whereHas('patientInfo', function ($patient) use ($date, $toDate) {
                $patient->whereIn('ccm_status', ['paused', 'withdrawn', 'enrolled'])
                        ->whereBetween('date_paused', [$date, $toDate])
                        ->orWhereBetween('date_withdrawn', [$date, $toDate])
                        ->orWhereBetween('registration_date', [$date, $toDate]);
            })->orWhereHas('carePlan', function ($c) use ($date, $toDate) {
                $c->where('status', 'to_enroll')
                  ->whereBetween('updated_at', [$date, $toDate]);
            })
                                         ->get();

        }

        return $filteredPatients;

    }
}