<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Algorithms\Invoicing;

use CircleLinkHealth\TimeTracking\Entities\Activity;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\NurseMonthlySummary;
use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 1/12/17
 * Time: 1:25 PM.
 */
class AlternativeCareTimePayableCalculator
{
    protected $month;
    protected $nurse;
    protected $nurseReport;

    public function __construct(Nurse $nurse)
    {
        $this->nurse = $nurse;

        $this->month = Carbon::parse(Carbon::now()->firstOfMonth()->format('Y-m-d'));
        $report      = NurseMonthlySummary::where('nurse_id', $this->nurse->id)->where('month_year', $this->month)->first();

        $this->nurseReport = $report;
    }

    public function adjustNursePayForActivity(Activity $activity)
    {
        $toAddToAccuredTowardsCCM = 0;
        $toAddToAccuredAfterCCM   = 0;
        $user                     = $activity->patient;
        $monthYear                = Carbon::parse($activity->performed_at)->firstOfMonth();

        $summary = $user->patientSummaries()
            ->whereMonthYear($monthYear)
            ->first();

        $patient = $user->patientInfo;

        $totalTime = $activity->is_behavioral
            ? $summary->bhi_time
            : $summary->ccm_time;

        $ccm_after_activity = intval($totalTime);

        $ccm_before_activity = $ccm_after_activity - $activity->duration;

        //logic switches:

        //read as: if ccm before was over 20...
        //20mins
        $ccm_before_over_20  = $ccm_before_activity >= 1200;
        $ccm_before_under_20 = $ccm_before_activity < 1200;
        $ccm_after_over_20   = $ccm_after_activity >= 1200;
        $ccm_after_under_20  = $ccm_after_activity < 1200;

        //60mins
        $ccm_before_under_60 = $ccm_before_activity < 3600;
        $ccm_before_over_60  = $ccm_before_activity >= 3600;
        $ccm_after_under_60  = $ccm_after_activity < 3600;
        $ccm_after_over_60   = $ccm_after_activity >= 3600;

        //90mins
        $ccm_before_under_90 = $ccm_before_activity < 5400;
        $ccm_before_over_90  = $ccm_before_activity >= 5400;
        $ccm_after_under_90  = $ccm_after_activity < 5400;
        $ccm_after_over_90   = $ccm_after_activity >= 5400;

        //120mins
        $ccm_before_under_120 = $ccm_before_activity < 7200;
        $ccm_before_over_120  = $ccm_before_activity >= 7200;
        $ccm_after_under_120  = $ccm_after_activity < 7200;
        $ccm_after_over_120   = $ccm_after_activity >= 7200;
        
            if ($ccm_before_over_20) { //if patient was already over 20 mins.
                // before: 1200, add: 200, total: 1400; target: 1200
                // towards: 0, after: 200

                $toAddToAccuredAfterCCM = $activity->duration;
            } elseif ($ccm_before_under_20) { //if patient hasn't met 20mins
                if ($ccm_after_over_20) { //patient reached 20 mins with this activity
                    // before: 600, add: 720, total: 1320; target: 1200
                    // towards: 600, after: 120

                    $toAddToAccuredAfterCCM   = $ccm_after_activity - 1200;
                    $toAddToAccuredTowardsCCM = 1200 - $ccm_before_activity;
                } else {//patient is still under 20mins
                    // before: 200, add: 200, total: 400; target: 1200
                    // towards: 200, after: 0

                    $toAddToAccuredTowardsCCM = $activity->duration;
                }
            }

        $this->createOrIncrementNurseSummary($toAddToAccuredTowardsCCM, $toAddToAccuredAfterCCM, $activity->id);

        return [
            'toAddToAccuredTowardsCCM' => $toAddToAccuredTowardsCCM,
            'toAddToAccuredAfterCCM'   => $toAddToAccuredAfterCCM,
            'activity_id'              => $activity->id,
        ];
    }

    public function createOrIncrementNurseSummary(// note, not storing call data for now.
        $toAddToAccruedTowardsCCM,
        $toAddToAccruedAfterCCM,
        $activityId
    ) {
        if ($this->nurseReport) {
            $this->nurseReport->accrued_after_ccm   = $toAddToAccruedAfterCCM + $this->nurseReport->accrued_after_ccm;
            $this->nurseReport->accrued_towards_ccm = $toAddToAccruedTowardsCCM + $this->nurseReport->accrued_towards_ccm;
        } else {
            $this->nurseReport = NurseMonthlySummary::create([
                'nurse_id'               => $this->nurse->id,
                'month_year'             => $this->month,
                'accrued_after_ccm'      => $toAddToAccruedAfterCCM,
                'accrued_towards_ccm'    => $toAddToAccruedTowardsCCM,
                'no_of_calls'            => 0,
                'no_of_successful_calls' => 0,
            ]);
        }

        if (0 != $toAddToAccruedAfterCCM) {
            NurseCareRateLog::create([
                'nurse_id'    => $this->nurse->id,
                'activity_id' => $activityId,
                'ccm_type'    => 'accrued_after_ccm',
                'increment'   => $toAddToAccruedAfterCCM,
            ]);
        }

        if (0 != $toAddToAccruedTowardsCCM) {
            NurseCareRateLog::create([
                'nurse_id'    => $this->nurse->id,
                'activity_id' => $activityId,
                'ccm_type'    => 'accrued_towards_ccm',
                'increment'   => $toAddToAccruedTowardsCCM,
            ]);
        }

        $this->nurseReport->save();

        return $this->nurseReport;
    }
}
