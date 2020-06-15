<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\OpsDashboardPracticeReport;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\DB;

class OpsDashboardReport
{
    const MIN_CALL       = 1;
    const TWENTY_MINUTES = 1200;

    protected $date;
    protected $enrolledPatients = [];
    protected $g0506Patients    = [];
    protected $patients;
    protected $pausedPatients = [];
    protected $practice;
    protected $revisionsAddedPatients       = [];
    protected $revisionsPausedPatients      = [];
    protected $revisionsUnreachablePatients = [];
    protected $revisionsWithdrawnPatients   = [];

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
    protected $unreachablePatients = [];
    protected $withdrawnPatients   = [];

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
            ->addCurrentTotalsToStats()
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
                'prior_day_report_updated_at' => null,
                //will be added outside this method - when db entry will be created
                'report_updated_at' => null,
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

    private function addCurrentTotalsToStats()
    {
        $this->stats['total_enrolled_count']    = count($this->enrolledPatients);
        $this->stats['total_paused_count']      = count($this->pausedPatients);
        $this->stats['total_withdrawn_count']   = count($this->withdrawnPatients);
        $this->stats['total_unreachable_count'] = count($this->unreachablePatients);

        return $this;
    }

    private function categorizePatientByStatusUsingPatientInfo(User $patient)
    {
        $ccmStatus = $patient->patientInfo->ccm_status;
        if (Patient::TO_ENROLL == $ccmStatus) {
            $this->g0506Patients[] = $patient;
        }
        if (Patient::PAUSED == $ccmStatus) {
            $this->pausedPatients[] = $patient;
        }
        if (in_array($ccmStatus, [Patient::WITHDRAWN, Patient::WITHDRAWN_1ST_CALL])) {
            $this->withdrawnPatients[] = $patient;
        }
        if (Patient::UNREACHABLE == $ccmStatus) {
            $this->unreachablePatients = $patient;
        }

        //enrolled are added in parent function
    }

    private function categorizePatientByStatusUsingRevisions(User $patient)
    {
        $revisionHistory = $patient->patientInfo->revisionHistory->sortByDesc('created_at');

        if ($revisionHistory->isNotEmpty()) {
            if (Patient::ENROLLED == $revisionHistory->last()->old_value) {
                if (Patient::UNREACHABLE == $revisionHistory->first()->new_value) {
                    $this->revisionsUnreachablePatients[] = $patient;
                }
                if (Patient::PAUSED == $revisionHistory->first()->new_value) {
                    $this->revisionsPausedPatients[] = $patient;
                }
                if (in_array($revisionHistory->first()->new_value, [Patient::WITHDRAWN, Patient::WITHDRAWN_1ST_CALL])) {
                    $this->revisionsWithdrawnPatients[] = $patient;
                }
            }
            if (Patient::ENROLLED !== $revisionHistory->last()->old_value &&
                Patient::ENROLLED == $revisionHistory->first()->new_value) {
                $this->revisionsAddedPatients[] = $patient;
            }
        }
    }

    private function consolidateStatsUsingPriorDayReport()
    {
        $countRevisionsAdded       = count($this->revisionsAddedPatients);
        $countRevisionsPaused      = count($this->revisionsPausedPatients);
        $countRevisionsWithdrawn   = count($this->revisionsWithdrawnPatients);
        $countRevisionsUnreachable = count($this->revisionsUnreachablePatients);

        $priorDayReport = OpsDashboardPracticeReport::where('practice_id', $this->practice->id)
            ->where('date', $this->date->copy()->subDay(1))
            ->whereNotNull('data')
            ->where('is_processed', 1)
            ->first();

        //if no prior day report OR
        //if new keys do not exist in yesterday's report, generate deltas from revisions
        //todo: make sure to manual test
        if ( ! $priorDayReport || ! array_keys_exist([
            'total_enrolled_count' => 0,
            'total_paused_count' => 0,
            'total_unreachable_count' => 0,
            'total_withdrawn_count' => 0,
            'total_g0506_to_enroll_count' => 0,
            'prior_day_report_updated_at' => 0,
            'report_updated_at' => 0,
        ], $priorDayReport->data)) {
            $this->stats['Added']            = $countRevisionsAdded;
            $this->stats['Paused']           = $countRevisionsPaused;
            $this->stats['Withdrawn']        = $countRevisionsWithdrawn;
            $this->stats['Unreachable']      = $countRevisionsUnreachable;
            $this->stats['Total']            = $this->stats['total_enrolled_count'];
            $this->stats['Prior Day totals'] = $this->calculateDelta(
                $this->stats['Total'],
                $this->stats['Paused'],
                $this->stats['Withdrawn'],
                $this->stats['Unreachable']
            );

            return $this;
        }

        $newPatientsAddedCount = $this->stats['total_enrolled_count'] - $priorDayReport->data['total_enrolled_count'];

        //check each status and send slack message with ids if you should.
        //use whereNotIn? and filterout ids to include in slack message

        return $this;
    }

    private function formatStats()
    {
        return $this;
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
        $totalCcmTime = [];

        foreach ($this->patients as $patient) {
            if (Patient::ENROLLED == $patient->patientInfo->ccm_status) {
                $this->enrolledPatients[] = $patient;
                $pms                      = $patient->patientSummaries->first();
                if ($pms) {
                    $totalCcmTime[] = $pms->ccm_time;
                    $this->incrementTimeRangeCount($pms);
                } else {
                    ++$this->stats['0 mins'];
                }
            }
            $this->categorizePatientByStatusUsingRevisions($patient);
            $this->categorizePatientByStatusUsingPatientInfo($patient);
        }

        $this->stats['total_ccm_time'] = array_sum($totalCcmTime);

        return $this;
    }

    private function getPatients()
    {
        $this->patients = $this->practice->patients->unique('id');

        return $this;
    }

    private function incrementTimeRangeCount(PatientMonthlySummary $pms)
    {
        $ccmTime = $pms->ccm_time;
        $bhiTime = $pms->bhi_time;

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
    }
}
