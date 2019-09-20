<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\FullCalendar;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\WorkHours;
use Illuminate\Database\Eloquent\Collection;

class FullCalendarService
{
    /**
     * @param Collection $nurses
     *
     * @return Collection|\Illuminate\Support\Collection
     */
    public function prepareDateForCalendar(Collection $nurses)
    {
        return $nurses->map(function ($nurse) {
            return collect($nurse->nurseInfo->windows)->map(function ($window) use ($nurse) {
                $weekMap = [
                    1 => Carbon::parse($window->date)->startOfWeek()->toDateString(),
                    2 => Carbon::parse($window->date)->startOfWeek()->addDay(1)->toDateString(),
                    3 => Carbon::parse($window->date)->startOfWeek()->addDay(2)->toDateString(),
                    4 => Carbon::parse($window->date)->startOfWeek()->addDay(3)->toDateString(),
                    5 => Carbon::parse($window->date)->startOfWeek()->addDay(4)->toDateString(),
                    6 => Carbon::parse($window->date)->startOfWeek()->addDay(5)->toDateString(),
                    7 => Carbon::parse($window->date)->startOfWeek()->addDay(6)->toDateString(),
                ];

                $dayInHumanLang = Carbon::parse($weekMap[$window->day_of_week])->format('l');

                $workHoursForDay = WorkHours::where('workhourable_id', $nurse->nurseInfo->id)->pluck($dayInHumanLang)->first();

                return collect([
                    'title' => "$nurse->display_name: $workHoursForDay Hrs",
                    'start' => "{$weekMap[$window->day_of_week]}T{$window->window_time_start}",
                    'end'   => "{$weekMap[$window->day_of_week]}T{$window->window_time_end}",
                ]);
            });
        })->flatten(1);
    }
}
