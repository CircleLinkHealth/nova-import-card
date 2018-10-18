<?php
/**
 * Created by PhpStorm.
 * User: kakoushias
 * Date: 06/03/2018
 * Time: 12:21 AM
 */

namespace App\Services;


use App\Activity;
use App\Patient;
use App\Repositories\OpsDashboardPatientEloquentRepository;
use App\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class OpsDashboardService
{

    private $repo;

    public function __construct(OpsDashboardPatientEloquentRepository $repo)
    {
        $this->repo = $repo;
    }


    /**
     * @param $practices
     * @param $format
     * @param Carbon $date
     *
     * @return mixed
     */
    public function getExcelReport($fromDate, $toDate, $status, $practiceId)
    {
        $data = [];


        $patients = $this->repo->getPatientsByStatus($fromDate, $toDate);
        if ($practiceId != 'all') {
            $patients = $this->filterPatientsByPractice($patients, $practiceId);
        }

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

    public function makeExcelReport($rows, $fromDate, $toDate)
    {

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


        if ($patient->patientInfo->registration_date >= $fromDate->toDateTimeString() && $patient->patientInfo->registration_date <= $toDate->toDateTimeString() && $patient->patientInfo->ccm_status != 'enrolled') {
            $status       = $patient->patientInfo->ccm_status;
            $statusColumn = "Added - $status ";
        } else {
            $statusColumn = $patient->patientInfo->ccm_status;
        }

        if ($patient->patientInfo->ccm_status == 'paused') {
            $statusDate       = $patient->patientInfo->date_paused;
            $statusDateColumn = "Paused: $statusDate";
        } elseif ($patient->patientInfo->ccm_status == 'withdrawn') {
            $statusDate       = $patient->patientInfo->date_withdrawn;
            $statusDateColumn = "Withdrawn: $statusDate";
        } else {
            $statusDateColumn = '-';
        }

        $rowData = [
            'Name'                  => $patient->display_name,
            'DOB'                   => $patient->patientInfo->birth_date,
            'Practice Name'         => $patient->getPrimaryPracticeNameAttribute(),
            'Status'                => $statusColumn,
            'Date Registered'       => $patient->patientInfo->registration_date,
            'Date Paused/Withdrawn' => $statusDateColumn,
            'Enroller'              => '-',
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
        $filteredPatients = $patients->where('program_id', $practiceId);


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

    public function filterSummariesByPractice($summaries, $practiceId)
    {
        $filteredSummaries = [];

        foreach ($summaries as $summary) {
            if ($summary->patient->program_id == $practiceId) {
                $filteredSummaries[] = $summary;
            }
        }

        return collect($filteredSummaries);
    }

    /**
     * Returns all the data needed for a row(for a single practice) in Daily Tab.
     *
     * @param $practice
     * @param $date
     *
     * @return \Illuminate\Support\Collection
     */
    public function dailyReportRow($patients, Carbon $date)
    {
        $fromDate = $date->copy()->subDay()->setTimeFromTimeString('23:00');

        $paused          = [];
        $withdrawn       = [];
        $enrolled        = [];
        $unreachable     = [];
        $to_enroll       = [];
        $count['0 mins'] = 0;
        $count['0-5']    = 0;
        $count['5-10']   = 0;
        $count['10-15']  = 0;
        $count['15-20']  = 0;
        $count['20+']    = 0;

        foreach ($patients as $patient) {
            if ( ! $patient->patientInfo) {
                continue;
            }
            if ($patient->patientInfo->ccm_status == Patient::ENROLLED) {
                if ($patient->patientSummaries->first()) {
                    $ccmTime = $patient->patientSummaries->first()->ccm_time;

                    if ($ccmTime === 0 || $ccmTime == null) {
                        $count['0 mins'] += 1;
                    }
                    if ($ccmTime > 0 and $ccmTime <= 300) {
                        $count['0-5'] += 1;
                    }
                    if ($ccmTime > 300 and $ccmTime <= 600) {
                        $count['5-10'] += 1;
                    }
                    if ($ccmTime > 600 and $ccmTime <= 900) {
                        $count['10-15'] += 1;
                    }
                    if ($ccmTime > 900 and $ccmTime <= 1200) {
                        $count['15-20'] += 1;
                    }
                    if ($ccmTime > 1200) {
                        $count['20+'] += 1;
                    }
                } else {
                    if ($patient->patientInfo->ccm_status == Patient::ENROLLED) {
                        $count['0 mins'] += 1;
                    }
                }
            }
            //It's assumed that a patient that had it's ccm_status change in the last day, will also have an entry in the revisions table
            if ($patient->patientInfo->revisionHistory->isNotEmpty()) {
                if ($patient->patientInfo->ccm_status == Patient::UNREACHABLE &&
                    $patient->patientInfo->date_unreachable >= $fromDate &&
                    $patient->patientInfo->revisionHistory->sortByDesc('created_at')->last()->old_value == Patient::ENROLLED) {
                    $unreachable[] = $patient;
                }
                if ($patient->patientInfo->ccm_status == Patient::PAUSED &&
                    $patient->patientInfo->date_paused >= $fromDate &&
                    $patient->patientInfo->revisionHistory->sortByDesc('created_at')->last()->old_value == Patient::ENROLLED) {
                    $paused[] = $patient;
                }
                if ($patient->patientInfo->ccm_status == Patient::WITHDRAWN &&
                    $patient->patientInfo->date_withdrawn >= $fromDate &&
                    $patient->patientInfo->revisionHistory->sortByDesc('created_at')->last()->old_value == Patient::ENROLLED) {
                    $withdrawn[] = $patient;
                }
            }

            if ($patient->patientInfo->ccm_status == Patient::ENROLLED &&
                $patient->patientInfo->registration_date >= $fromDate) {
                $enrolled[] = $patient;
            }
            if ($patient->patientInfo->ccm_status == Patient::TO_ENROLL) {
                $to_enroll[] = $patient;
            }
        }
        $count['Total'] = $count['0 mins'] + $count['0-5'] + $count['5-10'] + $count['10-15'] + $count['15-20'] + $count['20+'];

        $pausedCount      = count($paused);
        $withdrawnCount   = count($withdrawn);
        $enrolledCount    = count($enrolled);
        $unreachableCount = count($unreachable);
        $toEnrollCount    = count($to_enroll);
        $delta            = $this->calculateDelta($enrolledCount, $pausedCount, $withdrawnCount, $unreachableCount);

        if ($count['Total'] == 0 &&
            $count['Total'] - $delta == 0 &&
            $enrolledCount == 0 &&
            $pausedCount == 0 &&
            $withdrawnCount == 0 &&
            $unreachableCount == 0) {
            return null;
        }

        return collect([
            '0 mins'           => $count['0 mins'],
            '0-5'              => $count['0-5'],
            '5-10'             => $count['5-10'],
            '10-15'            => $count['10-15'],
            '15-20'            => $count['15-20'],
            '20+'              => $count['20+'],
            'Total'            => $count['Total'],
            'Prior Day totals' => $count['Total'] - $delta,
            'Added'            => $enrolledCount,
            'Paused'           => $pausedCount,
            'Unreachable'      => $unreachableCount,
            'Withdrawn'        => $withdrawnCount,
            'Delta'            => $delta,
            'G0506 To Enroll'  => $toEnrollCount,
        ]);
    }

    public function billingChurnRow($summaries, $months)
    {
        $row = [];

        //where month is carbon object
        foreach ($months as $month) {
            $row['Billed'][$month->format('m, Y')]            = $this->calculateBilledPatients($summaries, $month);
            $row['Added to Billing'][$month->format('m, Y')]  = $this->calculateAddedToBilling($summaries, $month);
            $row['Lost from Billing'][$month->format('m, Y')] = $this->calculateLostFromBilling($summaries, $month);
        }


        return collect($row);

    }

    public function lostAddedRow($patientsByPractice, $fromDate)
    {

        $countsByStatus = $this->countPatientsByStatus($patientsByPractice, $fromDate);

        return collect($countsByStatus);
    }


    public function calculateBilledPatients($summaries, Carbon $month)
    {

        $filteredSummaries = $summaries->where('month_year', '>=', $month->copy()->startOfMonth())
                                       ->where('month_year', '<=', $month->copy()->endOfMonth());

        return $filteredSummaries->count();
    }

    public function calculateAddedToBilling($summaries, Carbon $month)
    {

        $added = 0;

        $filteredSummaries = $summaries->where('month_year', '>=', $month->copy()->startOfMonth())
                                       ->where('month_year', '<=', $month->copy()->endOfMonth());

        if ($filteredSummaries->count() > 0) {
            foreach ($filteredSummaries as $summary) {
                $priorMonthSummary = $summaries->where('month_year', '>=', $month->copy()->subMonth()->startOfMonth())
                                               ->where('month_year', '<=', $month->copy()->subMonth()->endOfMonth())
                                               ->where('patient_id', $summary->patient_id);
                if ($priorMonthSummary->count() == 0) {
                    $added += 1;
                }
            }
        }


        return $added;

    }

    public function calculateLostFromBilling($summaries, Carbon $month)
    {
        $lost = 0;

        $fromDate = $month->copy()->startOfMonth();
        $toDate   = $month->copy()->endOfMonth();

        $pastMonthSummaries = $summaries->where('month_year', '>=', $month->copy()->subMonth()->startOfMonth())
                                        ->where('month_year', '<=', $month->copy()->subMonth()->endOfMonth());

        if ($pastMonthSummaries->count() > 0) {
            foreach ($pastMonthSummaries as $summary) {
                $thisMonthSummaries = $summaries->where('month_year', '>=', $month->copy()->startOfMonth())
                                                ->where('month_year', '<=', $month->copy()->endOfMonth())
                                                ->where('patient_id', $summary->patient_id);
                if ($thisMonthSummaries->count() == 0) {
                    $lost += 1;
                }
            }
        }

        return $lost;

    }

    /**
     * (AvgMinT - AvgMinA)*TotActPt/60
     *
     * @param $date
     *
     * @return float|int
     */
    public function calculateHoursBehind(Carbon $date, $practices)
    {
        $enrolledPatients = $practices->map(function ($practice) {
            return $practice->patients->filter(function ($user) {
                if ( ! $user) {
                    return false;
                }
                if ( ! $user->patientInfo) {
                    return false;
                }

                return $user->patientInfo->ccm_status == Patient::ENROLLED;
            });
        })->flatten()->unique('id');

        $totActPt                = $enrolledPatients->count();
        $targetMinutesPerPatient = 35;

        $startOfMonth       = $date->copy()->startOfMonth();
        $endOfMonth         = $date->copy()->endOfMonth();
        $workingDaysElapsed = $this->calculateWeekdays($startOfMonth->toDateTimeString(), $date->toDateTimeString());
        $workingDaysMonth   = $this->calculateWeekdays($startOfMonth->toDateTimeString(),
            $endOfMonth->toDateTimeString());
        $avgMinT            = ($workingDaysElapsed / $workingDaysMonth) * $targetMinutesPerPatient;

        $allPatients = $enrolledPatients->pluck('id')->unique()->all();

//        $sum = Activity::whereIn('patient_id', $allPatients)
//                       ->where('performed_at', '>=', $startOfMonth)
//                       ->where('performed_at', '<=', $date)
//                       ->sum('duration');

        $ccmTimeTotal = [];
        foreach ($enrolledPatients as $patient) {
            if ($patient->patientSummaries->first()) {
                $ccmTimeTotal[] = $patient->patientSummaries->first()->ccm_time;
            }
        }
        $sum = array_sum($ccmTimeTotal);

        $avg = $sum / count($allPatients);

        $avgMinA = round($avg / 60, 2);

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
    public function calculateDelta($enrolled, $paused, $withdrawn, $unreachable)
    {

        $delta = $enrolled - $paused - $withdrawn - $unreachable;

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