<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\NurseTimeAlgorithms;

use Carbon\Carbon;
use CircleLinkHealth\CcmBilling\Domain\Patient\PatientMonthlyServiceTime;
use CircleLinkHealth\CcmBilling\Entities\BillingConstants;
use CircleLinkHealth\Customer\CpmConstants;
use CircleLinkHealth\Customer\Entities\NurseCareRateLog;
use CircleLinkHealth\Customer\Entities\NurseMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Activity;
use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\SharedModels\Entities\Note;
use CircleLinkHealth\TimeTracking\Services\ActivityService;
use Facades\FriendsOfCat\LaravelFeatureFlags\Feature;
use Illuminate\Support\Facades\Log;

/**
 * Created by PhpStorm.
 * User: RohanM
 * Date: 1/12/17
 * Time: 1:25 PM.
 */
class AlternativeCareTimePayableCalculator
{
    /**
     * AlternativeCareTimePayableCalculator constructor.
     */
    public function __construct()
    {
    }

    public function adjustNursePayForActivity($nurseId, Activity $activity)
    {
        $user = $activity->patient;
        if ( ! $user) {
            $user = User::withTrashed()->findOrFail($activity->patient_id);
        }

        $monthYear = Carbon::parse($activity->performed_at)->startOfMonth();

        $totalTimeAfter  = $this->getCurrentTotalTime($activity, $user, $monthYear);
        $totalTimeBefore = $totalTimeAfter - $activity->duration;

        $this->updateNurseCareLogs(
            $nurseId,
            $totalTimeBefore,
            $totalTimeAfter,
            $user,
            $monthYear,
            $activity
        );
    }

    private function calculateTimeRanges(
        int $totalTimeBefore,
        int $totalTimeAfter,
        int $duration
    ) {
        $ranges = [];

        $add_to_accrued_towards_ccm = 0;
        $add_to_accrued_after_ccm   = 0;

        $was_above_20 = $totalTimeBefore >= CpmConstants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS;
        $is_above_20  = $totalTimeAfter >= CpmConstants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS;

        if ($was_above_20) {
            $add_to_accrued_after_ccm = $duration;
        } elseif ($is_above_20) {
            $add_to_accrued_after_ccm   = $totalTimeAfter - CpmConstants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS;
            $add_to_accrued_towards_ccm = CpmConstants::MONTHLY_BILLABLE_TIME_TARGET_IN_SECONDS - $totalTimeBefore;
        } else {
            $add_to_accrued_towards_ccm = $duration;
        }

        if ($add_to_accrued_towards_ccm) {
            $ranges[] = [
                'duration'          => $add_to_accrued_towards_ccm,
                'key'               => 'accrued_towards_ccm',
                'total_time_before' => $totalTimeBefore,
            ];
        }

        if ($add_to_accrued_after_ccm) {
            $ranges[] = [
                'duration'          => $add_to_accrued_after_ccm,
                'key'               => 'accrued_after_ccm',
                'total_time_before' => $totalTimeBefore + $add_to_accrued_towards_ccm,
            ];
        }

        return $ranges;
    }

    private function getCurrentTotalTime(Activity $activity, User $user, Carbon $monthYear)
    {
        if ( ! $activity->chargeable_service_id) {
            Log::critical("Activity[$activity->id] does not have a chargeable service id");

            return 0;
        }

        if (Feature::isEnabled(BillingConstants::BILLING_REVAMP_FLAG)) {
            return PatientMonthlyServiceTime::forChargeableServiceId($activity->chargeable_service_id, $user->id, $monthYear);
        }

        return app(ActivityService::class)->totalTimeForChargeableServiceId($user->id, $activity->chargeable_service_id, $monthYear);
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
        int $nurseId,
        int $totalTimeBefore,
        int $totalTimeAfter,
        User $patient,
        Carbon $monthYear,
        Activity $activity
    ) {
        $ranges = $this->calculateTimeRanges(
            $totalTimeBefore,
            $totalTimeAfter,
            $activity->duration
        );

        $addToAccruedTowards = 0;
        $addToAccruedAfter   = 0;

        foreach ($ranges as $item) {
            $ccmType    = $item['key'];
            $duration   = $item['duration'];
            $timeBefore = $item['total_time_before'];

            NurseCareRateLog::create(
                [
                    'nurse_id'              => $nurseId,
                    'patient_user_id'       => $patient->id,
                    'activity_id'           => $activity->id,
                    'ccm_type'              => $ccmType,
                    'increment'             => $duration,
                    'time_before'           => $timeBefore,
                    'is_successful_call'    => $this->isActivityForSuccessfulCall($activity),
                    'is_behavioral'         => $activity->is_behavioral,
                    'chargeable_service_id' => $activity->chargeable_service_id,
                    'performed_at'          => Carbon::parse($activity->performed_at),
                ]
            );

            if ('accrued_towards_ccm' === $ccmType) {
                $addToAccruedTowards += $duration;
            } else {
                $addToAccruedAfter += $duration;
            }
        }

        $this->updateNurseSummary(
            $nurseId,
            $addToAccruedTowards,
            $addToAccruedAfter,
            $monthYear
        );
    }

    private function updateNurseSummary(
        int $nurseId,
        int $toAddToAccruedTowardsCCM,
        int $toAddToAccruedAfterCCM,
        Carbon $monthYear
    ): NurseMonthlySummary {
        $report = NurseMonthlySummary::firstOrNew(
            [
                'nurse_id'   => $nurseId,
                'month_year' => $monthYear,
            ]
        );

        $report->accrued_after_ccm   += $toAddToAccruedAfterCCM;
        $report->accrued_towards_ccm += $toAddToAccruedTowardsCCM;
        $report->save();

        return $report;
    }
}
