<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use App\Reports\OpsDashboardPracticeReportData;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\OpsDashboardPracticeReport;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Support\Facades\DB;

class OpsDashboardReport
{
    const DEFAULT_TIME_GOAL = '35';

    protected $calculateLostAddedUsingRevisionsOnly;

    /**
     * @var Carbon
     */
    protected $date;
    /**
     * @var
     */
    protected $patients;
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
            ->setPriorDayReportData()
            ->countDeletedPatients()
            ->generateStatsFromPatientCollection()
            ->consolidateStatsUsingPriorDayReport();
    }

    /**
     * @return bool
     */
    public static function getTimeGoal()
    {
        return \Cache::remember('time_goal_per_billable_patient', 2, function () {
            $timeGoal = DB::table('report_settings')->where('name', 'time_goal_per_billable_patient')->first();

            return $timeGoal
                ? $timeGoal->value
                : self::DEFAULT_TIME_GOAL;
        });
    }

    /**
     * @param bool $patientWasEnrolledPriorDay
     */
    private function categorizePatientByStatusUsingPatientInfo(User $patient, $patientWasEnrolledPriorDay = false)
    {
        $ccmStatus = $patient->patientInfo->ccm_status;
        $patientId = $patient->id;

        switch ($ccmStatus) {
            case Patient::TO_ENROLL:
                $this->report->g0506ToEnrollIds[] = $patientId;
                break;
            case Patient::PAUSED:
                $this->report->pausedIds[] = $patientId;
                if ($patientWasEnrolledPriorDay) {
                    $this->report->incrementPausedCount();
                }
                break;
            case in_array($ccmStatus, [Patient::WITHDRAWN, Patient::WITHDRAWN_1ST_CALL]):
                $this->report->withdrawnIds[] = $patientId;
                if ($patientWasEnrolledPriorDay) {
                    $this->report->incrementWithdrawnCount();
                }
                break;
            case Patient::UNREACHABLE:
                $this->report->unreachableIds[] = $patientId;
                if ($patientWasEnrolledPriorDay) {
                    $this->report->incrementUnreachableCount();
                }
                break;
        }
    }

    private function categorizePatientByStatusUsingRevisions($patient)
    {
        $revisionHistory = $patient->patientInfo->revisionHistory->sortByDesc('created_at');

        if ($revisionHistory->isEmpty()) {
            return;
        }

        $oldestStatus = $revisionHistory->last()->old_value;
        $newestStatus = $revisionHistory->first()->new_value;
        $patientId    = $patient->id;
        if (Patient::ENROLLED == $oldestStatus) {
            if (Patient::UNREACHABLE == $newestStatus) {
                $this->report->revisionsUnreachableIds[] = $patientId;
                $this->report->incrementRevisionsUnreachableCount();
            } elseif (Patient::PAUSED == $newestStatus) {
                $this->report->revisionsPausedIds[] = $patientId;
                $this->report->incrementRevisionsPausedCount();
            } elseif (in_array($newestStatus, [Patient::WITHDRAWN, Patient::WITHDRAWN_1ST_CALL])) {
                $this->report->revisionsWithdrawnIds[] = $patient;
                $this->report->incrementRevisionsWithdrawnCount();
            }
        } elseif (Patient::ENROLLED !== $oldestStatus &&
                Patient::ENROLLED == $newestStatus) {
            $this->report->incrementRevisionsAddedCount();
            $this->report->revisionsAddedIds[] = $patientId;
        }
    }

    private function consolidateStatsUsingPriorDayReport(): array
    {
        if ($this->shouldCalculateLostAddedUsingRevisionsOnly()) {
            $this->report->setDeltasUsingRevisionCounts();

            return $this->report->toArray();
        }

        $alerts = [];
        if ( ! $this->report->totalsAreMatching()) {
            $alerts[] = 'Totals are not matching.';
        }
        //if prior day data exist, numbers should have been processed by now. Check if they match
        if ( ! $this->report->addedCountIsMatching()) {
            $addedRevisionIds = implode(',', $this->report->revisionsAddedIds);
            $alerts[]         = "Added: Total using status: {$this->report->addedCount} - Totals using revisions: {$this->report->revisionsAddedCount}. Revisionable User IDs: $addedRevisionIds.";
        }

        if ( ! $this->report->pausedCountIsMatching()) {
            $pausedRevisionIds = implode(',', $this->report->revisionsPausedIds);
            $alerts[]          = "Paused: Total using status: {$this->report->pausedCount} - Totals using revisions: {$this->report->revisionsPausedCount}. Revisionable User IDs: $pausedRevisionIds.";
        }

        if ($this->report->withdrawnCountIsMatching()) {
            $withdrawnRevisionIds = implode(',', $this->report->revisionsPausedIds);
            $alerts[]             = "Withdrawn: Total using status: {$this->report->withdrawnCount} - Totals using revisions: {$this->report->revisionsWithdrawnCount}}. Revisionable User IDs: $withdrawnRevisionIds.";
        }

        if ($this->report->unreachableCountIsMatching()) {
            $unreachableRevisionIds = implode(',', $this->report->revisionsUnreachableIds);
            $alerts[]               = "Unreachable: Total using status: {$this->report->unreachableCount} - Totals using revisions: {$this->report->revisionsUnreachableCount}}. Revisionable User IDs: $unreachableRevisionIds.";
        }

        if ( ! empty($alerts)) {
            $watchers = opsDashboardAlertWatchers();
            $message  = "$watchers Warning! The following discrepancies were found for Ops dashboard report for {$this->date->toDateString()} and Practice '{$this->practice->display_name}'. \n".implode("\n", $alerts);
            sendSlackMessage('#ops_dashboard_alers', $message);
        }

        return $this->report->toArray();
    }

    private function countDeletedPatients()
    {
        if (isset($this->priorDayReportData['enrolled_patient_ids'])) {
            $deletedIds = User::onlyTrashed()
                ->ofPractice($this->practice->id)
                ->where([
                    ['deleted_at', '>=', $this->date->copy()->subDay()],
                    ['deleted_at', '<=', $this->date],
                ])
                ->whereIn('id', $this->priorDayReportData['enrolled_patient_ids'])
                ->pluck('id')
                ->toArray();

            $this->report->deletedCount = count($deletedIds);
            $this->report->deletedIds[] = $deletedIds;
        }

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
        foreach ($this->patients as $patient) {
            $patientId                  = $patient->id;
            $patientWasEnrolledPriorDay = $this->patientWasEnrolledPriorDay($patientId);

            if (Patient::ENROLLED == $patient->patientInfo->ccm_status) {
                $this->report->incrementTotalCount();
                $this->report->enrolledPatientIds[] = $patientId;

                if ( ! $patientWasEnrolledPriorDay) {
                    $this->report->addedIds[] = $patientId;
                    $this->report->incrementAddedCount();
                }

                $pms = $patient->patientSummaries->first();
                if ($pms) {
                    $totalCcmTime[] = $pms->ccm_time;
                    $this->report->incrementTimeRangeCount($pms);
                } else {
                    $this->report->incrementZeroMinsCount();
                }
            }
            $this->categorizePatientByStatusUsingRevisions($patient);
            //categorize so we can count for totals as well. Obviously we could have used collection->whereStatus on patient collection
            //but since we are looping the patient collection here, categorize so we can count to help with performance
            $this->categorizePatientByStatusUsingPatientInfo($patient, $patientWasEnrolledPriorDay);
        }

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
     * @param $patientId
     *
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
     * @return $this
     */
    private function setPriorDayReportData()
    {
        $priorDayReport = OpsDashboardPracticeReport::where('practice_id', $this->practice->id)
            ->where('date', $this->date->copy()->subDay()->toDateString())
            ->whereNotNull('data')
            ->where('is_processed', 1)
            ->first();

        if ($priorDayReport) {
            $this->priorDayReportData              = $priorDayReport->data;
            $this->report->priorDayReportUpdatedAt = $priorDayReport->updated_at->toDateTimeString();
            $this->report->priorDayTotals          = $this->priorDayReportData['Total'];
        }

        return $this;
    }

    /**
     *  If last day report does not exist or does not contain new keys to calculate DELTA
     * Use revisions (old way) - This should only run on deployment date, or for the first ever generated report for a practice.
     */
    private function shouldCalculateLostAddedUsingRevisionsOnly(): bool
    {
        if (null === $this->calculateLostAddedUsingRevisionsOnly) {
            $this->calculateLostAddedUsingRevisionsOnly = ! array_keys_exist([
                'total_paused_count',
                'total_unreachable_count',
                'total_withdrawn_count',
                'prior_day_report_updated_at',
                'report_updated_at',
                'enrolled_patient_ids',
            ], $this->priorDayReportData);
        }

        return $this->calculateLostAddedUsingRevisionsOnly;
    }
}
