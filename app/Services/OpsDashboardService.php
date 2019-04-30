<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Repositories\OpsDashboardPatientEloquentRepository;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\DB;

class OpsDashboardService
{
    const TWENTY_MINUTES = 1200;

    protected $timeGoal;
    private $repo;

    public function __construct(OpsDashboardPatientEloquentRepository $repo)
    {
        $this->repo = $repo;
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
                if (0 == $priorMonthSummary->count()) {
                    ++$added;
                }
            }
        }

        return $added;
    }

    public function calculateBilledPatients($summaries, Carbon $month)
    {
        $filteredSummaries = $summaries->where('month_year', '>=', $month->copy()->startOfMonth())
            ->where('month_year', '<=', $month->copy()->endOfMonth());

        return $filteredSummaries->count();
    }

    /**
     * Gcode hold not calculated at the moment, to be added.
     *
     * @param $enrolled
     * @param $paused
     * @param $withdrawn
     * @param mixed $unreachable
     *
     * @return mixed
     */
    public function calculateDelta($enrolled, $paused, $withdrawn, $unreachable)
    {
        return $enrolled - $paused - $withdrawn - $unreachable;
    }

    /**
     * (AvgMinT - AvgMinA)*TotActPt/60.
     *
     * @param $date
     * @param mixed $practices
     *
     * @return float|int
     */
    public function calculateHoursBehind(Carbon $date, $practices)
    {
        $this->setTimeGoal();

        $enrolledPatients = $practices->map(
            function ($practice) {
                return $practice->patients->filter(
                function ($user) {
                    if ( ! $user) {
                        return false;
                    }
                    if ( ! $user->patientInfo) {
                        return false;
                    }

                    return Patient::ENROLLED == $user->patientInfo->ccm_status;
                }
                );
            }
        )->flatten()->unique('id');

        $totActPt                = $enrolledPatients->count();
        $targetMinutesPerPatient = floatval($this->timeGoal);

        $startOfMonth       = $date->copy()->startOfMonth();
        $endOfMonth         = $date->copy()->endOfMonth();
        $workingDaysElapsed = calculateWeekdays($startOfMonth->toDateTimeString(), $date->toDateTimeString());
        $workingDaysMonth   = calculateWeekdays(
            $startOfMonth->toDateTimeString(),
            $endOfMonth->toDateTimeString()
        );
        $avgMinT = ($workingDaysElapsed / $workingDaysMonth) * $targetMinutesPerPatient;

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

        $avgMinA = $avg / 60;

        $hoursBehind = ($avgMinT - $avgMinA) * $totActPt / 60;

        return round($hoursBehind, 1);
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
                if (0 == $thisMonthSummaries->count()) {
                    ++$lost;
                }
            }
        }

        return $lost;
    }

    /**
     * Returns all the data needed for a row(for a single practice) in Daily Tab.
     *
     * @param $practice
     * @param $date
     * @param mixed $patients
     *
     * @return \Illuminate\Support\Collection
     */
    public function dailyReportRow($patients, Carbon $date)
    {
        $paused           = [];
        $withdrawn        = [];
        $enrolled         = [];
        $unreachable      = [];
        $to_enroll        = [];
        $count['0 mins']  = 0;
        $count['0-5']     = 0;
        $count['5-10']    = 0;
        $count['10-15']   = 0;
        $count['15-20']   = 0;
        $count['20+']     = 0;
        $count['20+ BHI'] = 0;

        foreach ($patients as $patient) {
            if ( ! $patient->patientInfo) {
                continue;
            }
            if (Patient::ENROLLED == $patient->patientInfo->ccm_status) {
                if ($patient->patientSummaries->first()) {
                    $summary = $patient->patientSummaries->first();
                    $bhiTime = $summary->bhi_time;
                    $ccmTime = $summary->ccm_time;

                    if (0 === $ccmTime || null == $ccmTime) {
                        ++$count['0 mins'];
                    }
                    if ($ccmTime > 0 and $ccmTime <= 300) {
                        ++$count['0-5'];
                    }
                    if ($ccmTime > 300 and $ccmTime <= 600) {
                        ++$count['5-10'];
                    }
                    if ($ccmTime > 600 and $ccmTime <= 900) {
                        ++$count['10-15'];
                    }
                    if ($ccmTime > 900 and $ccmTime <= $this::TWENTY_MINUTES) {
                        ++$count['15-20'];
                    }
                    if ($ccmTime > $this::TWENTY_MINUTES) {
                        ++$count['20+'];
                    }
                    if ($bhiTime > $this::TWENTY_MINUTES) {
                        ++$count['20+ BHI'];
                    }
                } else {
                    if (Patient::ENROLLED == $patient->patientInfo->ccm_status) {
                        ++$count['0 mins'];
                    }
                }
            }
            $revisionHistory = $patient->patientInfo->revisionHistory->sortByDesc('created_at');

            if ($revisionHistory->isNotEmpty()) {
                if (Patient::ENROLLED == $revisionHistory->last()->old_value) {
                    if (Patient::UNREACHABLE == $revisionHistory->first()->new_value) {
                        $unreachable[] = $patient;
                    }
                    if (Patient::PAUSED == $revisionHistory->first()->new_value) {
                        $paused[] = $patient;
                    }
                    if (Patient::WITHDRAWN == $revisionHistory->first()->new_value) {
                        $withdrawn[] = $patient;
                    }
                }
                if (Patient::ENROLLED !== $revisionHistory->last()->old_value &&
                    Patient::ENROLLED == $revisionHistory->first()->new_value) {
                    $enrolled[] = $patient;
                }
            }
            if (Patient::TO_ENROLL == $patient->patientInfo->ccm_status) {
                $to_enroll[] = $patient;
            }
        }
        $count['Total'] = $patients->filter(function ($value, $key) {
            return 'enrolled' == $value->patientInfo->ccm_status;
        })->count();

        $pausedCount      = count($paused);
        $withdrawnCount   = count($withdrawn);
        $enrolledCount    = count($enrolled);
        $unreachableCount = count($unreachable);
        $toEnrollCount    = count($to_enroll);
        $delta            = $this->calculateDelta($enrolledCount, $pausedCount, $withdrawnCount, $unreachableCount);

        if (0 == $count['Total'] &&
            $count['Total'] - $delta == 0 &&
            0 == $enrolledCount &&
            0 == $pausedCount &&
            0 == $withdrawnCount &&
            0 == $unreachableCount) {
            return null;
        }

        return collect(
            [
                '0 mins'           => $count['0 mins'],
                '0-5'              => $count['0-5'],
                '5-10'             => $count['5-10'],
                '10-15'            => $count['10-15'],
                '15-20'            => $count['15-20'],
                '20+'              => $count['20+'],
                '20+ BHI'          => $count['20+ BHI'],
                'Total'            => $count['Total'],
                'Prior Day totals' => $count['Total'] - $delta,
                'Added'            => $enrolledCount,
                'Paused'           => $pausedCount,
                'Unreachable'      => $unreachableCount,
                'Withdrawn'        => $withdrawnCount,
                'Delta'            => $delta,
                'G0506 To Enroll'  => $toEnrollCount,
            ]
        );
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
        return $patients->where('program_id', $practiceId);
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
     * @param $practices
     * @param $format
     * @param Carbon $date
     * @param mixed  $fromDate
     * @param mixed  $toDate
     * @param mixed  $status
     * @param mixed  $practiceId
     *
     * @return mixed
     */
    public function getExcelReport($fromDate, $toDate, $status, $practiceId)
    {
        return (new OpsDashboardPatientsReport($practiceId, $status, $fromDate, $toDate))
            ->storeAndAttachMediaTo(auth()->user()->saasAccount);
    }

    /**
     * Old dashboard.
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

    public function lostAddedRow($patientsByPractice, $fromDate)
    {
        $countsByStatus = $this->countPatientsByStatus($patientsByPractice, $fromDate);

        return collect($countsByStatus);
    }
}
