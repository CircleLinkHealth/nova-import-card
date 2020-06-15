<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Support\Facades\DB;

class OpsDashboardReport
{
    const MIN_CALL       = 1;
    const TWENTY_MINUTES = 1200;

    protected $date;
    protected $enrolledPatients = [];
    protected $g0506Patients    = [];
    protected $patients;
    protected $pausedpatients = [];
    protected $practice;

    protected $stats = [
        //how many patients are in each CCM or BHI time category
        '0 mins'  => 0,
        '0-5'     => 0,
        '5-10'    => 0,
        '10-15'   => 0,
        '15-20'   => 0,
        '20+'     => 0,
        '20+ BHI' => 0,
        //total enrolled patients
        'Total'            => 0,
        'Prior Day totals' => 0,
        //How many patients have been added or lost for this date
        'Added'       => 0,
        'Paused'      => 0,
        'Unreachable' => 0,
        'Withdrawn'   => 0,
        //all patients added minus all patients lost
        'Delta'           => 0,
        'G0506 To Enroll' => 0,
        //added to help us generate next day's report - so we don't rely on revisions
        'total_enrolled_count'        => 0,
        'total_paused_count'          => 0,
        'total_unreachable_count'     => 0,
        'total_withdrawn_count'       => 0,
        'total_g0506_to_enroll_count' => 0,
        'prior_day_report_updated_at' => 0,
        'report_updated_at'           => 0,
        //adding to help us generate hours behind metric,
        'total_ccm_time' => 0,
    ];

    protected $timeGoal;
    protected $withdrawnPatients = [];

    public function __construct(Practice $practice, Carbon $date)
    {
        $this->practice = $practice;
        $this->date     = $date;
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
    public function dailyReportRow()
    {
        return $this->getPatients()
            ->generateStatsFromPatientCollection()
            ->consolidateStatsUsingPriorDayReport()
            ->formatStats();

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
                //added to help us generate next day's report
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

    public static function generate(Practice $practice, Carbon $date)
    {
        return (new static($practice, $date))->dailyReportRow();
    }

    public function setTimeGoal()
    {
        $timeGoal = DB::table('report_settings')->where('name', 'time_goal_per_billable_patient')->first();

        $this->timeGoal = $timeGoal
            ? $timeGoal->value
            : '35';

        return true;
    }

    /**
     * Loop through all the patients once and calculate:
     * Their Ccm time category
     * Status changes from revisions
     * Sum up their total time.
     *
     * @return self
     */
    private function generateStatsFromPatientCollection()
    {
        //revisions
        $totalCcmTime = [];
        $paused       = [];
        $withdrawn    = [];
        $enrolled     = [];
        $unreachable  = [];
        $to_enroll    = [];

        foreach ($this->patients as $patient) {
            if (Patient::ENROLLED == $patient->patientInfo->ccm_status) {
                $this->enrolledPatients[] = $patient;
                $pms                      = $patient->patientSummaries->first();
                if ($pms) {
                    $bhiTime        = $pms->bhi_time;
                    $totalCcmTime[] = $ccmTime = $pms->ccm_time;

                    if (0 === $ccmTime || null == $ccmTime) {
                        ++$this->stats['0 mins'];
                    }
                    if ($ccmTime > 0 and $ccmTime <= 300) {
                        ++$this->stats['0-5'];
                    }
                    if ($ccmTime > 300 and $ccmTime <= 600) {
                        ++$this->stats['5-10'];
                    }
                    if ($ccmTime > 600 and $ccmTime <= 900) {
                        ++$this->stats['10-15'];
                    }
                    if ($ccmTime > 900 and $ccmTime <= $this::TWENTY_MINUTES) {
                        ++$this->stats['15-20'];
                    }
                    if ($ccmTime > $this::TWENTY_MINUTES) {
                        ++$this->stats['20+'];
                    }
                    if ($bhiTime > $this::TWENTY_MINUTES) {
                        ++$this->stats['20+ BHI'];
                    }
                } else {
                    if (Patient::ENROLLED == $patient->patientInfo->ccm_status) {
                        ++$this->stats['0 mins'];
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

        $this->stats = array_merge($this->stats, [
            'total_ccm_time'        => array_sum($totalCcmTime),
            'revisions_enrolled'    => $enrolled,
            'revisions_paused'      => $paused,
            'revisions_withdrawn'   => $withdrawn,
            'revisions_unreachable' => $unreachable,
            'to_enroll'             => $to_enroll,
        ]);

        return $this;
    }
    
    private function consolidateStatsUsingPriorDayReport(){
        return $this;
    }

    private function getPatients()
    {
        $this->patients = $this->practice->patients->unique('id');

        return $this;
    }
    
    private function formatStats(){
    
    }
}
