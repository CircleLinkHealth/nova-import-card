<?php namespace App\Services\TimeTracking;

use App\PageTimer;
use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 17/10/16
 * Time: 11:14 AM
 */
class Service
{
    public function isCcmActivity(PageTimer $activity) : bool
    {
        return !($activity->patient_id == 0 || $activity->title == 'patient.activity.create' || $activity->title == 'patient.activity.create');
    }

    public function figureOutOverlaps(PageTimer $newActivity, PageTimer $overlappingActivity)
    {
        if ($this->isCcmActivity($overlappingActivity)) {
            $greedy = $overlappingActivity;
            $secondary = $newActivity;
        } else {
            $greedy = $newActivity;
            $secondary = $overlappingActivity;
        }

        $greedyStart = Carbon::createFromFormat('Y-m-d H:i:s', $greedy->start_time);
        $greedyEnd = Carbon::createFromFormat('Y-m-d H:i:s', $greedy->end_time);

        $secondaryStart = Carbon::createFromFormat('Y-m-d H:i:s', $secondary->start_time);
        $secondaryEnd = Carbon::createFromFormat('Y-m-d H:i:s', $secondary->end_time);

        if ($greedyStart->gte($secondaryStart) && $greedyEnd->gte($secondaryEnd)) {
            $greedy->billable_duration = $greedyStart->diffInSeconds($greedyEnd);

            //adjust secondary activity
            $secondaryDuration = $secondaryStart->diffInSeconds($greedyStart);
            $secondary->billable_duration = $secondaryDuration;
            $secondary->end_time = $secondaryStart->addSeconds($secondaryDuration)->toDateTimeString();
            $secondary->save();
        }
        elseif ($greedyStart->lte($secondaryStart) && $greedyEnd->gte($secondaryEnd)) {
            $greedy->billable_duration = $secondaryStart->diffInSeconds($secondaryEnd);

            //adjust secondary activity
            $secondary->billable_duration = 0;
            $secondary->end_time = $secondary->start_time;
            $secondary->save();
        }
        elseif ($greedyStart->gte($secondaryStart) && $greedyEnd->lte($secondaryEnd)) {
            $durationBeforeOverlap = $secondaryStart->diffInSeconds($greedyStart);
            $durationAfterOverlap = $greedyEnd->diffInSeconds($secondaryEnd);

            //adjust secondary activity
            $secondary->billable_duration = $durationBeforeOverlap + $durationAfterOverlap;

            $secondaryEnd = $secondaryStart->addSeconds($secondary->billable_duration)->copy();
            $secondary->end_time = $secondaryEnd->toDateTimeString();
            $secondary->save();

            $greedy->billable_duration = $greedyStart->diffInSeconds($greedyEnd);

            $greedyStart = $secondaryEnd->copy();
            $greedy->start_time = $greedyStart->toDateTimeString();
        }
        elseif ($greedyStart->lte($secondaryStart) && $greedyEnd->lte($secondaryEnd)) {
            $durationOverlap = $secondaryStart->diffInSeconds($greedyEnd);

            $secondaryStart = $greedyEnd->copy();

            $secondary->start_time = $secondaryStart->toDateTimeString();
            $secondary->billable_duration = $secondaryStart->diffInSeconds($secondaryEnd);
            $secondary->save();

            $greedy->billable_duration = $greedyStart->diffInSeconds($greedyEnd);
        }

        //adjust greedy activity
        $greedy->end_time = $greedyStart->addSeconds($greedy->billable_duration)->toDateTimeString();
        $greedy->save();

    }
}