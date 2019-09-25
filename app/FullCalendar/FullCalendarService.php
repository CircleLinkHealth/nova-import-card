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
    const ALL_DAY = 'allDay';
    const END     = 'end';
    const LABEL   = 'label';
    const START   = 'start';
    const TITLE   = 'title';

    /**
     * @param Collection $nurses
     *
     * @return Collection|\Illuminate\Support\Collection
     *
     * note for antoni: Im sending dropdown data in a different collection cause i need all active nurses (no just the working RNs)
     */
    public function getDataForDropdown(Collection $nurses)
    {
        return $nurses->map(function ($nurse) {
            return [
                'nurseId' => $nurse->nurseInfo->id,
                'label'   => $nurse->display_name,
            ];
        });
    }

    /**
     * @param Collection $nurses
     *
     * @return Collection|\Illuminate\Support\Collection
     */
    public function getUpcomingHolidays(Collection $nurses)
    {
        return $nurses->map(function ($nurse) {
            // @todo:make filter dropdown 1. future, 2. annual
            $holidays = $nurse->nurseInfo->holidays->where('date', '>=', Carbon::parse(now())->startOfWeek()->toDate());

            return collect($holidays)->map(function ($holiday) use ($nurse) {
                return collect(
                    [
                        self::TITLE   => "$nurse->display_name",
                        self::START   => Carbon::parse($holiday->date)->toDateString(),
                        self::ALL_DAY => true,
                    ]
                );
            });
        })->flatten(1);
    }

    /**
     * @param $nurse
     *
     * @return \Illuminate\Support\Collection
     */
    public function prepareData($nurse)
    {
        return collect($nurse->nurseInfo->windows)->map(function ($window) use ($nurse) {
            $weekMap = dayOfWeekToDate($window->date);
            $dayInHumanLang = clhDayOfWeekToDayName($window->day_of_week);
            $workHoursForDay = WorkHours::where('workhourable_id', $nurse->nurseInfo->id)->pluck($dayInHumanLang)->first();

            // i will NOT collect holidays here yet. Will load with axios post when requested
            return collect(
                [
                    self::TITLE => "$nurse->display_name - $workHoursForDay Hrs",
                    self::START => "{$weekMap[$window->day_of_week]}T{$window->window_time_start}",
                    self::END   => "{$weekMap[$window->day_of_week]}T{$window->window_time_end}",

                    //                        self::START => $weekMap[$window->day_of_week],
                    //                        self::END   => $window->window_time_end,

                    'allDay' => true,
                    'dow'    => [$window->day_of_week], //@todo:need to fix plugin(rrule)for this to work

                    'data' => [
                        'nurseId'   => $nurse->nurseInfo->id,
                        'windowId'  => $window->id,
                        'name'      => $nurse->display_name,
                        'day'       => $dayInHumanLang,
                        'date'      => $weekMap[$window->day_of_week],
                        'start'     => $window->window_time_start,
                        'end'       => $window->window_time_end,
                        'workHours' => $workHoursForDay,
                    ],
                ]
            );
        });
    }

    /**
     * @param Collection $nurses
     *
     * @return Collection|\Illuminate\Support\Collection
     */
    public function prepareDatesForCalendar(Collection $nurses)
    {
        return $nurses->map(function ($nurse) {
            return $this->prepareData($nurse);
        })->flatten(1);
    }
}
