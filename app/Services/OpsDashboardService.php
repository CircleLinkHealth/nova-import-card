<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Repositories\OpsDashboardPatientEloquentRepository;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Support\Facades\DB;

class OpsDashboardService
{
    const MIN_CALL       = 1;
    const TWENTY_MINUTES = 1200;

    protected $timeGoal;
    private $repo;

    public function __construct(OpsDashboardPatientEloquentRepository $repo)
    {
        $this->repo = $repo;
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
     * @param mixed $totalNumberOfEnrolledPatients
     * @param mixed $totalPatientCcmTime
     *
     * @return float|int
     */
    public function calculateHoursBehind(Carbon $date, int $totalNumberOfEnrolledPatients, int $totalPatientCcmTime)
    {
        $this->setTimeGoal();

        $totActPt                = $totalNumberOfEnrolledPatients;
        $targetMinutesPerPatient = floatval($this->timeGoal);

        $startOfMonth       = $date->copy()->startOfMonth();
        $endOfMonth         = $date->copy()->endOfMonth();
        $workingDaysElapsed = calculateWeekdays($startOfMonth->toDateTimeString(), $date->toDateTimeString());
        $workingDaysMonth   = calculateWeekdays(
            $startOfMonth->toDateTimeString(),
            $endOfMonth->toDateTimeString()
        );

        $avgMinT = ($workingDaysElapsed / $workingDaysMonth) * $targetMinutesPerPatient;

        $sum = $totalPatientCcmTime;

        $avg = $sum / $totActPt;

        $avgMinA = $avg / 60;

        $hoursBehind = ($avgMinT - $avgMinA) * $totActPt / 60;

        return round($hoursBehind, 1);
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
    public function dailyReportRow($patients, Carbon $date, array $priorDayReport = null)
    {
        //count patients by statuses
        $totalEnrolled = '';
        //caregorize by time

        //calculate delta

        //return final array

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

        $totalCcmTime = [];
        foreach ($patients as $patient) {
            if ( ! $patient->patientInfo) {
                continue;
            }
            if (Patient::ENROLLED == $patient->patientInfo->ccm_status) {
                if ($patient->patientSummaries->first()) {
                    $summary        = $patient->patientSummaries->first();
                    $bhiTime        = $summary->bhi_time;
                    $totalCcmTime[] = $ccmTime = $summary->ccm_time;

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
                    if (in_array($revisionHistory->first()->new_value, [Patient::WITHDRAWN, Patient::WITHDRAWN_1ST_CALL])) {
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
        //use where(patientInfo.ccmStatus)
        $count['Total'] = $patients->filter(function ($value, $key) {
            return 'enrolled' == $value->patientInfo->ccm_status;
        })->count();

        $countWhereEnrolled = $patients->where('patientInfo.ccm_status', Patient::ENROLLED)->count();

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
                '0 mins'                      => $count['0 mins'],
                '0-5'                         => $count['0-5'],
                '5-10'                        => $count['5-10'],
                '10-15'                       => $count['10-15'],
                '15-20'                       => $count['15-20'],
                '20+'                         => $count['20+'],
                '20+ BHI'                     => $count['20+ BHI'],
                'Total'                       => $count['Total'],
                'Prior Day totals'            => $count['Total'] - $delta,
                'Added'                       => $enrolledCount,
                'Paused'                      => $pausedCount,
                'Unreachable'                 => $unreachableCount,
                'Withdrawn'                   => $withdrawnCount,
                'Delta'                       => $delta,
                'G0506 To Enroll'             => $toEnrollCount,
                'total_enrolled_count'        => 0,
                'total_paused_count'          => 0,
                'total_unreachable_count'     => 0,
                'total_withdrawn_count'       => 0,
                'total_g0506_to_enroll_count' => 0,
                'prior_day_report_updated_at' => 0,
                'report_updated_at'           => 0,
                //adding to help us generate hours behind metric,
                'total_ccm_time' => array_sum($totalCcmTime),
            ]
        );
    }

    public function setTimeGoal()
    {
        $timeGoal = DB::table('report_settings')->where('name', 'time_goal_per_billable_patient')->first();

        $this->timeGoal = $timeGoal
            ? $timeGoal->value
            : '35';

        return true;
    }
}
