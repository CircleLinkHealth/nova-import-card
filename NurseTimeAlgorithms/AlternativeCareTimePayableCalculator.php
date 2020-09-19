<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\NurseTimeAlgorithms;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\NurseMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\SharedModels\Entities\Note;

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
     */
    public function __construct(Nurse $nurse)
    {
        $this->nurse = $nurse;
    }

    public function adjustNursePayForActivity(Activity $activity)
    {
        $user = $activity->patient;
        if ( ! $user) {
            //in case the patient was deleted
            $user = User::withTrashed()
                ->with('chargeableServices')
                ->findOrFail($activity->patient_id);
        } else {
            $user->loadMissing('chargeableServices');
        }

        $isActivityForSuccessfulCall = $this->isActivityForSuccessfulCall($activity);

        $monthYear = Carbon::parse($activity->performed_at)->startOfMonth();

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

        $this->updateNurseCareLogs(
            $total_time_before,
            $total_time_after,
            $activity->duration,
            $activity->id,
            $isActivityForSuccessfulCall,
            $user,
            $monthYear,
            $activity->is_behavioral,
            Carbon::parse($activity->performed_at)
        );
    }

    private function calculateTimeRanges(
        int $total_time_before,
        int $total_time_after,
        int $duration
    ) {
        $ranges = [];

        $add_to_accrued_towards_ccm = 0;
        $add_to_accrued_after_ccm   = 0;

        $was_above_20 = $total_time_before >= self::MONTHLY_TIME_TARGET_IN_SECONDS;
        $is_above_20  = $total_time_after >= self::MONTHLY_TIME_TARGET_IN_SECONDS;

        if ($was_above_20) {
            $add_to_accrued_after_ccm = $duration;
        } elseif ($is_above_20) {
            $add_to_accrued_after_ccm   = $total_time_after - self::MONTHLY_TIME_TARGET_IN_SECONDS;
            $add_to_accrued_towards_ccm = self::MONTHLY_TIME_TARGET_IN_SECONDS - $total_time_before;
        } else {
            $add_to_accrued_towards_ccm = $duration;
        }

        if ($add_to_accrued_towards_ccm) {
            $ranges[] = [
                'duration'          => $add_to_accrued_towards_ccm,
                'key'               => 'accrued_towards_ccm',
                'total_time_before' => $total_time_before,
            ];
        }

        if ($add_to_accrued_after_ccm) {
            $ranges[] = [
                'duration'          => $add_to_accrued_after_ccm,
                'key'               => 'accrued_after_ccm',
                'total_time_before' => $total_time_before + $add_to_accrued_towards_ccm,
            ];
        }

        return $ranges;
    }

    /**
     * Would like to move this method into Activity model,
     * but I would have to require Note and Call models which are in cpm-web app from time-tracking module.
     * This introduces a cyclic dependency from cpm-web to time-tracking and time-tracking to cpm-web.
     */
    private function isActivityForSuccessfulCall(Activity $activity): bool
    {
        if ( ! in_array($activity->type, ['Patient Note Creation', 'Patient Note Edit'])) {
            return false;
        }

        $performedAt = Carbon::parse($activity->performed_at);
        $noteIds     = Note
            ::whereBetween('performed_at', [
                $performedAt->copy()->startOfDay(),
                $performedAt->copy()->endOfDay(),
            ])
                ->where('status', '=', Note::STATUS_COMPLETE)
                ->where('author_id', '=', $activity->logger_id)
                ->where('patient_id', '=', $activity->patient_id)
                ->pluck('id');

        $hasSuccessfulCall = false;
        if ($noteIds->isNotEmpty()) {
            $hasSuccessfulCall = Call::whereIn('note_id', $noteIds)
                ->where('status', '=', Call::REACHED)
                ->count() > 0;
        }

        return $hasSuccessfulCall;
    }

    private function updateNurseCareLogs(
        int $total_time_before,
        int $total_time_after,
        int $duration,
        int $activityId,
        bool $isActivityForSuccessfulCall,
        \CircleLinkHealth\Customer\Entities\User $patient,
        Carbon $monthYear,
        bool $isBehavioral,
        Carbon $time
    ) {
        $ranges = $this->calculateTimeRanges(
            $total_time_before,
            $total_time_after,
            $duration
        );

        $add_to_accrued_towards = 0;
        $add_to_accrued_after   = 0;

        foreach ($ranges as $item) {
            $ccmType    = $item['key'];
            $duration   = $item['duration'];
            $timeBefore = $item['total_time_before'];

            NurseCareRateLog::create(
                [
                    'nurse_id'           => $this->nurse->id,
                    'patient_user_id'    => $patient->id,
                    'activity_id'        => $activityId,
                    'ccm_type'           => $ccmType,
                    'increment'          => $duration,
                    'time_before'        => $timeBefore,
                    'is_successful_call' => $isActivityForSuccessfulCall,
                    'is_behavioral'      => $isBehavioral,
                    'performed_at'       => $time,
                ]
            );

            if ('accrued_towards_ccm' === $ccmType) {
                $add_to_accrued_towards += $duration;
            } else {
                $add_to_accrued_after += $duration;
            }
        }

        $this->updateNurseSummary(
            $add_to_accrued_towards,
            $add_to_accrued_after,
            $monthYear
        );
    }

    private function updateNurseSummary(
        int $toAddToAccruedTowardsCCM,
        int $toAddToAccruedAfterCCM,
        Carbon $monthYear
    ): NurseMonthlySummary {
        $report = NurseMonthlySummary::firstOrNew(
            [
                'nurse_id'   => $this->nurse->id,
                'month_year' => $monthYear,
            ]
        );

        $report->accrued_after_ccm   += $toAddToAccruedAfterCCM;
        $report->accrued_towards_ccm += $toAddToAccruedTowardsCCM;
        $report->save();

        return $report;
    }
}
