<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Invoicing;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\NurseMonthlySummary;
use CircleLinkHealth\TimeTracking\Entities\Activity;

/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 1/12/17
 * Time: 1:25 PM.
 */
class AlternativeCareTimePayableCalculator
{
    const MONTHLY_TIME_TARGET_IN_SECONDS = 1200;

    protected $nurse;

    /**
     * AlternativeCareTimePayableCalculator constructor.
     *
     * @param Nurse $nurse
     */
    public function __construct(Nurse $nurse)
    {
        $this->nurse = $nurse;
    }

    public function adjustNursePayForActivity(Activity $activity)
    {
        $add_to_accrued_towards = 0;
        $add_to_accrued_after   = 0;
        $user                   = $activity->patient;
        $monthYear              = Carbon::parse($activity->performed_at)->startOfMonth();

        $summary = $user->patientSummaries()
            ->whereMonthYear($monthYear)
            ->first();

        $totalTime = $activity->is_behavioral
            ? $summary->bhi_time
            : $summary->ccm_time;

        //total time after storing activity
        $total_time_after = intval($totalTime);

        //total time before storing activity
        $total_time_before = $total_time_after - $activity->duration;

        //patient was above target before storing activity
        $was_above = $total_time_before >= self::MONTHLY_TIME_TARGET_IN_SECONDS;

        //patient was under target before storing activity
        $was_under = $total_time_before < self::MONTHLY_TIME_TARGET_IN_SECONDS;

        //patient went above target after activity
        $is_above = $total_time_after >= self::MONTHLY_TIME_TARGET_IN_SECONDS;

        if ($was_above) {
            $add_to_accrued_after = $activity->duration;
        } elseif ($was_under) {
            if ($is_above) {
                $add_to_accrued_after   = $total_time_after - self::MONTHLY_TIME_TARGET_IN_SECONDS;
                $add_to_accrued_towards = self::MONTHLY_TIME_TARGET_IN_SECONDS - $total_time_before;
            } else {
                $add_to_accrued_towards = $activity->duration;
            }
        }

        $this->createOrIncrementNurseSummary(
            $add_to_accrued_towards,
            $add_to_accrued_after,
            $activity->id,
            $monthYear
        );

        return [
            'toAddToAccuredTowardsCCM' => $add_to_accrued_towards,
            'toAddToAccuredAfterCCM'   => $add_to_accrued_after,
            'activity_id'              => $activity->id,
        ];
    }

    /**
     * @param int    $toAddToAccruedTowardsCCM
     * @param int    $toAddToAccruedAfterCCM
     * @param int    $activityId
     * @param Carbon $monthYear
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    private function createOrIncrementNurseSummary(// note, not storing call data for now.
        int $toAddToAccruedTowardsCCM,
        int $toAddToAccruedAfterCCM,
        int $activityId,
        Carbon $monthYear
    ) {
        $report = NurseMonthlySummary::firstOrNew(
            [
                'nurse_id'   => $this->nurse->id,
                'month_year' => $monthYear,
            ]
        );

        $report->accrued_after_ccm   += $toAddToAccruedAfterCCM;
        $report->accrued_towards_ccm += $toAddToAccruedTowardsCCM;
        $report->save();

        if ($toAddToAccruedAfterCCM > 0) {
            NurseCareRateLog::create(
                [
                    'nurse_id'    => $this->nurse->id,
                    'activity_id' => $activityId,
                    'ccm_type'    => 'accrued_after_ccm',
                    'increment'   => $toAddToAccruedAfterCCM,
                ]
            );
        }

        if ($toAddToAccruedTowardsCCM > 0) {
            NurseCareRateLog::create(
                [
                    'nurse_id'    => $this->nurse->id,
                    'activity_id' => $activityId,
                    'ccm_type'    => 'accrued_towards_ccm',
                    'increment'   => $toAddToAccruedTowardsCCM,
                ]
            );
        }

        return $report;
    }
}
