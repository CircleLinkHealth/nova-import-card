<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Listeners;

use App\Call;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Events\PatientContactWindowUpdatedEvent;
use Illuminate\Support\Collection;

class PatientContactWindowUpdated
{
    public function __construct()
    {
    }

    public function handle(PatientContactWindowUpdatedEvent $event)
    {
        // @var $auth \CircleLinkHealth\Customer\Entities\User
        $auth = auth()->user();

        if ( ! $auth) {
            return;
        }

        if ( ! $auth->isCareCoach()) {
            return;
        }

        $collection = collect($event->windows);
        $window     = $collection->first();

        $patientId = $window->patient_info->user_id;

        $calls = Call::where('inbound_cpm_id', '=', $patientId)
            ->where('status', '=', 'scheduled')
            ->whereIn('scheduler', ['core algorithm', 'rescheduler algorithm'])
            ->get();

        if ($calls->isEmpty()) {
            return;
        }

        $contactDays = $collection
            ->pluck('day_of_week')
            ->map(function ($elem) {
                return (int) $elem;
            });

        //NOTE:
        //not recommended way to update sql entries
        //each loop does a trip to the database,
        //which is a sign of bad practice (bad for performance)
        //however, for this use case, we know that only a small number of calls
        //will be updated (usually 1)
        $calls->each(
            function (Call $c) use ($window, $contactDays) {
                $hasChange = false;

                $scheduledDate = Carbon::parse($c->scheduled_date);
                if ( ! $contactDays->contains($scheduledDate->dayOfWeek)) {
                    $c->scheduled_date = $this->getNextAvailableContactDate($scheduledDate, $contactDays);
                    $hasChange = true;
                }

                if ($c->window_start != $window->window_time_start) {
                    $c->window_start = $window->window_time_start;
                    $hasChange = true;
                }

                if ($c->window_end != $window->window_time_end) {
                    $c->window_end = $window->window_time_end;
                    $hasChange = true;
                }

                if ($hasChange) {
                    $c->save();
                }
            }
        );
    }

    /**
     * @param Carbon     $currentDate
     * @param Collection $availableDays
     *
     * @return Carbon
     */
    private function getNextAvailableContactDate($currentDate, $availableDays)
    {
        $currentDay = $currentDate->dayOfWeek;
        if ($availableDays->isEmpty()) {
            return $currentDate;
        }

        //try going to the next available day
        $candidateDay = -1;
        foreach ($availableDays as $day) {
            if ($day > $currentDay) {
                $candidateDay = $day;
                break;
            }
        }

        if (-1 == $candidateDay) {
            // the next available day may be next week
            // current day 5, candidate 2. next week: 7 - (5 - 2)
            $candidateDay = $availableDays->first();
            $diff         = 7 - ($currentDay - $candidateDay);
        } else {
            $diff = $candidateDay - $currentDay;
        }

        $temp = $currentDate->copy()->addDays($diff);
        if ($temp->month != $currentDate->month) {
            return $currentDate;
        }

        return $temp;
    }
}
