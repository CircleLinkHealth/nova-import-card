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
    use Helpers;

    public function figureOutOverlaps(
        PageTimer $newActivity,
        Collection $overlappingActivities
    ) {
        $minDate = Carbon::createFromFormat('Y-m-d H:i:s', $newActivity->start_time)->copy();
        $maxDate = Carbon::createFromFormat('Y-m-d H:i:s', $newActivity->end_time)->copy();

        foreach ($overlappingActivities as $overlap) {
            if ($this->isCcmActivity($newActivity)
                || (!$this->isCcmActivity($newActivity)
                    && !$this->isCcmActivity($overlap))
            ) {
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
                    $secondary->start_time = '0000-00-00 00:00:00';
                    $secondary->end_time = '0000-00-00 00:00:00';
                    $secondary->save();
                }
                //if the secondary start is the minDate, we want to get $secondaryStart->diffInSeconds($greedyStart)
                // we are assuming that only the $secondary activity has this start date
                elseif ($minDate == $secondaryStart) {

                    $secondaryDuration = $secondaryStart->diffInSeconds($greedyStart);
                    $secondary->billable_duration = $secondaryDuration;
                    $secondary->end_time = $secondaryStart->addSeconds($secondaryDuration)->toDateTimeString();
                    $secondary->save();

                    $greedy->billable_duration = $greedyStart->diffInSeconds($greedyEnd);

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

                $this->overlapAllTheThings(
                    $secondary,
                    $newActivity,
                    $greedy,
                    $greedyStart,
                    $greedyEnd,
                    $secondaryStart,
                    $secondaryEnd,
                    $minDate,
                    $maxDate
                );

            } elseif ($greedyStart->gte($secondaryStart) && $greedyEnd->lte($secondaryEnd)) {
                $this->overlapAllTheThings(
                    $secondary,
                    $newActivity,
                    $greedy,
                    $greedyStart,
                    $greedyEnd,
                    $secondaryStart,
                    $secondaryEnd,
                    $minDate,
                    $maxDate
                );

            } elseif ($greedyStart->lte($secondaryStart) && $greedyEnd->lte($secondaryEnd)) {
                if ($greedyStart->lt($minDate)) {
                    $minDate = $greedyStart->copy();
                }

                if ($secondaryEnd->gte($maxDate)) {
                    $maxDate = $secondaryEnd->copy();
                    $secondaryStart = $greedyEnd->copy();
                    $secondary->start_time = $secondaryStart->toDateTimeString();
                    $secondary->billable_duration = $secondaryStart->diffInSeconds($secondaryEnd);

                } else {
                    $secondary->billable_duration = 0;
                    $secondary->start_time = '0000-00-00 00:00:00';
                    $secondary->end_time = '0000-00-00 00:00:00';
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