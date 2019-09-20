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
     * @param $nurse
     *
     * @return \Illuminate\Support\Collection
     */
    public function prepareData($nurse)
    {
        return collect($nurse->nurseInfo->windows)->map(function ($window) use ($nurse) {
            $weekMap = dayOfWeekToDate($window);
            $dayInHumanLang = Carbon::parse($weekMap[$window->day_of_week])->format('l');
            $workHoursForDay = WorkHours::where('workhourable_id', $nurse->nurseInfo->id)->pluck($dayInHumanLang)->first();

            return collect([
                'title' => "$nurse->display_name: $workHoursForDay Hrs",
                'start' => "{$weekMap[$window->day_of_week]}T{$window->window_time_start}",
                'end'   => "{$weekMap[$window->day_of_week]}T{$window->window_time_end}",
            ]);
        });
    }

    /**
     * @param Collection $nurses
     *
     * @return Collection|\Illuminate\Support\Collection
     */
    public function prepareDateForCalendar(Collection $nurses)
    {
        return $nurses->map(function ($nurse) {
            return $this->prepareData($nurse);
        })->flatten(1);
    }
}
