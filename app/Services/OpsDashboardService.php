<?php
/**
 * Created by PhpStorm.
 * User: kakoushias
 * Date: 06/03/2018
 * Time: 12:21 AM
 */

namespace App\Services;


use App\Patient;
use App\Practice;
use App\Repositories\OpsDashboardPatientEloquentRepository;
use App\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class OpsDashboardService
{

    private $repo;

    public function __construct()
    {
        $this->repo = new OpsDashboardPatientEloquentRepository();
    }


    /**
     * @param $practices
     * @param $format
     * @param Carbon $date
     *
     * @return mixed
     */
    public function getExcelReport($fromDate, $toDate, $status = null)
    {
        $data = [];


        $patients = $this->repo->getPatientsByStatus($fromDate, $toDate);

        if ($status == 'paused' || $status == 'withdrawn') {
            $patients = $this->filterPatientsByStatus($patients, $status);
        }

        foreach ($patients as $patient) {
            //collection
            $row = $this->makeExcelRow($patient, $fromDate, $toDate);
            if ($row != null) {
                $data[] = $row->toArray();
            }
        }

        return $this->makeExcelReport($data, $fromDate, $toDate);

    }

    public function makeExcelReport($rows, $fromDate, $toDate){

        $report = Excel::create("Ops Dashboard Patients Report - $fromDate to $toDate", function ($excel) use ($rows) {
            $excel->sheet('Ops Dashboard Patients', function ($sheet) use ($rows) {
                $sheet->fromArray($rows);
            });
        })
                       ->store('xls', false, true);

        return auth()->user()
            ->saasAccount
            ->addMedia($report['full'])
            ->toMediaCollection("excel_report_for_{$fromDate->toDateString()}_to{$toDate->toDateString()}");
    }

    public function makeExcelRow($patient, $fromDate, $toDate)
    {


        if($patient->patientInfo->registration_date >= $fromDate->toDateTimeString() && $patient->patientInfo->registration_date <= $toDate->toDateTimeString() && $patient->patientInfo->ccm_status != 'enrolled'){
            $status = $patient->patientInfo->ccm_status;
            $statusColumn = "Added - $status ";
        }else{
            $statusColumn = $patient->patientInfo->ccm_status;
        }

        if($patient->patientInfo->ccm_status == 'paused'){
            $statusDate = $patient->patientInfo->date_paused;
            $statusDateColumn = "Paused: $statusDate";
        }elseif($patient->patientInfo->ccm_status == 'withdrawn'){
            $statusDate = $patient->patientInfo->date_withdrawn;
            $statusDateColumn = "Withdrawn: $statusDate";
        }else{
            $statusDateColumn = '-';
        }

        $rowData = [
            'Name'   => $patient->display_name,
            'DOB' => $patient->patientInfo->birth_date,
            'Practice Name' => $patient->getPrimaryPracticeNameAttribute(),
            'Status' => $statusColumn,
            'Date Registered' => $patient->patientInfo->registration_date,
            'Date Paused/Withdrawn' => $statusDateColumn,
            'Enroller' => '-',
        ];

        return collect($rowData);

    }


    /**
     *
     * Old dashboard
     *
     *
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


    public function filterPatientsByStatus($patients, $status)
    {

        $filteredPatients = [];

        foreach ($patients as $patient) {
            if ($patient->patientInfo) {
                if ($patient->patientInfo->ccm_status == $status) {
                    $filteredPatients[] = $patient;
                }
            }
        }

        return collect($filteredPatients);

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
        $delta          = $this->calculateDelta($enrolledCount, $pausedCount, $withdrawnCount);


        return collect([
            'pausedPatients'    => $pausedCount,
            'withdrawnPatients' => $withdrawnCount,
            'enrolled'          => $enrolledCount,
            'gCodeHold'         => $gCodeHoldCount,
            'delta'             => $delta,
        ]);


    }


    /**
     * categorizes patient count by ccmTime(seconds)
     *
     * @param $patients
     * @param $fromDate
     * @param $toDate
     *
     * @return mixed
     */
    public function countPatientsByCcmTime($patients, $fromDate, $toDate)
    {

        $count['zero']   = 0;
        $count['0to5']   = 0;
        $count['5to10']  = 0;
        $count['10to15'] = 0;
        $count['15to20'] = 0;
        $count['20plus'] = 0;
        $count['total']  = 0;

        foreach ($patients as $patient) {

            if ($patient->activities) {

                $ccmTime = $this->repo->totalTimeForPatient($patient, $fromDate, $toDate);
                if ($ccmTime == 0) {
                    $count['zero'] += 1;
                }
                if ($ccmTime > 0 and $ccmTime <= 300) {
                    $count['0to5'] += 1;
                }
                if ($ccmTime > 300 and $ccmTime <= 600) {
                    $count['5to10'] += 1;
                }
                if ($ccmTime > 600 and $ccmTime <= 900) {
                    $count['10to15'] += 1;
                }
                if ($ccmTime > 900 and $ccmTime <= 1200) {
                    $count['15to20'] += 1;
                }
                if ($ccmTime > 1200) {
                    $count['20plus'] += 1;
                }
            } else {
                $count['zero'] += 1;
            }
        }
        $count['total'] = $count['zero'] + $count['0to5'] + $count['5to10'] + $count['10to15'] + $count['15to20'] + $count['20plus'];

        return $count;
    }


    /**
     * Returns counts categorised by ccmTime ranges for a single practice, for a given date range
     *
     * @param $practice
     * @param $patients
     * @param $fromDate
     * @param $toDate
     *
     * @return array
     */
    public function getPracticeCcmTotalCounts($practice, $fromDate, $toDate)
    {

        $filteredPatients = $this->getEnrolledPatientsFilteredByPractice($practice, $fromDate, $toDate);
        $counts           = $this->countPatientsByCcmTime($filteredPatients, $fromDate, $toDate);

        return $counts;
    }

    /**
     * Returns counts for enrolled, paused, withdrawn and delta for a single practice
     *
     * @param $practice
     * @param $fromDate
     * @param $toDate
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPracticeCountsByStatus($practice, $fromDate, $toDate)
    {
        $patientsByStatus = $this->repo->getPatientsByStatus($fromDate, $toDate);
        $filteredPatients = $this->filterPatientsByPractice($patientsByStatus, $practice->id);
        $counts           = $this->countPatientsByStatus($filteredPatients);

        return $counts;

    }


    /**
     * Returns all the data needed for a row(for a single practice) in Daily Tab.
     *
     * @param $practice
     * @param $date
     *
     * @return \Illuminate\Support\Collection
     */
    public function dailyReportRow($practice, $date)
    {
        $date     = new Carbon($date);
        $fromDate = $date->copy()->startOfMonth()->startOfDay()->toDateTimeString();

        //ccm from date must be start of month
        $ccmCounts = $this->getPracticeCcmTotalCounts($practice, $fromDate, $date->toDateTimeString());
        //total for day before
        $priorDay = $date->copy()->subDay(1)->toDateTimeString();

        $priorDayCcmCounts           = $this->getPracticeCcmTotalCounts($practice, $fromDate, $priorDay);
        $ccmCounts['priorDayTotals'] = $priorDayCcmCounts['total'];
        $ccmTotal                    = collect($ccmCounts);


        $countsByStatus = $this->getPracticeCountsByStatus($practice, $fromDate, $date->toDateTimeString());

        if ($ccmCounts['total'] == 0 && $ccmCounts['priorDayTotals'] == 0 &&
            $countsByStatus['enrolled'] == 0 &&
            $countsByStatus['pausedPatients'] == 0 &&
            $countsByStatus['withdrawnPatients'] == 0 &&
            $countsByStatus['gCodeHold'] == 0) {
            return null;
        }


        return collect([
            'ccmCounts'      => $ccmTotal,
            'countsByStatus' => $countsByStatus,
        ]);

    }

    public function lostAddedRow($practice, $fromDate, $toDate)
    {

        $countsByStatus = $this->getPracticeCountsByStatus($practice, $fromDate, $toDate);

        if ($countsByStatus['enrolled'] == 0 &&
            $countsByStatus['pausedPatients'] == 0 &&
            $countsByStatus['withdrawnPatients'] == 0 &&
            $countsByStatus['gCodeHold'] == 0) {
            return null;
        }

        return collect($countsByStatus);
    }

    /**
     * @param $practice
     * @param $fromDate
     * @param $toDate
     *
     * @return \Illuminate\Support\Collection
     */
    public function getEnrolledPatientsFilteredByPractice($practice, $fromDate, $toDate)
    {
        $enrolledPatients = $this->repo->getEnrolledPatients($fromDate, $toDate);
        $filteredPatients = $this->filterPatientsByPractice($enrolledPatients, $practice->id);

        return $filteredPatients;
    }

    /**
     * AvgMinT - AvgMinA)*TotActPt/60
     *
     * @param $date
     *
     * @return float|int
     */
    public function calculateHoursBehind($date)
    {

        $date = new Carbon($date);

        $totActPt = $this->repo->getTotalActivePatientCount();

        //date current day or last day completed 11:00 pm?
        $startOfMonth       = $date->copy()->startOfMonth()->startOfDay();
        $endOfMonth         = $date->copy()->endOfMonth()->endOfDay();
        $workingDaysElapsed = $this->calculateWeekdays($startOfMonth->toDateTimeString(), $date->toDateTimeString());
        $workingDaysMonth   = $this->calculateWeekdays($startOfMonth->toDateTimeString(),
            $endOfMonth->toDateTimeString());
        $avgMinT            = $workingDaysElapsed / $workingDaysMonth * 35;

        $totalCcm  = [];
        $practices = Practice::active()->get();
        foreach ($practices as $practice) {
            $patients = $this->getEnrolledPatientsFilteredByPractice($practice, $startOfMonth->toDateTimeString(),
                $date->toDateTimeString());
            foreach ($patients as $patient) {
                //format true to get minutes
                $totalCcm[] = $this->repo->totalTimeForPatient($patient, $startOfMonth->toDateTimeString(),
                    $date->toDateTimeString(), true);
            }
        }
        $totalCcm = array_filter($totalCcm);
        if (count($totalCcm) !== 0) {
            $average = array_sum($totalCcm) / count($totalCcm);
            $avgMinA = $average;
        } else {
            $avgMinA = 0;
        }

        //make to 1 decimal TODO
        $hoursBehind = ($avgMinT - $avgMinA) * $totActPt / 60;

        return round($hoursBehind, 1);


    }

    /**
     * Gcode hold not calculated at the moment, to be added
     *
     * @param $enrolled
     * @param $paused
     * @param $withdrawn
     *
     * @return mixed
     */
    public function calculateDelta($enrolled, $paused, $withdrawn)
    {

        $delta = $enrolled - $paused - $withdrawn;

        return $delta;
    }


    /**
     * @param $fromDate
     * @param $toDate
     *
     * @return int
     */
    public function calculateWeekdays($fromDate, $toDate)
    {

        return Carbon::parse($fromDate)->diffInDaysFiltered(function (Carbon $date) {
            return ! $date->isWeekend();
        }, new Carbon($toDate));

    }


}