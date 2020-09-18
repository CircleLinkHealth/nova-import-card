<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\TimeTracking;

use Carbon\Carbon;
use CircleLinkHealth\SharedModels\Entities\PageTimer;
use Illuminate\Support\Collection;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 17/10/16
 * Time: 11:14 AM.
 */
class Service
{
    use Helpers;

    public function figureOutOverlaps(
        PageTimer $newActivity,
        Collection $overlappingActivities
    ) {
        $overlapMin = $overlappingActivities->min('start_time');
        $overlapMax = $overlappingActivities->max('end_time');

        $minDate = Carbon::createFromFormat('Y-m-d H:i:s', min($newActivity->start_time, $overlapMin))->copy();
        $maxDate = Carbon::createFromFormat('Y-m-d H:i:s', max($newActivity->end_time, $overlapMax))->copy();

        foreach ($overlappingActivities as $overlap) {
            if ($this->isCcmActivity($newActivity)
                || ( ! $this->isCcmActivity($newActivity)
                    && ! $this->isCcmActivity($overlap))
            ) {
                $greedy    = $newActivity;
                $secondary = $overlap;
            } else {
                $greedy    = $overlap;
                $secondary = $newActivity;
            }

            $greedyStart = Carbon::createFromFormat('Y-m-d H:i:s', $greedy->start_time);
            $greedyEnd   = Carbon::createFromFormat('Y-m-d H:i:s', $greedy->end_time);

            $secondaryStart = Carbon::createFromFormat('Y-m-d H:i:s', $secondary->start_time);
            $secondaryEnd   = Carbon::createFromFormat('Y-m-d H:i:s', $secondary->end_time);

            if ($greedyStart->gte($secondaryStart) && $greedyEnd->gte($secondaryEnd)) {
                if ($secondaryStart->gt($minDate)) {
                    $secondary->billable_duration = 0;
                    $secondary->start_time        = '0000-00-00 00:00:00';
                    $secondary->end_time          = '0000-00-00 00:00:00';
                    $secondary->save();
                } //if the secondary start is the minDate, we want to get $secondaryStart->diffInSeconds($greedyStart)
                // we are assuming that only the $secondary activity has this start date
                elseif ($minDate == $secondaryStart) {
                    $secondaryDuration            = $secondaryStart->diffInSeconds($greedyStart);
                    $secondary->billable_duration = $secondaryDuration;
                    $secondary->end_time          = $secondaryStart->addSeconds($secondaryDuration)->toDateTimeString();
                    $secondary->save();

                    $greedy->billable_duration = $greedyStart->diffInSeconds($greedyEnd);
                } else {
                    $secondaryDuration            = $secondaryStart->diffInSeconds($minDate);
                    $minDate                      = $secondaryStart->copy();
                    $secondary->billable_duration = $secondaryDuration;
                    $secondary->end_time          = $secondaryStart->addSeconds($secondaryDuration)->toDateTimeString();
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
                    $maxDate                      = $secondaryEnd->copy();
                    $secondaryStart               = $greedyEnd->copy();
                    $secondary->start_time        = $secondaryStart->toDateTimeString();
                    $secondary->billable_duration = $secondaryStart->diffInSeconds($secondaryEnd);
                } elseif ($secondaryStart->lte($minDate)) {
                    $minDate                      = $secondaryStart->copy();
                    $secondary->billable_duration = $secondaryStart->diffInSeconds($greedyStart);
                } else {
                    $secondary->billable_duration = 0;
                    $secondary->start_time        = '0000-00-00 00:00:00';
                    $secondary->end_time          = '0000-00-00 00:00:00';
                }

                $secondary->save();

                $greedy->billable_duration = $greedyStart->diffInSeconds($greedyEnd);
            }

            //adjust greedy activity
            $greedy->end_time = $greedyStart->copy()->addSeconds($greedy->billable_duration)->toDateTimeString();
            $greedy->save();

            //If new activity is null, it means it was completely overlapped by another activity.
            //We want to stop at this point to avoid getting other dates compared with this and getting huge duration
            if ('0000-00-00 00:00:00' == $newActivity->start_time
                && '0000-00-00 00:00:00' == $newActivity->end_time
            ) {
                break;
            }
        }
    }

    public function isCcmActivity(PageTimer $activity): bool
    {
        return ! (0 == $activity->patient_id
            || 'patient.activity.create' == $activity->title
            || 'patient.activity.providerUIIndex' == $activity->title);
    }
}
