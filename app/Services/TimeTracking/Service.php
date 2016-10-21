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

            if ($greedyStart->gt($secondaryStart) && $greedyEnd->gt($secondaryEnd)) {
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

            } elseif ($greedyStart->lt($secondaryStart) && $greedyEnd->gt($secondaryEnd)) {
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

            } elseif ($greedyStart->gt($secondaryStart) && $greedyEnd->lt($secondaryEnd)) {
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

            } elseif ($greedyStart->lt($secondaryStart) && $greedyEnd->lt($secondaryEnd)) {
                if ($greedyStart->lt($minDate)) {
                    $minDate = $greedyStart->copy();
                }

                if ($secondaryEnd->gt($maxDate)) {
                    $maxDate = $secondaryEnd->copy();
                    $secondary->start_time = $greedyEnd->copy();
                    $secondaryStart = $greedyEnd->copy();
                } else {
                    $secondary->start_time = $maxDate->copy();
                    $secondaryStart = $maxDate->copy();
                }

                if ($secondaryEnd->gte($maxDate)) {
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
        $pageTimers = PageTimer::where('created_at', '>=', Carbon::now()->subMinutes(3))
            ->get()
            ->sum('billable_duration');
        if ($pageTimers > 155) {
            var_dump('greedy ' . $greedy->id . ' secondary ' . $secondary->id . ' sum ' . $pageTimers);
        }
    }

    public function isCcmActivity(PageTimer $activity) : bool
    {
        return !($activity->patient_id == 0
            || $activity->title == 'patient.activity.create'
            || $activity->title == 'patient.activity.providerUIIndex');
    }
}