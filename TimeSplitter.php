<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Nurseinvoices;

class TimeSplitter
{
    public function split(int $totalTimeBefore, $duration, $isBehavioral, $isPcm)
    {
        $totalTimeAfter = $totalTimeBefore + $duration;

        //ccm + bhi
        $add_to_accrued_towards_20 = 0;
        $add_to_accrued_after_20   = 0;
        $add_to_accrued_after_40   = 0;
        $add_to_accrued_after_60   = 0;

        //pcm
        $add_to_accrued_towards_30 = 0;
        $add_to_accrued_after_30   = 0;

        $was_above_20 = $totalTimeBefore >= VariablePayCalculator::MONTHLY_TIME_TARGET_IN_SECONDS;
        $was_above_30 = $totalTimeBefore >= VariablePayCalculator::MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM;
        $was_above_40 = $totalTimeBefore >= VariablePayCalculator::MONTHLY_TIME_TARGET_2X_IN_SECONDS;
        $was_above_60 = $totalTimeBefore >= VariablePayCalculator::MONTHLY_TIME_TARGET_3X_IN_SECONDS;

        $is_above_20 = $totalTimeAfter >= VariablePayCalculator::MONTHLY_TIME_TARGET_IN_SECONDS;
        $is_above_30 = $totalTimeAfter >= VariablePayCalculator::MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM;
        $is_above_40 = $totalTimeAfter >= VariablePayCalculator::MONTHLY_TIME_TARGET_2X_IN_SECONDS;
        $is_above_60 = $totalTimeAfter >= VariablePayCalculator::MONTHLY_TIME_TARGET_3X_IN_SECONDS;

        //ccm + bhi
        if ($was_above_60) {
            $add_to_accrued_after_60 = $duration;
        } elseif ($was_above_40) {
            if ($is_above_60) {
                $add_to_accrued_after_60 = $totalTimeAfter - VariablePayCalculator::MONTHLY_TIME_TARGET_3X_IN_SECONDS;
                $add_to_accrued_after_40 = VariablePayCalculator::MONTHLY_TIME_TARGET_3X_IN_SECONDS - $totalTimeBefore;
            } else {
                $add_to_accrued_after_40 = $duration;
            }
        } elseif ($was_above_20) {
            if ($is_above_40) {
                $add_to_accrued_after_40 = $totalTimeAfter - VariablePayCalculator::MONTHLY_TIME_TARGET_2X_IN_SECONDS;
                if ($add_to_accrued_after_40 > VariablePayCalculator::MONTHLY_TIME_TARGET_IN_SECONDS) {
                    $add_to_accrued_after_60 = $add_to_accrued_after_40 - VariablePayCalculator::MONTHLY_TIME_TARGET_IN_SECONDS;
                    $add_to_accrued_after_40 = VariablePayCalculator::MONTHLY_TIME_TARGET_IN_SECONDS;
                }
                $add_to_accrued_after_20 = VariablePayCalculator::MONTHLY_TIME_TARGET_2X_IN_SECONDS - $totalTimeBefore;
            } else {
                $add_to_accrued_after_20 = $duration;
            }
        } else {
            if ($is_above_20) {
                $add_to_accrued_after_20 = $totalTimeAfter - VariablePayCalculator::MONTHLY_TIME_TARGET_IN_SECONDS;
                if ($add_to_accrued_after_20 > VariablePayCalculator::MONTHLY_TIME_TARGET_IN_SECONDS) {
                    $add_to_accrued_after_40 = $add_to_accrued_after_20 - VariablePayCalculator::MONTHLY_TIME_TARGET_IN_SECONDS;
                    if ($add_to_accrued_after_40 > VariablePayCalculator::MONTHLY_TIME_TARGET_IN_SECONDS) {
                        $add_to_accrued_after_60 = $add_to_accrued_after_40 - VariablePayCalculator::MONTHLY_TIME_TARGET_IN_SECONDS;
                        $add_to_accrued_after_40 = VariablePayCalculator::MONTHLY_TIME_TARGET_IN_SECONDS;
                    }
                    $add_to_accrued_after_20 = VariablePayCalculator::MONTHLY_TIME_TARGET_IN_SECONDS;
                }
                $add_to_accrued_towards_20 = VariablePayCalculator::MONTHLY_TIME_TARGET_IN_SECONDS - $totalTimeBefore;
            } else {
                $add_to_accrued_towards_20 = $duration;
            }
        }

        if ( ! $isBehavioral && $isPcm) {
            if ($was_above_30) {
                $add_to_accrued_after_30 = $duration;
            } else {
                if ($is_above_30) {
                    $add_to_accrued_after_30   = $totalTimeAfter - VariablePayCalculator::MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM;
                    $add_to_accrued_towards_30 = VariablePayCalculator::MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM - $totalTimeBefore;
                } else {
                    $add_to_accrued_towards_30 = $duration;
                }
            }
        }

        $result            = new TimeSlots();
        $result->towards20 = $add_to_accrued_towards_20;
        $result->towards30 = $add_to_accrued_towards_30;
        $result->after20   = $add_to_accrued_after_20;
        $result->after30   = $add_to_accrued_after_30;
        $result->after40   = $add_to_accrued_after_40;
        $result->after60   = $add_to_accrued_after_60;

        return $result;
    }
}
