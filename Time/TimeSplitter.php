<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Time;

use CircleLinkHealth\NurseInvoices\Algorithms\NursePaymentAlgorithm;
use CircleLinkHealth\NurseInvoices\ValueObjects\TimeSlots;

class TimeSplitter
{
    public function split(
        int $totalTimeBefore,
        int $duration,
        bool $splitFor30Minutes = false,
        bool $upTo60Minutes = false
    ): TimeSlots {
        if ($splitFor30Minutes) {
            return $this->splitFor30MinuteIntervals($totalTimeBefore, $duration);
        }

        return $this->splitFor20MinuteIntervals($totalTimeBefore, $duration, $upTo60Minutes);
    }

    private function splitFor20MinuteIntervals(
        int $totalTimeBefore,
        int $duration,
        bool $upTo60Minutes = false
    ): TimeSlots {
        $totalTimeAfter = $totalTimeBefore + $duration;

        $add_to_accrued_towards_20 = 0;
        $add_to_accrued_after_20   = 0;
        $add_to_accrued_after_40   = 0;
        $add_to_accrued_after_60   = 0;

        $was_above_20 = $totalTimeBefore >= NursePaymentAlgorithm::MONTHLY_TIME_TARGET_IN_SECONDS;
        $was_above_40 = $totalTimeBefore >= NursePaymentAlgorithm::MONTHLY_TIME_TARGET_2X_IN_SECONDS;
        $was_above_60 = $totalTimeBefore >= NursePaymentAlgorithm::MONTHLY_TIME_TARGET_3X_IN_SECONDS;

        $is_above_20 = $totalTimeAfter >= NursePaymentAlgorithm::MONTHLY_TIME_TARGET_IN_SECONDS;
        $is_above_40 = $totalTimeAfter >= NursePaymentAlgorithm::MONTHLY_TIME_TARGET_2X_IN_SECONDS;
        $is_above_60 = $totalTimeAfter >= NursePaymentAlgorithm::MONTHLY_TIME_TARGET_3X_IN_SECONDS;

        //ccm + bhi
        if ($was_above_60) {
            $add_to_accrued_after_60 = $duration;
        } elseif ($was_above_40) {
            if ($is_above_60) {
                $add_to_accrued_after_60 = $totalTimeAfter - NursePaymentAlgorithm::MONTHLY_TIME_TARGET_3X_IN_SECONDS;
                $add_to_accrued_after_40 = NursePaymentAlgorithm::MONTHLY_TIME_TARGET_3X_IN_SECONDS - $totalTimeBefore;
            } else {
                $add_to_accrued_after_40 = $duration;
            }
        } elseif ($was_above_20) {
            if ($is_above_40) {
                $add_to_accrued_after_40 = $totalTimeAfter - NursePaymentAlgorithm::MONTHLY_TIME_TARGET_2X_IN_SECONDS;
                if ($add_to_accrued_after_40 > NursePaymentAlgorithm::MONTHLY_TIME_TARGET_IN_SECONDS) {
                    $add_to_accrued_after_60 = $add_to_accrued_after_40 - NursePaymentAlgorithm::MONTHLY_TIME_TARGET_IN_SECONDS;
                    $add_to_accrued_after_40 = NursePaymentAlgorithm::MONTHLY_TIME_TARGET_IN_SECONDS;
                }
                $add_to_accrued_after_20 = NursePaymentAlgorithm::MONTHLY_TIME_TARGET_2X_IN_SECONDS - $totalTimeBefore;
            } else {
                $add_to_accrued_after_20 = $duration;
            }
        } else {
            if ($is_above_20) {
                $add_to_accrued_after_20 = $totalTimeAfter - NursePaymentAlgorithm::MONTHLY_TIME_TARGET_IN_SECONDS;
                if ($add_to_accrued_after_20 > NursePaymentAlgorithm::MONTHLY_TIME_TARGET_IN_SECONDS) {
                    $add_to_accrued_after_40 = $add_to_accrued_after_20 - NursePaymentAlgorithm::MONTHLY_TIME_TARGET_IN_SECONDS;
                    if ($add_to_accrued_after_40 > NursePaymentAlgorithm::MONTHLY_TIME_TARGET_IN_SECONDS) {
                        $add_to_accrued_after_60 = $add_to_accrued_after_40 - NursePaymentAlgorithm::MONTHLY_TIME_TARGET_IN_SECONDS;
                        $add_to_accrued_after_40 = NursePaymentAlgorithm::MONTHLY_TIME_TARGET_IN_SECONDS;
                    }
                    $add_to_accrued_after_20 = NursePaymentAlgorithm::MONTHLY_TIME_TARGET_IN_SECONDS;
                }
                $add_to_accrued_towards_20 = NursePaymentAlgorithm::MONTHLY_TIME_TARGET_IN_SECONDS - $totalTimeBefore;
            } else {
                $add_to_accrued_towards_20 = $duration;
            }
        }

        $result            = new TimeSlots();
        $result->towards20 = $add_to_accrued_towards_20;
        $result->after20   = $add_to_accrued_after_20;

        if ($upTo60Minutes) {
            $current         = $is_above_60 ? 'after_60' : ($is_above_40 ? 'after_40' : ($is_above_20 ? 'after_20' : 'towards_20'));
            $result->after40 = $add_to_accrued_after_40;
            $result->after60 = $add_to_accrued_after_60;
        } else {
            $current = $is_above_20 ? 'after_20' : 'towards_20';
            $result->after20 += $add_to_accrued_after_40 + $add_to_accrued_after_60;
        }

        $result->current = $current;

        return $result;
    }

    private function splitFor30MinuteIntervals(int $totalTimeBefore, $duration): TimeSlots
    {
        $totalTimeAfter = $totalTimeBefore + $duration;

        $add_to_accrued_towards_30 = 0;
        $add_to_accrued_after_30   = 0;
        $was_above_30              = $totalTimeBefore >= NursePaymentAlgorithm::MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM;
        $is_above_30               = $totalTimeAfter >= NursePaymentAlgorithm::MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM;

        if ($was_above_30) {
            $add_to_accrued_after_30 = $duration;
        } else {
            if ($is_above_30) {
                $add_to_accrued_after_30   = $totalTimeAfter - NursePaymentAlgorithm::MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM;
                $add_to_accrued_towards_30 = NursePaymentAlgorithm::MONTHLY_TIME_TARGET_IN_SECONDS_FOR_PCM - $totalTimeBefore;
            } else {
                $add_to_accrued_towards_30 = $duration;
            }
        }

        $result            = new TimeSlots();
        $result->towards30 = $add_to_accrued_towards_30;
        $result->after30   = $add_to_accrued_after_30;
        $result->current   = $is_above_30 ? 'after_30' : 'towards_30';

        return $result;
    }
}
