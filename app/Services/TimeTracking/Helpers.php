<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 21/10/16
 * Time: 12:41 AM
 */

namespace App\Services\TimeTracking;

use App\PageTimer;
use Carbon\Carbon;

trait Helpers
{
    public function overlapAllTheThings(
        PageTimer &$secondary,
        PageTimer &$newActivity,
        PageTimer &$greedy,
        Carbon &$greedyStart,
        Carbon &$greedyEnd,
        Carbon &$secondaryStart,
        Carbon &$secondaryEnd,
        Carbon &$minDate,
        Carbon &$maxDate
    ) {
        if ($secondaryStart->lt($minDate)) {
            $durationBeforeOverlap = $secondaryStart->diffInSeconds($minDate);
            $minDate = $secondaryStart->copy();
        } elseif ($secondaryStart == $minDate) {
            $durationBeforeOverlap = $secondaryStart->diffInSeconds($greedyStart);
        } else {
            $durationBeforeOverlap = 0;
        }

        if ($secondaryEnd->gt($maxDate)) {
            $durationAfterOverlap = $maxDate->diffInSeconds($secondaryEnd);

            $maxDate = $secondaryEnd->copy();
        } elseif ($secondaryEnd == $maxDate) {
            $durationAfterOverlap = $greedyEnd->diffInSeconds($secondaryEnd);
        } else {
            $durationAfterOverlap = 0;
        }

        $secondary->billable_duration = $durationBeforeOverlap + $durationAfterOverlap;

        if ($secondary->billable_duration == 0) {
            $secondary->start_time = '0000-00-00 00:00:00';
            $secondary->end_time = '0000-00-00 00:00:00';
            $secondary->save();
            $greedy->billable_duration = $greedyStart->diffInSeconds($greedyEnd);
        } else {
            if ($durationBeforeOverlap == 0) {
                $secondary->start_time = $maxDate->toDateTimeString();
                $secondary->end_time = $maxDate->addSeconds($secondary->billable_duration)->toDateTimeString();
                $secondary->save();
                $greedy->billable_duration = $greedyStart->diffInSeconds($greedyEnd);
            } elseif ($durationAfterOverlap == 0) {
                $secondary->start_time = $minDate->toDateTimeString();
                $secondary->end_time = $minDate->addSeconds($secondary->billable_duration)->toDateTimeString();
                $secondary->save();
                $greedy->billable_duration = $greedyStart->diffInSeconds($greedyEnd);
            } else {
                //then it means there's overlap on both sides
                $secondary->start_time = $minDate->toDateTimeString();
                $maxDate = $minDate->copy()->addSeconds($secondary->billable_duration);
                $secondary->end_time = $maxDate->toDateTimeString();
                $secondary->save();

                $greedy->billable_duration = $greedyStart->diffInSeconds($greedyEnd);
                $greedyStart = $maxDate->copy();
                $greedy->start_time = $greedyStart->toDateTimeString();
            }
        }
    }
}
