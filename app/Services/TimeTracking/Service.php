<?php namespace App\Services\TimeTracking;

use App\PageTimer;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 17/10/16
 * Time: 11:14 AM
 */
class Service
{
    public function figureOutOverlaps(
        PageTimer $newActivity,
        Collection $overlappingActivities
    ) {
        $minDate = Carbon::createFromFormat('Y-m-d H:i:s', $newActivity->start_time);
        $maxDate = Carbon::createFromFormat('Y-m-d H:i:s', $newActivity->end_time);

        foreach ($overlappingActivities as $overlap) {
            if ($this->isCcmActivity($newActivity)) {
                $greedy = $newActivity;
                $secondary = $overlap;
            } else {
                $greedy = $overlap;
                $secondary = $newActivity;
            }

            $greedyStart = Carbon::createFromFormat('Y-m-d H:i:s', $greedy->start_time);
            $greedyEnd = Carbon::createFromFormat('Y-m-d H:i:s', $greedy->end_time);

            $secondaryStart = Carbon::createFromFormat('Y-m-d H:i:s', $secondary->start_time);
            $secondaryEnd = Carbon::createFromFormat('Y-m-d H:i:s', $secondary->end_time);

            if ($greedyStart->gte($secondaryStart) && $greedyEnd->gte($secondaryEnd)) {

                if ($secondaryStart->gte($minDate)) {
                    $secondary->billable_duration = 0;
                    $secondary->start_time = $newActivity->start_time;
                    $secondary->end_time = $newActivity->start_time;
                    $secondary->save();
                } else {
                    $secondaryDuration = $secondaryStart->diffInSeconds($minDate);
                    $minDate = $secondaryStart->copy();
                    $secondary->billable_duration = $secondaryDuration;
                    $secondary->end_time = $secondaryStart->addSeconds($secondaryDuration)->toDateTimeString();
                    $secondary->save();

                    $greedy->billable_duration = $greedyStart->diffInSeconds($greedyEnd);

                }

                if ($greedyEnd->gt($maxDate)) {
                    $maxDate = $greedyEnd->copy();
                }

            } elseif ($greedyStart->lte($secondaryStart) && $greedyEnd->gte($secondaryEnd)) {

                if ($greedyStart->lt($minDate)) {
                    $minDate = $greedyStart->copy();
                }
                if ($greedyEnd->gt($maxDate)) {
                    $maxDate = $greedyEnd->copy();
                }

                $greedy->billable_duration = $greedyStart->diffInSeconds($greedyEnd);

                //adjust secondary activity
                $secondary->billable_duration = 0;
                $secondary->start_time = $newActivity->start_time;
                $secondary->end_time = $newActivity->start_time;
                $secondary->save();
            } elseif ($greedyStart->gte($secondaryStart) && $greedyEnd->lte($secondaryEnd)) {

                if ($secondaryStart->lt($minDate)) {
                    $durationBeforeOverlap = $secondaryStart->diffInSeconds($minDate);

                    $minDate = $secondaryStart->copy();
                } else {
                    $durationBeforeOverlap = 0;
                }

                if ($secondaryEnd->gt($maxDate)) {
                    $durationAfterOverlap = $maxDate->diffInSeconds($secondaryEnd);

                    $maxDate = $secondaryEnd->copy();
                } else {
                    $durationAfterOverlap = 0;
                }

                $secondary->billable_duration = $durationBeforeOverlap + $durationAfterOverlap;

                if ($secondary->billable_duration == 0) {
                    $secondary->start_time = $newActivity->start_time;
                    $secondary->end_time = $newActivity->start_time;
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
                        $maxDate = $minDate->addSeconds($secondary->billable_duration)->copy();
                        $secondary->end_time = $maxDate->toDateTimeString();
                        $secondary->save();

                        $greedy->billable_duration = $greedyStart->diffInSeconds($greedyEnd);
                        $greedyStart = $maxDate->copy();
                        $greedy->start_time = $greedyStart->toDateTimeString();
                    }
                }
            } elseif ($greedyStart->lte($secondaryStart) && $greedyEnd->lte($secondaryEnd)) {

                if ($greedyStart->lt($minDate)) {
                    $minDate = $greedyStart->copy();
                }

                if ($secondaryEnd->gt($maxDate)) {
                    $maxDate = $secondaryEnd->copy();
                }

                $secondaryStart = $maxDate->copy();
                $secondary->start_time = $secondaryStart->toDateTimeString();

                if ($secondaryEnd->gt($maxDate)) {
                    $secondary->billable_duration = $secondaryStart->diffInSeconds($secondaryEnd);
                } else {
                    $secondary->billable_duration = 0;
                    $secondary->start_time = $newActivity->start_time;
                    $secondary->end_time = $newActivity->start_time;
                }
                $secondary->save();

                $greedy->billable_duration = $greedyStart->diffInSeconds($greedyEnd);
            }

            //adjust greedy activity
            $greedy->end_time = $greedyStart->addSeconds($greedy->billable_duration)->toDateTimeString();
            $greedy->save();
        }

    }

    public function isCcmActivity(PageTimer $activity) : bool
    {
        return !($activity->patient_id == 0
            || $activity->title == 'patient.activity.create'
            || $activity->title == 'patient.activity.providerUIIndex');
    }
}