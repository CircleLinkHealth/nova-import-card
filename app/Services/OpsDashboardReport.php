<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\ValueObjects\OpsDashboardPracticeReportData;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\OpsDashboardPracticeReport;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\DB;

class OpsDashboardReport
{
    const DEFAULT_TIME_GOAL = '35';

    const FIFTEEN_MINUTES = 900;

    const FIVE_MINUTES = 300;

    const MIN_CALL = 1;

    const TEN_MINUTES = 600;

    const TWENTY_MINUTES = 1200;

    /**
     * @var Carbon
     */
    protected $date;
    /**
     * @var array
     */
    protected $enrolledPatients = [];
    /**
     * @var array
     */
    protected $g0506Patients = [];
    /**
     * @var
     */
    protected $patients;
    /**
     * @var array
     */
    protected $pausedPatients = [];
    /**
     * @var Practice
     */
    protected $practice;

    /**
     * @var array
     */
    protected $priorDayReportData = [];

    /**
     * @var OpsDashboardPracticeReportData
     */
    protected $report;
    /**
     * @var array
     */
    protected $revisionsAddedPatients = [];
    /**
     * @var array
     */
    protected $revisionsPausedPatients = [];
    /**
     * @var array
     */
    protected $revisionsUnreachablePatients = [];
    /**
     * @var array
     */
    protected $revisionsWithdrawnPatients = [];

    /**
     * @var array
     */
    protected $unreachablePatients = [];
    /**
     * @var array
     */
    protected $withdrawnPatients = [];

    /**
     * OpsDashboardReport constructor.
     */
    public function __construct(Practice $practice, Carbon $date)
    {
        $this->practice = $practice;
        $this->date     = $date;
        $this->report   = new OpsDashboardPracticeReportData();
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
    public static function calculateHoursBehind(Carbon $date, int $totalNumberOfEnrolledPatients, int $totalPatientCcmTime)
    {
        $totActPt                = $totalNumberOfEnrolledPatients;
        $targetMinutesPerPatient = floatval(self::getTimeGoal());

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

    public static function generate(Practice $practice, Carbon $date): array
    {
        return (new static($practice, $date))->getReport();
    }

    public function getReport(): array
    {
        return $this->getPatients()
            ->getPriorDayReportData()
            ->generateStatsFromPatientCollection()
            ->addCurrentTotalsToStats()
            ->consolidateStatsUsingPriorDayReport()
            ->formatStats();
    }

    /**
     * @return bool
     */
    public static function getTimeGoal()
    {
        $timeGoal = DB::table('report_settings')->where('name', 'time_goal_per_billable_patient')->first();

        return $timeGoal
            ? $timeGoal->value
            : self::DEFAULT_TIME_GOAL;
    }

    /**
     * @return $this
     */
    private function addCurrentTotalsToStats()
    {
        $this->report->setTotalPausedCount(count($this->pausedPatients));
        $this->report->setTotalWithdrawnCount(count($this->withdrawnPatients));
        $this->report->setTotalUnreachableCount(count($this->unreachablePatients));

        //Add enrolled patient ids to stats so that they can be used the next day, to help us calculate delta
        $enrolledIds = collect($this->enrolledPatients)->pluck('id')->filter()->toArray();
        $this->report->setEnrolledPatientIds($enrolledIds);

        return $this;
    }

    /**
     * @param bool $patientWasEnrolledPriorDay
     */
    private function categorizePatientByStatusUsingPatientInfo(User $patient, $patientWasEnrolledPriorDay = false)
    {
        $ccmStatus = $patient->patientInfo->ccm_status;
        if (Patient::TO_ENROLL == $ccmStatus) {
            $this->g0506Patients[] = $patient;
        }
        if (Patient::PAUSED == $ccmStatus) {
            $this->pausedPatients[] = $patient;
            if ($patientWasEnrolledPriorDay) {
                $this->report->incrementPaused();
            }
        }
        if (in_array($ccmStatus, [Patient::WITHDRAWN, Patient::WITHDRAWN_1ST_CALL])) {
            $this->withdrawnPatients[] = $patient;
            if ($patientWasEnrolledPriorDay) {
                $this->report->incrementWithdrawn();
            }
        }
        if (Patient::UNREACHABLE == $ccmStatus) {
            $this->unreachablePatients[] = $patient;
            if ($patientWasEnrolledPriorDay) {
                $this->report->incrementUnreachable();
            }
        }
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

    /**
     * @return $this
     */
    private function consolidateStatsUsingPriorDayReport()
    {
        $countRevisionsAdded       = count($this->revisionsAddedPatients);
        $countRevisionsPaused      = count($this->revisionsPausedPatients);
        $countRevisionsWithdrawn   = count($this->revisionsWithdrawnPatients);
        $countRevisionsUnreachable = count($this->revisionsUnreachablePatients);

        if ($this->shouldCalculateStatsUsingRevisionsOnly()) {
            $this->report->setAdded($countRevisionsAdded);
            $this->report->setPaused($countRevisionsPaused);
            $this->report->setWithdrawn($countRevisionsWithdrawn);
            $this->report->setUnreachable($countRevisionsUnreachable);
            $this->report->setTotal(count($this->enrolledPatients));

            return $this;
        }

        $this->report->setPriorDayTotals($this->priorDayReportData['total_enrolled_count']);

        if ($this->report->getTotal() - $this->report->getDelta() !== $this->report->getPriorDayTotals()) {
            sendSlackMessage('#ops_dashboard_alers', "<?U8B3S8UBS> Warning! DELTA for Ops dashboard report for {$this->date->toDateString()} and Practice '{$this->practice->display_name}' does not match.");
        }

        //check each status and send slack message with ids if you should.
        //use whereNotIn? and filterout ids to include in slack message
        //if prior day data exist, numbers should have been processed by now. Check if they match
        if ($this->report->getAdded() != $countRevisionsAdded) {
            //ops dashboard watcher
            //slack revision patient ids. Enrolled patient IDs exist in db ops_dashboard_practice_reports
            $revisionIds = collect($this->revisionsAddedPatients)->pluck('id')->implode(',');
            sendSlackMessage('#ops_dashboard_alers', "<?U8B3S8UBS> Warning! Added Patients for Ops dashboard report for {$this->date->toDateString()} and Practice '{$this->practice->display_name}' do not match.
            Totals using status: {$this->report->getAdded()} - Totals using revisions: $countRevisionsAdded. Revisionable IDs: $revisionIds");
        }

        if ($this->report->getPaused() != $countRevisionsPaused) {
            //ops dashboard watcher
            //slack revision patient ids. Enrolled patient IDs exist in db ops_dashboard_practice_reports
            $revisionIds = collect($this->revisionsPausedPatients)->pluck('id')->implode(',');
            sendSlackMessage('#ops_dashboard_alers', "<?U8B3S8UBS> Warning! Paused Patients for Ops dashboard report for {$this->date->toDateString()} do not match.
            Totals using status: {$this->report->getPaused()} - Totals using revisions: $countRevisionsPaused. Revisionable IDs: $revisionIds");
        }

        if ($this->report->getWithdrawn() != $countRevisionsWithdrawn) {
            //ops dashboard watcher
            //slack revision patient ids. Enrolled patient IDs exist in db ops_dashboard_practice_reports
            $revisionIds = collect($this->revisionsWithdrawnPatients)->pluck('id')->implode(',');
            sendSlackMessage('#ops_dashboard_alers', "<?U8B3S8UBS> Warning! Withdrawn Patients for Ops dashboard report for {$this->date->toDateString()} do not match.
            Totals using status: {$this->report->getWithdrawn()} - Totals using revisions: $countRevisionsWithdrawn. Revisionable IDs: $revisionIds");
        }

        if ($this->report->getUnreachable() != $countRevisionsUnreachable) {
            //ops dashboard watcher
            //slack revision patient ids. Enrolled patient IDs exist in db ops_dashboard_practice_reports
            $revisionIds = collect($this->revisionsUnreachablePatients)->pluck('id')->implode(',');
            sendSlackMessage('#ops_dashboard_alers', "<?U8B3S8UBS> Warning! Unreachable Patients for Ops dashboard report for {$this->date->toDateString()} do not match.
            Totals using status: {$this->report->getUnreachable()} - Totals using revisions: $countRevisionsUnreachable. Revisionable IDs: $revisionIds");
        }

        return $this;
    }

    private function formatStats(): array
    {
        //CHECK DATA INTEGRITY
//        if (0 == $count['Total'] &&
//            $count['Total'] - $delta == 0 &&
//            0 == $enrolledCount &&
//            0 == $pausedCount &&
//            0 == $withdrawnCount &&
//            0 == $unreachableCount) {
//            return null;
//        }
        return $this->report->toArray();
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
            $patientWasEnrolledPriorDay = $this->patientWasEnrolledPriorDay($patient->id);
            if (Patient::ENROLLED == $patient->patientInfo->ccm_status) {
                $this->report->incrementTotal();
                if ( ! $patientWasEnrolledPriorDay) {
                    $this->report->incrementAdded();
                }

                $this->enrolledPatients[] = $patient;
                $pms                      = $patient->patientSummaries->first();
                if ($pms) {
                    $totalCcmTime[] = $pms->ccm_time;
                    $this->incrementTimeRangeCount($pms);
                } else {
                    $this->report->incrementZeroMins();
                }
            }
            $this->categorizePatientByStatusUsingRevisions($patient);
            //categorize so we can count for totals as well. Obviously we could have used collection->whereStatus on patient collection
            //but since we are looping the patient collection here, categorize so we can count to help with performance
            $this->categorizePatientByStatusUsingPatientInfo($patient, $patientWasEnrolledPriorDay);
        }

        $this->report->setTotalCcmTime(array_sum($totalCcmTime));

        return $this;
    }

    /**
     * @return $this
     */
    private function getPatients()
    {
        $this->patients = $this->practice->patients->unique('id');

        return $this;
    }

    /**
     * @return $this
     */
    private function getPriorDayReportData()
    {
        $priorDayReport = OpsDashboardPracticeReport::where('practice_id', $this->practice->id)
            ->where('date', $this->date->copy()->subDay()->toDateString())
            ->whereNotNull('data')
            ->where('is_processed', 1)
            ->first();

        if ($priorDayReport) {
            $this->priorDayReportData = $priorDayReport->data;
            $this->report->setPriorDayReportUpdatedAt($priorDayReport->updated_at);
        }

        return $this;
    }

    private function incrementTimeRangeCount(PatientMonthlySummary $pms)
    {
        $ccmTime = $pms->ccm_time;
        $bhiTime = $pms->bhi_time;

        if (0 === $ccmTime || null == $ccmTime) {
            $this->report->incrementZeroMins();
        }
        if ($ccmTime > 0 and $ccmTime < self::FIVE_MINUTES) {
            $this->report->incrementZeroToFiveMins();
        }
        if ($ccmTime >= self::FIVE_MINUTES and $ccmTime < self::TEN_MINUTES) {
            $this->report->incrementFiveToTenMins();
        }
        if ($ccmTime >= self::TEN_MINUTES and $ccmTime < self::FIFTEEN_MINUTES) {
            $this->report->incrementTenToFifteenMins();
        }
        if ($ccmTime >= self::FIFTEEN_MINUTES and $ccmTime < $this::TWENTY_MINUTES) {
            $this->report->incrementFifteenToTwentyMins();
        }
        if ($ccmTime >= $this::TWENTY_MINUTES) {
            $this->report->incrementTwentyPlusMins();
        }
        if ($bhiTime >= $this::TWENTY_MINUTES) {
            $this->report->incrementTwentyPlusBhiMins();
        }
    }

    /**
     * @param $patientId
     * @return bool
     */
    private function patientWasEnrolledPriorDay($patientId)
    {
        if (array_key_exists('enrolled_patient_ids', $this->priorDayReportData)) {
            return in_array($patientId, $this->priorDayReportData['enrolled_patient_ids']);
        }

        return false;
    }

    /**
     *  If last day report does not exist or does not contain new keys to calculate DELTA
     * Use revisions (old way) - This should only run on deployment date, or for the first ever generated report for a practice.
     *
     * @return bool
     */
    private function shouldCalculateStatsUsingRevisionsOnly()
    {
        return ! array_keys_exist([
            'total_enrolled_count',
            'total_paused_count',
            'total_unreachable_count',
            'total_withdrawn_count',
            'total_g0506_to_enroll_count',
            'prior_day_report_updated_at',
            'report_updated_at',
            'enrolled_patient_ids',
        ], $this->priorDayReportData);
    }
}
